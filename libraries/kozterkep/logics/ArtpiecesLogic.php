<?php

namespace Kozterkep;

class ArtpiecesLogic {

  public function __construct($app_config = false, $DB) {
    $this->Cache = new CacheComponent();
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();

    $this->Notifications = new NotificationsLogic($app_config, $DB);
    $this->Comments = new CommentsLogic($app_config, $DB);
    $this->Events = new EventsLogic($app_config, $DB);

    $this->Arrays = new ArraysHelper();
    $this->Text = new TextHelper();
    $this->Image = new ImageHelper();
    $this->Html = new HtmlHelper($app_config);

    if ($app_config) {
      $this->Artists = new ArtistsLogic($app_config, $DB);
      $this->Places = new PlacesLogic($app_config, $DB);
    }
  }


  /**
   *
   * Műlap ellenőrzés
   *
   * @param array $artpiece_data - most adott, esetleg még nem mentett adat
   * @param bool $act_user - felhasználó, aki módosít(ana)
   * @param bool $different_saved - más-e a mentett, mint az átadott?
   * @return array|mixed
   */
  public function check ($artpiece_data = [], $act_user = false, $different_saved = true) {
    $validation = [];

    if (is_numeric($artpiece_data)) {
      $artpiece = $this->DB->first('artpieces', $artpiece_data);
      $saved_artpiece = $artpiece;
    } else {
      $artpiece = $artpiece_data;
      if ($different_saved) {
        $saved_artpiece = $artpiece['id'] > 0 ? $this->DB->first('artpieces', $artpiece['id']) : false;
      } else {
        $saved_artpiece = $artpiece;
      }
    }

    if (isset($artpiece['id'])) {
      // Hiányzó adatok...
      if (!isset($artpiece['user_id'])) {
        $ap = $this->MC->t('artpieces', $artpiece['id']);
        $artpiece['user_id'] = $ap['user_id'];
      }
      //$app = APP;
      $user = $act_user;
      $saved_descriptions = $this->Mongo->find_array('artpiece_descriptions', ['artpieces' => (int)$artpiece['id']]);
      $validation = include_once CORE['PATHS']['DATA'] . DS . 'constants' . DS . 'valid_artpiece.php';
    }

    return $validation;
  }



  /**
   *
   * Köztérre küldés
   *
   * @param $artpiece_id
   * @param array $user
   * @return bool
   */
  public function submit ($artpiece_id, $user = []) {
    $artpiece = $this->DB->first('artpieces', $artpiece_id);

    if ($artpiece && $artpiece['status_id'] != 5) {
      $validations = $this->check($artpiece, $user, false);

      if ($validations['operations']['submission'] == 1) {

        // Megosztás előtti saját szerkesztések törlése
        $this->Mongo->delete('artpiece_edits', [
          'artpiece_id' => (int)$artpiece['id'],
          'user_id' => (int)$artpiece['user_id'],
          'before_shared' => 1,
        ]);

        // Kommentek ne legyenek hiddenek
        $this->Mongo->update('comments', [
          'hidden' => 0,
        ], [
          'artpiece_id' => (int)$artpiece['id'],
          'hidden' => 1,
        ]);

        $submitted = $this->DB->update('artpieces', [
          'status_id' => 2,
          'invited_users' => '',
          'submitted' => time(),
          'modified' => time(),
          'updated' => time(),
        ], $artpiece['id']);

        if ($submitted) {
          // Cachetelenítés
          $this->generate($artpiece['id']);

          // Friss listák
          $this->delete_artpieces_cache();

          // Tulaj dolgainak frissítése
          $this->update_user_things($artpiece['user_id']);

          // Fotók elérhetővé tétele
          $this->DB->update('photos', [
            'before_shared' => 0
          ], ['artpiece_id' => $artpiece['id']]);

          // Köztérgép is mondja eseményben, hogy mi van
          $this->Events->create(1, [
            'artpiece_id' => $artpiece['id'],
            'target_user_id' => $artpiece['user_id'],
          ]);

          return true;
        }
      }
    }

    return false;
  }


  /**
   * Visszahívás
   *
   */
  public function call_back($artpiece_id, $user = []) {
    $artpiece = $this->DB->first('artpieces', $artpiece_id);

    if ($artpiece && $artpiece['status_id'] == 2) {

      $got_back = $this->DB->update('artpieces', [
        'status_id' => 1,
        'submitted' => 0,
        'published' => 0,
        'modified' => time(),
        'updated' => time(),
      ], $artpiece['id']);

      if ($got_back) {
        // Cachetelenítés
        $this->generate($artpiece['id']);

        // Friss listák
        $this->delete_artpieces_cache();

        // Tulaj dolgainak frissítése
        $this->update_user_things($artpiece['user_id']);

        // Fotók elérhetetlenné tétele
        $this->DB->update('photos', [
          'before_shared' => 1
        ], ['artpiece_id' => $artpiece['id']]);

        // Köztérgép is mondja
        $this->Events->create(2, [
          'artpiece_id' => $artpiece['id'],
          'target_user_id' => $artpiece['user_id'],
        ]);

        return true;
      }
    }

    return false;
  }





  /**
   *
   * Publikálás és kapcsolódó ürítések, számolások stb.
   *
   * @param $artpiece_id
   * @param array $user
   * @return bool
   */
  public function publish ($artpiece_id, $user = [], $publish_by_vote = false) {
    $artpiece = $this->DB->first('artpieces', $artpiece_id);
    $artpiece_user = $this->DB->first('users', $artpiece['user_id']);

    if ($artpiece && $artpiece['status_id'] != 5) {
      $check_user = $publish_by_vote ? 'community' : $user;
      $validations = $this->check($artpiece, $check_user, false);

      // ez akkor menne, ha a submission feltétel ugyanolyan erős lenne, mint a publish
      // de nem az, mert akkor nem lehetne segítség kérésre beküldeni a műlapot a köztérre
      /*if ($validations['operations']['publish'] == 1
        || ($validations['operations']['submission'] == 1 && $publish_by_vote)) {*/
      if ($validations['operations']['publish'] == 1
        || ($publish_by_vote && $validations['operations']['publishable'] == 1)) {

        if ($artpiece_user['weekly_artpieces'] >= sDB['limits']['artpieces']['weekly_max']) {
          $this->DB->update('artpieces', [
            'publish_at' => strtotime('next Monday 06:00'),
          ], $artpiece['id']);
          $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);

          if ($publish_by_vote) {
            $this->Notifications->create($artpiece['user_id'], 'Műlapod hétfőn pubikáljuk', '"' . $artpiece['title'] . '" c. műlapodat a közösség megszavazta, de a heti limited betelt, így hétfő reggel automatikusan publikálni fogjuk.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);
          } else {
            $this->Notifications->create($artpiece['user_id'], 'Műlapod hétfőn pubikáljuk', '"' . $artpiece['title'] . '" c. műlapod publikálható, de a heti limited betelt, így hétfő reggel automatikusan publikálni fogjuk.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);
          }

          return 2;
        }

        // Megosztás előtti saját szerkesztések törlése
        $this->Mongo->delete('artpiece_edits', [
          'artpiece_id' => (int)$artpiece['id'],
          'user_id' => (int)$artpiece['user_id'],
          'before_shared' => 1,
        ]);

        // Kommentek ne legyenek hiddenek
        $this->Mongo->update('comments', [
          'hidden' => 0,
        ], [
          'artpiece_id' => (int)$artpiece['id'],
          'hidden' => 1,
        ]);

        // Jeah! :D
        $published = $this->DB->update('artpieces', [
          'status_id' => 5,
          'invited_users' => '',
          'publish_at' => 0,
          'published' => time(),
          'modified' => time(),
          'updated' => time(),
          'view_total' => 0,
          'view_day' => 0,
          'view_week' => 0,
        ], $artpiece['id']);

        if ($published) {

          // Cachetelenítés és egyéb generálások
          $this->generate($artpiece['id']);

          // Település és alkotó rekalk
          $this->recalc_things($artpiece);

          // Friss listák
          $this->delete_artpieces_cache();

          // Tulaj dolgainak frissítése
          $user_artpieces = $this->update_user_things($artpiece['user_id']);

          // Fotók elérhetővé tétele
          $this->DB->update('photos', [
            'before_shared' => 0
          ], ['artpiece_id' => $artpiece['id']]);

          // Publikálási esemény
          if ($artpiece['user_id'] == @$user['id']) {
            $event_type_id = 4;
          } else {
            $event_type_id = 5;
          }
          $this->Events->create($event_type_id, [
            'artpiece_id' => $artpiece['id'],
            'target_user_id' => $artpiece['user_id'],
          ]);

          // Közösségi publikálásról is értesítés
          if ($publish_by_vote) {
            $this->Notifications->create($artpiece['user_id'], 'Műlapodat publikálta a közösség!', '"' . $artpiece['title'] . '" c. műlapodat a közösség szavazással publikálta.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);
          }


          // Szép szám esett!
          if (in_array($user_artpieces, [25, 50, 100])
            || ($user_artpieces < 1000 && $user_artpieces%100 == 0)
            || ($user_artpieces >= 1000 && $user_artpieces%500 == 0)) {

            // :))) !!! :)))
            $this->Notifications->create($artpiece['user_id'], 'Ez igen! :) Szívből gratulálunk ' . $user_artpieces . '. műlapodhoz!', 'Büszkék vagyunk arra, hogy közénk tartozol, és ilyen lelkesen gazdagítod a Köztérkép adatbázisát és ezzel a világot is. Csak így tovább!', [
              'link' => '/' . $artpiece['id'],
              'type' => 'artpieces',
            ]);

            // Mérföldkő esemény
            $this->Events->create(21, [
              'text' => 'Ez a műlap tagunk ' . $user_artpieces . '. műlapja, gratulálunk!',
              'artpiece_id' => $artpiece['id'],
              'user_id' => $artpiece['user_id'],
            ]);
          }

          // Előző legfrissebb műlap regen
          $last = $this->DB->first('artpieces',
            array(
              'status_id' => 5,
              'id <>' => $artpiece['id'],
            ),
            array(
              'order' => 'published DESC',
              'fields' => 'id',
            )
          );

          $this->generate($last['id']);

          return 1;
        }
      }
    }

    return 3;
  }


  /**
   * Visszaküldés
   *
   */
  public function send_back($artpiece_id, $user = []) {
    $artpiece = $this->DB->first('artpieces', $artpiece_id);

    if ($artpiece && in_array($artpiece['status_id'], [2, 5])) {
      $sent_back = $this->DB->update('artpieces', [
        'status_id' => 3,
        'published' => 0,
        'modified' => time(),
        'updated' => time(),
      ], $artpiece['id']);

      if ($sent_back) {
        // Cachetelenítés
        $this->generate($artpiece['id']);

        // Település és alkotó rekalk
        $this->recalc_things($artpiece);

        // Törölni a publikálási szavazatokat
        $this->Mongo->delete('artpiece_votes', [
          'type_id' => 1,
          'artpiece_id' => (int)$artpiece['id'],
        ]);

        // Friss listák
        $this->delete_artpieces_cache();

        // Tulaj dolgainak frissítése
        $this->update_user_things($artpiece['user_id']);

        // Fotók elérhetetlenné tétele
        $this->DB->update('photos', [
          'before_shared' => 1
        ], ['artpiece_id' => $artpiece['id']]);

        // Tulaj értesítése
        $this->Notifications->create($artpiece['user_id'], 'Műlap visszaküldése', '"' . $artpiece['title'] . '" c. műlapodat visszaküldtük szerkesztésre. A műlapot ne alakítsd át és küldd be újra más alkotással! Ha elvekbe ütköző volt, törölhető, egyébként érdemes megtartani további kutatások miatt.', [
          'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm',
          'type' => 'artpieces',
        ]);

        // KöztérGép esemény
        $this->Events->create(3, [
          'artpiece_id' => $artpiece['id'],
          'target_user_id' => $artpiece['user_id'],
        ]);

        return true;
      }
    }

    return false;
  }



  /**
   * Visszaküldés
   *
   */
  public function reopen($artpiece_id, $user = []) {
    $artpiece = $this->DB->first('artpieces', $artpiece_id);

    if ($artpiece && $artpiece['status_id'] == 3) {
      $sent_back = $this->DB->update('artpieces', [
        'status_id' => 1,
        'published' => 0,
        'modified' => time(),
        'updated' => time(),
      ], $artpiece['id']);

      if ($sent_back) {
        // Cachetelenítés
        $this->generate($artpiece['id']);

        // Törölni a publikálási szavazatokat
        $this->Mongo->delete('artpiece_votes', [
          'type_id' => 1,
          'artpiece_id' => (int)$artpiece['id'],
        ]);

        // Friss listák
        $this->delete_artpieces_cache();

        // Tulaj dolgainak frissítése
        $this->update_user_things($artpiece['user_id']);

        // Fotók elérhetetlenné tétele
        $this->DB->update('photos', [
          'before_shared' => 1
        ], ['artpiece_id' => $artpiece['id']]);

        // Tulaj értesítése
        $this->Notifications->create($artpiece['user_id'], 'Műlap visszanyitása', '"' . $artpiece['title'] . '" c. műlapodat visszanyitottuk szerkesztésre. Mostantól újra beküldhető lesz.', [
          'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm',
          'type' => 'artpieces',
        ]);

        // KöztérGép esemény
        $this->Events->create(29, [
          'artpiece_id' => $artpiece['id'],
          'target_user_id' => $artpiece['user_id'],
        ]);

        return true;
      }
    }

    return false;
  }



  /**
   *
   * Műlap szerkesztés létrehozása vagy frissítése
   *
   * @param $data
   * @param $artpiece
   * @param $user_id
   * @param array $options
   * @return array
   */
  public function upsert_edit($data, $artpiece, $user_id, $options = []) {

    $options = (array)$options + [
      'status_id' => 2,
      'parsed' => false,
    ];

    // Ha update lesz
    $edit_id = @$data['edit_id'] != '' ? $data['edit_id'] : false;

    // Átalakítások az összehasonlításhoz
    if (!$options['parsed']) {
      $data = $this->parse_edit_data($data, $artpiece);
    }

    //var_dump($data);

    // Töröljük, ami nem kell
    $data = _unset($data, ['id', 'user_id', 'country_code', 'address_json', 'edit_id', 'place_', 'artists_order', 'new_artist', 'new_connected_artpiece', 'new_connected_common_set', 'new_connected_user_set', 'photoranks']);

    $changes = $this->diff_edit($data, $artpiece);

    //var_dump($data); exit;

    $prev_data = [];

    if (isset($changes['descriptions'])) {
      list($changes, $prev_data) = $this->diff_descriptions($changes, $artpiece, $prev_data);
    }

    if (isset($data['photolist'])) {
      $changes['photolist'] = $data['photolist'];
      list($changes, $prev_data) = $this->diff_photos($changes, $artpiece, $prev_data);
    }
    
    /*
     * Végigmegyünk a változásokon, és ha van ilyen adat a műlapon és ugyanaz, akkor
     * ezt töröljük. Ami nincs a műlapon, de benne van a változásokban, azt
     * békén hagyjuk. Ilyenek a kapcsolódó dolgok.
     */
    if (count($changes) > 0) {
      foreach ($changes as $key => $value) {
        if (!isset($artpiece[$key])) {
          continue;
        }
        if ($value == $artpiece[$key]) {
          unset($changes[$key]);
        } else {
          $prev_data[$key] = $artpiece[$key];
        }
      }
    }


    if (count($changes) > 0) {

      if ($edit_id) {
        // UPDATE
        $edit = $this->Mongo->first('artpiece_edits', [
          '_id' => $edit_id,
          'user_id' => (int)$user_id
        ]);

        if ($edit) {
          // Ha ugyanaz egy adat, mint a műlapon, ill. a szerkesztésben,
          // és nem most állt üresre, akkor kivesszük a mentésből
          // @todo: checkboxok, leírások stb. Lesz itt buli.
          foreach ($data as $key => $value) {
            if ((@$edit[$key] == $value || $artpiece[$key] == $value) && $value != '') {
              unset($data[$key]);
            }
            // Törlődjön, ami változatlanul jönne be
            if (isset($edit[$key]) && $artpiece[$key] == $edit[$key] && $edit[$key] == $value) {
              unset($data[$key]);
              unset($edit[$key]);
            }
          }

          $edit['prev_data'] = $prev_data;
          $this->Mongo->update('artpiece_edits',
            array_merge($edit, $data), [
            '_id' => $edit_id
          ], false, true, true);

        } else {
          $changes = 0;
          $edit_id = false;
        }
      } else {

        // INSERT
        $edit_id = $this->Mongo->insert('artpiece_edits', [
            'artpiece_id' => (int)$artpiece['id'],
            'status_id' => (int)$options['status_id'],
            'user_id' => (int)$user_id,
            'receiver_user_id' => (int)$artpiece['user_id'],
            'created' => time(),
            'modified' => time(),
            'before_shared' => in_array($artpiece['status_id'], [2,5]) ? 0 : 1,
            'prev_data' => $prev_data,
          ] + $changes, false, true);
      }
    }

    return [$changes, $edit_id];
  }


  /**
   *
   * A tömb jellegű adatokat elő kell készíteni,
   * mert ajax POST miatt nem szépen jönnek
   *
   * @param $data
   * @return array
   */
  public function parse_edit_data($data, $artpiece) {
    $parsed = [];
    $artists = [];
    $place_id = false;

    foreach ($data as $key => $value) {

      // Címek entityjeinek visszacserélése, hogy ne diffeljen a mysql-ben tárolttal
      if (in_array($key, ['title', 'title_en', 'title_alternatives', 'address', 'place_description'])) {
        $value = _replace([
          "&#39;" => "'",
          "&#34;" => '"'
        ], $value);
      }

      // Alkotók
      if ($key == 'artists' && is_array($value)) {
        $artists = [];
        foreach ($value as $artist) {
          $artists[] = [
            'id' => (int)$artist['id'],
            'rank' => (int)$artist['rank'],
            //'question' => (int)$artist['question'],
            'contributor' => (int)$artist['contributor'],
            'profession_id' => (int)$artist['profession_id'],
          ];
        }
        $artists = $this->Arrays->sort_by_key($artists, 'rank');

        // Ezt nem mentjük
        $parsed['artists'] = json_encode($artists);
        continue;
      }

      // Ezzel a szerkesztéssel ürítettük az alkotókat
      if ($key == 'no_artists' && $value == 1) {
        $parsed['artists'] = json_encode([]);
        continue;
      }

      // Település
      if ($key == 'place_' && $value != '' && $data['place_id'] == 0) {
        $place_id = $this->create_place($data['place_'], $data);
        continue;
      }

      // Köztéri, nullázzuk a nem köztértípust
      if ($key == 'not_public_type_id' && $value > 0 && $data['artpiece_location_id'] == 1) {
        $parsed['not_public_type_id'] = 0;
        continue;
      }


      // Évszámok...
      // kell a date mezőbe a yyyy-mm-dd alak, és ha üres jön, nullává konvertáljuk
      if ($key == 'dates') {
        foreach ($data['dates'] as $dkey => $date) {

          $data['dates'][$dkey]['bc'] = 0; // egyelőre nem adminoljuk az i.e. műveket

          if (!isset($data['dates'][$dkey]['century'])) {

            // üres éves nembizonytalan nem kell
            if ($data['dates'][$dkey]['y'] == ''
              & @$data['dates'][$dkey]['cca'] != 1) {
              unset($data['dates'][$dkey]);
              continue;
            }

            // ha nem jött század, akkor dátumnál vagyunk
            $data['dates'][$dkey]['century'] = 0;

            // Nap
            $data['dates'][$dkey]['d'] = $data['dates'][$dkey]['d'] == ''
              ? 0 : (int)$data['dates'][$dkey]['d'];

            // Hónap
            $data['dates'][$dkey]['m'] = $data['dates'][$dkey]['m'] == ''
              ? 0 : (int)$data['dates'][$dkey]['m'];

            // date kell egyben
            $data['dates'][$dkey]['date'] = _cdate($data['dates'][$dkey]['y'] . '-'
              . $data['dates'][$dkey]['m'] . '-' . $data['dates'][$dkey]['d']);
          } else {
            // ha jött század, akkor beállítjuk a dátumot
            // ezzel vigyázni kell, nehogy évnek vegyük!

            // üres százados nem kell
            if ($data['dates'][$dkey]['century'] == '') {
              unset($data['dates'][$dkey]);
              continue;
            }

            $c = ($data['dates'][$dkey]['century'] - 1) * 100;
            $data['dates'][$dkey]['date'] = _cdate($c . '-0-0');
            $data['dates'][$dkey]['y'] = $c;
            $data['dates'][$dkey]['m'] = 0;
            $data['dates'][$dkey]['d'] = 0;
            $data['dates'][$dkey]['cca'] = '1'; // mindenképp
          }
        }
        $parsed['dates'] = json_encode($data['dates']);
        continue;
      }

      // Ezzel a szerkesztéssel ürítettük a dátumokat
      if ($key == 'no_dates' && $value == 1) {
        $parsed['dates'] = json_encode([]);
        continue;
      }

      // Paraméterek
      if ($key == 'parameters') {
        $parameters = [];
        foreach ($data['parameters'] as $pkey => $parameter) {
          if ($parameter['value'] == 1) {
            $parameters[] = (string)$parameter['id'];
          }
        }
        sort($parameters);
        $parsed['parameters'] = json_encode($parameters);
        continue;
      }


      // Kapcsolódó műlapok
      if ($key == 'connected_artpieces') {
        $connected_artpieces = [];
        foreach ($data['connected_artpieces'] as $cakey => $connected_artpiece) {
          $connected_artpieces[$connected_artpiece['id']] = (int)$connected_artpiece['type'];
        }
        asort($connected_artpieces);
        $parsed['connected_artpieces'] = json_encode($connected_artpieces);
        continue;
      }

      // Ezzel a szerkesztéssel ürítettük a kapcsolódó műlapokat
      if ($key == 'no_connected_artpieces' && $value == 1) {
        $parsed['connected_artpieces'] = json_encode([]);
        continue;
      }

      // Kapcsolódó gyűjtemények
      if ($key == 'connected_sets' && $value != '') {
        $parsed['connected_sets'] = urldecode($value);
        continue;
      }

      // Leírások
      if ($key == 'descriptions') {
        foreach ($value as $key => $description) {
          if (trim($description['text']) == '' && trim($description['source']) == '') {
            unset($value[$key]);
          }
        }
        if (count($value) > 0) {
          $parsed['descriptions'] = json_encode($value);
        }
        continue;
      }

      // Fotók (photos néven az artpieces.photos JSON mező jön)
      if ($key == 'photolist') {
        if (count($value) > 0) {

          $parsed['photolist'] = json_encode($value);

          // Borítóállítás kiderítése
          foreach ($value as $photo) {
            if (@$photo['cover'] == 1 && $photo['id'] != $artpiece['photo_id']) {
              $parsed['photo_id'] = $photo['id'];
              $parsed['photo_slug'] = $photo['slug'];
              break;
            }
          }
        }
        continue;
      }
      // Ez a fotó json
      if ($key == 'photos' && $value != '') {
        $parsed['photos'] = urldecode($value);
        continue;
      }


      // egész számok
      if (is_int($value)) {
        $value = (int)$value;
      }

      $parsed[$key] = $value;

    }

    if ($place_id) {
      $parsed['place_id'] = $place_id;
    }

    return $parsed;
  }


  /**
   *
   * Szerkesztés jóváhagyása
   *
   * @param $artpiece_id
   * @param $edit_id
   * @param $user_id
   * @return array|bool|int|null|string
   */
  public function approve_edit($artpiece_id, $edit_id, $user_id) {
    $edit = $this->Mongo->first('artpiece_edits', [
      'artpiece_id' => $artpiece_id,
      '_id' => $edit_id
    ]);

    if ($edit) {
      $artpiece = $this->DB->find_by_id('artpieces', $artpiece_id, [
        'fields' => ['id', 'title', 'status_id', 'user_id', 'updated']
      ]);

      $updates = [
        'status_id' => 5,
        'approved' => time(),
        'manage_user_id' => $user_id,
      ];

      if ($artpiece['user_id'] == $edit['user_id']) {
        $updates['own_edit'] = 1;
      }

      $this->Mongo->update('artpiece_edits', $updates, ['_id' => $edit_id]);

      $changes = array_merge($edit, $updates);
      $prev_data = @$changes['prev_data'];
      $changes = _unset($changes, ['id', 'own_edit', 'artpiece_id', 'status_id', 'user_id', 'created', 'modified', 'approved', 'before_shared', 'manage_user_id', 'receiver_user_id', 'prev_data', 'hug_id', 'edit_type_id', 'invisible']);


      // Kapcsolt műlapok
      if (isset($changes['connected_artpieces'])) {
        $this->handle_connected_artpieces($changes['connected_artpieces'], $artpiece_id, $edit['user_id']);
      }

      // Kapcsolt gyűjtemények
      if (isset($changes['connected_sets'])) {
        $this->handle_connected_sets($changes['connected_sets'], $artpiece_id, $edit['user_id']);
      }

      // Dátumok
      if (isset($changes['dates'])) {
        // Időrendi növekvőbe tesszük
        $dates = $this->Arrays->sort_by_key($changes['dates'], 'date', 1);
        if (count($dates) > 0) {
          reset($dates);
          $first_key = key($dates);
          end($dates);
          $last_key = key($dates);
          $changes['first_date'] = _cdate($dates[$first_key]['date']);
          $changes['last_date'] = _cdate($dates[$last_key]['date']);
        } else {
          $changes['first_date'] = '0000-0-0';
          $changes['last_date'] = '0000-0-0';
        }
      }

      // Leírások (új rögzítés és szerkesztés)
      $changes = $this->handle_descriptions($changes, $artpiece_id, $edit['user_id'], $user_id, $edit['receiver_user_id']);

      // Fotó törzsadat módosulások
      $changes = $this->handle_photos($changes, $artpiece_id, $edit['user_id'], $user_id);

      // Hely módosulások
      $changes = $this->handle_place_change($changes, @$prev_data, $artpiece_id, $edit['user_id']);

      // Borító állítás vagy Élménykép pipálás volt (előző change-ből kapjuk meg); fontos a fotók után
      if (isset($changes['photo_slug']) || isset($changes['joy_set'])) {
        $top_photo_count = isset($changes['top_photo_count']) ? $changes['top_photo_count'] : false;
        $changes['photos'] = $this->update_photos_field($artpiece_id, false, $top_photo_count);
        // Hogy ne mentsük
        unset($changes['joy_set']);
      }

      // A végén mentjük, mert kellhet még az artpiece-ből eredeti adat
      // Ha már rég frissült, akkor long_update is
      if ($artpiece['updated'] < strtotime('-30 days')) {
        $changes['long_updated'] = time();
      }

      // Paraméterekkel gond van; [1,2,5] formában jön
      // de így kell menteni: ["1","2","5"]
      if (isset($changes['parameters']) && is_array($changes['parameters'])) {
        $params = [];
        foreach ($changes['parameters'] as $param) {
          $params[] = (string)$param;
        }
        $changes['parameters'] = json_encode($params);
      }


      $saved = $this->DB->update('artpieces', [
          'modified' => time(),
          'updated' => time(),
        ] + $changes, $artpiece_id);

      if ($saved && $edit['user_id'] != $user_id) {
        $this->Notifications->create($edit['user_id'], 'Szerkesztésed elfogadásra került', _z($artpiece['title'], true) . ' c. műlapon lévő szerkesztésed elfogadásra került.', [
          'link' => '/mulapok/szerkesztes/' . $artpiece_id,
          'type' => 'edits',
        ]);
      }


      // VÁLTOZOTT DOLGOK újraszámolása
      // csak itt, mert ha "túl gyorsan" fut le, akkor ugye a save előtt mehet
      // Változott valami a településeknél
      if (isset($changes['place_id'])) {
        $this->Mongo->insert('jobs', [
          'class' => 'places',
          'action' => 'recalc',
          'options' => [
            'id' => [$changes['place_id'], @$prev_data['place_id']]
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      // Változott valami az alkotóknál
      if (isset($changes['artists'])) {
        $this->Mongo->insert('jobs', [
          'class' => 'artists',
          'action' => 'recalc',
          'options' => [
            'json' => [$changes['artists'], @$prev_data['artists']]
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }


      return $saved;
    }

    return false;
  }




  /**
   *
   * Artpiece tömb alapján újraszámoltatja a kapcsolódó dolgokat
   * most település és alkotók van itt
   *
   * @param $artpiece
   */
  public function recalc_things($artpiece) {
    $this->Mongo->insert('jobs', [
      'class' => 'places',
      'action' => 'recalc',
      'options' => [
        'id' => [$artpiece['place_id']]
      ],
      'created' => date('Y-m-d H:i:s'),
    ]);
    $this->Mongo->insert('jobs', [
      'class' => 'artists',
      'action' => 'recalc',
      'options' => [
        'json' => [$artpiece['artists']]
      ],
      'created' => date('Y-m-d H:i:s'),
    ]);
  }


  /**
   *
   * Szerkesztés alatti saját műlapok listája
   *
   * @param $user_id
   * @param bool $limit
   * @return array|bool|int|mixed
   */
  public function get_edit_list($user_id, $limit = false) {
    $results = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user_id,
        'status_id' => [1,2,3] // Közteres és visszaküldött is kell
      ],
      'fields' => ['id', 'photo_slug', 'title', 'modified', 'status_id'],
      'order' => 'updated DESC',
      'limit' => $limit ? $limit : 10000
    ], ['name' => __METHOD__ . '::' . $user_id]);

    return $results;
  }


  /**
   *
   * Mostanában módosított saját műlapok
   *
   * @param $user_id
   * @param bool $limit
   * @return array|bool|int|mixed
   */
  public function get_modified_list($user_id, $limit = false) {
    $results = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user_id,
        'status_id' => 5
      ],
      'fields' => ['id', 'photo_slug', 'title', 'modified'],
      'order' => 'updated DESC',
      'limit' => $limit ? $limit : 10000
    ], ['name' => __METHOD__ . '::' . $limit . '_' . $user_id]);

    return $results;
  }


  /**
   *
   * Hely szinteket generál, amit morzsamenüben használunk
   *
   * @param $artpiece
   * @return array
   */
  public function get_breadcrumbs_menu($artpiece) {
    //$items = ['Műlapok' => '/kereses'];
    $items = [];

    // Ország
    $list = sDB['countries'];
    if (isset($list[$artpiece['country_id']][0])) {
      $items[$list[$artpiece['country_id']][1]] = $this->Html->link_url('', [
        'country' => sDB['countries'][$artpiece['country_id']] + ['id' => $artpiece['country_id']]
      ]);
    }

    // Megye, és nem BP
    if ($artpiece['county_id'] > 1) {
      $list = sDB['counties'];
      if (isset($list[$artpiece['county_id']][0])) {
        $items[$list[$artpiece['county_id']][0]] = $this->Html->link_url('', [
          'county' => sDB['counties'][$artpiece['county_id']] + ['id' => $artpiece['county_id']]
        ]);
      }
    }

    // Település
    $place = strip_tags($this->Places->name($artpiece['place_id'], ['link' => false]));
    if ($place) {
      $items[$place] = $this->Html->link_url('', ['place' => [
        'id' => $artpiece['place_id'],
        'name' => $place
      ]]);
    }

    // Kerület, ha
    if ($artpiece['district_id'] > 0) {
      $list = sDB['districts'];
      if (isset($list[$artpiece['district_id']][0])) {
        $items[$list[$artpiece['district_id']][0]] = $this->Html->link_url('', [
          'district' => sDB['districts'][$artpiece['district_id']] + ['id' => $artpiece['district_id']]
        ]);
      }
    }

    if (count($items) == 0) {
      // Ez egy szerk alatti hiányos lap
      $items['Saját műlapjaim'] = '/kozter/mulapjaim';
    }

    return $items;
  }



  /**
   *
   * Szerkesztés részletei megjelenítési céllal
   *
   * @param $edit
   * @param array $options
   * @return array
   */
  public function edit_details($edit, $options = []) {
    $options = (array)$options + [
      'simple' => false,
      'full_values' => false,
      'excluded' => [],
      'change_separator' => '<br />',
    ];

    $details = [];

    $artpiece_fields = sDB['artpiece_fields'];
    asort($artpiece_fields);

    foreach ($edit as $field => $value) {

      if (in_array($field, $options['excluded'])) {
        continue;
      }

      // Ha ugyanaz, mint az előző
      if (isset($edit['prev_data'][$field]) && $value == $edit['prev_data'][$field]) {
        continue;
      }

      list($name, $v) = $this->edit_field($field, $value, [
        'artpiece_fields' => $artpiece_fields,
        'change_separator' => $options['change_separator'],
        'full_values' => $options['full_values']
      ]);

      if (is_array($v)) {
        $v = _json_encode($v);
      }

      $v = $v == '' ? '-' : $v;

      if ($options['simple']) {
        /*$details[] = '<div class="col-12 col-sm-6 col-md-4 col-lg-3 text-muted">' . $name . '</div>'
          . '<div class="col-12 col-sm-6 col-md-8 col-lg-9 mb-2 font-weight-semibold">' . $v . '</div>';*/
        $details[] = $name . ': <span class="font-weight-semibold">' . $v . '</span>';
      } else {
        $details[$field] = [$name => $v];
      }
    }

    return $details;
  }


  /**
   *
   * Szerkesztésben érkező műlap mezőket és értéküket értelmezhetővé tesszük
   *
   * @param $field
   * @param $value
   * @param array $options
   * @return array
   */
  public function edit_field ($field, $value, $options = []) {
    $options = (array)$options + [
      'artpiece_fields' => false,
      'change_separator' => '<br />',
      'full_values' => false
    ];


    if (!$options['artpiece_fields']) {
      $artpiece_fields = sDB['artpiece_fields'];
      asort($artpiece_fields);
    } else {
      $artpiece_fields = $options['artpiece_fields'];
    }

    $name = isset($artpiece_fields[$field]) ? $artpiece_fields[$field] : $field;

    $v = is_array($value) || is_array(json_decode($value, true)) ? '...' : $value;

    switch ($field) {

      case 'status_id':
        $v = sDB['edit_statuses'][$value][0];
        break;

      case 'user_id':
        $v = @$this->MC->t('users', $value)['name'];
        break;

      case 'created':
        $v = _date($value);
        break;

      case 'modified':
        $v = _date($value);
        break;

      case 'approved':
        $v = _date($value);
        break;

      case 'prev_data':
        $name = 'Előző adat';
        $v = '[' . implode($options['change_separator'], $this->edit_details($value)) . ']';
        break;

      case 'artists':
        $name = 'Alkotó';
        $artists = [];
        $i = 0;
        // @todo -- ha csak egy role vagy contrib adat változott, azt okosabban mutatni
        $array = is_array($value) ? $value : json_decode($value, true);
        if (is_array($array) && @count($array) > 0) {
          foreach ($array as $artist) {
            if ($artist['id'] == 0 || !$this->MC->t('artists', $artist['id'])) {
              continue;
            }

            $cached_artist = $this->MC->t('artists', @$artist['id']);

            if ($cached_artist) {
              $artist_name = $cached_artist['name'];
            } else {
              // Nincs, most rögzítettük
              $artist_row = $this->DB->first('artists', $artist['id'], ['fields' => 'name']);
              $artist_name = $artist_row['name'];
            }

            $artists[$i] = $this->Html->link($artist_name, '', [
              'artist' => [
                'id' => $artist['id'],
                'name' => $artist_name,
              ],
              'target' => '_blank'
            ]);
            $artists[$i] .= @$artist['profession_id'] > 0 ? ' (' . strtolower(sDB['artist_professions'][$artist['profession_id']][0]) . ')' : '';
            $artists[$i] .= @$artist['contributor'] == 1 ? ', közreműködő' : '';
            $i++;
          }
        }
        $v = count($artists) > 0 ? implode($options['change_separator'], $artists) : '-';
        break;

      case 'parameters':
        $parameters_ = _json_decode($value);
        $parameters = [];
        if (count($parameters_) > 0) {
          foreach ($parameters_ as $parameter) {
            $parameters[] = $this->MC->t('parameters', $parameter)['name'];
          }
        }
        $v = count($parameters) > 0 ? implode($options['change_separator'], $parameters) : '-';
        break;

      case 'connected_sets':
        $sets_ = _json_decode($value);
        $sets = [];
        if (count($sets_) > 0) {
          foreach ($sets_ as $set) {
            $this_set = $this->MC->t('sets', $set);
            $member = $this_set['set_type_id'] == 2 ? ' (tagi)' : '';
            $sets[] = $this->Html->link($this_set['name'] . $member, '', [
              'set' => $this_set,
              'target' => '_blank',
            ]);
          }
        }
        $v = count($sets) > 0 ? implode($options['change_separator'], $sets) : '-';
        break;

      case 'connected_artpieces':
        $artpieces_ = _json_decode($value);
        $artpieces = [];
        if (count($artpieces_) > 0) {
          foreach ($artpieces_ as $artpiece => $null) {
            $this_artpiece = $this->MC->t('artpieces', $artpiece);
            $artpieces[] = $this->Html->link($this_artpiece['title'], '', [
              'artpiece' => $this_artpiece,
              'target' => '_blank',
            ]);
          }
        }
        $v = count($artpieces) > 0 ? implode($options['change_separator'], $artpieces) : '-';
        break;

      case 'place_id':
        $place = $this->MC->t('places', $value);
        if (!$place) {
          // Nincs, most rögzítettük
          $place = $this->DB->first('places', $value, ['fields' => 'name']);
        }
        $v = $place['name'];
        break;

      case 'country_id':
        $v = $value > 0 ? sDB['countries'][$value][1] : '-';
        break;

      case 'county_id':
        $v = $value > 0 ? sDB['counties'][$value][0] : '-';
        break;

      case 'district_id':
        $v = $value > 0 ? sDB['districts'][$value][0] : '-';
        break;

      case 'artpiece_location_id':
        $v = $value == 1 ? 'Köztéri' : 'Nem köztéri';
        break;

      case 'artpiece_condition_id':
        $v = $value > 0 ? sDB['artpiece_conditions'][$value][0] : '-';
        break;

      case 'not_public_type_id':
        $v = $value > 0 ? sDB['not_public_types'][$value][0] : '-';
        break;

      case 'dates':
        $dates_ = _json_decode($value);
        $dates = [];
        if (count($dates_) > 0) {
          foreach ($dates_ as $date) {
            list($date_type, $date_string) = $this->parse_date_row($date);
            $dates[] = $date_string .  ' (' . strtolower($date_type) . ')';
          }
        }
        $v = count($dates) > 0 ? implode($options['change_separator'], $dates) : '-';
        break;


      // Ezeket csak lokálisan lehet megoldani
      case 'photo_id':
      case 'photo_slug':
      case 'descriptions':
      case 'photolist':
      case 'photos':
        $v = $options['full_values'] ? $value : '...';
        continue;
        break;

      case 'hun_related':
      case 'not_artistic':
      case 'temporary':
      case 'anniversary':
      case 'local_importance':
      case 'national_heritage':
      case 'copy':
      case 'reconstruction':
        $v = $value == 1 ? 'Jelölve' : '-';
        break;

      case 'manage_user_id':
        $v = @$this->MC->t('users', $value)['name'];
        break;
    }

    return [$name, $v];
  }


  /**
   *
   * Fotó mezők neve és értékének parszolása
   * edit részleteknél hasznos
   *
   * @param $field
   * @param $value
   * @return array
   */
  public function photo_field($field, $value) {
    $field_name = $field_value = '';

    $field_name = sDB['photo_fields'][$field];

    switch ($field) {
      case 'artist':
      case 'sign':
      case 'other':
      case 'joy':
      case 'unveil':
      case 'other_place':
      case 'cover':
      case 'archive':
        $field_value = $value == 1 ? 'Jelölve' : 'Jelölés levéve';
        break;

      case 'top':
        $field_value = $value == 1 ? 'Igen' : '-';
        break;

      case 'sign_artist_id':
        $field_value = @$this->MC->t('artists', $value)['name'];
        break;

      case 'license_type_id':
        $field_value = sDB['license_types'][$value];
        break;

      default:
        $field_value = $value;
    }

    return [$field_name, $field_value];
  }


  /**
   *
   * Alkotó lista
   *
   * @param $artpiece
   * @param array $options
   * @return array
   */
  public function artists_array($artpiece, $options = []) {
    $options = (array)$options + [
      'separated' => false, // alkotók és közreműködők külön
    ];

    if ($options['separated']) {
      $array = [
        'artists' => [],
        'contributors' => [],
      ];
    } else {
      $array = [];
    }

    $artists_array = json_decode($artpiece['artists'], true);

    if (@count($artists_array) > 0) {

      $i = 0;

      foreach ($artists_array as $item) {
        $artist_item = [
          'id' => $item['id'],
          'name' => $this->Artists->name($item['id']),
          'profession_id' => $item['profession_id'],
          'contributor' => $item['contributor'],
          //'question' => $item['question'],
          'rank' => $item['rank'],
        ];

        if ($options['separated']) {
          $type = $item['contributor'] == 1 ? 'contributors' : 'artists';
          $array[$type][] = $artist_item;
        } else {
          $i++;
          // Ezzel garantáljuk, hogy a közreműködők az alkotók után kerülnek
          $rank = $item['contributor'] == 1 ? 20 + $i  : $i;
          $array[$rank] = $artist_item;
        }
      }

      if (!$options['separated']) {
        ksort($array);
        $array = array_values($array);
      }
    }

    return $array;
  }



  /**
   *
   * A beküldött adat és a műlap adatainak összevetése
   * a problémát az okozza, hogy a több dimenziós tömb második és további szintjeit
   * már képtelen összevetni.
   * Az alapgond az, hogy ez a két tömb szerinte különbözik:
   *
   * [
   *   'id' => 1,
   *   'rank' => 0
   * ]
   *
   * [
   *   'rank' => 0,
   *   'id' => 1
   * ]
   * Pedig csak a kulcsok sorrendje más.
   * Tehát minden egyes tömböt egyesével kell lebontani és összevetni...(?)
   *
   *
   * @param $data
   * @param $artpiece
   * @return array
   */
  public function diff_edit ($data, $artpiece, $debug = false) {

    // Az összehasonlíthatóság miatt tömbbé tesszük a JSON mezőket,
    // mert a JSON string nem egészen azonos
    $data_ = $this->Arrays->json_to_array($data);
    // Ki kell szedni a NULL-okat, mert az nem ugyanaz, mint az üres bizonyos vizsgálatoknál...
    foreach ($artpiece as $key => $value) {
      if ($value == NULL) {
        $artpiece[$key] = '';
      }
    }
    $artpiece_ = $this->Arrays->json_to_array($artpiece);

    // ez nem tud normális rekurzív összevetést kavart kulcsoknál
    /*$changes = array_map('unserialize', array_diff(
      array_map('serialize', $data_),
      array_map('serialize', $artpiece_)
    ));*/

    $changes = $this->Arrays->array_diff_recursive($data_, $artpiece_);

    if ($debug) {
      var_dump($artpiece_);
      echo '<br /><br />==========================<br /><br />';
    }

    // Visszaalakítjuk a tömböket JSON-ná, hogy menthető legyen
    foreach ($changes as $key => $value) {
      if (is_array($value)) {
        $changes[$key] = _json_encode($value, false);
      }
    }

    if ($debug) {
      var_dump($changes); exit;
    }

    return $changes;
  }


  /**
   *
   * Fotó törzsadat diffelő.
   * Kiszedi azokat a fotókat a tömbből, amelyek adata egyezik.
   *
   * @param $changes
   * @param $artpiece
   * @return mixed
   */
  public function diff_photos ($changes, $artpiece, $prev_data) {
    $photolist = _json_decode($changes['photolist']);

    $changed_photolist = [];

    if (!is_array($photolist) || count($photolist) == 0) {
      unset($changes['photolist']);
      return $changes;
    }

    $photos = $this->DB->find('photos', [
      'type' => 'list',
      'conditions' => ['artpiece_id' => $artpiece['id']],
    ]);

    $prev_data_photos = [];

    foreach ($photolist as $key => $item) {
      $has_changed = false;

      // Sort mentés előtt töröltek a listából egy fotót
      if (!isset($photos[$item['id']])) {
        continue;
      }

      $photo = $photos[$item['id']];

      // Ezeket a mezőket nézzük
      $comparables = ['text', 'source', 'other', 'joy', 'archive', 'year', 'artist', 'artist_id',
        'sign', 'sign_artist_id', 'unveil', 'other_place', 'license_type_id'];
      foreach ($comparables as $field) {
        /**
         *
         *
         * Akkor változik a mező, ha teljesen egységesre dekódoltan más a string
         * itt nem volt elég a htmlentities, mert a &#39; nem lesz ok, ezért azt
         * visszaalakítjuk előbb a _html_entity_decode-dal, aztán rájaeresztjük
         * a kódolót, így minden garantáltan ugyanúgy néz ki.
         *
         * Gond volt itt azzal, ha extra karakter volt a fotó mezőben. Pl.: "malacok"
         * Ez mysql-ben így van. De bement mongoba így az editbe: &#34; mert a diffelő
         * erre alakította át az inputot. Na, ez ugye ugyanaz. De nem, mert az aposztróf
         * másik formája &quot; is.
         * Éljenek a karakterek!
         * @karakterekkel töltött szívásidő eddig: 25 óra
         *
         */
        if (isset($item[$field])
          && trim(htmlentities(_html_entity_decode($item[$field]))) != trim(htmlentities(_html_entity_decode($photo[$field])))) {
          $has_changed = true;
          $changed_photolist[$key][$field] = $item[$field];
          $prev_data_photos[$key][$field] = $photo[$field];
        }
      }

      // Kell az ID és a slug mindenképp, hogy tudjuk, mi az
      if ($has_changed) {
        $changed_photolist[$key]['id'] = $photo['id'];
        $changed_photolist[$key]['slug'] = $photo['slug'];
        $prev_data_photos[$key]['id'] = $photo['id'];
        $prev_data_photos[$key]['slug'] = $photo['slug'];
      }
    }

    if (count($changed_photolist) > 0) {
      $changes['photolist'] = _json_encode($changed_photolist);
    } else {
      unset($changes['photolist']);
    }

    if (count($prev_data_photos) > 0) {
      $prev_data['photolist'] = $prev_data_photos;
    }

    return [$changes, $prev_data];
  }



  public function diff_descriptions ($changes, $artpiece, $prev_data) {
    $descriptions = _json_decode($changes['descriptions']);

    if (!is_array($descriptions) || count($descriptions) == 0) {
      unset($changes['descriptions']);
      return $changes;
    }

    $changed_descriptions = [];
    $prev_data_descriptions = [];

    foreach ($descriptions as $key => $description) {
      // Új jött létre
      if (in_array($description['id'], ['new_hun', 'new_eng'])) {
        $changed_descriptions = $description;
        continue;
      }
      // Meglévő módosulna
      $prev_desc = $this->Mongo->first('artpiece_descriptions', ['_id' => $description['id']]);
      if ($prev_desc) {

        foreach (['text', 'source'] as $field) {
          if (trim(htmlentities($description[$field])) != trim(htmlentities($prev_desc[$field]))) {
            $changed_descriptions[$key][$field] = $prev_desc[$field];
            $prev_data_descriptions[$key][$field] = $prev_desc[$field];
          } else {
            unset($descriptions[$key][$field]);
          }
        }

        if (isset($prev_data_descriptions[$key])) {
          $prev_data_descriptions[$key]['id'] = $prev_desc['id'];
        }
      }
    }

    if (count($prev_data_descriptions) > 0) {
      $prev_data['descriptions'] = $prev_data_descriptions;
    }

    $changes['descriptions'] = $descriptions;

    return [$changes, $prev_data];
  }



  /**
   *
   * Hely létrehozása műlapról
   *
   * @param $name
   * @param $data
   * @return array|bool|int|null|string
   */
  public function create_place($name, $data) {
    $place_id = $this->DB->insert('places', [
      'name' => $name,
      'country_code' => strtoupper(@$data['country_code']),
      'created' => time(),
      'modified' => time(),
      'user_id' => CORE['USERS']['places'],
      'creator_artpiece_id' => @$data['id'] > 0 ? $data['id'] : 0,
      'creator_user_id' => @$data['user_id'] > 0 ? $data['user_id'] : 0,
      'artpiece_count' => 1, // ez, ami miatt szerkesztjük
    ]);

    return $place_id;
  }


  /**
   *
   * Kapcsolódó műlapok listáját megépítjük
   *
   * @param $artpiece
   * @return array
   */
  public function get_connected_artpieces($artpiece) {
    // ID-t kaptunk
    if (is_int($artpiece)) {
      $artpiece = $this->DB->first('artpieces', $artpiece, ['fields' => 'connected_artpieces']);
    }

    $connected_artpieces = [];

    $connected_artpieces_ = _json_decode($artpiece['connected_artpieces']);
    if (is_array($connected_artpieces_) && count($connected_artpieces_) > 0) {
      foreach ($connected_artpieces_ as $ap_id => $type_id) {
        $ap = $this->DB->first('artpieces', $ap_id, ['fields' => ['id', 'title', 'photo_slug', 'status_id']]);
        if ($ap) {
          $ap['type'] = $type_id;
          $connected_artpieces[] = $ap;
        }
      }
    }

    return $connected_artpieces;
  }


  /**
   *
   * Kapcsolt gyűjtemények listája
   *
   * @param $artpiece
   * @param array $types
   * @return array
   */
  public function get_connected_sets($artpiece, $types = [1,2]) {
    $sets_ = _json_decode($artpiece['connected_sets']);
    $sets = [];
    if (is_array($sets_) && count($sets_) > 0) {
      foreach ($sets_ as $item) {
        $set = $this->Mongo->first('sets', [
          '_id' => $item,
          'set_type_id' => ['$in' => $types]
        ]);
        if ($set) {
          $set['artpiece_count'] = count($set['artpieces']);
          unset($set['artpieces']);
          $sets[] = $set;
        }
      }
    }

    return $sets;
  }



  /**
   *
   * Kapcsolt műlapok kezelése
   *
   * Kiolvassuk a kapcsolt műlap kapcsolásait, és ha benne van ez
   * akkor megváltoztatjuk a kapcsolás típusá ott is
   * ha nincs benne, betesszük.
   *
   * Előzmény/utód pont fordítva kell: előzményre utódként,
   * utódra előzényként jegyezzük rá a lapot.
   *
   *
   * Azt is kezeljük, ha eddig benne volt, most nincs valami,
   * mert akkor a már nem kapcsoltnál is törölni kell ezt.
   *
   * Itt szúrjuk be a másik lapra a szerkesztést is, mint elfogadott.
   *
   * @param $connected_artpieces
   * @param $artpiece_id
   * @param $user_id
   */
  public function handle_connected_artpieces($connected_artpieces, $artpiece_id, $user_id) {

    // Kapcsolódó lapokat ebbe gyűjtjük
    $connected_updates = [];

    // Valószínűleg JSON-t kapunk
    if (!is_array($connected_artpieces)) {
      $connected_artpieces = _json_decode($connected_artpieces);
    }


    // Kiolvassuk az adott lapot; itt még eredeti adatok vannak
    $artpiece = $this->DB->first('artpieces', $artpiece_id, [
      'fields' => ['id', 'connected_artpieces']
    ]);

    $c_prev = _json_decode($artpiece['connected_artpieces']);

    // Kivett dolgok; ott is kivenni
    if (count($c_prev) > 0) {
      foreach ($c_prev as $c_p_id => $c_p_type) {
        if (!isset($connected_artpieces[$c_p_id])) {
          // az újban nincs benne ez a régi, törölni kell ott
          // A kapcsolt lap, ahol törlünk
          $connected_artpiece = $this->DB->first('artpieces', $c_p_id, [
            'fields' => ['id', 'connected_artpieces']
          ]);

          $cc = _json_decode($connected_artpiece['connected_artpieces']);
          $cc = !is_array($cc) ? [] : $cc;

          // itt a kapcsolódó kapcsolódiból kivesszük eztetet
          unset($cc[$artpiece_id]);

          $connected_updates[$c_p_id] = $cc;
        }
      }
    }


    // Aktuális állapot kezelése
    if (count($connected_artpieces) > 0) {
      foreach ($connected_artpieces as $connected_artpiece_id => $type) {

        // A kapcsolt lap
        $connected_artpiece = $this->DB->first('artpieces', $connected_artpiece_id, [
          'fields' => ['id', 'connected_artpieces']
        ]);

        if ($connected_artpiece) {
          $cc = _json_decode($connected_artpiece['connected_artpieces']);
          $cc = !is_array($cc) ? [] : $cc;
          $type_pair = $type;
          if ($type == 2) {
            $type_pair = 3;
          }
          if ($type == 3) {
            $type_pair = 2;
          }

          $cc[$artpiece_id] = $type_pair;

          $connected_updates[$connected_artpiece['id']] = $cc;
        }

      }
    }


    // Ha volt kapcsolódó változás kapcsoltaknál, akkor
    // mentjük + editet is beszúrunk róla
    if (count($connected_updates) > 0) {
      foreach ($connected_updates as $connected_id => $connections) {
        asort($connections);

        // beszúrjuk elfogadottként
        $artpiece = $this->DB->first('artpieces', $connected_id, [
          'fields' => ['id', 'connected_artpieces', 'user_id', 'status_id']
        ]);
        $this->upsert_edit(['connected_artpieces' => json_encode($connections)], $artpiece, $user_id, [
          'status_id' => 5,
          'parsed' => true,
        ]);

        // frissítjük a lapot
        $this->DB->update('artpieces', [
          'connected_artpieces' => json_encode($connections),
          'modified' => time(),
        ], $connected_id);

        $this->Cache->delete('cached-view-artpieces-view-' . $connected_id);
      }
    }

  }


  /**
   *
   * Kapcsolt gyűjtemények kezelése
   * beteszi, amiben még nincs benne
   * kiveszi, ami nem jött a küldött JSOn-ban
   *
   * @param $connected_sets
   * @param $artpiece_id
   * @param $user_id
   */
  public function handle_connected_sets($connected_sets, $artpiece_id, $user_id) {
    // Bepakoljuk a setekbe, ha van ilyen set és nincs már benne eleve
    if ($connected_sets != '') {
      $set_ids = $connected_sets;
      if (is_array($set_ids) && count($set_ids) > 0) {
        foreach ($set_ids as $set_id) {
          $set = $this->Mongo->first('sets', ['_id' => $set_id]);
          if ($set && array_search($artpiece_id, array_column($set['artpieces'], 'artpiece_id')) === false) {
            $set['artpieces'][] = [
              'artpiece_id' => (int)$artpiece_id,
              'user_id' => (int)$user_id,
              'created' => time(),
            ];
            $this->Mongo->update('sets', ['artpieces' => $set['artpieces']], ['_id' => $set_id]);
          }

          $this->Cache->delete('cached-view-sets-view-' . $set_id);
        }
        $this->Cache->delete('cached-view-sets-index');
      }
    }

    // Itt pedig kivesszük azokból, amik nem jöttek
    // ha üres jött, mindből kivesszük
    $sets = $this->Mongo->find_array('sets', [
      'artpieces.artpiece_id' => (int)$artpiece_id
    ]);
    if (count($sets) > 0) {
      foreach ($sets as $set) {
        foreach ($set['artpieces'] as $key => $array) {
          // ez nem jött, vagy üres
          if (($connected_sets == '' || !in_array($set['id'], $connected_sets))
            && $array['artpiece_id'] == $artpiece_id) {
            unset($set['artpieces'][$key]);
          }
        }
        $this->Mongo->update('sets', ['artpieces' => $set['artpieces']], ['_id' => $set['id']]);
        $this->Cache->delete('cached-view-sets-view-' . $set['id']);
      }

      $this->Cache->delete('cached-view-sets-index');
    }

  }



  /**
   *
   * Leírások beszúrása / felülírása
   *
   * @param $changes
   * @return mixed
   */
  public function handle_descriptions($changes, $artpiece_id, $user_id, $manage_user_id, $receiver_user_id = 0) {
    if (isset($changes['descriptions'])) {

      $descriptions = $changes['descriptions'];

      foreach ($descriptions as $description) {
        if (in_array($description['id'], ['new_hun', 'new_eng'])) {
          // ÚJ BESZÚRÁS
          $this->Mongo->insert('artpiece_descriptions', [
            'artpieces' => [(int)$artpiece_id],
            'status_id' => 5,
            'user_id' => (int)$user_id,
            'manage_user_id' => (int)$manage_user_id,
            'receiver_user_id' => (int)$receiver_user_id,
            'lang' => $description['id'] == 'new_hun' ? 'HUN' : 'ENG',
            'text' => $description['text'],
            'source' => $description['source'],
            'created' => @$description['comment_time'] > 0
              ? $description['comment_time'] : time(),
            'modified' => time(),
            'approved' => time(),
          ]);
        } else {
          // MEGLÉVŐ FELÜLÍRÁSA
          $updates = ['modified' => time()];
          if (isset($description['text'])) {
            $updates['text'] = $description['text'];
          }
          if (isset($description['source'])) {
            $updates['source'] = $description['source'];
          }
          $this->Mongo->update('artpiece_descriptions', $updates, ['_id' => $description['id']]);
        }

      }

      // Hogy ne próbáljon mentődni a műlapra
      unset($changes['descriptions']);
    }

    return $changes;
  }


  /**
   *
   * Helység változás van
   *
   * @param $changes
   * @param $prev_data
   * @param $artpiece_id
   * @param $edit_user_id
   * @return mixed
   */
  public function handle_place_change($changes, $prev_data, $artpiece_id, $edit_user_id) {
    if (isset($changes['place_id'])) {

      if ($changes['place_id'] == 110 && @$prev_data['place_id'] != 110) {
        // Most lett BP, BP, mint megye
        $changes['county_id'] = 1;
        $changes['country_id'] = 101;

      } elseif ($changes['place_id'] != 110) {
        // Helység változás és nem BP lett
        $place = $this->MC->t('places', $changes['place_id']);
        $changes['county_id'] = (int)$place['county_id'];
        $changes['country_id'] = (int)$place['country_id'];
        $changes['district_id'] = 0;
      }

    }
    return $changes;
  }


  /**
   *
   * Fotó változások mentése
   * itt csak update van - új fotókat nem approve-osan
   * szúrunk be. Azok mentődnek.
   *
   * @param $changes
   * @param $artpiece_id
   * @param $user_id
   * @param $manage_user_id
   * @return mixed
   */
  public function handle_photos($changes, $artpiece_id, $user_id, $manage_user_id) {
    // Rank update
    if (isset($changes['photos'])) {
      foreach ($changes['photos'] as $p) {
        $this->DB->update('photos', [
          'rank' => $p['rank']
        ], $p['id']);
      }
    }

    if (isset($changes['photolist'])) {
      $photolist = _json_decode($changes['photolist']);

      $user = $this->MC->t('users', $user_id);

      if (count($photolist) > 0) {
        foreach ($photolist as $photo) {
          if (isset($photo['license_type_id']) && $user['license_type_id'] != $photo['license_type_id']) {
            $photo['special_license'] = 1;
          }

          if (isset($photo['artist']) && $photo['artist'] == 0) {
            $photo['artist_id'] = 0;
          }

          if (isset($photo['sign']) && $photo['sign'] == 0) {
            $photo['sign_artist_id'] = 0;
          }

          $data = ['modified' => time()] + $photo;
          if (@$photo['cover'] == 1) {
            $photo_ = $this->DB->first('photos', $photo['id'], ['fields' => ['slug']]);
            $this->DB->update('artpieces', ['photo_id' => $photo['id'], 'photo_slug' => $photo_['slug']], $artpiece_id);
          }
          // ezek nem menthetőek, ill. máshol kezeljük a covert
          $data = _unset($data, ['id', 'slug', 'cover', 'user_id']);

          $this->DB->update('photos', $data, $photo['id']);

          if (isset($data['joy'])) {
            $changes['joy_set'] = true;
          }
        }
      }

      // Hogy ne próbáljon mentődni a műlapra
      unset($changes['photolist']);
    }

    return $changes;
  }


  /**
   *
   * Műlap borító frissítése
   *
   * @param $artpiece_id
   * @param $photo
   * @return array|bool|int|null|string
   */
  public function update_cover($artpiece_id, $photo = false) {
    if ($photo && is_numeric($photo) && $photo > 0) {
      $photo = $this->DB->first('photos', $photo, ['fields' => ['id', 'slug']]);
    }

    if (!$photo) {
      // Nem kaptunk fotót!, vagyis túrni kell.
      // megvan-e, ami most be van állítva és ha nem, akkor
      // beállítani az rank = 1-et
      $artpiece = $this->DB->first('artpieces', $artpiece_id, ['fields' => ['id', 'photo_id', 'photo_slug']]);
      $cover = $this->DB->count('photos', $artpiece['photo_id']);
      if ($cover == 0) {
        $photo = $this->DB->first('photos',['artpiece_id' => $artpiece_id], [
          'fields' => ['id', 'slug', 'rank'],
          'order' => 'rank'
        ]);
      }
    }

    if ($photo) {
      return $this->DB->update('artpieces', [
        'photo_id' => $photo['id'],
        'photo_slug' => $photo['slug']
      ], $artpiece_id);
    }
    return false;
  }


  /**
   *
   * Kiszedi a kapcsolható közös gyűjteményeket
   *
   * @param $artpiece
   * @return array|bool|mixed
   */
  public function get_possible_sets($artpiece) {
    $p = explode(' ', $artpiece['title']);
    $word_filters = [];
    foreach ($p as $word) {
      if (_contains(mb_strtolower($word), ['szobr', 'szobor', 'király', 'szent', 'emlék', 'dombor'])
        || strlen($word) < 4) {
        continue;
      }
      $word_filters[] = ['name' => ['$regex' => $word, '$options' => 'i']];
    }

    // Nem maradt szó, nem SZÓrakozunk!
    if (count($word_filters) == 0) {
      return [];
    }

    $filters = ['$and' => [
      ['set_type_id' => 1], // csak közös
      ['$or' => $word_filters],
    ]];

    $possible_sets = $this->Mongo->find_array('sets', $filters, ['projection' => ['name' => 1]]);

    // Amiket kihagyunk; mongo feltételbe nem tudok _id $nin filtert tenni;
    // sztem azért, mert mert mongoid-vé kellene alakítani az _id tömb minden elemét
    // @todo?
    $excludables = _json_decode($artpiece['connected_sets']);
    if (count($possible_sets) > 0 && is_array($excludables) && count($excludables) > 0) {
      foreach ($possible_sets as $key => $possible_set) {
        if (in_array($possible_set['id'], $excludables)) {
          unset($possible_sets[$key]);
        }
      }
    }

    return $possible_sets;
  }


  /**
   *
   * artpiece['dates'] egy sorát értelmes dátumá alakítja
   *
   * @param $date
   * @param array $options
   * @return array
   */
  public function parse_date_row($date, $options = []) {
    $options = (array)$options + [
      'only_year' => false,
    ];

    $date_string = '';

    // Ha bizonytalan; századnál mindenképp
    if ((int)$date['century'] > 0 || $date['cca'] == 1) {
      $margin = (int)$date['century'] > 0 || $date['y'] > 0 ? 'mr-1' : '';
      $date_string .= '<span class="far fa-question-circle text-muted ' . $margin . '" data-toggle="tooltip" title="Az időpont bizonytalan, körülbelüli vagy ismeretlen"></span>';
    }

    // Csak az évszám kell
    if ($options['only_year']) {
      if ($date['century'] == 0 && $date['y'] > 0) {
        $date_string .= $date['y'];
      } elseif ($date['century'] > 0) {
        $date_string .= $date['century'] < 21 ? (int)$date['century'] . '. század' : '';
      }
      return $date_string;
    }

    if ($date['century'] == 0 && $date['y'] > 0) {
      $date_string .= $date['y'];
      $date_string .= (int)$date['m'] > 0 ? '. ' . mb_strtolower(sDB['month_names'][(int)$date['m']]) : '';
      $date_string .= (int)$date['m'] > 0 && (int)$date['d'] > 0 ? ' ' . (int)$date['d'] . '.' : '';
    } elseif ((int)$date['century'] > 0) {
      $date_string .= $date['century'] < 21 ? (int)$date['century'] . '. század' : '';
    }

    $date_type = sDB['date_types'][$date['type']];

    return [$date_type, $date_string];
  }


  /**
   *
   * Első és utolsó évszám
   *
   * @param $dates
   * @param array $options
   * @return string
   */
  public function get_artpiece_year($dates, $options = []) {
    $options = (array)$options + [
      'last' => true, // ez a legkorábbi igazából
      'only_last_year' => false,
    ];

    $s = '';

    $dates = _json_decode($dates);

    if (count($dates) > 0 && $options['only_last_year']) {
      return $dates[0]['y'];
    }

    if (count($dates) > 0) {
      $dates = $this->Arrays->sort_by_key($dates, 'date', 1);
      $s .= $this->parse_date_row($dates[count($dates)-1], ['only_year' => true]);
    }

    if ($options['last'] && count($dates) > 1) {
      $s .= ' <span data-toggle="tooltip" title="Legkorábbi ismert évszám az alkotással kapcsolatban">(' . $this->parse_date_row($dates[0], ['only_year' => true]) . ')</span>';
    }

    return $s;
  }


  /**
   *
   * Kiolvassa az 1. alkotót
   *
   * @param $artists
   * @param array $options
   * @return mixed|string
   */
  public function get_artpiece_artist($artists, $options = []) {
    $options = (array)$options + [];
    $artists = $this->artists_array(['artists' => $artists], ['separated' => false]);
    $artist = count($artists) > 0 ? $artists[0] : false;
    return $artist;
  }


  /**
   *
   * Hasonló lapok listájának kiolvasása
   *
   * @param $artpiece
   * @param array $options
   * @return mixed
   */
  public function get_similars($artpiece, $options = []) {
    $options = (array)$options + [
      'fields' => ['id', 'title', 'photo_slug'],
      'limit' => 6,
      'conditions' => [
        'status_id' => 5,
        'id <>' => @$artpiece['id'],
      ],
      'type' => 'all',
    ];

    // Cím jött, nem ID
    if (is_string($artpiece)) {
      $title = $artpiece;
      // Nem kell bele az expkludált ID
      $options['conditions'] = ['status_id' => 5];
    } else {
      $title = $artpiece['title'];
    }

    $title = $this->Text->value_words($title, [
      'ignorandus' => sDB['similar_excludes'],
      'min_length' => 2,
    ]);

    $title = addslashes($title);

    $similars = $this->DB->find('artpieces', [
      'type' => $options['type'],
      'conditions' => $options['conditions'],
      'having' => 'score > 0',
      'order' => 'score2 DESC',
      'fields' => array_merge($options['fields'], [
        "MATCH(title) AGAINST ('" . $title . "*' IN BOOLEAN MODE) AS score",
        "MATCH(title) AGAINST ('" . $title . "*') AS score2"
      ]),
      'limit' => $options['limit'],
      //'debug' => true,
    ]);

    return $similars;
  }



  /**
   *
   * Sztori blokk
   *
   * @param $description
   * @param array $options
   * @return string
   */
  public function story($description, $options = []) {
    $options = (array)$options + [
      'intro' => false
    ];

    $s = '';
    $s .= $this->Text->format($description['text'], [
      'source_id' => $description['id'],
      'intro' => $options['intro'],
    ]);
    if (@$description['source'] != '') {
      $s .= '<hr class="highlighter my-2" />';
      $s .= '<div class="text-muted">';
      $s .= '<span class="font-weight-semibold">Források:</span><br /><linkify_custom class="small">' . $this->Text->format_source($description['source'], ['source_id' => $description['id']]) . '</linkify_custom>';
      $s .= '</div>';
    }
    return $s;
  }



  /**
   *
   * Műlap photos mezőjét összerakja és vagy visszaadja,
   * vagy be is menti. A topot is okosan beállítja kapott, vagy kiolvasott értékből.
   *
   * Élménykép nem lehet top!
   *
   * @param $artpiece_id
   * @param bool $update
   * @param bool $top_photo_count
   * @return array|bool|int|null|string
   */
  public function update_photos_field($artpiece_id, $update = true, $top_photo_count = false) {
    $photos = $this->DB->find('photos',[
      'conditions' => ['artpiece_id' => $artpiece_id],
      'fields' => ['id', 'slug', 'rank', 'joy', 'other', 'other_place'],
      'order' => 'rank'
    ]);

    if (!$top_photo_count) {
      $artpiece = $this->DB->first('artpieces', $artpiece_id, ['fields' => ['top_photo_count', 'photo_count']]);
      $top_photo_count = $artpiece['top_photo_count'] == 0
        ? $artpiece['photo_count'] : $artpiece['top_photo_count'];
    }


    // Maxminoljuk a konfigban megadottal
    $top_photo_count = min(sDB['limits']['artpieces']['top_photo_max'],
      max(sDB['limits']['artpieces']['top_photo_min'], $top_photo_count));

    // ?? miért?
    $top_photo_count = max(sDB['limits']['artpieces']['top_photo_min'], $top_photo_count);

    $photos_array = [];

    if (count($photos) > 0) {
      $rank = $rank_joy = $rank_others = 0;
      foreach ($photos as $photo) {
        if ($photo['joy'] == 1) {
          $rank_joy++;
          $rank_fact = $rank_joy;
        } elseif ($photo['other'] == 1 || $photo['other_place'] == 1) {
          $rank_others++;
          $rank_fact = $rank_others;
        } else {
          $rank++;
          $rank_fact = $rank;
        }
        $photos_array[] = [
          'id' => (int)$photo['id'],
          'slug' => $photo['slug'],
          'rank' => $rank_fact,
          'top' => $rank_fact < $top_photo_count && $photo['joy'] != 1
            && $photo['other'] != 1 && $photo['other_place'] != 1 ? 1 : 0
        ];
      }
    }

    $json = _json_encode($photos_array);

    if ($update) {
      $success = $this->DB->update('artpieces', [
        'photos' => $json,
        'photo_count' => count($photos),
        'top_photo_count' => $top_photo_count,
        'updated' => time(),
      ], $artpiece_id);
      return $success;
    } else {
      return $json;
    }
  }


  /**
   * Friss műlapokkal kapcsolatos cache-ek ürítése
   */
  public function delete_artpieces_cache() {
    $this->Cache->delete('SearchController::index');
    $this->Cache->delete('SpaceController::index::submissions');
    $this->Cache->delete('SpaceController::index::latests');
    $this->Cache->delete('cached-view-pages-index');
    $this->Cache->delete('cached-view-artpieces-index');
    return true;
  }



  /**
   *
   * Frissíti a mongo bejegyzést és törli a cache-t,
   * amit majd az curlos automata újracsinál
   *
   * @param $artpiece_id
   * @return bool
   */
  public function generate($artpiece_id) {
    if ($artpiece_id > 0) {
      $this->Cache->delete('cached-view-artpieces-view-' . $artpiece_id);
      $this->Mongo->insert('jobs', [
        'class' => 'artpieces',
        'action' => 'generate',
        'options' => ['id' => $artpiece_id],
        'created' => date('Y-m-d H:i:s'),
      ]);
      return true;
    }
    return false;
  }



  /**
   *
   * User műlapszámának változásakor futó újraszámolások,
   * cach ürítések, stb.
   *
   * Azonnal számolunk, nem jobba tesszük.
   *
   * @param $user_id
   * @return bool
   */
  public function update_user_things($user_id) {
    // Létrehozó user dolgai
    $user_artpieces = $this->DB->count('artpieces', [
      'status_id' => 5,
      'user_id' => $user_id,
    ]);
    $user_weekly_artpieces = $this->DB->count('artpieces', [
      'status_id' => 5,
      'user_id' => $user_id,
      'published >' => strtotime('last monday 00:00', strtotime('Sunday'))
    ]);

    $this->DB->update('users', [
      'artpiece_count' => $user_artpieces,
      'weekly_artpieces' => (int)$user_weekly_artpieces
    ], $user_id);

    // Edit lista törlése
    $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_edit_list::' . $user_id);
    $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_modified_list::' . $user_id);

    return $user_artpieces;
  }



  /**
   *
   * Műlap kiolvasás a beadott tömb/objektum megadott kulcsai alapján.
   *
   *
   * @param $array
   * @param array $options
   * @return array
   */
  public function extract ($array, $options = []) {
    $options = (array)$options + [
      'type' => 'list',
      'key' => 'artpiece_id',
      'fields' => ['id', 'title', 'photo_id', 'photo_slug'],
      'order' => 'id ASC',
    ];
    $artpieces = [];
    $artpiece_ids = [];

    if (count($array) > 0) {
      // Direkt nem konvertálom, hogy ezzel se menjen a proci
      foreach ($array as $item) {
        if (!is_array($item)) {
          $key = $options['key'];
          if (@$item->$key > 0) {
            $artpiece_ids[] = $item->$key;
          }
        } else {
          if (@$item[$options['key']] > 0) {
            $artpiece_ids[] = $item[$options['key']];
          }
        }
      }

      $artpieces = $this->DB->find('artpieces', [
        'conditions' => ['id' => $artpiece_ids],
        'type' => $options['type'],
        'fields' => $options['fields'],
        'order' => $options['order'],
      ]);
    }

    return $artpieces;
  }



  /**
   *
   * Műlapra kiírandó paraméterek sorbarendezése típus szerint, hogy ne legyen kaotikus
   *
   * @param $artpiece_parameters
   * @return array|mixed
   */
  public function sort_displayed_parameters($parameters, $artpiece_parameters) {
    if (!is_array($parameters)) {
      $parameters = _json_decode($parameters);
    }

    $sorted = [];

    if (count($parameters) > 0) {
      $final_parameters = [];
      foreach ($parameters as $parameter_id) {
        if (!isset($artpiece_parameters[$parameter_id])) {
          continue;
        }
        $final_parameters[] = $artpiece_parameters[$parameter_id];
      }
      $sorted = $this->Arrays->sort_by_key($final_parameters, 'parameter_group_id');
    }

    return $sorted;
  }

}

