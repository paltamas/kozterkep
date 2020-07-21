<?php

class PatchJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
    $this->apikey = C_WS_SENDGRID['apikey'];

    $this->oldDB = new Kozterkep\DatabaseComponent('kt_old');

    die('stoppolva contructban, nehogy.' . PHP_EOL);
  }


  public function ww_export() {
    /*$monuments = $this->DB->find('ww_monuments', [
      'conditions' => 'artpiece_id > 0',
      'fields' => 'id,artpiece_id',
    ]);*/

    /*$parameters = $this->Mongo->find_array('ww_parameters', [], ['sort' => ['parameter_id' => 1]]);
    foreach ($parameters as $item) {
      $this->DB->insert('ww_parameters', [
        'parameter_id' => $item['parameter_id'],
        'parameter_type_id' => $item['parameter_type_id'],
        'description' => $item['description'],
      ]);
    }
    unset($parameters);*/

    $photos = $this->Mongo->find_array('ww_photos', [], ['sort' => ['photo_id' => 1]]);
    foreach ($photos as $item) {
      $this->DB->insert('ww_photos', [
        'photo_id' => $item['photo_id'],
        'monument_id' => $item['monument_id'],
        'slug' => $item['slug'],
      ]);
    }
    unset($photos);
  }


  public function read() {

    exit;

    $results = $this->Mongo->find_array('comments', ['user_id' => 10]);
    var_dump($results);

    echo count($results);

  }

  public function passed_artpieces() {
    $users = $this->DB->find('artpieces', [
      'conditions' => 'creator_user_id <> user_id',
      'fields' => 'creator_user_id',
      'group' => 'creator_user_id',
    ]);
    foreach ($users as $user) {
      $passed_count = $this->DB->count('artpieces', [
        'creator_user_id' => $user['creator_user_id']
      ]);
      $this->DB->update('users', [
        'artpiece_count_passed' => $passed_count
      ], $user['creator_user_id']);
    }
  }


  public function wikicopy() {
    $posts = $this->oldDB->find('wikiposts', [
      'conditions' => ['status_id' => 5],
      'order' => 'wikicategory_id ASC, wikichapter_id ASC, ordera ASC',
    ]);

    foreach ($posts as $post) {
      $this->DB->insert('wikiposts', [
        'title' => $post['title'],
        'text' => $post['text'],
      ]);
    }
  }



  public function datecorr() {
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'dates <>' => '',
      ],
      'fields' => ['id', 'dates'],
    ]);

    $i = 0;

    foreach ($artpieces as $artpiece) {
      $old = $this->oldDB->first('artpieces', $artpiece['id'], [
        'fields' => ['year','unveil_date']
      ]);
      $dates = _json_decode($artpiece['dates']);
      $dates_bak = $dates;

      if ($old && @$old['year'] > 0
        && count($dates) == 1
        && @$dates[0]['y'] == $old['year']
        && @$dates[0]['type'] == 'unveil') {
        $dates[0]['type'] = 'erection';

        $this->DB->update('artpieces', [
          'dates' => json_encode($dates),
          'dates_bak' => json_encode($dates_bak),
        ], $artpiece['id']);

        $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);

        $i++;
      }
    }

    echo $i . ' rendbe teve';
  }




  /**
   * Paraméter mezőben a JSON-t javítja aposztrófosra, mert így keresünk benne
   */
  public function parameters() {
    $results = $this->DB->query("SELECT id,parameters FROM artpieces 
      WHERE parameters <> '' AND parameters <> '[]' AND parameters NOT LIKE '%\"%'");

    if (count($results) > 0) {
      foreach ($results as $row) {
        $params = [];
        foreach (_json_decode($row['parameters']) as $param) {
          $params[] = (string)$param;
        }
        $this->DB->update('artpieces', [
          'parameters' => json_encode($params),
        ], $row['id']);
      }
    }
  }



  public function not_own_edits() {
    $artpieces = $this->DB->find('artpieces', [
      'fields' => ['id', 'user_id', 'creator_user_id'],
      'status_id' => 5,
    ]);

    $sum_edits = $sum_artpieces = 0;

    foreach ($artpieces as $artpiece) {
      $edits = $this->Mongo->find('artpiece_edits', [
        'artpiece_id' => $artpiece['id'],
        'own_edit' => 1,
        'user_id' => ['$not' => ['$in' => [$artpiece['user_id'], $artpiece['creator_user_id']]]]
      ]);

      if (count($edits) > 0) {
        $sum_artpieces++;
        $sum_edits += count($edits);
        foreach ($edits as $edit) {
          $this->Mongo->update('artpiece_edits', [
            'own_edit' => 0,
          ], [
            '_id' => $edit->_id
          ]);
        }
      }
    }

    echo $sum_artpieces . ' műlap ' . $sum_edits . ' szerkesztése' . PHP_EOL;
  }



  public function migrate_bookmarks() {
    $this->Mongo->delete('bookmarks', [
      'user_id' => ['$gt' => 0],
    ]);

    $bins = $this->oldDB->find('tags', [
      'conditions' => [
        'tagtype_id' => 11,
      ],
      'fields' => 'id, name, user_id, created, hidden',
    ]);

    foreach ($bins as $bin) {
      $items = [];

      $artpieces = $this->oldDB->find('artpieces_tags', [
        'conditions' => [
          'user_id' => $bin['user_id'],
          'tag_id' => $bin['id'],
        ],
        'fields' => ['artpiece_id', 'created'],
        'order' => 'created DESC',
      ]);

      if (count($artpieces) > 0) {
        foreach ($artpieces as $artpiece) {
          $items[] = $artpiece['artpiece_id'];
        }
      }

      $this->Mongo->insert('bookmarks', [
        'user_id' => (int)$bin['user_id'],
        'name' => $bin['name'],
        'hidden' => (int)$bin['hidden'],
        'created' => (int)$bin['created'],
        'type_id' => 1,
        'items' => $items,
      ]);
    }
  }



  public function editcomments () {
    $comments = $this->Mongo->find('comments', [
      'artpiece_id' => ['$gt' => 0]
    ], [
      'projection' => [
        'artpiece_id' => 1,
      ],
    ]);
    $i = 0;

    foreach ($comments as $comment) {
      $artpiece = $this->MC->t('artpieces', (int)$comment->artpiece_id);
      if ($artpiece['status_id'] != 5) {
        $this->Mongo->update('comments', [
          'artpiece_edit' => 1
        ], ['_id' => (string)$comment->_id]);
        $i++;
      }
    }
    echo $i . ' volt';
  }

  public function conversations () {
    $conversations = $this->Mongo->find('conversations', [
    ], [
      'projection' => [
        'read' => 1,
      ],
    ]);
    $i = 0;

    foreach ($conversations as $conversation) {
      if (isset($conversation->read)) {
        $read_ = (array)$conversation->read;
        $read = [];
        foreach ($read_ as $k => $v) {
          $read[] = $v;
        }
        $this->Mongo->update('conversations', [
          'read' => $read
        ], ['_id' => (string)$conversation->_id]);
        $i++;
      }
    }
    echo $i . ' volt';
  }



  public function photo_copy() {
    $photos = $this->DB->find('photos', [
      'conditions' => [
        'created >' => strtotime('2019-04-22 22:00:00'),
        'original_copied' => 0,
      ],
      'fields' => ['id', 'original_slug'],
      'limit' => 100,
    ]);

    foreach ($photos as $photo) {
      $source_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg';
      if (is_file($source_path)
        && $this->File->s3_copy($source_path, 'originals/' . $photo['original_slug'] . '.jpg')) {
        $this->DB->update('photos', [
          'original_copied' => 1,
        ], $photo['id']);
      }
    }
  }


  public function photo_ranks() {
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'modified >' => strtotime('2019-04-22 22:00:00'),
        'photo_count >' => 0,
      ],
      'fields' => ['id', 'photos']
    ]);

    foreach ($artpieces as $artpiece) {
      $photoranks = _json_decode($artpiece['photos']);
      if (is_array($photoranks) && count($photoranks) > 0) {
        foreach ($photoranks as $p) {
          $this->DB->update('photos', [
            'rank' => $p['rank']
          ], $p['id']);
        }
      }
    }
  }



  /**
   * Alakosból => Személyek lett a migrációnál,
   * de ez hülyeség. Nem is értem, miért csináltam.
   */
  public function person_para() {
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'parameters LIKE' => '%"42"%',
        'created <' => strtotime('2019-04-22'),
      ],
      'fields' => ['id', 'parameters']
    ]);

    $i = 0;

    foreach ($artpieces as $artpiece) {
      $parameters = _json_decode($artpiece['parameters']);
      $key = array_search('42', $parameters);
      if ($key) {
        // Megnézzük, rá volt-e jelölve a régiben (47 a régi száma)
        $old_tag = $this->oldDB->find('artpieces_tags', [
          'conditions' => [
            'tag_id' => 47,
            'artpiece_id' => $artpiece['id'],
            'status_id' => 5,
          ],
          'debug' => false,
        ]);
        $exists = $this->oldDB->first('artpieces', $artpiece['id']);
        // Nem volt => töröljük és rámentjük
        if ($exists && count($old_tag) == 0) {
          $i++;
          unset($parameters[$key]);
          $json = _json_encode(array_values($parameters), false, false);
          $this->DB->update('artpieces', [
            'parameters' => $json,
          ], $artpiece['id']);
        }
      }
    }

    echo 'Ennyinél lesz: ' . $i;
  }



  public function own_edits() {
    $edits = $this->Mongo->find('artpiece_edits', [
      'own_edit' => ['$ne' => 1]
    ], [
      'projection' => [
        'receiver_user_id' => 1,
        'user_id' => 1,
        'hug_id' => 1,
      ],
    ]);
    $i = 0;
    foreach ($edits as $edit) {
      if (@$edit->hug_id > 0) {
        $hug = $this->oldDB->first('hugs', $edit->hug_id, [
          'fields' => ['receiver_user_id'],
        ]);
      } else {
        $hug = false;
      }
      if ((isset($edit->receiver_user_id) && $edit->receiver_user_id == $edit->user_id)
        || ($hug && $hug['receiver_user_id'] != $edit->user_id)) {
        $this->Mongo->update('artpiece_edits', [
          'own_edit' => 1,
        ], [
          '_id' => (string)$edit->_id
        ]);
        $i++;
      }
    }
    echo $i . ' volt';
  }

  public function old_filenames() {
    $this->oldDB = new Kozterkep\DatabaseComponent('kt_old');
    $files = $this->oldDB->find('files', [
      'fields' => 'id, filename',
    ]);

    foreach ($files as $file) {
      $this->DB->update('files', [
        'old_filename' => $file['filename']
      ], $file['id']);
    }
  }

  public function post_files() {
    $posts = $this->DB->find('posts', [
      'conditions' => ['file_id >' => 0],
      'fields' => ['id', 'file_id'],
    ]);
    $i = 0;
    foreach ($posts as $post) {
      $file = $this->DB->first('files', $post['file_id']);
      if ($file['onesize'] != '') {
        $i++;
        $this->DB->update('posts', [
          'file_slug' => $file['onesize']
        ], $post['id']);
      }
    }
    echo $i . ' volt';
  }


  public function make_webstat() {

    for ($i = 1; $i < 5000000; $i++) {

      $time = strtotime('-' . floor($i*.7) . ' minutes');

      $paths = [
        '/webstat',
        '/webstat/szerverallapot',
        '/tudsz-rola',
        '/',
        '/hirek/lista',
        '/oldalak/idovonal',
        '/oldalak/segedlet',
        '/oldalak/mukodesi-elvek',
        '/kozter/szerkesztesek/paltamas',
        '/kozter/ertesitesek',
        '/kozter/mulapjaim',
        '/kozter/munkalap',
        '/jatekok/info',
        '/kereses',
        '/terkep',
        '/adattar/alkotok',
        '/adattar/helyek',
        '/adattar/konyvtar',
        '/oldalak/jogi-nyilatkozat',
        '/tagsag/beallitasok',
      ];

      $path = $paths[rand(1, count($paths)-1)];

      $this->Mongo->insert('webstat', [
        's' => uniqid(), // session ID az egyediséghez
        'u' => 1, // user_id, ha
        'p' => $path, // tisztított path query és hash nélkül
        'fp' => $path, // teljes path
        'r' => '', // referrer
        'vp' => '', // visit page: minősített oldalak
        'vi' => 0, // visit ID: minősített oldal ID-k
        't' => (int)$time, // itt jött ide
        'tt' => (int)($time+1), // eddig nézte
        'd' => 1, // done, feldolgozott
      ]);

    }

  }

  /**
   * Beszélgetések beszúrása "igazi" tagokkal
   */
  public function make_conversations() {

    exit;

    $users = $this->DB->find('users', [
      'type' => 'list',
      'fields' => 'id,name',
      'conditions' => ['id <' => 1000]
    ]);

    $what = ['szép', 'ügyes', 'okos', 'frappánsan ír', 'kedves', 'nagy autót birtokol titokban', 'ordítson', 'írlyon hejesen', 'kapjon szalagot'];

    $string = 'Interdum et malesuada fames ac ante ipsum primis in faucibus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ac egestas velit. Vestibulum leo elit, egestas nec sapien vitae, hendrerit tempor massa. Proin tristique dolor quis turpis pretium facilisis et at nulla. Curabitur sed arcu non turpis maximus bibendum. Vestibulum ac ante quam. Quisque ultricies aliquam magna, sed laoreet ex imperdiet id. Morbi interdum suscipit magna id ornare. Sed pharetra nisl at sagittis tincidunt.
    
    Sed vestibulum quis massa eu cursus. Nunc sed dapibus massa. Duis sit amet justo et nisl sagittis porta. In vitae purus a lorem maximus lobortis. Quisque sit amet dignissim odio, eu elementum tortor. Fusce cursus sem lectus, eu tempor diam auctor nec. Integer tincidunt enim ac nunc congue, vel tempus augue sagittis. Praesent molestie, nulla eu venenatis faucibus, mauris eros fringilla quam, id posuere libero neque sit amet velit. Nam iaculis diam vitae neque sodales aliquet. Aenean fringilla odio ullamcorper porta dapibus. Interdum et malesuada fames ac ante ipsum primis in faucibus.';

    for ($i = 1; $i <= 50000; $i++) {

      $user_1 = rand(1, count($users));
      $user_2 = rand(1, count($users));

      $insert_id = $this->Mongo->insert('conversations', [
        'users' => [$user_1, $user_2],
        'user_names' => [
          $users[$user_1]['name'],
          $users[$user_2]['name'],
        ],
        'started' => time(),
        'updated' => time(),
        'subject' => $users[rand(1, count($users))]['name'] . ' szerintem ' . $what[rand(0, count($what) - 1)],
        'read' => [$user_1, $user_2],
        'favored' => [],
        'archived' => [],
        'trashed' => [],
        'deleted' => [],
        'words' => str_word_count($string, 1),
        'messages' => [
          [
            'mid' => uniqid(),
            'user_name' => $users[$user_1]['name'], // hogy ne kelljen joinolni...
            'user_id' => $user_1,
            'created' => time(),
            'body' => $string,
            'deleted' => [],
          ],
          [
            'mid' => uniqid(),
            'user_name' => $users[$user_2]['name'], // hogy ne kelljen joinolni...
            'user_id' => $user_2,
            'created' => time(),
            'body' => $string,
            'deleted' => [],
          ],
        ]
      ]);

    }
  }



  /**
   *
   * Fotó fogadó user beállítása, hogy lássuk, _kiéiké_
   * a Migrációs logika már tartalmazza!
   */
  public function photo_users() {
    $this->DB->query("UPDATE photos SET receiver_users = CONCAT('[\"', user_id ,'\"]')");

    echo 'update kesz' . PHP_EOL;

    $photos = $this->DB->find('photos', [
      'fields' => ['id', 'artpieces', 'user_id'],
    ]);

    $artpieces = $this->DB->list_by_id('artpieces', [], [
      'fields' => ['id', 'user_id'],
    ]);

    $i = 0;

    foreach ($photos as $photo) {
      $i++;
      $artpiece_ids = _json_decode($photo['artpieces']);

      // Ha van legalább 1 másműlap, akkor másé is
      $receiver_users = [];
      $for_me = true;
      foreach ($artpiece_ids as $artpiece_id) {
        if ($artpieces[$artpiece_id]['user_id'] != $photo['user_id']) {
          $receiver_users[] = (string)$artpieces[$artpiece_id]['user_id'];
          $for_me = false;
        } else {
          $receiver_users[] = (string)$photo['user_id'];
        }
      }

      if (!$for_me) {
        $this->DB->update('photos', [
          'receiver_users' => json_encode(array_unique($receiver_users))
        ], $photo['id']);
      }

      if ($i%1000 == 0) {
        echo $i . PHP_EOL;
      }
    }

    echo 'foto kesz';
  }



  /**
   * Tulaj user beállító migráció után edit és description-höz
   * a Migrációs logika már tartalmazza!
   */
  public function owner_users () {

    // Műlapok
    $artpieces = $this->DB->list_by_id('artpieces', [], [
      'fields' => ['id', 'user_id'],
    ]);

    $i = 0;

    foreach ($artpieces as $artpiece) {

      $i++;

      $this->Mongo->update('artpiece_edits', [
        'receiver_user_id' => (int)$artpiece['user_id']
      ], [
        'artpiece_id' => (int)$artpiece['id'],
        'user_id' => ['$ne' => (int)$artpiece['user_id']],
      ]);

      $this->Mongo->update('artpiece_descriptions', [
        'receiver_user_id' => (int)$artpiece['user_id']
      ], [
        'artpieces' => (int)$artpiece['id'],
        'user_id' => ['$ne' => (int)$artpiece['user_id']],
      ]);

      if ($i%1000 == 0) {
        echo $i . PHP_EOL;
      }

    }

    echo 'kesz';
  }

}