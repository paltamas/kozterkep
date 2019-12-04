<?php
class MigrationsJob extends Kozterkep\JobBase {

  private $kt_url;

  public function __construct() {

    if (CORE['env'] == 'prod') {
      echo 'prod környezetben nem futok';
      return;
    }

    parent::__construct();
    ini_set('max_execution_time', 3000000000);
    ini_set('memory_limit', '5000M');
    define('MAX_FILE_SIZE', 60000000);
    $this->start = time();
    $this->Notifications->create(CORE['USERS']['admins'], 'Migráció indul: ' . date('H:i:s'));

    $this->kt_url = 'https://www.kozterkep.hu';

    // Régi KT DB
    $this->oldDB = new Kozterkep\DatabaseComponent('kt_old');

    // Régi KT / Hősi emlék DB
    $this->oldHE = new Kozterkep\DatabaseComponent('kt_old_he');
  }

  public function __destruct() {
    $this->Notifications->create(CORE['USERS']['admins'], 'Migráció kész: ' . date('H:i:s'));

    // Hogy garantáltan minden újrakészüljön
    $this->Mongo->insert('jobs', [
      'class' => 'cache',
      'action' => 'reset',
      'created' => date('Y-m-d H:i:s'),
    ]);
  }

  public function notification($method, $error = 0) {
    $length = ceil((time() - $this->start) / 60);
    $length = $length > 59 ? round($length / 60, 1) . ' óra' : $length . ' perc';
    $this->Notifications->create(CORE['USERS']['admins'], $method . ' OK (' . $length . ')');
  }


  /**
   *
   * Futtatás
   *
   * minden mehet simán, kivéve az alábbiakat:
   *
   *  - artpieces || photos előtt copy_hugs_table kell
   *  - comments előtt futtasd a folderst (folders üríti a files, és a comments is oda pakkint)
   * comments előtt érdemes még futtatni: books, artpieces, posts - a related_users mező miatt
   *  - artpieces előtt érdemes a photos-t
   *
   */


  public function sync_custom() {
    $this->artpieces();
    $this->artpiece_photos();
  }


  /**
   * Élesítés
   *
   */
  public function sync_release() {
    $this->Mongo->delete('events');
    $this->Migrations->prepare('files', true);
    $this->copy_hugs_table();
    $this->users();
    $this->photos();
    // az artists akarja a fotókat a slug miatt
    $this->artists();
    $this->places();
    $this->comments();
    $this->photo_events();
    $this->hugs();
    $this->artpieces();
    $this->artpiece_photos();
    $this->sets();
    $this->posts();
    $this->photo_receivers();
    $this->Migrations->aftermath('files');
  }


  public function sync_all() {
    $this->Migrations->prepare('files');
    $this->Mongo->delete('events');
    $this->copy_hugs_table();
    //$this->users();
    $this->photos();
    // az artists akarja a fotókat a slug miatt
    $this->artpeople();
    $this->artists();
    $this->places();
    //$this->forums(); // fordítás kell, csak cake-ből jöhet
    $this->folders();
    $this->comments();
    $this->photo_events();
    $this->hugs();
    //$this->parameters(); // fordítás kell, csak cake-ből jöhet, és még bele is nyúltam
    $this->messages(); // ez előtt meg kell csinálni a táblát
    $this->artpieces();
    $this->artpiece_photos();
    $this->sets();
    $this->posts();
    $this->photo_receivers();
    $this->ww_monuments();
    $this->words();
    $this->books();
    $this->Migrations->aftermath('files');
  }

  /*
   * Átveszi a hugs táblát
   * bizonyos okokból képtelen mindent átvenni
   * bajok: néhány esetben nem ad választ az API
   * de már nincs erőm kidebuggolni
   * voltak hülyekarakterek meg minden
   * aztán az lett az eredmény, hogy htmlentities() megy rá ott
   * html_entity_decode() itt és így marad a szép karakterkódolás itt
   *
   */

  public function copy_hugs_table() {
    $this->Migrations->prepare('hugs');

    $total_count = $this->oldDB->count('hugs');

    $step = 5000;
    $pages = round(($total_count / $step) * 1.1);

    for ($i = 1; $i < $pages; $i++) {

      if ($this->DB->count('hugs') >= $total_count) {
        break;
      }

      $result = $this->oldDB->find('hugs', [
        'limit' => $step,
        'page' => $i
      ]);

      echo count($result) . ' átvéve' . PHP_EOL;

      if ($result) {
        $items = $this->Migrations->clear_old_hug_rows($result);
        $this->DB->insert_multi('hugs', $items);
      } else {
        echo 'hiba: ' . $i . ' 
';
        $i--;
      }

      echo $i * $step . ' beszúrva' . PHP_EOL;

    }

    $this->Migrations->aftermath('hugs');
    $this->notification(__FUNCTION__);
  }

  /*
   * Teljes újraépítés és szinkronizálás.
   * A userekhez ez elég.
   *
   */

  public function users() {
    $result = $this->oldDB->find('users', ['order_by' => 'id']);

    $this->Mongo->delete('users', ['user_id' => ['$gt' => 0]]);
    $this->Mongo->delete('notifications', ['user_id' => ['$gt' => 0]]);

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('users');

      foreach ($result as $row) {
        if ($row['email'] == '') {
          // A régi KT-ben van ilyen hiba...
          continue;
        }

        $nickname = $row['id'] == 1 ? 'paltamas' : '';

        if ($row['id'] == 2) {
          $row['name'] = 'KöztérGép';
          // (C) Robo Otto :)
          $nickname = 'Eltévedt űrhajós';
          $row['link'] = 'koztergep';
        }

        $alert_settings = json_encode([
          'conversations' => $row['email_messages'],
          'work' => $row['email_messages'],
          'notifications_artpieces' => $row['email_artpieces'] == 1 ? 1 : 0,
          'notifications_edits' => $row['email_hugs'] == 1 ? 1 : 0,
          'notifications_comments' => $row['email_answers'] == 1 ? 1 : 0,
          'notifications_games' => 0,
          'notifications_others' => 0,
        ]);

        $newsletter_settings = json_encode([
          'weekly_harvest' => (int)$row['newsletter'],
          'daily' => 0,
        ]);


        // Képfájlok másolása, mielőtt mentünk, hogy ha nincs, akkor ürítsük a mezőt
        $kt_folder = $this->kt_url . '/user/' . $row['id'] . '/';
        $folder = CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/tagok/';
        if ($row['profile_photo_filename'] != '') {
          $copied = true;
          for ($i = 1; $i <= 5; $i++) {
            $missing = copy(
              $kt_folder . $row['profile_photo_filename'] . '_' . $i . '.jpg',
              $folder . $row['profile_photo_filename'] . '_' . $i . '.jpg'
            );
            if (!$missing) {
              // Akár 1 méret is hiányzik...
              $copied = false;
            }
          }
          if (!$copied) {
            $row['profile_photo_filename'] = '';
          }
        }

        $row['header_photo_height'] = 0;

        if ($row['header_photo_filename'] != '') {
          $copied = copy(
            $kt_folder . $row['header_photo_filename'],
            $folder . str_replace('.JPG', '.jpg', $row['header_photo_filename'])
          );
          if (!$copied) {
            $row['header_photo_filename'] = '';
          } else {
            $row['header_photo_filename'] = str_replace('.JPG', '.jpg', $row['header_photo_filename']);
            $row['header_photo_height'] = getimagesize($folder . $row['header_photo_filename'])[1];
          }
        }


        // Ha harakiri volt...
        if ($row['harakiri'] == 1) {
          $row['email'] = 'torolt_profil_' . $row['id'];
          $row['link'] = 'torolt_profil_' . $row['id'];
        }


        $this->DB->insert('users', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id', 'email', 'profile_photo_filename', 'header_photo_filename', 'link', 'link_changed', 'introduction', 'pause', 'blocked', 'harakiri'],
            'trim' => ['name'],
            'int' => ['license_type_id', 'last_here_before', 'last_here', 'created', 'modified', 'activated', 'test_member', 'artpiece_count'],
            'rename' => [
              'city_name' => 'place_name',
              'vacation' => 'int:out_of_work',
              'vacation_responder' => 'auto_reply',
              'editor_was' => 'int:headitor_was',
              'hug_photo_count_own' => 'int:photo_count',
            ],
            'custom' => [
              'license_type_id' => $row['artpiece_count'] == 0 && $row['hug_photo_count_own'] == 0
                ? 6 : $row['license_type_id'],
              'blog_title' => $row['name'] . ' blogja',
              'email_notification_interval' => 30,
              'active' => $row['active'] == 1 && $row['blocked'] == 0 && $row['blocked'] == 0 ? 1 : 0,
              'header_photo_height' => $row['header_photo_height'],
              'web_links' => $row['web_link'] == 'http://' ? '' : $row['web_link'],
              'nickname' => $nickname,
              'alert_settings' => $alert_settings,
              'newsletter_settings' => $newsletter_settings,
              'repassword' => 1, // Kényszerítjük a jelszóváltást
              'editor_on' => 1, // Mindenki szerkesztő
              'managing_on' => $row['last_here'] < strtotime('-' . sDB['limits']['edits']['inactive_after_months'] . ' months') ? 0 : 1, // Az inaktívokat itt nemkezelőre állítjuk
              'admin' => $row['user_level_id'] == 7 ? 1 : 0,
              'headitor' => $row['user_level_id'] < 7 && $row['editor'] == 1 ? 1 : 0,
              'user_level' => $row['user_level_id'] == 0 ? 0 : 1,
              'highlighted' => $row['highlighted_monday'] > '0000-00-00' && trim($row['highlighted_monday']) != '' ? strtotime($row['highlighted_monday'] . ' 00:00:00') : 0,
            ]
          ]
        ));

        // Mongoba bele
        $insert = [
          'user_id' => (int)$row['id'],
        ];

        $follows = $this->DB->find('hugs', [
          'conditions' => [
            'user_id' => $row['id'],
            'hugtype_id' => 14,
          ],
          'order' => 'approved ASC'
        ]);

        if (count($follows) > 0) {
          foreach ($follows as $follow) {
            foreach ([
              'artpiece_id' => 'follow_artpieces',
              'artist_id' => 'follow_artists',
              'city_id' => 'follow_places',
              'followed_user_id' => 'follow_users',
                     ] as $id_field => $array_name) {
              if ($follow[$id_field] > 0) {
                if (!isset($insert[$array_name])) {
                  $insert[$array_name] = [];
                }
                $insert[$array_name][] = $follow[$id_field];
              }
            }
          }
        }

        $this->Mongo->insert('users', $insert);
      }

      $this->Migrations->aftermath('users');
      $this->notification(__FUNCTION__);
    }
  }


  /*
   * Teljesen újraépíti az alkotó táblát
   */
  public function artists() {
    $result = $this->oldDB->find('artists');

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('artists');

      foreach ($result as $row) {
        if ($row['name'] == '') {
          continue;
        }

        if ($row['born_year'] > 0) {
          $born_date = $row['born_year'] . '-';
          if ($row['born_month'] > 0) {
            $born_date .= $row['born_month'] < 10 ? '0' : '';
            $born_date .= $row['born_month'] . '-';
            $born_date .= $row['born_day'] < 10 ? '0' : '';
            $born_date .= $row['born_day'];
          } else {
            $born_date .= '-';
          }
        } else {
          $born_date = '';
        }

        if ($row['death_year'] > 0) {
          $death_date = $row['death_year'] . '-';
          if ($row['death_month'] > 0) {
            $death_date .= $row['death_month'] < 10 ? '0' : '';
            $death_date .= $row['death_month'] . '-';
            $death_date .= $row['death_day'] < 10 ? '0' : '';
            $death_date .= $row['death_day'];
          } else {
            $death_date .= '-';
          }
        } else {
          $death_date = '';
        }

        $photo_slug = '';
        if ($row['photo_id'] > 0) {
          $photo = $this->DB->first('photos', $row['photo_id'], ['fields' => ['slug']]);
          if ($photo) {
            $photo_slug = $photo['slug'];
          }
        }

        // KMML
        $artpeople_id = $row['artpeople_id'];
        if ($artpeople_id == 0) {
          $filters = ['$and' => [
            ['name' => ['$regex' => mb_strtolower($row['name']), '$options' => 'i'],]
          ]];
          if ($row['born_year'] > 0) {
            $filters['$and'][] = [
              'subtitle' => ['$regex' => $row['born_year'], '$options' => 'i'],
            ];
          }
          $similar = $this->Mongo->find_array('artpeople', $filters);
          if (isset($similar[0]['person_id']) && count($similar) == 1) {
            $artpeople_id = (int)$similar[0]['person_id'];
          }
        }

        $this->DB->insert('artists', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id'],
            'trim' => ['name', 'artist_name', 'before_name', 'alternative_names', 'admin_memo', 'inner_memo', 'website_url'],
            'int' => ['corporation', 'artistgroup', 'english_form', 'profession_id', 'photo_id', 'checked', 'checked_time', 'merged_into', 'created', 'modified', 'view_total', 'view_week', 'view_day', 'artpiece_count', 'last_artpiece_id', 'top_artpiece_id'],
            'rename' => [
              'born_city_name' => 'born_place_name',
              'death_city_name' => 'death_place_name',
            ],
            'custom' => [
              'first_name' => trim($row['first_name']) == '' ? @explode(' ', trim($row['name']))[1] : trim($row['first_name']),
              'last_name' => trim($row['last_name']) == '' ? explode(' ', trim($row['name']))[0] : trim($row['last_name']),
              'born_date' => $born_date,
              'death_date' => $death_date,
              'born_place_id' => (int)$row['born_city_id'],
              'death_place_id' => (int)$row['death_city_id'],
              'hun_origin' => $row['english_form'] == 0 ? 1 : 0,
              'user_id' => CORE['USERS']['artists'],
              'creator_user_id' => (int)$row['user_id'],
              'photo_slug' => $photo_slug,
              'artpeople_id' => $artpeople_id,
            ]
          ]
        ));
      }

      $this->Migrations->aftermath('artists');
      $this->notification(__FUNCTION__);
    }
  }

  /*
   * Teljesen újraépíti a település táblát
   */

  public function places() {
    $result = $this->oldDB->find('cities');

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('places');

      foreach ($result as $row) {
        if ($row['name'] == '') {
          continue;
        }

        $country_code = '';
        if ($row['country_code'] != '') {
          $country_code = $row['country_code'];
        } else {
          if ($row['country_id'] > 0) {
            $country_code = strtoupper(sDB['countries'][$row['country_id']][2]);
          }
        }

        $this->DB->insert('places', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id', 'nominatim'],
            'trim' => ['name', 'original_name', 'inner_memo'],
            'int' => ['country_id', 'county_id', 'photo_id', 'checked', 'checked_time', 'merged_into', 'view_total', 'view_week', 'view_day', 'artpiece_count', 'created', 'modified', 'last_artpiece_id', 'top_artpiece_id'],
            'rename' => [],
            'custom' => [
              'alternative_names' => trim($row['admin_memo']),
              'country_code' => $country_code,
              'user_id' => CORE['USERS']['places'],
              'creator_user_id' => (int)$row['user_id'],
            ]
          ]
        ));
      }

      $this->Migrations->aftermath('places');
      $this->notification(__FUNCTION__);
    }
  }

  /*
   * Teljesen újraépíti a bejegyzés táblát
   */

  public function posts() {

    $result = $this->oldDB->find('posts');

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('posts');

      foreach ($result as $row) {
        // Kapcsolódó műlapok
        $connected_artpieces = '[]';
        // 1 kiemelt műlap
        $artpiece_id = 0;
        $hugs = $this->DB->find('hugs', [
          'type' => 'fieldlist',
          'conditions' => [
            'post_id' => $row['id'],
            'hugtype_id' => 11,
            'status_id' => 5
          ],
          'fields' => ['artpiece_id'],
          'key' => 'artpiece_id',
        ]);

        // Ha van megadva artpiece_id, az a kiemelt, ha 1 kapcsolódó van, az kiemelt,
        // ha több, akkor az mind a kapcsolódóba
        // a 2-ben a műlapon máshol szerepel az 1 kapcsolás, mint a több esetén
        $artpiece_id = (int)$row['artpiece_id'];
        if (count($hugs) == 1 && $artpiece_id == 0) {
          $artpiece_id = array_values($hugs)[0];
        } elseif (count($hugs) > 0) {
          $connected_artpieces = _json_encode(array_values($hugs), false, false);
        }

        $photo = false;
        if ($row['photo_id'] > 0) {
          $photo = $this->DB->first('photos', $row['photo_id'], [
            'fields' => ['slug', 'artpiece_id']
          ]);
        }

        $file = false;
        if ($row['file_id'] > 0) {
          $file = $this->DB->first('files', $row['file_id'], [
            'fields' => ['name', 'folder_id']
          ]);
        }

        $row['text'] = str_replace(['http://www.szoborlap.hu', 'https://www.szoborlap.hu'], 'https://www.kozterkep.hu', $row['text']);

        $set_id = '';

        if ($row['tag_id'] > 0) {
          $set = $this->Mongo->first('sets', [
            'tag_id' => $row['tag_id']
          ]);
          if ($set) {
            $set_id = $set['id'];
          }
        }

        $this->DB->insert('posts', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id'],
            'trim' => ['title', 'text'],
            'int' => ['postcategory_id', 'artist_id', 'photo_id', 'file_id', 'folder_id', 'status_id', 'highlighted', 'newsletter', 'view_total', 'view_week', 'view_day', 'created', 'modified', 'published', 'user_id'],
            'rename' => [],
            'custom' => [
              'artpiece_id' => $artpiece_id,
              'set_id' => $set_id,
              'photo_slug' => $photo ? $photo['slug'] : '',
              'photo_artpiece_id' => $photo ? $photo['artpiece_id'] : 0,
              'file_slug' => $file ? $file['name'] : '',
              'file_folder_id' => $file ? $file['folder_id'] : 0,
              'intro' => trim(strip_tags($row['excerpt'])),
              'place_id' => (int)$row['city_id'],
              'connected_artpieces' => $connected_artpieces,
            ]
          ]
        ));
      }

      $this->Migrations->aftermath('posts');
      $this->notification(__FUNCTION__);
    }
  }



  /*
   * Teljesen újraépíti a fórumtopik tábláját
   * A translate opció miatt ezt csak a régiről tudjuk átvenni Cake-en keresztül, utána már nem
   */
  public function forums() {

    $result = $this->Migrations->get_model('ForumTopic', ['translate' => 1]);

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('forum_topics');

      foreach ($result as $row) {
        if (in_array($row['id'], [1,2,3,5])) {
          $row['classic'] = 0;
        }

        $this->DB->insert('forum_topics', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id'],
            'trim' => ['description'],
            'int' => ['editorial', 'closed', 'classic', 'created'],
            'rename' => [],
            'custom' => [
              'title' => $row['id'] == 6 ? 'FőszerkSzoba' : $row['name'],
            ]
          ]
        ));
      }

      $this->Migrations->aftermath('forum_topics');
      $this->notification(__FUNCTION__);
    }
  }



  /*
   * Teljesen újraépíti a mappák és fájlok tábláit
   */
  public function folders() {

    $result = $this->oldDB->find('folders');

    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {

      $this->Migrations->prepare('folders');

      foreach ($result as $row) {
        $this->DB->insert('folders', $this->Migrations->build_model_fields(
          $row, [
            'keep' => ['id', 'auth_key'],
            'trim' => ['name', 'description'],
            'int' => ['file_count', 'common', 'in_library', 'basic', 'user_id', 'created', 'modified'],
            'rename' => [],
            'custom' => [
              'public' => $row['user_id'] == 2 ? 0 : (int)$row['public'],
              'updated' => (int)$row['last_file_time'] > 0 ? (int)$row['last_file_time'] : (int)$row['modified']
            ]
          ]
        ));

        if ($row['file_count'] > 0) {
          // Fájlok
          $items = $this->oldDB->find('files', [
            'conditions' => ['folder_id' => $row['id']]
          ]);
          foreach ($items as $item) {
            $this->Migrations->folder_file($item, $row['id']);
            if ($item['cover'] == 1 && _contains($item['mime_type'], 'image')) {
              $this->DB->update('folders', ['file_id' => $item['id']], $row['id']);
            }
          }
        }
      }

      $this->Migrations->aftermath('folders');
      $this->notification(__FUNCTION__);
    }
  }

  /*
   * Mindenféle kommentet épít
   * Az _id-ket nézd és látod, hogy mit.
   *
   * Ez már a kozterkep.hugs-ra épül
   *
   */

  public function comments() {
    // Törlöm az eddig beszúrt kommenteket
    $this->Mongo->delete('comments');
    $this->Mongo->delete('artist_descriptions');

    $items = $this->DB->find('hugs', [
      'conditions' => ['hugtype_id' => 6],
      'order' => 'id ASC'
    ]);

    foreach ($items as $item) {
      // A könyvborítókommenteket nem vesszük figyelembe
      if ($item['book_id'] > 0 && $item['file_id'] > 0) {
        continue;
      }

      if (in_array($item['forum_topic_id'], [1,2,3,5])) {
        $item['forum_topic_id'] = 4;
      }

      $item['text'] = html_entity_decode($item['text']);

      $item['user_name'] = $this->MC->t('users', $item['user_id'])['name'];

      // megépítem a sor mezőit
      $fields = $this->Arrays->push_if_not_null(
        $item, // innen kellenek az értékek
        [// ezek mindenképp kellenek, némelyik átnevezve
          'id' => 'hug_id',
          'text',
          'answered_hug_id' => 'answered_id',
          'user_id',
          'user_name',
          'editorial',
          'created',
          'modified',
          'approved'
        ], [// ezek közül kell az, ami nem 0/null
          'forum_topic_id',
          'artpiece_id',
          'commented_hug_id',
          'post_id',
          'city_id',
          'artist_id',
          'book_id',
        ]
      );

      // megépítem a sor mezőit - adatokkal
      $data = $this->Migrations->build_mongo_data($item, $fields);

      // Related users... emiatt csak
      $data['related_users'] = [];
      // Aki mondta
      $data['related_users'][] = $data['user_id'];

      // Kiolvasom azt, amire válaszoltunk; meglesz, mert időrendben megyünk
      // és a hug_id-t rendes itteni _id-re cserélem; így már linkelődik szépen majd
      if ($data['answered_id'] > 0) {
        $answered = $this->Mongo->first('comments', [
          'hug_id' => (int)$data['answered_id']
        ]);
        if ($answered) {
          $data['answered_id'] = $answered['id'];
          // Ha már, akkor a kapcsolódó emberekhez betesszük a válaszolt komment tulaját
          $data['related_users'][] = (int)$answered['user_id'];
        } else {
          // Nem komment
          $data['answered_id'] = '';
        }
      }

      // Ha valamihez ment (sql)
      foreach ([
                 'post_id' => 'posts',
                 'folder_id' => 'folders',
                 'artpiece_id' => 'artpieces',
                 'book_id' => 'books',
               ] as $id_field => $model) {
        if (@$data[$id_field] > 0) {
          $thing = $this->DB->first($model, $data[$id_field], ['fields' => ['user_id']]);
          if ($thing && isset($thing['user_id'])) {
            $data['related_users'][] = (int)$thing['user_id'];
          }
        }
      }
      // Szerkesztésre kommentelt valaki
      if (@$data['artpiece_edits_id'] != '') {
        $thing = $this->Mongo->first('artpiece_edits', ['_id' => $data['artpiece_edits_id']]);
        if ($thing && isset($thing['user_id'])) {
          $data['related_users'][] = (int)$thing['user_id'];
        }
      }

      // Hogy egyvalaki egyszer legyen csak
      $data['related_users'] = array_unique($data['related_users']);


      if (@$data['artist_id'] > 0
        && (!isset($data['forum_topic_id']) || $data['forum_topic_id'] == 0)) {
        // Alkotói komment => Alkotói adalék
        $mongo_collection = 'artist_descriptions';
        // Ezek nem kellenek
        $data = _unset($data, ['editorial', 'answered_id']);
      } else {
        // Sima komment volt
        $mongo_collection = 'comments';
      }


      // Ha komment fájl, hozzá biggyesztjük a forrást a szöveghez:
      if ($mongo_collection == 'comments' && $item['file_id'] > 0 && $item['file_source'] != '') {
        $data['text'] .= ' (Csatolmány forrása: ' . html_entity_decode($item['file_source']) . ')';
      }


      // Beszúrom
      $comment_id = $this->Mongo->insert($mongo_collection, $data);

      // Ha volt fájl (sima kommentek és könyvborítók szépen komment csatolmányok maradnak)
      if ($item['file_id'] > 0) {
        $result = $this->oldDB->find('files', [
          'conditions' => ['id' => $item['file_id']],
          'limit' => 1
        ]);

        if (@$result[0]['id'] > 0) {
          $file = $result[0];

          $this->Migrations->comment_file($file, $comment_id);
          $this->Mongo->update($mongo_collection, [
            'files' => [
              [$item['file_id'], 'Feltöltött fájl']
            ]
          ], ['_id' => $comment_id]);
        }
      }

      // Ha válasz volt, az őst kibányászom, hogy meglegyen a thread
      if ($mongo_collection == 'comments' && @$data['answered_id'] != '') {
        $answered = $this->Mongo->first('comments', ['_id' => $data['answered_id']]);
        $parent_id = @$answered['parent_answered_id'] != '' ? $answered['parent_answered_id'] : $data['answered_id'];
        if (!is_numeric($parent_id)) {
          // Ha szám, akkor egy régi hug ID, amit nem akarunk
          $this->Mongo->update('comments', [
            'parent_answered_id' => $parent_id
          ], ['_id' => $comment_id]);
        }
      }
    }

    // Érintések kommentjei
    $items = $this->DB->find('hugs', [
      'conditions' => ['hugtype_id' => 27],
    ]);

    foreach ($items as $item) {
      if ($item['text'] == '') {
        continue;
      }

      $item['text'] = html_entity_decode($item['text']);

      $this->Mongo->insert('comments', [
        'text' => trim((string)$item['text']),
        'artpiece_id' => (int)$item['artpiece_id'],
        'commented_hug_id' => (int)$item['id'],
        'user_id' => (int)$item['user_id'],
        'created' => (int)$item['created'],
        'modified' => (int)$item['modified']
      ]);
    }

    $this->notification(__FUNCTION__);
  }


  /*
   * Műlap építése
   * minden lelógót külön fgv-ből hívunk, kivéve
   *  - vote-ok
   *
   * Ennek futtatása előtt át kell másolni a hugs táblát
   * Futtasd előtte a comment-et, hogy minden komment törlődjön!
   *
   */

  public function artpieces() {

    $this->Migrations->prepare('artpieces');

    $this->Mongo->delete('artpiece_descriptions', ['created' => ['$gt' => 0]]);
    $this->Mongo->delete('artpiece_edits', ['artpiece_id' => ['$gt' => 0]]);
    $this->Mongo->delete('artpiece_flags', ['artpiece_id' => ['$gt' => 0]]);

    $this->Mongo->delete('events', ['type_id' => ['$in' => [4,5,11,12]]]);

    ini_set('memory_limit', '10000M');

    $total_count = $this->oldDB->count('artpieces');
    //$total_count = 100;
    $step = 300;
    $pages = round(($total_count / $step) * 1.1);

    for ($i = 1; $i <= $pages; $i++) {

      if ($this->DB->count('artpieces') >= $total_count) {
        break;
      }

      $result = $this->oldDB->find('artpieces', [
        'limit' => $step,
        'page' => $i
      ]);

      if (!$result || count($result) == 0) {
        break;
        echo 'Hibás válasz.';
        $i--;
      } else {

        foreach ($result as $row) {
          // Első tulaj, ha átadogatás volt
          $creator_user_id = $row['user_id'];
          if ($row['owners'] != '') {
            $owners = json_decode($row['owners'], true);
            if (is_numeric(@$owners[0])) {
              $creator_user_id = $owners[0];
            }
          }

          // Basedesc hug, innen jön a links...
          $base_description_hug = $this->DB->first('hugs', [
            'id' => $row['base_description_hug_id']
          ]);
          $links = $base_description_hug ? $base_description_hug['links'] : '';

          // Alkotók, JSON-ban
          $artists = [];

          if ($row['artists'] != '') {
            // Kiszedem a lokális hugokból az akt artistokat...
            $artist_hugs = $this->DB->find('hugs', [
              'conditions' => [
                'artpiece_id' => $row['id'],
                'hugtype_id' => 4,
                'status_id' => 5,
              ],
              'fields' => ['artist_id', 'artist_contributor', 'profession_id', 'rank'],
              'order' => 'artist_contributor ASC, rank ASC'
            ]);

            if (count($artist_hugs) > 0) {
              $r = 0;
              foreach ($artist_hugs as $artist_hug) {
                $r++;
                $artists[] = [
                  'id' => (int)$artist_hug['artist_id'],
                  'profession_id' => $artist_hug['profession_id'],
                  'contributor' => $artist_hug['artist_contributor'] == 1
                    || in_array($artist_hug['profession_id'], [3,5]) ? 1 : 0, // építész || kivitelező => közreműködő
                  //'question' => 0,
                  'rank' => $r
                ];
              }
            }
          }
          // Alkotók --


          // DÁTUMOK
          // Összerakjuk a dátumokat
          $dates = [];
          $date_id = 0;

          // Elbontás
          if ($row['dismantling_date'] != '' && $row['dismantling_date'] != '--') {
            $date_id++;
            $dates[] = [
              'id' => $date_id,
              'date' => _cdate($row['dismantling_date']),
              'y' => _cdate($row['dismantling_date'], 'y'),
              'm' => _cdate($row['dismantling_date'], 'm'),
              'd' => _cdate($row['dismantling_date'], 'd'),
              'century' => 0,
              'type' => 'dismantle',
              'cca' => 0,
              'bc' => 0,
            ];
          }
          // Avatás
          if ($row['unveil_century'] > 0) {
            $date_id++;
            // Ha század
            $dates[] = [
              'id' => $date_id,
              'date' => '0000-00-00',
              'y' => '0000',
              'm' => '00',
              'd' => '00',
              'century' => (int)$row['unveil_century'],
              'type' => 'unveil',
              'cca' => 1,
              'bc' => 0,
            ];
          } elseif ($row['year'] > 0 && $row['unveil_date'] != '--') {
            $date_id++;
            // Ha év-dátum
            $dates[] = [
              'id' => $date_id,
              'date' => _cdate($row['unveil_date']),
              'y' => _cdate($row['unveil_date'], 'y'),
              'm' => _cdate($row['unveil_date'], 'm'),
              'd' => _cdate($row['unveil_date'], 'd'),
              'century' => 0,
              'type' => 'unveil',
              'cca' => $row['unveil_date_cca'] == 1 ? 1 : 0,
              'bc' => 0,
            ];
          } elseif ($row['year'] > 0 && $row['unveil_date'] == '--' && $row['original_unveil_date'] != '--') {
            $date_id++;
            // Van eredeti, de nincs új
            // Ekkor mentünk egy üres éves bizonytalan avatást
            $dates[] = [
              'id' => $date_id,
              'date' => '0000-00-00',
              'y' => '0000',
              'm' => '00',
              'd' => '00',
              'century' => 0,
              'type' => 'unveil',
              'cca' => 1,
              'bc' => 0,
            ];
          }
          // Eredeti
          if ($row['original_unveil_date'] != '' && $row['original_unveil_date'] != '--' && $row['original_unveil_date'] != $row['unveil_date']) {
            $date_id++;
            // Megvan
            $dates[] = [
              'id' => $date_id,
              'date' => _cdate($row['original_unveil_date']),
              'y' => _cdate($row['original_unveil_date'], 'y'),
              'm' => _cdate($row['original_unveil_date'], 'm'),
              'd' => _cdate($row['original_unveil_date'], 'd'),
              'century' => 0,
              'type' => 'unveil',
              'cca' => 0,
              'bc' => 0,
            ];
          } elseif ($row['original_unveil_date_unknown'] == 1) {
            $date_id++;
            // Van, de nem tudjuk
            // Tudományosan kiszámoljuk a századot
            $dates[] = [
              'id' => $date_id,
              'date' => '0000-0-0',
              'y' => 0,
              'm' => 0,
              'd' => 0,
              'century' => ceil(($row['year'] - 1)/100),
              'type' => 'unveil',
              'cca' => 1,
              'bc' => 0,
            ];
          }
          // Első és utolsó dátum kiolvasása
          $dates_ = $this->Arrays->sort_by_key($dates, 'date', 1);
          $first_date = '0000-0-0';
          $last_date = '0000-0-0';
          if (count($dates_) > 0) {
            reset($dates_);
            $first_key = key($dates_);
            end($dates_);
            $last_key = key($dates_);
            $first_date = _cdate($dates_[$first_key]['date']);
            $last_date = _cdate($dates_[$last_key]['date']);
          }
          // Dátumok --


          // Nyilvántartott műemlék -- paraméterből bányászunk
          // még mielőtt szétszedjük a paramétereket
          $national_heritage = strpos($row['tags'], '"118580"') !== false ? 1 : 0;


          // Új paraméter ID-kre állunk át
          $parameters = [];
          if ($row['tags'] != '') {
            // Pár csere, hogy ne jöjjenek a gyűjtők külön paraméterként
            // itt a régi ID-ket adjuk még meg
            $tags = str_replace('"22"', '"25"', $row['tags']); // murália típus
            $tags = str_replace('"120101"', '"33"', $tags); // egyéb típus
            $tags = str_replace('"46"', '"47"', $tags); // alakos => személyek
            $tags = str_replace('"16"', '"1"', $tags); // kútszobor => szobor
            $tags_array = json_decode($tags, true);
            if (count($tags_array) > 0) {
              foreach ($tags_array as $t) {
                $tag_id = (int)$t;
                $new_id = $this->Migrations->tag_to_parameter($tag_id);
                $not_needed_empty_things = [81,93,97,100];
                if ($new_id && !in_array($new_id, $parameters) && !in_array($new_id, $not_needed_empty_things)) {
                  $parameters[] = (int)$new_id;
                }
              }
            }
          }
          sort($parameters);
          $row['tags'] = _json_encode(array_unique($parameters), false, false);

          // Első megosztás ideje
          $shared = 0;
          if ($row['submitted'] > 0) {
            $shared = $row['submitted'];
          } elseif ($row['published'] > 0) {
            $shared = $row['published'];
          }

          // A nem használt elvetett legyen visszaküldött
          $row['status_id'] = $row['status_id'] == 6 ? 3 : $row['status_id'];

          // Szerkesztésre visszavett helyett sima szerk. alatt legyen
          $row['status_id'] = $row['status_id'] == 4 ? 1 : $row['status_id'];


          // Lebontottra állítjuk, ami már lebontott
          if (!in_array($row['dismantling_date'], ['--', '', '0000-00-00'])
            && $row['dismantling_date'] < date('Y-m-d')) {
            $row['artpiece_condition_id'] = 5;
          }

          // Nem MO-ra van drótozva, pedig az...
          if (($row['county_id'] > 0 || $row['district_id']) && $row['country_id'] != 101) {
            $row['country_id'] = 101;
          }

          $this->DB->insert('artpieces', $this->Migrations->build_model_fields(
            $row, [
              'keep' => ['id', 'photo_slug', 'address', 'lat', 'similar_artpieces', 'redirect_address'],
              'int' => ['artpiece_condition_id', 'artpiece_location_id', 'not_public_type_id', 'hun_related', 'not_artistic', 'photo_id', 'photo_copied', 'temporary', 'anniversary', 'local_importance', 'copy', 'reconstruction', 'country_id', 'county_id', 'district_id', 'resubmitted', 'status_id', 'user_id', 'bignumber', 'kiscell_rank', 'view_total', 'view_week', 'view_day', 'created', 'submitted', 'published', 'modified', 'updated', 'rephoto', 'photo_count'],
              'rename' => [
                'lng' => 'lon',
                'tags' => 'parameters',
                'monument_id' => 'int:ww_monument_id',
                'city_id' => 'int:place_id',
              ],
              'trim' => ['title', 'title_en', 'title_alternatives', 'place_description'],
              'custom' => [
                'national_heritage' => $national_heritage,
                'shared' => $shared,
                'superb' => (int)$row['szoborlap'],
                'superb_time' => (int)$row['szoborlap_time'],
                'creator_user_id' => (int)$creator_user_id,
                'artists' => json_encode($artists),
                'dates' => json_encode($dates),
                'first_date' => $first_date,
                'last_date' => $last_date,
                'links' => $links,
                'top_photo_count' => 16,
                'open_question' => $row['questionable'] == 1 || $row['split'] == 1 ? 1 : 0,
                'admin_memo' => $row['split'] == 1 ? 'Szét kellene szedni a műlapot.' : ''
              ]
            ]
          ));

          // Minden érdekes hug ehhez a műlaphoz
          $hugs = $this->DB->find('hugs', [
            'conditions' => [
              'artpiece_id' => $row['id'],
              'hugtype_id' => [1, 3, 4, 5, 7, 16, 17, 18, 23, 24],
            ],
            'order' => 'approved ASC'
          ]);

          // Ezekbe gyűjtünk a Mongonak
          $descriptions = [];
          $edits = [];
          $flags = [];

          // Ez visszamegy majd az artpiece-be
          $connected_artpieces = [];
          // Utód van
          if ($row['child_artpiece_id'] > 0) {
            $connected_artpieces[$row['child_artpiece_id']] = 3;
          }
          // Előzmény van
          if ($row['parent_artpiece_id'] > 0) {
            $connected_artpieces[$row['parent_artpiece_id']] = 2;
          }

          // Leírás hugok
          // külön szedni a nyelveket, ha egy hug-ban van, akkor is
          // források maradnak
          // cimek is jöhetnek itt
          foreach ($hugs as $hug) {
            $hug = $this->Migrations->clear_old_hug($hug);

            // Alapadatok

            // Státusz; a KT1-ben 2-es státuszt kap a saját HUG (vagy átadott műlapra érkező), amíg a lap
            // nem publikus. Ezt 5-re kell venni itt. A többi maradhat ott, ahol,
            // mert az vagy publikus lapon van, vagy nem saját.
            $status_id = $row['status_id'] < 5
              && ($row['user_id'] == $hug['user_id'] || $creator_user_id == $hug['user_id'])
              ? 5 : (int)$hug['status_id'];

            // Vannak a KT1-ben olyan HUG-ok, amik publikálás előttiek, gazda / létrehozó csinálta, de nem 5-ösek
            if ($row['status_id'] == 5
              && ($row['user_id'] == $hug['user_id'] || $creator_user_id == $hug['user_id'])) {
              $status_id = 5;
            }

            // Az alap 5-öst kap mindenképp
            if ($hug['id'] == $row['base_description_hug_id'] && $status_id != 5) {
              $status_id = 5;
            }

            // A huggal leváltottat és elutasítottat pedig visszatesszük arra, ami volt
            // na, szépet kavartam ezekkel a státuszokkal; most már asszem egy soros feltétel
            // is elég lenne :)
            // de ez már így marad -- ez a migráció. köszi (ma esti poloskák száma a monitoron: 2)
            if ($hug['status_id'] > 5) {
              $status_id = $hug['status_id'];
            }

            $hug['status_id'] = $status_id;

            $hug_approved = $status_id == 5 && $hug['approved'] == 0 ? (int)$hug['modified'] : (int)$hug['approved'];


            $data = [
              'artpiece_id' => (int)$row['id'],
              'hug_id' => (int)$hug['id'],
              'status_id' => (int)$status_id,
              'user_id' => (int)$hug['user_id'],
              'receiver_user_id' => (int)$hug['owner_user_id'],
              'created' => (int)$hug['created'],
              'modified' => (int)$hug['modified'],
              'approved' => $hug_approved,
            ];

            if (!in_array($hug['old_data'], ['', '[]'])) {
              $data['prev_data'] = json_decode(str_replace('"lng"', '"lon"', $hug['old_data']), true);
            }

            // Kezelő személye
            if ($hug['status_id'] > 4) {
              if ($hug['receiver_user_id'] > 0 && $hug['receiver_user_id'] != $hug['owner_user_id']) {
                // Más kezelte
                $data['manage_user_id'] = $hug['receiver_user_id'];
              } else {
                // Fogadó (gazda) kezelte
                $data['manage_user_id'] = $hug['owner_user_id'];
              }
            }

            switch ($hug['hugtype_id']) {
              case 1: // Publish --
                $flags[] = [
                  'artpiece_id' => (int)$row['id'],
                  'hug_id' => (int)$hug['id'],
                  'flag_type_id' => 1,
                  'user_id' => (int)$hug['user_id'],
                  'created' => (int)$hug['created']
                ];

                if ($hug['user_id'] == $row['user_id']) {
                  // Saját publikálás
                  $event_type_id = 4;
                } else {
                  $event_type_id = 5;
                }

                if ($hug['status_id'] == 5 && $hug['artpiece_status_id'] == 5) {
                  $this->Events->create($event_type_id, [
                    'user_id' => (int)$hug['user_id'],
                    'target_user_id' => (int)$creator_user_id, // kreátor, mert lehet, átadott
                    'artpiece_id' => (int)$row['id'],
                    'created' => (int)$hug['created'],
                  ], ['cache_delete' => false]);
                }

                break;

              case 3: // Description --
                // Külön megy:
                // Descriptions:
                // - magyar leírás
                // - angol leírás
                // Edits:
                // - egyéb szöveges módosítások
                // Magyar szöveg
                if ($hug['text'] != '' && $status_id == 5) {
                  $data['lang'] = 'HUN';
                  $data['text'] = trim($hug['text']);
                  $data['source'] = trim($hug['source']);
                  if ($hug['initial'] == 1) {
                    $data['main'] = 1;
                  }
                  if ($hug['broken_links'] != '') {
                    $data['broken_links'] = $hug['broken_links'];
                  }
                  $descriptions[] = $data;
                }

                // Angol szöveg
                if ($hug['text_en'] != '' && $status_id == 5) {
                  $data['lang'] = 'ENG';
                  $data['text'] = trim($hug['text_en']);
                  $data['source'] = trim($hug['source_en']);
                  if ($hug['initial'] == 1) {
                    $data['main'] = 1;
                  }
                  if ($hug['broken_links'] != '') {
                    $data['broken_links'] = $hug['broken_links'];
                  }
                  $descriptions[] = $data;
                }

                // @todo: nem aktív leírások edit-be? hova?...

                // Egyéb szerkesztések -- editbe
                if ($hug['title'] != '' || $hug['title_alternatives'] != '' || $hug['title_en'] != '' || $hug['links'] != '') {

                  // Ürítem, hogy ne mentsem ide is
                  unset($data['lang']);
                  unset($data['text']);
                  unset($data['text_en']);
                  unset($data['main']);
                  unset($data['broken_links']);

                  $data['edit_type_id'] = 2;

                  $data = $this->Migrations->add_data_if_not_null($hug, $data, [
                    'title' => 'title',
                    'title_alternatives' => 'title_alternatives',
                    'title_en' => 'title_en',
                    'links' => 'links',
                  ]);

                  if (($hug['title'] != '' || $hug['title_alternatives'] != '') && $hug['source'] != '') {
                    // Ha van angol cím és forrás, akkor ide is
                    // kell az angol forrás
                    $data['source'] = trim($hug['source']);
                  }

                  if ($hug['title_en'] != '' && $hug['source_en'] != '') {
                    // Ha van angol cím és forrás, akkor ide is
                    // kell az angol forrás
                    $data['source'] = isset($data['source']) ? $data['source'] . '<br />' . $hug['source_en'] : $data['source_en'];
                  }
                  $edits[] = $data;
                }

                break;

              case 4: // Artist --
                $data['artists'] = [];
                $data['artists'][] = [
                  'id' => (int)$hug['artist_id'],
                  'profession_id' => (int)$hug['profession_id'],
                  'contributor' => (int)$hug['artist_contributor'],
                  //'question' => 0,
                  'rank' => (int)$hug['rank'],
                ];

                $edits[] = $data;
                break;

              case 5: // Place --
                $data = $this->Migrations->add_data_if_not_null($hug, $data, [
                  'city_id' => 'place_id',
                  'country_id' => 'country_id',
                  'county_id' => 'county_id',
                  'district_id' => 'district_id',
                  'address' => 'address',
                  'lat' => 'lat',
                  'lng' => 'lon',
                ]);
                $edits[] = $data;
                break;

              case 7: // Details --
                // Kellenek a paraméterek az artpieces-tags táblából...
                if ($hug['parameters_changed'] == 1) {
                  $data['parameters'] = $this->Migrations->get_hug_tags($hug['id']);
                }

                if (!in_array($hug['unveil_date'], ['', '--'])) {
                  $data['unveil_date'] = $hug['unveil_date'];
                }
                if (!in_array($hug['original_unveil_date'], ['', '--'])) {
                  $data['original_unveil_date'] = $hug['original_unveil_date'];
                }
                $data = $this->Migrations->add_data_if_not_null($hug, $data, [
                  'anniversary' => 'anniversary',
                  'local_importance' => 'local_importance',
                  'not_artistic' => 'not_artistic',
                  'artpiece_location_id' => 'artpiece_location_id',
                  'artpiece_condition_id' => 'artpiece_condition_id',
                  'reconstruction' => 'reconstruction',
                  'copy' => 'copy',
                ]);

                $edits[] = $data;
                break;

              case 16: // Connected Artpieces --
                // Ezt mentjük vissza majd a lapra
                if (!isset($connected_artpieces[$hug['connected_artpiece_id']])) {
                  $connected_artpieces[$hug['connected_artpiece_id']] = 0;
                }
                $edits[] = $connected_artpieces;
                break;

              case 17: // Duplication --
                // Erről semmit se mondunk
                $data['flag_type_id'] = 3;
                $flags[] = $data;
                break;

              case 18: // Correction (régi dolog) --
                // Erről semmit se tudunk
                $edits[] = $data;
                break;

              case 23: // Badge earned --
                // Erről semmit se kell mondanunk
                $data['flag_type_id'] = 2;
                $flags[] = $data;
                break;

              case 24: // Transmission (kiről, kire) --
                $data['flag_type_id'] = 4;
                $data['old_user_id'] = (int)$hug['old_user_id'];
                $data['new_user_id'] = (int)$hug['new_user_id'];
                $flags[] = $data;

                // Esemény lesz a nyerő
                if (@$hug['status_id'] == 5) {
                  $this->Events->create(11, [
                    'target_user_id' => (int)$hug['new_user_id'],
                    'artpiece_id' => (int)$row['id'],
                    'created' => (int)$hug['approved'],
                  ], ['cache_delete' => false]);
                }

                break;
            }


            // Ha komment van benne
            if ($hug['sender_message'] != '') {
              $this->Mongo->insert('comments', [
                'hug_id' => (int)$hug['id'],
                'artpiece_id' => (int)$hug['artpiece_id'],
                'commented_hug_id' => (int)$hug['id'],
                'text' => $hug['sender_message'],
                'user_id' => (int)$hug['user_id'],
                'user_name' => $this->MC->t('users', $hug['user_id'])['name'],
                'created' => (int)$hug['created'],
                'modified' => (int)$hug['created'],
                'approved' => (int)$hug['created'],
                'no_wall' => 1,
              ]);
            }
            if ($hug['receiver_message'] != '') {
              $this->Mongo->insert('comments', [
                'hug_id' => (int)$hug['id'],
                'artpiece_id' => (int)$hug['artpiece_id'],
                'commented_hug_id' => (int)$hug['id'],
                'text' => $hug['receiver_message'],
                'user_id' => (int)$hug['receiver_user_id'],
                'user_name' => $this->MC->t('users', $hug['receiver_user_id'])['name'],
                'created' => (int)$hug['modified'],
                'modified' => (int)$hug['modified'],
                'approved' => (int)$hug['modified']
              ]);
            }
            // Kommentek--
          }


          // Mentések
          // Sztorik
          foreach ($descriptions as $item) {
            $item['artpieces'] = [$item['artpiece_id']];
            unset($item['artpiece_id']);

            // Publikálás előtti-e
            if (in_array($row['status_id'], [1,3,4,6])
              || ($row['status_id'] == 2 && $item['created'] < $row['submitted'])
              || ($row['status_id'] == 5 && (int)$row['submitted'] > 0 && $item['created'] < $row['submitted'])
              || ($row['status_id'] == 5 && (int)$row['submitted'] == 0 && $item['created'] < $row['published'])
            ) {
              $item['before_shared'] = 1;
            } else {
              $item['before_shared'] = 0;
            }

            $this->Mongo->insert('artpiece_descriptions', $item);

            // HA elfogadott, eseményt is
            if ($item['before_shared'] == 0
              && @$item['status_id'] == 5 && @$item['artpiece_id'] > 0) {
              $this->Events->create(12, [
                'user_id' => $row['user_id'],
                'target_user_id' => $item['user_id'],
                'artpiece_id' => $item['artpiece_id'],
                'created' => (int)$item['approved'],
              ], ['cache_delete' => false]);
            }

          }

          // Szerkesztések
          foreach ($edits as $item) {

            // Publikálás előtti-e
            if (in_array($row['status_id'], [1,3,4,6])
              || ($row['status_id'] == 2 && @$item['created'] < $row['submitted'])
              || ($row['status_id'] == 5 && (int)$row['submitted'] > 0 && @$item['created'] < $row['submitted'])
              || ($row['status_id'] == 5 && (int)$row['submitted'] == 0 && @$item['created'] < $row['published'])
            ) {
              $item['before_shared'] = 1;
            } else {
              $item['before_shared'] = 0;
            }

            // még ez is.
            if (isset($item['hug_id']) && @$item['approved'] > 0 && $item['status_id'] == 5
              && $item['approved'] <= $row['published']) {
              $item['before_shared'] = 1;
            }

            if ($item['before_shared'] == 0) {

              $id_ = $this->Mongo->insert('artpiece_edits', $item);

              // HA elfogadott, eseményt is
              if ($item['before_shared'] == 0
                && @$item['status_id'] == 5 && @$item['artpiece_id'] > 0) {
                $this->Events->create(12, [
                  'user_id' => $row['user_id'],
                  'target_user_id' => $item['user_id'],
                  'artpiece_id' => $item['artpiece_id'],
                  'created' => (int)$item['approved'],
                ], ['cache_delete' => false]);
              }

              if (!isset($item['hug_id'])) {
                continue;
              }

              // Frissítjük a kapcsolódó kommenteket
              $this->Mongo->update('comments', [
                'artpiece_edits_id' => $id_
              ], [
                'commented_hug_id' => $item['hug_id']
              ]);

            }
          }

          foreach ($flags as $item) {
            $this->Mongo->insert('artpiece_flags', $item);
          }

          if (count($connected_artpieces) > 0) {
            $this->DB->update('artpieces', [
              'connected_artpieces' => json_encode($connected_artpieces)
            ], [
              'id' => $row['id']
            ]);
          }
        }
      }

      echo $i * $step . ' kész' . PHP_EOL;
    }


    // Publikációs idők egymásutánisága
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => ['published >' => 0],
      'order' => 'published ASC',
      'fields' => ['id', 'published']
    ]);

    $last = ['id' => 0, 'published' => 0];
    foreach ($artpieces as $artpiece) {
      if ($artpiece['published'] <= $last['published']) {
        $artpiece['published'] = $last['published'] + rand(1,15);
        $this->DB->update('artpieces', ['published' => $artpiece['published']], $artpiece['id']);
      }
      $last = $artpiece;
    }

    echo 'artpieces kész' . PHP_EOL;

    $this->Migrations->aftermath('artpieces');
    $this->Artpieces->delete_artpieces_cache();

    // Fullos átvétel
    $this->Mongo->insert('jobs', [
      'class' => 'artpieces',
      'action' => 'generate',
      'created' => date('Y-m-d H:i:s'),
    ]);

    $this->notification(__FUNCTION__);
  }


  // KÉP ÖSSZESZEDŐ LOGIKA
  // csak artpieces után
  public function artpiece_photos() {
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => "photos IS NULL OR photos = '' OR photos = '[]'",
      'fields' => ['id', 'photo_slug']
    ]);

    foreach ($artpieces as $artpiece) {
      $photos = $this->DB->find('photos',[
        'conditions' => ['artpiece_id' => $artpiece['id']],
        'fields' => ['id', 'slug', 'rank', 'other'],
        'order' => 'rank ASC'
      ]);

      // Sajnos sokan csináltak olyan műlapot, hogy minden képre rájelölték, hogy más...
      // ezekről le kell venni a jelölést, mert hibásak
      $others = 0;
      $wrong_others = false;
      foreach ($photos as $photo) {
        if ($photo['other'] == 1) {
          $others++;
        }
      }
      if ($others == count($photos)) {
        $wrong_others = true;
      }


      $photos_array = [];
      if (count($photos) > 0) {
        $rank = 0;
        foreach ($photos as $photo) {
          $rank++;
          // Ezzel a trükkel vesszük le garantáltan a kezdőlapról az adalékokat
          $fact_rank = !$wrong_others && $photo['other'] == 1 ? $rank + 1000 : $rank;
          $photos_array[] = [
            'id' => (int)$photo['id'],
            'slug' => $photo['slug'],
            'rank' => $fact_rank,
            'top' => $fact_rank <= 18 ? 1 : 0
          ];
        }
      }
      $this->DB->update('artpieces', ['photos' => _json_encode($photos_array)], $artpiece['id']);
    }

    echo 'artpiece_photos kész' . PHP_EOL;

    $this->notification(__FUNCTION__);
  }



  /**
   * Helyreteszi a fotókat, hogy melyik kinek szól
   */
  public function photo_receivers() {
    // Pörgetjük végig még egyszer hogy a fotók kikhez tartoznak,
    // mert itt már van műlapunk
    $photos = $this->DB->find('photos', array(
      'conditions' => 'receiver_users LIKE \'["0"]\' OR receiver_users LIKE \'[""]\'',
      'fields' => ['id', 'artpiece_id']
    ));
    foreach ($photos as $photo) {
      // képet fogadó user
      $artpiece = $this->MC->t('artpieces', $photo['artpiece_id']);
      $receiver_users = _json_encode([(string)$artpiece['user_id']], false, false);
      $this->DB->update('photos', array(
        'receiver_users' => $receiver_users,
        'before_shared' => in_array($artpiece['status_id'], [2,5]) ? 0 : 1,
      ), $photo['id']);
    }

    echo 'photo_receivers kész' . PHP_EOL;

    $this->notification(__FUNCTION__);
  }


  /*
   * Simán áthúzom a fotókat
   */

  public function photos() {
    $this->Migrations->prepare('photos');

    $total_count = $this->oldDB->count('photos');
    $step = 5000;
    $pages = round(($total_count / $step) * 1.1);

    for ($i = 1; $i <= $pages; $i++) {

      if ($this->DB->count('photos') >= $total_count) {
        break;
      }

      $result = $this->oldDB->find('photos', [
        'limit' => $step,
        'page' => $i
      ]);

      if (!$result || count($result) == 0) {
        echo 'Hibás válasz.';
        $i--;
      } else {

        $insert_rows = [];

        foreach ($result as $row) {
          $year = '';
          // Évszám kibontása
          if (preg_match('/\b\d{4}\b/', $row['text'] . ' ' . $row['source'], $matches)) {
            $year = $matches[0];
            // realitás
            if ($year < 1900 || $year > 2018) {
              $year = '';
            }
          }

          // képet fogadó user
          $artpiece = $this->MC->t('artpieces', $row['artpiece_id']);
          $receiver_users = _json_encode([(string)$artpiece['user_id']], false, false);

          // Alkotói képek
          $first_artist_id = $artist_id = $sign_artist_id = 0;

          // Első alkotó kiolvasása
          $artists = _json_decode($artpiece['artists']);
          if (is_array($artists) && isset($artists[0]['id'])) {
            $first_artist_id = $artists[0]['id'];
          }

          if ($row['artist'] == 1) {
            $artist_id = $row['artist_id'] > 0 ? $row['artist_id'] : $first_artist_id;
          }
          if ($row['sign'] == 1) {
            $sign_artist_id = $row['artist_id'] > 0 ? $row['artist_id'] : $first_artist_id;
          }

          $insert_rows[] = $this->Migrations->build_model_fields(
            $row, [
              'keep' => ['id', 'slug', 'original_slug', 'exif_json'],
              'trim' => ['source'],
              'int' => ['status_id', 'artpiece_id', 'other', 'archive', 'artist', 'sign', 'unveil', 'license_type_id', 'special_license', 'missing_original', 'user_id', 'rank', 'created', 'modified', 'approved'],
              'rename' => [],
              'custom' => [
                // https://hu.wikipedia.org/wiki/Exif 2002 április előtt nem valós
                // jövőbeli 12 órás, időeltolódás miatt
                'exif_taken' => $row['exif_taken'] < strtotime('2002-04-01')
                  || $row['exif_taken'] > strtotime('+12 hours') ? 0 : $row['exif_taken'],
                'before_shared' => $artpiece['status_id'] == 5 ? 0 : 1,
                'artpieces' => _json_encode([$row['artpiece_id']], false, false),
                'text' => is_numeric($row['text']) && trim($row['text']) < 100 ? '' : html_entity_decode(trim($row['text'])),
                'copied' => $row['copied_gen_time'] > 0 ? $row['copied_gen_time'] : 0,
                'artist_id' => $artist_id,
                'sign_artist_id' => $sign_artist_id,
                'year' => $year,
                'added' => @$artpiece['user_id'] != $row['user_id'] ? 1 : 0,
                'receiver_users' => $receiver_users,
              ]
            ]
          );
        }

        $this->DB->insert_multi('photos', $insert_rows);
      }
    }

    $this->Migrations->aftermath('photos');
    $this->notification(__FUNCTION__);
  }


  public function photo_events() {
    $this->Mongo->delete('events', ['photo_count' => 1]);

    $photos = $this->DB->find('photos', [
      'conditions' => "status_id = 5 AND receiver_users != CONCAT('[\"', user_id, '\"]') "
        . " AND receiver_users != CONCAT('[', user_id, ']') AND user_id > 0",
      'fields' => ['id', 'artpiece_id', 'slug', 'user_id', 'receiver_users', 'approved', 'created'],
      'order' => 'created',
    ]);

    foreach ($photos as $photo) {
      $artpiece = $this->MC->t('artpieces', $photo['artpiece_id']);

      // A publikálás előttiekről nem tolunk eseményt
      if (($artpiece['published'] > 0 && $photo['approved'] <= $artpiece['published'])
        || $artpiece['published'] == 0) {
        continue;
      }

      $data = [
        'user_id' => (int)$photo['user_id'],
        'artpiece_id' => (int)$photo['artpiece_id'],
        'photo_count' => 1,
        'photos' => [['id' => $photo['id'], 'slug' => $photo['slug']]],
        'created' => $photo['created'],
      ];

      $receiver = _json_decode($photo['receiver_users'])[0];
      if ($receiver > 0) {
        $data['target_user_id'] = (int)$receiver;
      }

      $this->Events->create(6, $data, ['cache_delete' => false]);
    }

    $this->notification(__FUNCTION__);
  }


  /*
   * Ezeket húzom: 13,14,27
   */

  public function hugs() {
    // Törlöm az eddig beszúrtakat; a szavazásokat is itt, mert oda innentől
    // kerül majd még a publikálási szavazás, a példás lap, a nyitott kérdés - stb.
    // de nem baj, ha annak a historyját nem húzzuk át.
    $this->Mongo->delete('artpiece_votes', ['user_id' => ['$gt' => 0]]);
    $this->Mongo->delete('artpiece_hugs', ['user_id' => ['$gt' => 0]]);

    $this->Mongo->delete('events', ['type_id' => 7]);

    $hugs = $this->DB->find('hugs', [
      'conditions' => ['hugtype_id' => [13, 14, 27]]
    ]);
    foreach ($hugs as $hug) {
      switch ($hug['hugtype_id']) {

        case 13: // Nice work
          if ($hug['status_id'] == 5) {
            $this->Mongo->insert('artpiece_votes', [
              'type_id' => 3,
              'hug_id' => (int)$hug['id'],
              'artpiece_id' => (int)$hug['artpiece_id'],
              'user_id' => (int)$hug['user_id'],
              'user_name' => $this->MC->t('users', $hug['user_id'])['name'],
              'created' => (int)$hug['created'],
            ]);
          }
          break;

        case 27: // Touch, ami már hug ;]
          // Kommentet a commentsben kezelem,
          // hogy ne kelljen egymás után futtatni őket
          if ($hug['status_id'] == 5) {
            $this->Mongo->insert('artpiece_hugs', [
              'hug_id' => (int)$hug['id'],
              'id' => (int)$hug['artpiece_id'],
              'user_id' => (int)$hug['user_id'],
              'created' => (int)$hug['created']
            ]);
            $this->Events->create(7, [
              'user_id' => (int)$hug['user_id'],
              'target_user_id' => (int)$hug['owner_user_id'],
              'artpiece_id' => (int)$hug['artpiece_id'],
              'created' => (int)$hug['approved'],
            ], ['cache_delete' => false]);
          }
          break;
      }
    }
    $this->notification(__FUNCTION__);
  }



  /*
   * 10-es hugtype
   * tagtype (8 common, 9 user, 10 custom (múlt kincse), 11 bin)
   * 445 darab
   *
   * Csak artpieces után futtatható
   *
   */
  public function sets() {

    /*$artpieces = $this->DB->find('artpieces', [], ['fields' => ['id']]);
    $connected_sets = $this->Mongo->find_array('sets', ['artpieces' => 31]);
    debug($connected_sets);
    exit;*/


    // Törlöm az eddig beszúrtakat
    $this->Mongo->delete('sets');

    // régi => új (DB.set_types -ban van az új)
    $set_types = [
      8 => 1, // Közös gyűjtemény
      9 => 2, // Saját gyűjtemény
      10 => 3, // Múlt kincse
      11 => 4, // Raklap
    ];

    // 5 => műlap kapcsolás

    // Ebbe gyűjtöm a műlapgyűjteményeket
    $artpiece_sets = [];

    //foreach ([8, 9, 10, 11] as $tagtype_id) {
    foreach ([8, 9, 10] as $tagtype_id) {
      // kiszedjük a tageket
      $tags = $this->oldDB->find('tags', [
        'conditions' => ['tagtype_id' => $tagtype_id]
      ]);

      if (!$tags) {
        echo 'Hibás válasz.';
      } else {

        foreach ($tags as $tag) {
          if ($tag['id'] == 107) {
            // A Rajna-neszták archívumot nem vesszük át
            // más kiemelési megoldást keresünk hozzá
            continue;
          }

          $set_artpieces = [];

          // Kiszedem a beszúrásokat
          $inserts = $this->oldDB->find('artpieces_tags', [
            'conditions' => [
              'tag_id' => $tag['id'],
              'status_id' => 5
            ]
          ]);
          if ($inserts) {
            foreach ($inserts as $insert) {
              $set_artpieces[] = $this->Migrations->build_mongo_data($insert, [
                'artpiece_id',
                'user_id',
                'created'
              ]);
            }
          }

          $photo = $tag['photo_id'] > 0
            ? $this->DB->first('photos', $tag['photo_id'], ['fields' => 'slug']) : ['slug' => ''];


          if ($tag['id'] == 120493) {
            // Absztrakt Kiscelli bepakolása 120493 gyűjteménybe
            $artpiece_ids = $this->DB->find('artpieces', [
              'type' => 'fieldlist',
              'conditions' => [
                'kiscell_rank >' => 0,
              ]
            ]);
            $set_artpieces = [];
            foreach ($artpiece_ids as $artpiece_id) {
              $set_artpieces[] = [
                'artpiece_id' => $artpiece_id,
                'user_id' => 1,
                'created' => 1496268000,
              ];
            }
          }


          $data = [
            'tag_id' => (int)$tag['id'],
            'set_type_id' => (int)$set_types[$tagtype_id],
            'name' => trim($tag['name']),
            'description' => trim($tag['description']),
            'photo_slug' => $photo['slug'],
            'user_id' => (int)$tag['user_id'],
            'created' => (int)$tag['created'],
            'updated' => (int)$tag['last_artpiece_time'],
            'artpieces' => $set_artpieces
          ];

          $oid = $this->Mongo->insert('sets', $data);

          // A raklapokat külön kezelem; könyvjelző lesz belőle.
          // A múlt kincse a 3-as...?
          if ($set_types[$tagtype_id] <= 2) {
            foreach ($set_artpieces as $set_artpiece) {
              if (!isset($artpiece_sets[$set_artpiece['artpiece_id']])
                || !in_array($oid, $artpiece_sets[$set_artpiece['artpiece_id']])) {
                $artpiece_sets[$set_artpiece['artpiece_id']][] = $oid;
              }
            }
          }

          // @todo raklap => "Műlapok" mappába tett könyvjelző
          // @todo múlt kincsét el kellene tenni, mert ha rámentenek egy lapra,
          // ahol gyűjteményt módosítanak, és volt múlt kincse, kiesik az
        }
      }
    }

    foreach ($artpiece_sets as $artpiece_id => $set) {
      $this->DB->update('artpieces', [
        'connected_sets' => _json_encode($set, false, false)
      ], $artpiece_id);
    }

    $this->notification(__FUNCTION__);
  }


  /**
   * Paraméterek átvétele
   * A translate opció miatt ezt csak a régiről tudjuk átvenni Cake-en keresztül, utána már nem
   */
  public function parameters() {
    // Itt csak ennyi van, mert a régi ID-t nem primary key-ként használjuk
    $this->DB->query("TRUNCATE parameters");

    $result = $this->Migrations->get_model('Tag', ['translate' => 1], [
      'tagtype_id' => [1, 2, 3, 4, 5, 6, 7]
    ]);

    if (!$result) {
      echo 'Hibás válasz.';
    } else {

      foreach ($result as $row) {
        // Nem kapcsolható tört/vall. ne látszódjon
        if (in_array($row['id'], [88,100])) {
          $row['hidden'] = 1;
        }

        $this->DB->insert('parameters', $this->Migrations->build_model_fields(
          $row, [
            'keep' => [
              'name'
            ],
            'int' => [
              'rank', 'parent_id', 'kill_id', 'check_pair_id', 'default_checked', 'not_enough', 'will_be_merged_into', 'hidden', 'highlighted'
            ],
            'rename' => [
              'id' => 'int:old_id',
              'tagtype_id' => 'int:parameter_group_id',
              'default_child' => 'int:default_child_id'
            ],
            'custom' => []
          ]
        ));
      }
      // Kis trükk, hogy végigmegyünk, és a régi ID-ket átírjuk az újra
      $parameters = $this->DB->query("SELECT * FROM parameters");
      $on = [];
      foreach ($parameters as $p) {
        $on[$p['old_id']] = $p['id'];
      }
      foreach ($parameters as $p) {
        $updates = [
          'parent_id' => isset($on[$p['parent_id']]) ? (int)$on[$p['parent_id']] : 0,
          'default_child_id' => isset($on[$p['default_child_id']]) ? (int)$on[$p['default_child_id']] : 0,
          'kill_id' => isset($on[$p['kill_id']]) ? (int)$on[$p['kill_id']] : 0,
          'check_pair_id' => isset($on[$p['check_pair_id']]) ? (int)$on[$p['check_pair_id']] : 0,
          'will_be_merged_into' => isset($on[$p['will_be_merged_into']]) ? (int)$on[$p['will_be_merged_into']] : 0,
        ];
        $this->DB->update('parameters', $updates, ['id' => $p['id']]);
      }
    }

    $this->notification(__FUNCTION__);
  }


  /*
   *
   * Üzenetek teljes átvétele.
   * Logika:
   * 1. zazen.kt2-ben: truncate table new_messages;
   * 2. lefuttatom ezt: http://demo.kozterkep.hu/godmodules/message_export
   * 3. átmásolom a táblát
   * 4. ráengedem a messages-t (sql => mongo, thread építés)
   *
   */

  /**
   * Mongoba pakolás és a thread építése
   */
  public function messages() {

    $this->Mongo->delete('conversations', ['updated' => ['$gt' => 0]]);
    // tyiha.
    ini_set('memory_limit', '5000M');

    $this->DB->update('new_messages', ['done' => 0], ['done' => 1]);

    $result = $this->DB->find('new_messages', [
      'conditions' => "sending_time > 0"
      //  . "AND ((sender_user_id = 1 AND receiver_user_id = 2456) "
      //  . "OR (sender_user_id = 2456 AND receiver_user_id = 1))"
      ,
      'fields' => ['id'],
      'order' => 'id',
    ]);

    foreach ($result as $item) {
      // Újra kiolvasom, hátha már kezeltem
      $message = $this->DB->find_by_id('new_messages', $item['id']);

      if ($message['done'] == 1 || $message['sender_user_id'] == 0 || $message['receiver_user_id'] == 0) {
        continue;
      }

      $conversation_array = [
        'users' => [$message['sender_user_id'], $message['receiver_user_id']],
        'user_names' => [
          $this->MC->t('users', $message['sender_user_id'])['name'],
        $this->MC->t('users', $message['receiver_user_id'])['name'],
        ],
        'started' => $message['sending_time'],
        'updated' => $message['sending_time'],
        'subject' => $message['subject'] == '' ? '...' : strip_tags($message['subject']),
        'read' => [$message['sender_user_id'], $message['receiver_user_id']],
        'favored' => [],
        'archived' => [],
        'trashed' => $message['deleted'] == 1 ? [$message['sender_user_id']] : [],
        'deleted' => $message['deleted'] == 1 ? [$message['sender_user_id']] : [],
      ];


      // Rájegyzem a tesóra, hogy done, hogy a thread-be bele se kerüljön
      $this->DB->update('new_messages', ['done' => 1], $message['brother_message_id']);

      // Kiszedem az azonos (és RE: tárgy) tárgyú leveleket ugyanazon személyek közt (köztük ezt is)
      $thread = $this->DB->find('new_messages', [
        'conditions' => "((receiver_user_id = " . $message['receiver_user_id'] . " AND sender_user_id = " . $message['sender_user_id'] . ") OR (receiver_user_id = " . $message['sender_user_id'] . " AND sender_user_id = " . $message['receiver_user_id'] . ")) AND sending_time >= " . $message['sending_time'] . " AND (subject LIKE '" . addslashes($message['subject']) . "' OR subject LIKE 'RE: " . addslashes($message['subject']) . "' OR subject LIKE 'RE: RE: " . addslashes($message['subject']) . "') AND done = 0 AND deleted = 0",
        'fields' => ['id'],
        'order' => 'id'
      ]);

      $conversation_array['messages'] = [];

      $read = $archived = $favored = [];
      $full_body = '';

      // Ezt feltételezzük alapból, aztán később visszaállítjuk hipphopp, ha megvan a címzettnél is
      $only_sender_has_this = true;

      // megépítem a thread-et és közben mindenre rájelölöm, hogy done
      foreach ($thread as $thread_message) {
        // Itt is ki kell venni, nincs-e még megcsinálva a threadben...
        $act_message = $this->DB->find_by_id('new_messages', $thread_message['id']);

        if ($act_message['done'] == 1) {
          continue;
        }

        // Rájegyzem a tesóra, hogy done, hogy a thread-be bele se kerüljön
        $this->DB->update('new_messages', ['done' => 1], $act_message['id']);
        $this->DB->update('new_messages', ['done' => 1], $act_message['brother_message_id']);

        // Kell a tesó az olvasottság, kedvenc, archiváltság miatt
        $act_brother = $this->DB->first('new_messages', [
          'id' => $act_message['brother_message_id'],
          'deleted' => 0,
        ]);

        $message_id = uniqid();

        // Hacsak nem FW-vel kezdődik a tárgy, akkor csak a legutolsó body kell, és eddig ugye összefűzött volt
        if (strpos($act_message['subject'], 'FW: ') !== 0) {
          $act_message['body'] = str_replace(['-------- Er', '--------Er'], '----------- Er', $act_message['body']);
          $body = explode('----------- Er', $act_message['body']);
          // Picit még takarítunk
          $body = str_replace('

---', '', $body);
        }

        $message_deleted = [];

        if ($act_message['receiver'] == 1 && $act_message['deleted'] == 1
          && !in_array($act_message['receiver_user_id'], $message_deleted)) {
          $message_deleted[] = $act_message['receiver_user_id'];
        }
        if ($act_message['sender'] == 1 && $act_message['deleted'] == 1
          && !in_array($act_message['sender_user_id'], $message_deleted)) {
          $message_deleted[] = $act_message['sender_user_id'];
        }
        if ($act_brother['receiver'] == 1 && $act_brother['deleted'] == 1
          && !in_array($act_brother['receiver_user_id'], $message_deleted)) {
          $message_deleted[] = $act_brother['receiver_user_id'];
        }
        if ($act_brother['sender'] == 1 && $act_brother['deleted'] == 1
          && !in_array($act_brother['sender_user_id'], $message_deleted)) {
          $message_deleted[] = $act_brother['sender_user_id'];
        }

        $conversation_array['messages'][] = [
          'mid' => $message_id,
          'user_name' => $this->MC->t('users', $act_message['sender_user_id'])['name'], // hogy ne kelljen joinolni...
          'user_id' => $act_message['sender_user_id'],
          'created' => $act_message['sending_time'],
          'body' => trim(strip_tags($body[0])),
          'deleted' => $message_deleted
        ];
        // Itt kezeljük ezeket is
        if ($act_message['file_id'] > 0) {
          if (!isset($conversation_array['files'])) {
            $conversation_array['files'] = [];
          }

          $file_name = $this->Migrations->message_file($act_message, $message_id, $act_message['file_id']);

          $conversation_array['files'] = [$message_id => [[$act_message['file_id'], $file_name]]];
        }

        // Kedvencelések
        list($read, $favored, $archived) = $this->Migrations->message_flags($act_message, $read, $favored, $archived);
        list($read, $favored, $archived) = $this->Migrations->message_flags($act_brother, $read, $favored, $archived);

        $full_body .= ' ' . trim(strip_tags($body[0]));

        if ($act_message['receiver'] == 1 || @$act_brother['receiver'] == 1) {
          $only_sender_has_this = false;
        }
      }

      if (count($thread) == 0) {
        continue;
      }

      $conversation_array['read'] = $read;
      $conversation_array['favored'] = $favored;
      $conversation_array['archived'] = $archived;

      $conversation_array['words'] = $this->Arrays->words_array($full_body);

      // egy levél és címzett törölte
      if (count($thread) == 2) {
        if ($act_message['deleted'] == 1) {
          $conversation_array['trashed'] = $act_message['sender'] == 1
            ? [$act_message['receiver_user_id']] :  [$act_message['receiver_user_id']];
        }
        if ($act_brother['deleted'] == 1) {
          $conversation_array['trashed'] = $act_brother['sender'] == 1
            ? [$act_brother['receiver_user_id']] :  [$act_brother['receiver_user_id']];
        }

        $conversation_array['deleted'] = $conversation_array['trashed'];

        foreach ($conversation_array['deleted'] as $user) {
          if (!in_array($user, $conversation_array['read'])) {
            $conversation_array['read'][] = $user;
          }
        }
      }

      // 1 levél van, vagy csak a sendernél van meg => címzett törlés lesz
      if ((count($thread) == 1 || $only_sender_has_this) && $act_message['sender'] == 1) {
        if (!in_array($act_message['receiver_user_id'], $conversation_array['read'])) {
          $conversation_array['read'][] = $act_message['receiver_user_id'];
        }
        $conversation_array['deleted'] = [$act_message['receiver_user_id']];
      }

      // Egy levél van, nincs tesó és csak a receivernél van meg => feladó törlés lesz
      if (count($thread) == 1 && $act_message['receiver'] == 1) {
        if (!in_array($act_message['sender_user_id'], $conversation_array['read'])) {
          $conversation_array['read'][] = $act_message['sender_user_id'];
        }
        $conversation_array['deleted'] = [$act_message['sender_user_id']];
      }

      // Az utolsó küldőnél olvasottnak kell lennie
      if (!in_array($act_message['sender_user_id'], $conversation_array['read'])) {
        $conversation_array['read'][] = $act_message['sender_user_id'];
      }

      // Gépház legyen olvasott
      if ($act_message['sender_user_id'] == 2 && !in_array($act_message['receiver_user_id'], $conversation_array['read'])) {
        $conversation_array['read'][] = $act_message['receiver_user_id'];
      }

      // Ha valakinél minden üzenet törölt, akkor törölt a folyam
      $all_deleted = [
        $message['sender_user_id'] => true,
        $message['receiver_user_id'] => true
      ];
      foreach ($conversation_array['messages'] as $m) {
        if (!in_array($message['sender_user_id'], $m['deleted'])) {
          $all_deleted[$message['sender_user_id']] = false;
        }
        if (!in_array($message['receiver_user_id'], $m['deleted'])) {
          $all_deleted[$message['receiver_user_id']] = false;
        }
      }
      if ($all_deleted[$message['sender_user_id']]
        && !in_array($message['sender_user_id'], $conversation_array['deleted'])) {
        $conversation_array['deleted'][] = $message['sender_user_id'];
      }
      if ($all_deleted[$message['receiver_user_id']]
        && !in_array($message['receiver_user_id'], $conversation_array['deleted'])) {
        $conversation_array['deleted'][] = $message['receiver_user_id'];
      }

      $conversation_array['trashed'] = $conversation_array['deleted'];

      if (count($thread) > 0) {
        $this->Mongo->insert('conversations', $conversation_array);
      }
    }

    $this->notification(__FUNCTION__);
  }


  public function books() {
    $this->Migrations->prepare('books');
    $result = $this->oldDB->find('books');

    if ($result) {
      foreach ($result as $row) {
        $this->DB->insert('books', $this->Migrations->build_model_fields(
          $row, [
            'keep' => [
              'title', 'writers', 'publisher', 'publishing_place', 'owners', 'user_id'
            ],
            'int' => [
              'id', 'page_number', 'published', 'cover_file_id', 'owner_count', 'created', 'modified', 'user_id',
            ],
            'rename' => [],
            'custom' => []
          ]
        ));

        // Ha volt fájl (sima kommentek és könyvborítók szépen komment csatolmányok maradnak)
        if ($row['cover_file_id'] > 0) {
          $files = $this->oldDB->find('files', [
            'conditions' => ['id' => $row['cover_file_id']],
            'limit' => 1
          ]);

          if (@$files[0]['id'] > 0) {
            $file = $files[0];

            $this->Migrations->book_file($file, $row['id']);
          }
        }

      }
    }

    $this->Migrations->aftermath('books');

    $this->notification(__FUNCTION__);
  }


  public function ww_monuments() {

    $this->Migrations->prepare('ww_monuments');


    $result = $this->oldHE->find('parameters');
    $this->Mongo->delete('ww_parameters', [
      'parameter_id' => ['$gt' => 0]
    ]);
    if ($result) {
      foreach ($result as $row) {
        $this->Mongo->insert('ww_parameters', [
          'parameter_id' => (int)$row['id'],
          'parameter_type_id' => (int)$row['parameter_type_id'],
          'description' => $row['description'],
        ]);
      }
    }

    $result = $this->oldHE->find('photos');
    $this->Mongo->delete('ww_photos', [
      'monument_id' => ['$gt' => 0]
    ]);
    if ($result) {
      foreach ($result as $row) {
        $this->Mongo->insert('ww_photos', [
          'photo_id' => (int)$row['id'],
          'monument_id' => (int)$row['monument_id'],
          'slug' => $row['slug'],
          'original_path' => $row['original_path'],
        ]);
      }
    }

    $result = $this->oldHE->find('additions');
    $this->Mongo->delete('ww_comments', [
      'monument_id' => ['$gt' => 0]
    ]);
    if ($result) {
      foreach ($result as $row) {
        $this->Mongo->insert('ww_comments', [
          'comment_id' => (int)$row['id'],
          'monument_id' => (int)$row['monument_id'],
          'user_id' => (int)$row['user_id'],
          'created' => (int)$row['created'],
          'text' => $row['text'],
        ]);
      }
    }


    $result = $this->oldHE->find('monuments');
    if ($result) {
      foreach ($result as $row) {
        $this->DB->insert('ww_monuments', $this->Migrations->build_model_fields(
          $row, [
            'keep' => [
              'hm_him', 'address', 'unveil_text', 'cover_photo_path',
            ],
            'int' => [
              'id', 'district_id', 'county_id', 'country_id', 'artpiece_id', 'cover_photo_id', 'type_id', 'unveil_year', 'before_45', 'renewed', 're_established', 're_unveiled', 'moved', 'damaged', 'alternative_unveil_year', 'ready_year', 'dead_count', 'enrolled_count'
            ],
            'rename' => [
              'artists_full_json' => 'artists_search',
              'city_id' => 'int:place_id',
              'city' => 'place_name',
            ],
            'custom' => [
              'topics' => _json_encode(_json_decode($row['topics_json']), false, false),
              'sources' => _json_encode(_json_decode($row['sources_json']), false, false),
              'connected_buildings' => _json_encode(_json_decode($row['connected_buildings_json']), false, false),
              'connected_monuments' => _json_encode(_json_decode($row['connected_monuments_json']), false, false),
              'unveilers' => _json_encode(_json_decode($row['unveilers_json']), false, false),
              'states' => _json_encode(_json_decode($row['states_json']), false, false),
              'corps' => _json_encode(_json_decode($row['corps_json']), false, false),
              'maintainers' => _json_encode(_json_decode($row['maintainers_json']), false, false),
              'artists' => _json_encode(_json_decode($row['artists_json']), false, false),
              'second_artists' => _json_encode(_json_decode($row['second_artists_json']), false, false),
              'creator_artists' => _json_encode(_json_decode($row['creator_artists_json']), false, false),
              'founders' => _json_encode(_json_decode($row['founders_json']), false, false),
              'nationalities' => _json_encode(_json_decode($row['nationalities_json']), false, false),
              'connected_events' => _json_encode(_json_decode($row['connected_events_json']), false, false),
              'symbols' => _json_encode(_json_decode($row['symbols_json']), false, false),
            ]
          ]
        ));
      }
    }

    $this->Migrations->aftermath('ww_monuments');
    $this->notification(__FUNCTION__);
  }




  // ========================  Szinkronok, amik nem törölnek


  /**
   * Műlapok egyes mezőit szinkronizálja
   */
  public function _artpieces() {

    $total_count = $this->oldDB->count('artpieces');
    $step = 5000;
    $pages = round(($total_count / $step) * 1.1);

    for ($i = 1; $i <= $pages; $i++) {

      if ($this->DB->count('artpieces') >= $total_count) {
        break;
      }

      $result = $this->oldDB->find('artpieces', [
        'limit' => $step,
        'page' => $i
      ]);

      if (!$result || count($result) == 0) {
        break;
        echo 'Hibás válasz.';
        $i--;
      } else {

        foreach ($result as $row) {
          $updates = [];

          // Lebontottra állítjuk, ami már lebontott
          if (!in_array($row['dismantling_date'], ['--', '', '0000-00-00'])
            && $row['dismantling_date'] < date('Y-m-d')) {
            $updates['artpiece_condition_id'] = 5;
          } else {
            $updates['artpiece_condition_id'] = $row['artpiece_condition_id'];
          }

          $this->DB->update('artpieces', $updates, $row['id']);
        }
      }
    }

    $this->notification(__FUNCTION__);
  }



  public function words() {
    $this->DB->query("TRUNCATE words");
    $result = $this->oldDB->find('words');
    if ($result) {
      foreach ($result as $row) {
        $this->DB->insert('words', $this->Migrations->build_model_fields(
          $row, [
            'keep' => [
              'hash', 'title', 'artist'
            ],
            'int' => [
              'photo_id', 'artpiece_id', 'user_id', 'created', 'modified', 'active',
            ],
            'rename' => [],
            'custom' => [
              'text' => html_entity_decode($row['text'])
            ]
          ]
        ));
      }
    }

    $this->notification(__FUNCTION__);
  }


  public function artpeople() {
    $result = $this->oldDB->find('artpeople');
    if (!$result || count($result) == 0) {
      echo 'Hibás válasz.';
      $this->notification(__FUNCTION__, 1);
    } else {
      $this->Mongo->delete('artpeople', ['person_id' => ['$gt' => 0]]);
      foreach ($result as $row) {
        if ($row['name'] == '') {
          continue;
        }
        $this->Mongo->insert('artpeople', [
          'person_id' => (int)$row['id'],
          'name' => $row['name'],
          'first_name' => $row['firstname'],
          'last_name' => $row['surname'],
          'subtitle' => $row['subtitle'],
          'text' => html_entity_decode($row['text']),
        ]);
      }
    }
    $this->notification(__FUNCTION__);
  }

  public function newsletters () {
    $this->DB->query("TRUNCATE newsletters");

    $results = $this->oldDB->find('email_newsletters', [
      'conditions' => [
        'user_id' => 2,
        'sending_time >' => 0,
        'archived' => 0,
        'body <>' => '',
      ],
      'order' => 'sending_time ASC',
    ]);

    foreach ($results as $row) {

      if ($row['body'] == '') {
        continue;
      }

      $this->DB->insert('newsletters', [
        'weekly_harvest' => 1,
        'subject' => $row['subject'],
        'body' => htmlentities($row['body']),
        'template' => '',
        'sendable' => 1,
        'sent' => $row['sending_time'],
        'receiver_count' => $this->oldDB->count('email_newsletters', [
          'sending_time >' => $row['sending_time']-(60*60),
          'sending_time <' => $row['sending_time']+(10*60*60),
        ]),
      ]);
    }

    $this->notification(__FUNCTION__);
  }
}
