<?php
class ConversationsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {
    $filters = ['users' => static::$user['id']];

    // Olvasatlanokat kérjük le
    if (isset($this->data['unread']) && in_array($this->data['unread'], [0, 1])) {
      $filters['read'] = ['$nin' => [static::$user['id']]];
      // Törölteke, valamint kukából és archívumból ne hozzuk be
      $filters['archived'] = ['$nin' => [static::$user['id']]];
      $filters['trashed'] = ['$nin' => [static::$user['id']]];
      $filters['deleted'] = ['$nin' => [static::$user['id']]];
    }

    if (@$this->data['id'] != '') {
      $filters['_id'] = $this->data['id'];
    }

    $result = $this->Mongo->find_array('conversations', $filters, [
      'sort' => ['updated' => 1],
      'limit' => 100
    ]);

    /**
     * Ha egy üzenetet szedtünk le ID alapján,
     * akkor csekkoljuk, hogy a törölt üzeneteket ne adjuk,
     * és olvasottá tesszük a user által, ha még nem volna
     */
    if (@$this->data['id'] != '' && count($result) == 1) {
      // Olvasás; ezt először, mert itt update van!!!
      if (!in_array(static::$user['id'], $result[0]['read'])) {
        $result[0]['read'][] = static::$user['id'];
        $this->Mongo->update('conversations', $result[0], $filters);
      }

      // Törölt üzenetek kiszedése a threadből
      $messages = [];
      foreach ($result[0]['messages'] as $key => $message) {
        if (!isset($message['deleted'][static::$user['id']])) {
          $message_data = $result[0]['messages'][$key];
          if (@count(@$result[0]['files'][$message['mid']]) > 0) {
            // Ha van fájl, betoljuk ide
            $message_data = array_merge($message_data, ['files' => $result[0]['files'][$message['mid']]]);
          }
          $messages[] = $message_data;
        }
      }
      $result[0]['messages'] = $messages;
    }

    $this->send($result);
  }


  /**
   * Új beszélgetés indítása
   */
  public function post() {
    // Létezik-e a címzett user
    $exists = $this->DB->find_by_id('users', (int)$this->data['user_id']);
    if (!$exists) {
      $this->send(['errors' => ['Nem létező címzett. Kérjük ellenőrizd a bevitt adatokat.']]);
    }

    $message_id = uniqid();

    $data = [
      'users' => [static::$user['id'], (int)$this->data['user_id']],
      'user_names' => [
        /* Ez azért kell, mert a címzett neve addig
         * nem jelenik meg a threadben, amíg nem válaszol
         * tehát addig kereshetetlen lesz
         */
        $this->MC->t('users', static::$user['id'])['name'],
        $this->MC->t('users', $this->data['user_id'])['name'],
      ],
      'started' => time(),
      'updated' => time(),
      'subject' => $this->data['subject'] == '' ? '...' : strip_tags($this->data['subject']),
      'read' => [static::$user['id']], // a folyam olvasottsága, csak az író
      'words' => $this->Arrays->words_array($this->data['message']), // ebben is keresünk majd
      'favored' => [],
      'archived' => [],
      'trashed' => [],
      'deleted' => [],
      'messages' => [[
        'mid' => $message_id,
        'user_name' => static::$user['name'], // hogy ne kelljen joinolni...
        'user_id' => static::$user['id'],
        'created' => time(),
        'body' => strip_tags($this->data['message']),
        'deleted' => []
      ]]
    ];

    if (@$this->data['artpiece_id'] > 0) {
      $data['artpiece_id'] = (int)$this->data['artpiece_id'];
    }
    if (@$this->data['photo_id'] > 0) {
      $data['photo_id'] = (int)$this->data['photo_id'];
    }
    if (@$this->data['artist_id'] > 0) {
      $data['artist_id'] = (int)$this->data['artist_id'];
    }
    if (@$this->data['file_id'] > 0) {
      $data['file_id'] = (int)$this->data['file_id'];
    }

    $insert_id = $this->Mongo->insert('conversations', $data);

    if (isset($this->data['_files'])) {
      $file_array = $this->File->upload_posted(
        'files',
        $this->data,
        [
          'permissions' => '"' . static::$user['id'] . '","' . (int)$this->data['user_id'] . '"',
          'user_id' => static::$user['id'],
          'conversation_id' => $insert_id,
          'conversation_message_id' => $message_id
        ]
      );

      $this->Mongo->update('conversations', ['files' => [$message_id => $file_array]], ['_id' => $insert_id]);
    }

    // Auto-reply, aki nemcímzett
    $this->Users->auto_reply((int)$this->data['user_id'], static::$user['id']);

    if (!$insert_id) {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    } else {
      $this->send(['success' => $insert_id]);
    }
  }


  /**
   *
   * Új üzenet hozzáadása
   *
   * @param $id
   */
  public function append($id) {
    $filters = [
      'users' => (int)static::$user['id'],
      '_id' => $id
    ];

    $conversation = $this->Mongo->first(
      'conversations',
      $filters
    );

    if (!$conversation) {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    }

    // Új üzenet
    if (isset($this->data['message'])) {

      if ($this->data['message'] == '' && !isset($this->data['_files'])) {
        $this->send(['errors' => ['Kérjük, írj üzenet szöveget.']]);
      }

      $message_id = uniqid();

      $conversation['messages'][] = [
        'mid' => $message_id,
        'user_name' => static::$user['name'],
        'user_id' => static::$user['id'],
        'created' => time(),
        'body' => strip_tags($this->data['message'])
      ];

      $conversation['updated'] = time();
      $conversation['read'] = [static::$user['id']]; // a folyam olvasottsága, újracsak az író

      // Mindenkinél kiszedjük a kukából és az archívak közül is!
      // meg a törlést is levesszük, de attól még nem látja a törlés előtti üziket!
      // itt nézzük az auto-reply-t is a nemküldőnél
      $permission_users = '';
      foreach ($conversation['users'] as $user_id) {
        if (($key = array_search($user_id, $conversation['archived'])) !== false) {
          unset($conversation['archived'][$key]);
        }
        if (($key = array_search($user_id, $conversation['trashed'])) !== false) {
          unset($conversation['trashed'][$key]);
        }
        if (($key = array_search($user_id, $conversation['deleted'])) !== false) {
          unset($conversation['deleted'][$key]);
        }

        if ($user_id != static::$user['id']) {
          $this->Users->auto_reply($user_id, static::$user['id']);
        }
        $permission_users .= '"' . $user_id . '",';
      }

      $conversation['words'] = $this->Arrays->words_array($conversation['messages'], 'body'); // ebben is keresünk majd

      if (isset($this->data['_files'])) {
        $file_array = $this->File->upload_posted(
          'files',
          $this->data,
          [
            'permissions' => rtrim($permission_users, ','),
            'user_id' => static::$user['id'],
            'conversation_id' => $conversation['id'],
            'conversation_message_id' => $message_id
          ]
        );

        $conversation['files'][$message_id] = $file_array;
      }

      $this->Mongo->update('conversations', $conversation, $filters);

      $this->send(['success' => true]);
    }

    $this->send([]);
  }



  /**
   *
   * Beszélgetéssel kapcsolatos tecékenységek
   *
   * @param $id
   */
  public function alter($id) {
    $filters = [
      'users' => (int)static::$user['id'],
      '_id' => $id
    ];

    $conversation = $this->Mongo->first(
      'conversations',
      $filters
    );

    if (!$conversation) {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    }

    // Read toggle
    if (isset($this->data['read_toggle'])) {
      if (($key = array_search(static::$user['id'], $conversation['read'])) !== false) {
        unset($conversation['read'][$key]);
      } else {
        $conversation['read'][] = static::$user['id'];
      }
      $this->Mongo->update('conversations', [
        'read' => $conversation['read']
      ], $filters);
      $this->send(['success' => true]);
    }

    // Favor toggle
    if (isset($this->data['favor_toggle'])) {
      if (($key = array_search(static::$user['id'], $conversation['favored'])) !== false) {
        unset($conversation['favored'][$key]);
      } else {
        $conversation['favored'][] = static::$user['id'];
      }
      $this->Mongo->update('conversations', [
        'favored' => $conversation['favored']
      ], $filters);
      $this->send(['success' => true]);
    }

    // Archiválás
    if (@$this->data['archive'] == 1) {
      if (($key = array_search(static::$user['id'], $conversation['archived'])) === false) {
        $conversation['archived'][] = static::$user['id'];
      }
      $this->Mongo->update('conversations', [
        'archived' => $conversation['archived']
      ], $filters);
      $this->send(['success' => true]);
    }

    // Kukázás
    if (@$this->data['trash'] == 1) {
      if (($key = array_search(static::$user['id'], $conversation['trashed'])) === false) {
        $conversation['trashed'][] = static::$user['id'];
      }
      $this->Mongo->update('conversations', [
        'trashed' => $conversation['trashed']
      ], $filters);
      $this->send(['success' => true]);
    }

    // Vissza az aktívak közé (kukából vagy archívakból, mindegy)
    if (@$this->data['active'] == 1) {
      if (($key = array_search(static::$user['id'], $conversation['archived'])) !== false) {
        unset($conversation['archived'][$key]);
      }
      if (($key = array_search(static::$user['id'], $conversation['trashed'])) !== false) {
        unset($conversation['trashed'][$key]);
      }
      $this->Mongo->update('conversations', [
        'archived' => $conversation['archived'],
        'trashed' => $conversation['trashed']
      ], $filters);
      $this->send(['success' => true]);
    }

    // Végleges törlés
    if (@$this->data['delete'] == 1) {
      if ($this->Conversations->delete($conversation, static::$user['id'])) {
        $this->send(['success' => true]);
      }
    }

    $this->send([]);
  }


  /**
   * Végleges törlés minden kukában lévő levélre
   */
  public function empty_trash() {
    $result = $this->Mongo->find_array('conversations',
      [
        'users' => static::$user['id'],
        'trashed' => ['$in' => [static::$user['id']]],
        'deleted' => ['$nin' => [static::$user['id']]],
      ],
      [
        'sort' => ['updated' => 1],
        'limit' => 1000
      ]
    );

    foreach ($result as $conversation) {
      if (!$this->Conversations->delete($conversation, static::$user['id'])) {
        $error = true;
      }
    }
    if (!isset($error)) {
      $this->send(['success' => true]);
    }

    $this->send([]);
  }


}