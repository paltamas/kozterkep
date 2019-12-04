<?php
namespace Kozterkep;

class CommentsLogic {

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Email = new EmailHelper($app_config);
    $this->Notifications = new NotificationsLogic($app_config, $DB);
    $this->Text = new TextHelper();
  }


  /**
   *
   * Szoborgép kommentel
   *
   * @param $text
   * @param array $data
   * @return array|string
   */
  public function robot($text, $data = []) {
    $data = (array)$data + [
      'user_id' => 2,
      'user_name' => $this->MC->t('users', 2)['name'],
      'text' => strip_tags($text),
      'created' => time(),
      'modified' => time(),
    ];
    $insert_id = $this->Mongo->insert('comments', $data);
    $this->notify_them($data + ['id' => $insert_id]);
    return $insert_id;
  }



  /**
   *
   * Értesítőket generálunk a tulajoknak,
   * és akiket még érdekel a dolog.
   *
   * @param $comment
   * @param $my_id
   * @return bool
   */
  public function notify_them($comment, $my_id = 0) {
    $extra_fields = [];

    // Amihez szóltunk
    $thing = false;

    // Akiket érdekelhet
    $related_users = [];
    $notified_users = [];

    $related_users[] = $comment['user_id'];

    $commenter = $this->MC->t('users', $comment['user_id']);
    $comment_text = $this->Text->truncate($comment['text'], 200);


    if (@$comment['forum_topic_id'] > 0
      && sDB['forum_topics'][$comment['forum_topic_id']][1] == 1) {
      $headitorial_forum = true;
    } else {
      $headitorial_forum = false;
    }


    // Kiolvassuk a kapcsolódó dolog tulaját
    if (@$comment['folder_id'] > 0) {
      //$thing = $this->DB->first('folders', $comment['folder_id'], ['fields' => ['user_id', 'name']]);
      $thing = $this->MC->t('folders', $comment['folder_id']);
      if ($thing) {
        $body = '"' . $thing['name'] . '" c. mappádnál: ';
        $extra_fields['link'] = '/mappak/megtekintes/' . $comment['folder_id'];
        $related_users[] = $thing['user_id'];
      }
    }

    if (@$comment['artist_id'] > 0) {
      //$thing = $this->DB->first('artists', $comment['artist_id'], ['fields' => ['user_id', 'name']]);
      $thing = $this->MC->t('artists', $comment['artist_id']);
      if ($thing) {
        $body = '"' . $thing['name'] . '" alkotónál: ';
        $extra_fields['link'] = '/alkotok/megtekintes/' . $comment['artist_id'];
        $related_users[] = $thing['user_id'];
      }
    }

    if (@$comment['place_id'] > 0) {
      //$thing = $this->DB->first('places', $comment['place_id'], ['fields' => ['user_id', 'name']]);
      $thing = $this->MC->t('places', $comment['place_id']);
      if ($thing) {
        $body = '"' . $thing['name'] . '" településnél: ';
        $extra_fields['link'] = '/helyek/megtekintes/' . $comment['place_id'];
        $related_users[] = $thing['user_id'];
      }
    }

    if (@$comment['artpiece_id'] > 0) {
      //$thing = $this->DB->first('artpieces', $comment['artpiece_id'], ['fields' => ['user_id', 'title']]);
      $thing = $this->MC->t('artpieces', $comment['artpiece_id']);
      if ($thing) {
        $body = '"' . $thing['title'] . '" c. műlapodnál: ';
        $extra_fields['link'] = '/mulapok/szerkesztes/' . $comment['artpiece_id'] . '#szerk-szerkkomm';
        $related_users[] = $thing['user_id'];
      }
    }

    if (@$comment['post_id'] > 0) {
      //$thing = $this->DB->first('posts', $comment['post_id'], ['fields' => ['user_id', 'title']]);
      $thing = $this->MC->t('posts', $comment['post_id']);
      if ($thing) {
        $body = '"' . $thing['title'] . '" c. bejegyzésednél: ';
        $extra_fields['link'] = '/blogok/megtekintes/' . $comment['post_id'];
        $related_users[] = $thing['user_id'];
      }
    }

    // Akinek válaszoltunk, ha az nem a tulaj
    if (@$comment['answered_id'] != '') {

      // Ez a komment linkelés mindenre jó lenne egyébként
      if (!isset($extra_fields['link']) && @$comment['forum_topic_id'] > 0) {
        $extra_fields['link'] = '/kozter/komment/' . $comment['id'];
      }

      $answered = $this->Mongo->first('comments', [
        '_id' => $comment['answered_id']
      ]);

      if ($answered && $answered['user_id'] != $comment['user_id']
        && (!$headitorial_forum || ($headitorial_forum && in_array($answered['user_id'], CORE['USERS']['headitors'])))) {
        if ($my_id != $answered['user_id']) {
          $extra_fields['type'] = 'comments';
          $this->Notifications->create($answered['user_id'], $commenter['name'] . ' válaszolt', $comment_text, $extra_fields);
        }
        $related_users[] = $answered['user_id'];
        $notified_users[] = $answered['user_id'];
      }

      // Ez pedig az ős, ha nem ugyanaz annak is a tulaja, neki is szólunk
      if (@$comment['parent_answered_id'] != ''
        && $comment['parent_answered_id'] != $comment['answered_id']) {

        $parent_answered = $this->Mongo->first('comments', [
          '_id' => $comment['parent_answered_id']
        ]);

        if ($parent_answered
          && $parent_answered['user_id'] != $comment['user_id']
          && $parent_answered['user_id'] != $answered['user_id']
          && (!$headitorial_forum || ($headitorial_forum && in_array($parent_answered['user_id'], CORE['USERS']['headitors'])))) {
          if ($my_id != $parent_answered['user_id']) {
            $extra_fields['type'] = 'comments';

            $this->Notifications->create($parent_answered['user_id'], $commenter['name'] . ' válaszolt', $comment_text, $extra_fields);
          }
          $related_users[] = $parent_answered['user_id'];
          $notified_users[] = $parent_answered['user_id'];
        }
      }
    }

    // A dolog tulajdonosa, ha nem épp neki válaszoltunk :)
    if ($my_id != @$thing['user_id'] && !in_array(@$thing['user_id'], $notified_users)
      && @$answered['user_id'] != @$thing['user_id'] && @$thing['user_id'] > 0 && $my_id != @$thing['user_id']) {
      $extra_fields['type'] = @$comment['artpiece_id'] > 0 ? 'artpieces' : 'things';
      $this->Notifications->create($thing['user_id'], $commenter['name'] . ' hozzászólást írt', $body . $comment_text, $extra_fields);
      $notified_users[] = $thing['user_id'];
    } 


    // Egyéb értesítendők

    // Szerkesztésre kommentelt valaki
    if (@$comment['artpiece_edits_id'] != '') {
      $item = $this->Mongo->first('artpiece_edits', ['_id' => $comment['artpiece_edits_id']]);
      if ($item && $item['user_id'] != $comment['user_id']) {
        if ($my_id != $item['user_id'] && !in_array($item['user_id'], $notified_users)) {
          $this->Notifications->create($item['user_id'], $commenter['name'] . ' hozzászólt egy szerkesztésedhez', '"' . $thing['title'] . '" c. műlapon: ' . $comment_text, [
            'link' => '/mulapok/szerkesztes_reszletek/' . $comment['artpiece_id'] . '/' . $item['id'],
            'type' => 'edits',
          ]);
        }
        $related_users[] = $item['user_id'];
      }
    }



    // Mentjük a kapcsolódó usereket
    if (count($related_users) > 0) {
      $this->Mongo->update('comments', [
        'related_users' => array_unique($related_users)
      ], ['_id' => $comment['id']]);
    }


    // Fórum témában új komment született. Ez tök más,
    // itt szólunk a feliratkozottaknak, ha nem kaptak már eleve
    // értesítést (válasz miatt)
    if (@$comment['forum_topic_id'] > 0) {
      $thing = $this->MC->t('forum_topics', $comment['forum_topic_id']);
      $subscribed_users = $this->DB->find('users', [
        'conditions' => [
          'tiny_settings LIKE' => '%fn_' . $comment['forum_topic_id'] . '": 1%'
        ]
      ], ['fields' => ['id']]);
      if (count($subscribed_users) > 0) {
        foreach ($subscribed_users as $subscribed_user) {
          if (in_array($subscribed_user['id'], $notified_users)
            || $subscribed_user['id'] == $comment['user_id']) {
            continue;
          }

          $this->Notifications->create($subscribed_user['id'], 'Új "' . $thing['title'] . '" fórum hozzászólás', $commenter['name'] . ' hozzászólása: ' . $comment_text, [
            'link' => '/kozter/forum-tema/' . $comment['forum_topic_id'],
            'type' => 'others',
          ]);
        }
      }
    }

  }


  /**
   *
   * URL-ben érkező query alapján megépítis a comments filtert
   *
   * @param array $query
   * @return array
   */
  public function build_filters($query = [], $user_id = 0) {
    $search_filters = [];
    if (@$query['kifejezes'] != '') {
      $search_filters[] = ['text' => ['$regex' => $query['kifejezes'], '$options' => 'i']];
    }
    if (@$query['tag'] > 0) {
      $search_filters[] = ['user_id' => (int)$query['tag']];
    } elseif (@$query['tag'] == 'ennekem' && $user_id > 0) {
      $search_filters[] = ['related_users' => (int)$user_id];
    } elseif (@$query['tag'] == 'nekem' && $user_id > 0) {
      $search_filters[] = [
        'user_id' => ['$ne' => (int)$user_id],
        'related_users' => (int)$user_id,
      ];
    }
    if (@$query['tipus'] != '') {
      $qtype = $query['tipus'];
      if (strpos($qtype, 'forum') !== false) {
        $p = explode('-', $qtype);
        if (is_numeric(@$p[1])) {
          $search_filters[] = ['forum_topic_id' => (int)$p[1]];
        }
      }
      foreach ([
                 'mulap' => 'artpiece_id',
                 'alkoto' => 'artist_id',
                 'hely' => 'place_id',
                 'blog' => 'post_id',
                 'mappa' => 'folder_id',
                 'konyv' => 'book_id',
               ] as $type => $field) {
        if ($qtype == $type) {
          $search_filters[] = [$field => ['$gt' => 0]];
        }
      }
    }

    return $search_filters;
  }


  /**
   *
   * Visszaadja a komment URL-ét
   * (ahol meg lehet nézni)
   *
   * @param $comment
   * @return bool|string
   */
  public function thread_url($comment) {
    $url = false;
    if ((@$comment['forum_topic_id'] > 0) != true) {
      // Nem fórumos
      if (@$comment['artpiece_id'] > 0) {
        $url = '/' . $comment['artpiece_id'] . '?komment=' . $comment['id'] . '#szerkkomm';
      } elseif (@$comment['artist_id'] > 0) {
        $url = '/alkotok/megtekintes/' . $comment['artist_id'] . '?komment=' . $comment['id'];
      } elseif (@$comment['place_id'] > 0) {
        $url = '/helyek/megtekintes/' . $comment['place_id'] . '?komment=' . $comment['id'];
      } elseif (@$comment['folder_id'] > 0) {
        $url = '/mappak/megtekintes/' . $comment['folder_id'] . '?komment=' . $comment['id'];
      } elseif (@$comment['post_id'] > 0) {
        $url = '/blogok/megtekintes/' . $comment['post_id'] . '?komment=' . $comment['id'];
      }
    } else {
      // Fórumos
      // na, de hányadik oldal?
      // hány van utána, ami frissebb ebben a fórumban?
      $newer = $this->Mongo->count('comments', [
        'forum_topic_id' => (int)$comment['forum_topic_id'],
        'created' => ['$gt' => $comment['created']]
      ]);
      $page_count = max(1,ceil($newer / APP['comments']['thread_count']));
      $url = '/kozter/forum-tema/' . $comment['forum_topic_id']
        . '?oldal=' . $page_count . '&komment=' . $comment['id'];
    }
    return $url;
  }


}

