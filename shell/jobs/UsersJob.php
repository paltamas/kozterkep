<?php
class UsersJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   * Aktivitási pont és szavazati súly kalkuláló
   * mellette részletes aktivitási mutatókat is mentünk
   *
   * @info
   * Az első futtatásnál ki kell venni az 1. users kiolvasásban
   * szereplő "last_here" feltételt, hogy minden aktívra lefuttassuk.
   *
   */
  public function scores() {
    $options = self::$_options;

    $latest_from = strtotime('-12 months');


    // Userek
    $users = $this->DB->find('users', [
      // A "last_here" feltételt az első futásnál ki kell kommentelni, hogy mindenkire generáljunk
      'conditions' => [
        'activated >' => 0,
        'last_here >' => strtotime('-5 days'),
      ],
      'fields' => ['id', 'artpiece_count', 'user_level', 'headitor', 'admin'],
    ]);

    $user_updates = [];

    /**
     * Userek pontjainak összegzése a static_database fájlban megadott pontokkal
     */
    $i = 0;
    foreach ($users as $user) {
      $i++;

      $user_updates[$user['id']] = [
        'photo_count' => 0,
        'photo_count_latest' => 0,
        'photo_other_count' => 0,
        'photo_other_count_latest' => 0,
        'edit_other_count' => 0,
        'edit_other_count_latest' => 0,
        'description_other_count' => 0,
        'description_other_count_latest' => 0,
        'points' => 0,
        'points_latest' => 0,

        'post_count' => 0,
        'comment_count' => 0,
        'comment_count_latest' => 0,
        'book_count' => 0,
        'folder_count' => 0,
        'set_count' => 0,
        'hug_count' => 0,
        'spacecapsule_count' => 0,
      ];

      // Műlapok száma
      $user_updates[$user['id']]['points'] += $user['artpiece_count'] * sDB['user_scores']['values']['artpiece'];
      $artpiece_count_latest = $this->DB->count('artpieces', [
        'user_id' => $user['id'],
        'status_id' => 5,
        'published >' => $latest_from,
      ]);
      $user_updates[$user['id']]['artpiece_count_latest'] = $artpiece_count_latest;
      $user_updates[$user['id']]['points_latest'] += $artpiece_count_latest * sDB['user_scores']['values']['artpiece'];

      // Fotók száma
      $photo_count = $this->DB->count('photos', ['user_id' => $user['id']]);
      $user_updates[$user['id']]['photo_count'] = $photo_count;
      $user_updates[$user['id']]['points'] += $photo_count * sDB['user_scores']['values']['photo'];
      $photo_count_latest = $this->DB->count('photos', [
        'user_id' => $user['id'],
        'approved >' => $latest_from,
      ]);
      $user_updates[$user['id']]['photo_count_latest'] = $photo_count_latest;
      $user_updates[$user['id']]['points_latest'] += $photo_count_latest * sDB['user_scores']['values']['photo'];

      // Máshoz töltöttek (ezt csak mentjük a userhez)
      $photo_other_count = $this->DB->count('photos', [
        'user_id' => $user['id'],
        'added' => 1,
      ]);
      $user_updates[$user['id']]['photo_other_count'] = $photo_other_count;

      $photo_other_count_latest = $this->DB->count('photos', [
        'user_id' => $user['id'],
        'added' => 1,
        'approved >' => $latest_from,
      ]);
      $user_updates[$user['id']]['photo_other_count_latest'] = $photo_other_count_latest;

      // Szerkesztések máshoz
      $edit_count = $this->Mongo->count('artpiece_edits', [
        'status_id' => 5,
        'user_id' => $user['id'],
        'receiver_user_id' => [
          '$gt' => 0,
          '$ne' => $user['id']
        ]
      ]);

      $user_updates[$user['id']]['edit_other_count'] = $edit_count;
      $user_updates[$user['id']]['points'] += $edit_count * sDB['user_scores']['values']['edit'];

      $edit_count_latest = $this->Mongo->count('artpiece_edits', [
        'status_id' => 5,
        'user_id' => $user['id'],
        'receiver_user_id' => [
          '$gt' => 0,
          '$ne' => $user['id']
        ],
        'approved' => ['$gt' => $latest_from],
      ]);
      $user_updates[$user['id']]['edit_other_count_latest'] = $edit_count_latest;
      $user_updates[$user['id']]['points_latest'] += $edit_count_latest * sDB['user_scores']['values']['edit'];

      // Leírások máshoz
      $description_count = $this->Mongo->count('artpiece_descriptions', [
        'status_id' => 5,
        'user_id' => $user['id'],
        'receiver_user_id' => [
          '$gt' => 0,
          '$ne' => $user['id']
        ]
      ]);
      $user_updates[$user['id']]['description_other_count'] = $description_count;
      $user_updates[$user['id']]['points'] += $description_count * sDB['user_scores']['values']['description'];

      $description_count_latest = $this->Mongo->count('artpiece_descriptions', [
        'status_id' => 5,
        'user_id' => $user['id'],
        'receiver_user_id' => [
          '$gt' => 0,
          '$ne' => $user['id']
        ],
        'approved' => ['$gt' => $latest_from],
      ]);
      $user_updates[$user['id']]['description_other_count_latest'] = $description_count_latest;
      $user_updates[$user['id']]['points_latest'] += $description_count_latest * sDB['user_scores']['values']['description'];


      // Egyéb adatok
      $user_updates[$user['id']]['post_count'] = $this->DB->count('posts', [
        'status_id' => 5,
        'user_id' => $user['id'],
      ]);
      $user_updates[$user['id']]['comment_count'] = $this->Mongo->count('comments', [
        'user_id' => (int)$user['id'],
      ]);
      $user_updates[$user['id']]['comment_count_latest'] = $this->Mongo->count('comments', [
        'user_id' => (int)$user['id'],
        'created' => ['$gt' => $latest_from]
      ]);
      $user_updates[$user['id']]['book_count'] = $this->DB->count('books', [
        'user_id' => $user['id'],
      ]);
      $user_updates[$user['id']]['folder_count'] = $this->DB->count('folders', [
        'public' => 1,
        'user_id' => $user['id'],
      ]);
      $user_updates[$user['id']]['set_count'] = $this->Mongo->count('sets', [
        'set_type_id' => 2,
        'user_id' => (int)$user['id'],
      ]);
      $user_updates[$user['id']]['hug_count'] = $this->Mongo->count('artpiece_hugs', [
        'user_id' => (int)$user['id'],
      ]);
      $user_updates[$user['id']]['spacecapsule_count'] = $this->Mongo->count('artpiece_spacecapsules', [
        'user_id' => (int)$user['id'],
      ]);


      // Mentjük az aktivitási pontokat
      $this->DB->update('users', $user_updates[$user['id']], $user['id']);
    }




    // Kiolvassuk az össz aktivitási pontot, hogy ehhez viszonyítva mindenkinél
    // újra tudjuk számolni a saját részesedését és ebből a score-t
    $points = $this->DB->find('users', ['fields' => 'SUM(points) AS sum']);




    /**
     * Visszamentjük az összhöz képesti rate és abból számolt score értéket
     * De itt mindig minden usert kiolvasunk, és ha valaki most kapott pontot, azzal
     * számolunk, egyébként az adatbázisban lévővel
     */
    $users = $this->DB->find('users', [
      'fields' => ['id', 'points', 'user_level', 'headitor', 'admin', 'artpiece_count', 'blocked', 'active', 'harakiri', 'kt2'],
    ]);

    foreach ($users as $user) {

      $user_point = isset($user_updates[$user['id']])
        ? $user_updates[$user['id']]['points'] : $user['points'];

      $contribution_rate = $user_point / $points[0]['sum'] * 100;

      if ($user['headitor'] == 1) {
        // Főszerkesztők és adminok pontjai
        $score = sDB['user_scores']['settings']['headitor_points'];

      } elseif ($user['user_level'] > 0
        || ($user['artpiece_count'] >= sDB['user_scores']['settings']['artpiece_limit']
          && $user['blocked'] == 0 && $user['active'] == 1 && $user['harakiri'] == 0
          && $user['kt2'] > 0)) {
        // Szabadon publikálók pontjai
        $min = sDB['user_scores']['settings']['min_points'];
        $max = sDB['user_scores']['settings']['max_points'];
        $score = max($min, min($max, ceil($contribution_rate)));
      } else {
        // Aki nem szabad publikáló, annak nincs pontja
        $score = 0;
      }

      $this->DB->update('users', [
        'score' => $score,
        'contribution_rate' => $contribution_rate
      ], $user['id']);
    }

    return true;
  }


  /**
   *
   * User átnevezése minden statikusen nevet tároló
   *
   * @return bool
   */
  public function rename() {
    $options = self::$_options;
    if (!isset($options['user_id']) || !isset($options['new_name'])
      || @$options['user_id'] == 0 || @$options['new_name'] == '') {
      return false;
    }

    $user_id = $options['user_id'];
    $new_name = $options['new_name'];

    // Beszélgetések
    $conversations = $this->Mongo->find('conversations', [
      'users' => (int)$user_id
    ], ['projection' => [
      '_id' => 1,
      'users' => 1,
      'user_names' => 1
    ]]);

    if (count($conversations) > 0) {
      foreach ($conversations as $conversation) {
        // Hányadik user
        foreach ($conversation->users as $key => $value) {
          if ($value == $user_id) {
            $user_key = $key;
            break;
          }
        }

        $user_names = $conversation->user_names;
        $user_names[$user_key] = $new_name;

        $this->Mongo->update('conversations', [
          'user_names' => $user_names
        ], ['_id' => (string)$conversation->_id]);
      }
    }

    // Kommentek
    $comments = $this->Mongo->find('comments', [
      'user_id' => (int)$user_id
    ], ['projection' => ['_id' => 1]]);

    if (count($comments) > 0) {
      foreach ($comments as $comment) {
        $this->Mongo->update('comments', [
          'user_name' => $new_name
        ], ['_id' => (string)$comment->_id]);
      }
    }

    // Szavazatok
    $votes = $this->Mongo->find('artpiece_votes', [
      'user_id' => (int)$user_id
    ], ['projection' => ['_id' => 1]]);

    if (count($votes) > 0) {
      foreach ($votes as $vote) {
        $this->Mongo->update('artpiece_votes', [
          'user_name' => $new_name
        ], ['_id' => (string)$vote->_id]);
      }
    }

    return true;
  }


}