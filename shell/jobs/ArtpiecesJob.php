<?php
class ArtpiecesJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   *
   * Mongo-ba upsertel + törli a view cache-t.
   * Ha nincs 'id' paraméter, (pl konzolból futtatva) mindent végignéz
   *
   * @return bool
   */
  public function generate($artpiece_id = false) {
    $options = self::$_options;

    $fields = ['id AS artpiece_id', 'published', 'modified', 'title', 'title_en', 'title_alternatives', 'photo_slug', 'lat', 'lon', 'status_id', 'artpiece_condition_id', 'artpiece_location_id', 'not_public_type_id', 'first_date', 'last_date', 'place_id', 'address', 'place_description', 'view_total', 'artists', 'user_id', 'photos'];

    if (!isset($options['id']) && @self::$_argv['id'] > 0) {
      $options['id'] = self::$_argv['id'];
    } elseif ($artpiece_id) {
      $options['id'] = $artpiece_id;
    }

    if (@$options['id'] > 0) {
      // Adott műlap frissítése
      $artpieces = $this->DB->find('artpieces', [
        'conditions' => [
          'id' => $options['id'],
        ],
        'fields' => $fields
      ]);

      // Itt kérem az új meghívást és frissítem, amit kell
      $this->DB->query("UPDATE artpieces SET cached = 0, titles = CONCAT(title, ' ', title_en, ' ', title_alternatives) WHERE id = " . $options['id']);

    } else {
      ini_set('memory_limit','5000M');
      // Minden műlap frissítése
      $artpieces = $this->DB->find('artpieces', [
        'conditions' => [],
        'fields' => $fields,
        'order' => 'id ASC'
      ]);

      // Itt jegyezzük rá a lapokra, nem egyesével
      // update-elgetünk lent a ciklusban, mert az fél óra...
      $this->DB->query("UPDATE artpieces SET cached = 0, titles = CONCAT(title, ' ', title_en, ' ', title_alternatives) WHERE cached > 0");

    }

    if (count($artpieces) > 0) {
      foreach ($artpieces as $artpiece) {

        // Képek újragenerálása - a lényeg itt annyi, hogy a photos-ban,
        // ha nincs benne egy kép, akkor betesszük a végére
        $photos = $this->DB->find('photos', [
          'conditions' => ['artpieces LIKE' => '%"' . $artpiece['artpiece_id'] . '"%'],
          'fields' => ['id', 'slug'],
        ]);

        $photos_array = _json_decode($artpiece['photos']);
        $start_count = count($photos_array);
        $i = 0;
        $last_item = $photos_array[count($photos_array)-1];
        foreach ($photos as $photo) {
          $found = false;
          foreach ($photos_array as $item) {
            if ($photo['id'] == $item['id']) {
              $found = true;
              break;
            }
          }
          if (!$found) {
            $i++;
            $rank = count($photos_array) + $i;
            // Nincs a JSON-ban, belegenerálni
            $photos_array[] = [
              'id' => $photo['id'],
              'slug' => $photo['slug'],
              'rank' => $rank,
              'top' => $last_item['top'] == 1
                && $rank <= sDB['limits']['artpieces']['top_photo_max'] ? 1 : 0,
            ];
          }
        }
        if ($start_count < count($photos_array)) {
          $this->DB->update('artpieces', [
            'photos' => json_encode($photos_array)
          ], $artpiece['artpiece_id']);
        }

        // Mongo artpieces csak publikusoknál
        if ($artpiece['status_id'] == 5 && $artpiece['lat'] != '') {
          $artpiece['location'] = [
            'type' => 'Point',
            'coordinates' => [(float)$artpiece['lon'], (float)$artpiece['lat']]
          ];
          unset($artpiece['lat']);
          unset($artpiece['lon']);

          // Hely neve
          $place = $this->MC->t('places', $artpiece['place_id']);
          $artpiece['place_name'] = $place['name'];

          // Első alkotó
          $artists = _json_decode($artpiece['artists']);
          $artpiece['artist'] = '';
          if (is_array($artists) && isset($artists[0]['id'])) {
            $first_artist_id = $artists[0]['id'];
            $artist = $this->MC->t('artists', $first_artist_id);
            if ($artist) {
              $artpiece['artist'] = $artist['name'];
            }
          }
          unset($artpiece['artists']);

          $artpiece['year'] = 0;
          $p = explode('-', $artpiece['first_date']);
          if (@$p[0] > 0) {
            $artpiece['year'] = (int)$p[0];
          }

          $this->Mongo->upsert('artpieces', $artpiece, ['artpiece_id' => (int)$artpiece['artpiece_id']]);
        } else {
          // Visszaküldés vagy más olyan esemény, hogy nem publikus => töröljük
          $this->Mongo->delete('artpieces', ['artpiece_id' => (int)$artpiece['artpiece_id']]);
        }

        // Cache ürítés, hogy a legközelebbi látogatásnál újraszülessen majd
        $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['artpiece_id']);

        // Kitörlöm a többi generálást erre a műlapra
        $this->Mongo->delete('jobs', [
          'class' => 'artpieces',
          'action' => 'generate',
          'options' => ['id' => $artpiece['artpiece_id']],
        ]);
      }
    }

    return true;
  }



  /**
   * Legnépszerűbb műlapok kiszedése
   * Az adott modellhez kapcsolt recalc ezt nem nézi
   *
   */
  public function tops() {

    // Alkotók
    $items = $this->DB->find('artists', [
      'conditions' => ['artpiece_count >' => 0],
      'fields' => ['id']
    ]);

    if (count($items) > 0) {
      foreach ($items as $item) {
        $top_artpiece = $this->DB->first('artpieces', [
          'artists LIKE' => '%"id":' . $item['id'] . ',"%',
          'status_id' => 5,
        ], [
          'order' => 'view_total DESC',
          'fields' => ['id'],
        ]);

        if ($top_artpiece) {
          $this->DB->update('artists', ['top_artpiece_id' => $top_artpiece['id']], $item['id']);
        }
      }
    }



    // Helyek
    $items = $this->DB->find('places', [
      'conditions' => ['artpiece_count >' => 0],
      'fields' => ['id']
    ]);

    if (count($items) > 0) {
      foreach ($items as $item) {
        $top_artpiece = $this->DB->first('artpieces', [
          'place_id' => $item['id'],
          'status_id' => 5,
        ], [
          'order' => 'view_total DESC',
          'fields' => ['id']
        ]);

        if ($top_artpiece) {
          $this->DB->update('places', ['top_artpiece_id' => $top_artpiece['id']], $item['id']);
        }
      }
    }



    // Gyűjtemények
    $items = $this->Mongo->find_array('sets');

    if (count($items) > 0) {
      foreach ($items as $item) {

        $artpiece_ids = $this->Sets->get_artpieces($item, ['only_ids' => true]);

        if (count($artpiece_ids) > 0) {
          $top_artpiece = $this->DB->first('artpieces', [
            'id' => $artpiece_ids,
            'status_id' => 5,
          ], [
            'order' => 'view_total DESC',
            'fields' => ['id']
          ]);

          if ($top_artpiece) {
            $this->Mongo->update('sets', ['top_artpiece_id' => (int)$top_artpiece['id']], ['_id' => $item['id']]);
          }
        }
      }
    }



    // Tagok
    $items = $this->DB->find('users', [
      'conditions' => ['artpiece_count >' => 0],
      'fields' => ['id']
    ]);

    if (count($items) > 0) {
      foreach ($items as $item) {
        $top_artpiece = $this->DB->first('artpieces', [
          'user_id' => $item['id'],
          'status_id' => 5,
        ], [
          'order' => 'view_total DESC',
          'fields' => ['id']
        ]);

        if ($top_artpiece) {
          $this->DB->update('users', ['top_artpiece_id' => $top_artpiece['id']], $item['id']);
        }
      }
    }


    $this->Notifications->create(CORE['USERS']['admins'], 'Lefutott az Artpieces::tops job', 'Le, biza.');
  }


  /**
   *
   * Minden reggel megpróbáljuk publikálni a késleltetetteket.
   * Ugyanazt futtatjuk, mint a szavazáskor, vagyis ha közben leromlott a lap.
   *
   * @return bool
   */
  public function auto_publish() {
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'publish_at >' => 0,
        'publish_at <=' => time()+(3*60*60), // 3 órán belül
        'status_id' => [1,2],
        'publish_pause' => 0,
      ],
      'fields' => ['id'],
    ]);

    if (count($artpieces) > 0) {
      foreach ($artpieces as $artpiece) {
        $this->Artpieces->publish($artpiece['id'], false, true);
      }
    }

    return true;
  }



  /**
   *
   * Automatikus szüret logika; amikor PT szabin van.
   * Igen, ez az ízlésem. Szubjektív.
   *
   * Szobrokat, muráliákat és vízzel kapcsolatos alkotásokat emel ki,
   * amelyek nem vallási vagy trianonos kapcsolódásúak.
   *
   * @return bool
   */
  public function auto_harvest() {
    $this->DB->query("UPDATE artpieces 
      SET
        harvested = 1, harvested_time = " . time() . "
      WHERE 
        status_id = 5
        AND published > " . strtotime('-4 hours') . "
        AND harvested = 0
        AND (parameters LIKE '%\"1\"%' 
          OR parameters LIKE '%\"18\"%' 
          OR parameters LIKE '%\"108\"%')
        
        AND parameters NOT LIKE '%\"8\"%'
        AND parameters NOT LIKE '%\"23\"%'
        AND parameters NOT LIKE '%\"101\"%'
        
        AND parameters NOT LIKE '%\"104\"%'
        
        AND parameters NOT LIKE '%\"74\"%'
        AND parameters NOT LIKE '%\"75\"%'
        AND parameters NOT LIKE '%\"76\"%'
        AND parameters NOT LIKE '%\"77\"%'
        AND parameters NOT LIKE '%\"78\"%'
        AND parameters NOT LIKE '%\"79\"%'
        AND parameters NOT LIKE '%\"80\"%'
        AND parameters NOT LIKE '%\"81\"%'
        AND parameters NOT LIKE '%\"110\"%'");

    $this->Cache->delete('cached-view-pages-index');
  }


  /**
   * A nem generált műlapok generáltatása
   * ellenőrzésképp naponta futtatjuk cronjobból
   */
  public function check_generateds() {
    ini_set('memory_limit','5000M');
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
      ],
      'fields' => ['id']
    ]);

    foreach ($artpieces as $artpiece) {
      $a = $this->Mongo->find('artpieces', [
        'artpiece_id' => $artpiece['id']
      ]);

      if (!$a) {
        $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
        // Beszúrjuk üresen
        $this->Mongo->insert('artpieces', ['artpiece_id' => (int)$artpiece['id']]);
        // Ez pedig majd megcsinálja jól
        /*$job = $this->Mongo->insert('jobs', [
          'class' => 'artpieces',
          'action' => 'generate',
          'options' => ['id' => $artpiece['id']],
          'created' => date('Y-m-d H:i:s'),
        ]);*/
        $this->generate($artpiece['id']);
      }
    }
  }



}