<?php

use Kozterkep\AppBase as AppBase;

class ConversationsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => 35
    ];

    $this->users_only();

    $this->set([
      '_shareable' => false,
    ]);
  }

  private function _get_list($filters, $pagination) {
    if (@$this->params->query['kulcsszo'] != '') {
      $filters['$and'] = [
        ['$or' => [
          ['subject' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
          ['user_names' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
          ['words' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
        ]]
      ];
    }

    $conversations = $this->Mongo->find_array(
      'conversations',
      $filters,
      [
        'sort' => ['updated' => -1],
        'limit' => $pagination['limit'],
        'skip' => ($pagination['page'] - 1) * $pagination['limit']
      ]
    );

    return $conversations;
  }

  public function index() {

    $filters = [
      'users' => $this->user['id'],
      'archived' => ['$nin' => [$this->user['id']]],
      'trashed' => ['$nin' => [$this->user['id']]],
      'deleted' => ['$nin' => [$this->user['id']]],
    ];

    if (isset($this->params->query['olvasatlanok'])) {
      $filters['read'] = ['$nin' => [$this->user['id']]];
    }

    // Régi üzenetek száma
    // @todo: kivenni innen majd, ha már senkinek nem kell
    if (date('Y') == 2019) {
      $old_unread_filters = [
        'users' => $this->user['id'],
        'archived' => ['$nin' => [$this->user['id']]],
        'trashed' => ['$nin' => [$this->user['id']]],
        'deleted' => ['$nin' => [$this->user['id']]],
        'updated' => ['$lt' => strtotime(APP['kt2_start'] . ' 23:57:00')],
        'read' => ['$nin' => [$this->user['id']]],
      ];

      $old_unreads = $this->Mongo->count('conversations', $old_unread_filters);
    } else {
      $old_unreads = 0;
    }

    // Régi üzenetek olvasása
    if ($old_unreads > 0 && isset($this->params->query['regiek-elolvasva'])) {
      $old_conversations = $this->Mongo->find_array('conversations', $old_unread_filters);
      if (count($old_conversations) > 0) {
        foreach ($old_conversations as $conversation) {
          if (!in_array($this->user['id'], $conversation['read'])) {
            $conversation['read'][] = $this->user['id'];
            $this->Mongo->update('conversations', [
              'read' => $conversation['read']
            ], ['_id' => $conversation['id']]);
          }
        }
      }
      $this->redirect('/beszelgetesek/aktiv', 'Minden április 22. előtti üzenetet olvasottá tettünk');
    }

    $this->set([
      'pagination' => $this->pagination,
      'conversations' => $this->_get_list($filters, $this->pagination),
      'old_unreads' => $old_unreads,
      '_sidemenu' => true,
      '_title' => 'Beszélgetések',
    ]);
  }

  public function favorites() {
    $filters = [
      'users' => $this->user['id'],
      'favored' => ['$in' => [$this->user['id']]],
      'deleted' => ['$nin' => [$this->user['id']]],
    ];

    $this->set([
      'pagination' => $this->pagination,
      'conversations' => $this->_get_list($filters, $this->pagination),
      '_sidemenu' => true,
      '_title' => 'Kedvencnek jelöltek',
    ]);
  }

  public function archive() {
    $filters = [
      'users' => $this->user['id'],
      'archived' => ['$in' => [$this->user['id']]],
      'trashed' => ['$nin' => [$this->user['id']]],
      'deleted' => ['$nin' => [$this->user['id']]],
    ];

    $this->set([
      'pagination' => $this->pagination,
      'conversations' => $this->_get_list($filters, $this->pagination),
      '_sidemenu' => true,
      '_title' => 'Archivált beszélgetések',
    ]);
  }

  public function trash() {
    $filters = [
      'users' => $this->user['id'],
      'trashed' => ['$in' => [$this->user['id']]],
      'deleted' => ['$nin' => [$this->user['id']]],
    ];

    $this->set([
      'pagination' => $this->pagination,
      'conversations' => $this->_get_list($filters, $this->pagination),
      '_sidemenu' => true,
      '_title' => 'Törölt beszélgetések',
    ]);
  }



  public function thread($id) {
    $conversation = $this->Mongo->first(
      'conversations',
      [
        '_id' => $id,
        'users' => $this->user['id'],
        'deleted' => ['$nin' => [$this->user['id']]],
      ]
    );

    if (!$conversation) {
      $this->redirect('/beszelgetesek/aktiv', [texts('hibas_url'), 'warning']);
    }

    if (!in_array($this->user['id'], $conversation['read'])) {
      $read = array_merge($conversation['read'], [$this->user['id']]);
      $messages_updated = [];
      foreach ($conversation['messages'] as $message) {
        $messages_updated[] = $message;
      }
      $this->Mongo->update(
        'conversations',
        [
          'read' => $read,
          'messages' => $messages_updated
        ],
        ['_id' => $id]
      );
    }

    $artpiece = $photo = $artist = $file = $folder = false;
    if (@$conversation['artpiece_id'] > 0) {
      $artpiece = $this->MC->t('artpieces', $conversation['artpiece_id']);
    }
    if (@$conversation['photo_id'] > 0) {
      $photo = $this->DB->first('photos', $conversation['photo_id']);
    }
    if (@$conversation['artist_id'] > 0) {
      $artist = $this->MC->t('artists', $conversation['artist_id']);
    }
    if (@$conversation['file_id'] > 0) {
      $file = $this->DB->first('files', $conversation['file_id']);
      $folder = $this->MC->t('folders', $file['folder_id']);
    }

    $this->set([
      '_sidemenu' => true,
      '_title' => $conversation['subject'],

      'conversation' => $conversation,
      'artpiece' => $artpiece,
      'photo' => $photo,
      'artist' => $artist,
      'file' => $file,
      'folder' => $folder,
    ]);
  }

  public function start() {
    $artpiece = $photo = $file = $folder = $artist = $same = $subject = false;
    $same_conditions = [];

    // Műlap miatt írunk
    if (@$this->params->query['mulap_az'] > 0) {
      $artpiece = $this->MC->t('artpieces', $this->params->query['mulap_az']);
      $same_conditions['artpiece_id'] = $artpiece['id'];
      $subject = $artpiece['title'] . ' műlap kapcsán';
    }

    // Fotó miatt írunk
    if (@$this->params->query['foto_az'] > 0) {
      $photo = $this->DB->first('photos', $this->params->query['foto_az']);

      if ($photo) {
        if ($photo['artpiece_id'] > 0) {
          $artpiece = $this->MC->t('artpieces', $photo['artpiece_id']);
          $same_conditions['artpiece_id'] = $photo['artpiece_id'];
        } elseif ($photo['portrait_artist_id'] > 0) {
          $artist = $this->MC->t('artists', $photo['portrait_artist_id']);
          $same_conditions['artist_id'] = $photo['portrait_artist_id'];
        }

        if ($artpiece || $artist) {
          $same_conditions['photo_id'] = $photo['id'];
          $subject = 'Fotó kapcsán';
        }
      }
    }

    // Fájl miatt írunk
    if (@$this->params->query['fajl_az'] > 0) {
      $file = $this->DB->first('files', $this->params->query['fajl_az']);
      if ($file) {
        $same_conditions['file_id'] = $file['id'];
        $subject = 'Fájl kapcsán';
        $folder = $this->MC->t('folders', $file['folder_id']);
      }
    }

    // Ha van címzettünk is az URL-ben, megnézzük, van-e ilyen már
    if (@$this->params->query['tag'] > 0) {
      $filters = [
        'users' => [
          (int)$this->user['id'],
          (int)$this->params->query['tag']
        ],
        'artpiece_id' => (int)$artpiece['id'],
        'trashed' => [],
      ] + $same_conditions;

      $same = $this->Mongo->first('conversations', $filters);
    }

    $this->set([
      '_sidemenu' => true,
      '_title' => 'Beszélgetés indítása',

      'subject' => $subject,
      'artpiece' => $artpiece,
      'photo' => $photo,
      'artist' => $artist,
      'file' => $file,
      'folder' => $folder,
      'same' => $same,
    ]);
  }

}