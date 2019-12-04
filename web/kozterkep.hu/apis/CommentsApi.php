<?php
class CommentsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();

    // Csak ezekkel a modellekel lehet menteni kommentet
    $this->commentable_models = ['artpiece', 'forum_topic', 'place', 'artist', 'folder', 'post', 'book'];

    $this->editorial_forum_topics = $this->DB->find('forum_topics', [
      'conditions' => ['editorial' => 1],
      'type' => 'fieldlist',
      'fields' => ['id']
    ], ['name' => 'editorial_forum_topics']);
  }


  /**
   * Komment lista megadott modell / ID párosra,
   * esetleg extra szűrésekkel
   */
  public function get() {
    if (!in_array($this->data['model_name'], $this->commentable_models)) {
      $this->send(['errors' => ['Betöltési hiba.']]);
    }

    $filter = ['$and' => []];

    $filter['$and'][] = [$this->data['model_name'] . '_id' => (int)$this->data['model_id']];
    $sort = ['created' => -1];

    if (@$this->data['custom_field'] != '' && @$this->data['custom_value'] != '') {
      $value = is_integer($this->data['custom_value'])
        ? (int)$this->data['custom_value'] : $this->data['custom_value'];

      // Headitor fórumból csak headitorok kérhetnek le kommenteket
      if ($this->data['custom_field'] == 'forum_topic_id' && $value == 6
        && static::$user['headitor'] == 0) {
        $this->send([]);
      }

      if ($value == 0) {
        // Ha pl. forum_topic_id jön 0-val, akkor be kell tenni OR-ba azt is,
        // hogy nincs ilyen field...
        $filter['$and'][] = ['$or' => [
          [$this->data['custom_field'] => $value],
          [$this->data['custom_field'] => ['$exists' => false]],
        ]];
      } else {
        $filter['$and'][] = [$this->data['custom_field'] => $value];
      }
    }

    // Editorial forum topikok leszűrése a nem admin/nem headitor userek számára
    // és a műlapok szerkesztéseiben se jelenjen meg headitor fórum
    if (static::$user['headitor'] == 0 && static::$user['admin'] == 0
      || (@$this->data['model_name'] == 'artpiece' && @$this->data['custom_field'] == 'artpiece_edits_id')) {
      $filter['$and'][] = ['forum_topic_id' => ['$nin' => $this->editorial_forum_topics]];
    }


    $results = $this->Mongo->find_array('comments',
      $filter,
      [
        'sort' => $sort,
        'limit' => (int)$this->data['limit'] // @todo: pagináció, vagy limit beküldés
      ]
    );

    $results = array_reverse($results);

    $this->send($results);
  }



  /**
   * Legfrissebb kommentek, egyedi szűrésekkel, de mindből
   */
  public function latests() {

    $filter = ['$and' => []];

    // Editorial forum topikok leszűrése a nem admin/nem headitor userek számára
    if (static::$user['headitor'] == 0 && static::$user['admin'] == 0) {
      $filter['$and'][] = ['forum_topic_id' => ['$nin' => $this->editorial_forum_topics]];
    }

    // Ha jött model
    if (isset($this->data['model_name']) && is_numeric($this->data['model_id'])) {
      if (!in_array($this->data['model_name'], $this->commentable_models)) {
        $this->send(['errors' => ['Betöltési hiba.']]);
      }
      $filter['$and'][] = [$this->data['model_name'] . '_id' => (int)$this->data['model_id']];
    } else {
      // Nem jött modell, a falon vagyunk, rejtjük, ami nem oda való
      $filter['$and'][] = ['no_wall' => ['$exists' => false]];
    }

    $filter['$and'][] = ['hidden' => ['$ne' => 1]];

    // Komment-típusok szűrése, falon
    if (@$this->data['custom_field'] == 'spacewall') {
      if (@$this->data['custom_value'] == 'editcomments') {
        $filter['$and'][] = ['$or' => [
          ['artpiece_edit' => 1],
          ['artpiece_edits_id' => ['$exists' => true]],
          ['artist_id' => ['$gt' => 0]],
          ['place_id' => ['$gt' => 0]],
        ]];
      } elseif (@$this->data['custom_value']== 'comments') {
        $filter['$and'][] = ['artpiece_edit' => ['$ne' => 1]];
        $filter['$and'][] = ['artpiece_edits_id' => ['$exists' => false]];
        $filter['$and'][] = ['artist_id' => ['$exists' => false]];
        $filter['$and'][] = ['place_id' => ['$exists' => false]];
      }
    }

    $results = $this->Mongo->find_array('comments', $filter, [
      'sort' => ['created' => -1],
      'limit' => @$this->data['limit'] > 0 ? $this->data['limit'] : 10,
    ]);

    $results = array_reverse($results);

    $this->send($results);
  }



  /**
   * Komment rögzítése
   */
  public function post() {


    //debug($this->data); exit;

    if (!in_array($this->data['model_name'], $this->commentable_models)) {
      $this->send(['errors' => ['Mentési hiba.']]);
    } elseif (!isset($this->data['_files']) && @$this->data['comment'] == '') {
      $this->send(['errors' => ['Kérjük, írj szöveget vagy legalább csatolj egy fájlt.']]);
    }

    // @todo: closed vagy headitorial forumba ne lehessen posztolni

    $data = [
      'user_id' => static::$user['id'],
      'user_name' => $this->MC->t('users', static::$user['id'])['name'],
      'text' => strip_tags($this->data['comment']),
      'created' => time(),
      'modified' => time(),
      $this->data['model_name'] . '_id' => (int)$this->data['model_id']
    ];

    if ($this->data['model_name'] == 'artpiece') {
      $artpiece = $this->MC->t('artpieces', (int)$this->data['model_id']);
      if ($artpiece['status_id'] == 1) {
        $data['hidden'] = 1;
      }
      if ($artpiece['status_id'] != 5) {
        $data['artpiece_edit'] = 1;
      }
    }

    if (@$this->data['answered_id'] != '') {
      $data['answered_id'] = $this->data['answered_id'];
    }

    if (@$this->data['custom_field'] != '' && @$this->data['custom_value'] != '') {
      $value = is_integer($this->data['custom_value'])
        ? (int)$this->data['custom_value'] : $this->data['custom_value'];
      $data[$this->data['custom_field']] = $value;
    }

    $insert_id = $this->Mongo->insert('comments', $data);

    $updates = [];

    if (isset($this->data['_files'])) {
      $file_array = $this->File->upload_posted(
        'files',
        $this->data,
        [
          'user_id' => static::$user['id'],
          'comment_id' => $insert_id,
        ],
        ['onesize']
      );

      $updates['files'] = $file_array;
    }


    if (@$this->data['answered_id'] != '') {
      // Válaszolt kiolvasása, hogy az őst megállapítsuk
      $answered = $this->Mongo->first('comments', ['_id' => $this->data['answered_id']]);
      $updates['parent_answered_id'] = @$answered['parent_answered_id'] != '' ? $answered['parent_answered_id'] : $this->data['answered_id'];
      if (@$answered['artpiece_edit'] == 1) {
        $updates['artpiece_edit'] = 1;
      }
    }

    if (count($updates) > 0) {
      $this->Mongo->update('comments', $updates, ['_id' => $insert_id]);
    }

    $this->Comments->notify_them($data + ['id' => $insert_id], static::$user['id']);

    $this->Cache->delete('latest_comments_head');
    $this->Cache->delete('latest_comments_public');

    if (!$insert_id) {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    } else {
      $this->send(['success' => $insert_id]);
    }
  }



  // Komment módosítása
  public function put() {
    $comment = $this->Mongo->first('comments', [
      '_id' => $this->data['id'],
    ]);

    if ($comment && static::$user['id'] == $comment['user_id']) {
      $updated = $this->Mongo->update('comments', [
        'text' => $this->data['text'],
        'modified' => time(),
      ], ['_id' => $this->data['id']]);

      if ($updated) {
        $this->send(['success']);
      }
    }

    $this->send(['errors' => [texts('mentes_hiba')]]);
  }



  public function highlight_toggle() {
    $comment = $this->Mongo->first('comments', [
      '_id' => $this->data['id'],
    ]);

    if ($comment && @$comment['artpiece_id'] > 0) {

      $artpiece = $this->DB->find_by_id('artpieces', $comment['artpiece_id'], [
        'fields' => ['id', 'user_id', 'title'],
      ]);

      if ($artpiece) {

        if (@$comment['highlighted'] > 0) {
          $this->Mongo->update('comments', [
            'highlighted' => 0
          ], ['_id' => $comment['id']]);
          $highlight = false;
        } else {
          $this->Mongo->update('comments', [
            'highlighted' => time()
          ], ['_id' => $comment['id']]);
          $highlight = true;
        }

        $this->Artpieces->generate($artpiece['id']);

        if ($highlight) {
          // Komment létrehozójának
          if ($comment['user_id'] != static::$user['id']) {
            $this->Notifications->create($artpiece['user_id'], 'Hozzászólásod kiemelése', '"' . $artpiece['title'] . '" c. műlapon kiemeltük hozzászólásodat, mert értékes aktualitást tartalmaz.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);
          }

          // Műlap tulajnak, ha nem ő csinálta a módosítást
          if ($artpiece['user_id'] != static::$user['id']) {
            $this->Notifications->create($artpiece['user_id'], 'Hozzászólás kiemelése műlapodon', '"' . $artpiece['title'] . '" c. műlapodon egy hozzászólást kiemeltünk, mert értékes aktualitást tartalmaz.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);
          }
        }

        $this->send(['success' => true]);
      }
    }
  }

  public function story_convert() {
    $comment = $this->Mongo->first('comments', [
      '_id' => $this->data['id'],
    ]);

    if ($comment && @$comment['artpiece_id'] > 0) {

      $artpiece = $this->DB->find_by_id('artpieces', $comment['artpiece_id'], [
        'fields' => ['id', 'user_id', 'title'],
      ]);

      if ($artpiece) {
        $inserted_id = $this->Mongo->insert('artpiece_edits', [
          'artpiece_id' => (int)$comment['artpiece_id'],
          'status_id' => 2, // mindenképp jóváhagyandó
          'user_id' => (int)$comment['user_id'],
          'receiver_user_id' => (int)$artpiece['user_id'],
          'created' => (int)$comment['created'],
          'modified' => (int)$comment['modified'],
          'prev_data' => [],
          'descriptions' => [
            [
              'id' => 'new_hun',
              'text' => $comment['text'],
              'source' => '',
              'comment_time' => $comment['created'],
            ]
          ],
        ]);

        if ($inserted_id) {
          // Töröljük a kommentet
          $this->Mongo->delete('comments', ['_id' => $comment['id']]);
          // Műlap cache-t ürítünk
          $this->Artpieces->generate($artpiece['id']);

          // Műlap tulajnak, ha nem ő csinálta a módosítást
          if ($artpiece['user_id'] != static::$user['id']) {
            $this->Notifications->create($artpiece['user_id'], 'Hozzászólásból sztori lett', '"' . $artpiece['title'] . '" c. műlapodon egy hozzászólást sztorivá változtattunk.', [
              'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $inserted_id,
              'type' => 'artpieces',
            ]);
          }

          if ($comment['user_id'] != static::$user['id']) {
            $this->Notifications->create($comment['user_id'], 'Hozzászólásodból sztori lett', '"' . $artpiece['title'] . '" c. műlapon tett hozzászólásod sztorivá változott.', [
              'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $inserted_id,
              'type' => 'edits',
            ]);
          }
        }

        $this->send(['success' => $inserted_id]);
      }
    }
  }



  public function artist_description_convert() {
    $comment = $this->Mongo->first('comments', [
      '_id' => $this->data['id'],
    ]);

    if ($comment && @$comment['artist_id'] > 0) {

      $artist = $this->DB->find_by_id('artists', $comment['artist_id'], [
        'fields' => ['id', 'user_id'],
      ]);

      if ($artist) {
        $data = [
          'text' => $comment['text'],
          'artist_id' => (int)$comment['artist_id'],
          'user_id' => (int)$comment['user_id'],
          'user_name' => $comment['user_name'],
          'related_users' => [(int)$comment['user_id'], (int)CORE['USERS']['artists']],
          'created' => (int)$comment['created'],
          'modified' => (int)$comment['modified'],
          'approved' => (int)$comment['modified'],
        ];

        if (isset($comment['files'])) {
          $data['files'] = $comment['files'];
        }

        $inserted_id = $this->Mongo->insert('artist_descriptions', $data);

        if ($inserted_id) {
          // Töröljük a kommentet
          $this->Mongo->delete('comments', ['_id' => $comment['id']]);
          // Cache-t ürítünk
          $this->Cache->delete('cached-view-artists-view-' . $artist['id']);
        }

        $this->send(['success' => $inserted_id]);
      }
    }
  }



  // Adalékből vissza kommentté
  public function artist_description_convert_back() {
    $description = $this->Mongo->first('artist_descriptions', [
      '_id' => $this->data['id'],
    ]);

    if ($description && @$description['artist_id'] > 0) {

      $artist = $this->DB->find_by_id('artists', $description['artist_id'], [
        'fields' => ['id', 'user_id'],
      ]);

      if ($artist) {
        $data = [
          'text' => $description['text'],
          'artist_id' => (int)$description['artist_id'],
          'user_id' => (int)$description['user_id'],
          'user_name' => $description['user_name'],
          'related_users' => [(int)$description['user_id'], (int)CORE['USERS']['artists']],
          'created' => (int)$description['created'],
          'modified' => (int)$description['modified'],
          'approved' => (int)$description['modified'],
        ];

        if (isset($description['files'])) {
          $data['files'] = $description['files'];
        }

        $inserted_id = $this->Mongo->insert('comments', $data);

        if ($inserted_id) {
          // Töröljük a kommentet
          $this->Mongo->delete('artist_descriptions', ['_id' => $description['id']]);
          // Cache-t ürítünk
            $this->Cache->delete('cached-view-artists-view-' . $artist['id']);
        }

        $this->send(['success' => $inserted_id]);
      }
    }
  }



  public function delete() {
    $comment = $this->Mongo->first('comments', [
      '_id' => $this->data['id'],
    ]);
    if ($comment && $this->Users->is_head(static::$user)) {
      $deleted = $this->Mongo->delete('comments', ['_id' => $comment['id']]);
      if ($deleted) {
        $this->send(['success' => 1]);
      }
    }
  }

}