<?php
class ArtpiecesApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

    $artpieces = [];

    if (isset($this->data['lat'])) {
      $artpieces = $this->_get_by_radius($this->data);
    }

    if (isset($this->data['nwlat'])) {
      $artpieces = $this->_get_by_bounds($this->data);
    }

    if (isset($this->data['ids'])) {
      $artpieces = $this->_get_by_ids($this->data);
    }

    $this->send($artpieces);
  }

  /**
   * Szerkesztés
   */
  public function post() {
    $artpiece = $this->DB->first('artpieces', @$this->data['artpiece']['id']);
    if ($artpiece) {

      list($changes, $edit_id) = $this->Artpieces->upsert_edit(
        $this->data['artpiece'],
        $artpiece,
        static::$user['id']
      );

      // Láthatatlan szerkesztésre rámentjük, hogy az
      if (@$this->data['invisible'] == 1 && $edit_id) {
        $this->Mongo->update('artpiece_edits', ['invisible' => 1], ['_id' => $edit_id]);
      }


      /**
       * Nem saját műlapon változtattunk fotó dolgot. Ha saját a fotó, akkor
       * ebből is approve lesz.
       */
      $only_my_photos = false;
      if ($edit_id
        && ($artpiece['user_id'] != static::$user['id'])
        && count($changes) == 1 && isset($changes['photolist'])) {
        $photolist = _json_decode($changes['photolist']);
        if (count($photolist) > 0) {
          $only_my_photos = true;
          foreach ($photolist as $item) {
            $count = $this->DB->count('photos', [
              'id' => @$item['id'],
              'user_id' => static::$user['id'],
            ]);
            if ($count != 1) {
              $only_my_photos = false;
            }
          }
        }

      }

      // Ha volt változás
      // Saját műlap, vagy meghívott vagy headitor vagy admin vagyok
      // és jóváhagyást szerkesztést pipáltam
      // vagy csak a saját fotóimat piszkáltam más lapján
      if ($edit_id
        && ($artpiece['user_id'] == static::$user['id']
          || ($this->Users->is_head(static::$user) && @$this->data['approve'] == 1)
          || $only_my_photos
        )) {

        $this->Artpieces->approve_edit($artpiece['id'], $edit_id, static::$user['id']);

        $this->Artpieces->generate($artpiece['id']);

        $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_edit_list::' . static::$user['id']);

        if ($artpiece['status_id'] == 5) {
          $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_modified_list::' . static::$user['id']);
        }
      } else {
        // Ha nincs azonnali elfogadás, akkor is deletézünk,
        // hogy megjelenjen a nyitott szerk. a történetben
        $this->Artpieces->generate($artpiece['id']);

        // Frissebb műlapoknál töröljük a kereső kezdőlap cache-t is
        if ($artpiece['published'] > strtotime('-1 week')) {
          $this->Artpieces->delete_artpieces_cache();
        }
      }

      // Ha jött komment, beszúrjuk kapcsolódóként,
      // hacsak nem láthatatlan szerk. mert akkor nincsenek kommentek
      if (@$this->data['comment'] != '' && @$this->data['invisible'] != 1) {
        $this->Mongo->insert('comments', [
          'artpiece_edits_id' => $edit_id,
          'artpiece_id' => (int)$artpiece['id'],
          'text' => $this->data['comment'],
          'user_id' => (int)static::$user['id'],
          'user_name' => static::$user['name'],
          'no_wall' => 1,
          'created' => time(),
          'modified' => time(),
          'approved' => time()
        ]);
      }

      // Ha nem a tulaj csinálta a lapján, akkor szólunk neki
      if ($artpiece['user_id'] != static::$user['id']) {
        $title = isset($artpiece['edit_id']) ? 'Módosult szerkesztés' : 'Új szerkesztés';
        $body_end = isset($artpiece['edit_id']) ? 'módosult egy szerkesztés.' : 'új szerkesztés jött létre.';
        $this->Notifications->create($artpiece['user_id'], $title, '"' . $artpiece['title'] . '" c. műlapodon ' . $body_end, [
          'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit_id,
          'type' => 'artpieces',
        ]);
      }

      // Ez pedig ha (többek közt) nem a műlapgazda által írt
      // sztorit írja át nem a sztori szerzője... :)
      // Csak az új beszúrásról szólunk
      if (!isset($artpiece['edit_id']) && isset($changes['descriptions'])) {
        foreach ($changes['descriptions'] as $description) {
          if (!isset($description['id']) || strpos($description['id'], 'new') !== false) {
            continue;
          }
          $desc = $this->Mongo->first('artpiece_descriptions', $description['id']);
          if ($desc['user_id'] != $artpiece['user_id'] && $desc['user_id'] != static::$user['id']) {
            $this->Notifications->create($desc['user_id'], 'Sztoridat módosítanák', '"' . $artpiece['title'] . '" c. műlapon módosítanák a sztoridat. Ezt a műlap gazdája, inaktivitása esetén a közösség hagyhatja jóvá.', [
              'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit_id,
              'type' => 'artpieces',
            ]);
          }
        }
      }

      $this->send([
        'edits' => count($changes),
        'edit_id' => $edit_id
      ]);
    }

    $this->send([]);
  }




  public function put() {

  }



  public function user_memo() {
    $artpiece = $this->DB->first('artpieces', [
      'id' => $this->data['id'],
      'user_id' => static::$user['id']
    ], [
      'fields' => ['id']
    ]);

    if ($artpiece) {
      $update = $this->DB->update('artpieces', [
        'user_memo' => $this->data['text'],
        'user_memo_updated' => time(),
      ], $artpiece['id']);

      if ($update) {
        $this->send(['success' => true]);
      }
    }

    $this->send([]);
  }


  public function admin_memo() {
    $artpiece = $this->DB->first('artpieces', [
      'id' => $this->data['id'],
    ], [
      'fields' => ['id']
    ]);

    if ($artpiece && $this->Users->is_head(static::$user)) {
      $update = $this->DB->update('artpieces', [
        'admin_memo' => $this->data['text'],
        'admin_memo_updated' => time(),
      ], $artpiece['id']);

      if ($update) {
        $this->send(['success' => time()]);
      }
    }

    $this->send([]);
  }


  public function photos() {

    if (isset($this->data['_files']) && @count($this->data['_files']) > 0) {
      $artpiece = $this->DB->first('artpieces', $this->data['artpiece_id']);

      // Ha nem saját, akkor csak közteres vagy publikus lap lehet
      if (!$artpiece
        || ($artpiece['user_id'] != static::$user['id']
          && !in_array($artpiece['status_id'], [2,5])
          && !$this->Users->owner_or_head_or_invited($artpiece, static::$user)
        )) {
        $this->send(['errors' => [texts('mentes_hiba')]]);
      }

      $photos = _json_decode($artpiece['photos']);
      $photo_count = is_array($photos) ? count($photos) : 0;

      $min_size = $this->data['photo_upload_type'] == 2
        ? sDB['limits']['photos']['archive_min_size'] : sDB['limits']['photos']['min_size'];

      $i = 0;
      $inserts = 0;
      $errors = [];

      $event_photos = [];

      // Rendezem fájlnév szerint
      $this->data['_files'] = $this->Arrays->sort_by_key($this->data['_files'], 0);

      foreach ($this->data['_files'] as $key => $file) {
        $i++;

        $rank = $photo_count + $i;

        // Eredeti kép mentése
        $filename = $file[0];
        $ext = $this->File->get_ext($file[0]);
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;
        $prefix = 'u' . static::$user['id'] . '-a' . $artpiece['id'] . '-';
        $slug = $prefix . uniqid() . '-' . bin2hex(random_bytes(12));
        $original_slug = $prefix . uniqid() . '-' . sha1(uniqid());
        $original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $original_slug . '.' . $ext;
        $type = $this->File->save_base64_data($file[1], $original_path);

        // Szegény nagy fájloktól elfárad
        usleep(300);

        if (!_contains($type, 'image')) {
          unlink($original_path);
          $errors[] = $file[0] . ' nem képfájl.';
          continue;
        }

        list($width, $height) = getimagesize($original_path);
        if ($width < $min_size && $height < $min_size) {
          unlink($original_path);
          $errors[] = $file[0] . ' mérete kisebb, mint ' . $min_size . ' px.';
          continue;
        }


        if (_contains($type, ['jpg', 'jpeg'])) {
          $image_data = $this->File->read_image_data($original_path);
        } else {
          // Nem jpg, konvertáljuk!
          $image_data = [];
          $new_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $original_slug . '.jpg';
          $converted = $this->File->convert_to_jpg($original_path, $new_path, [
            'quality' => 100,
            'delete_source' => true,
          ]);
          if (!$converted) {
            // upsz...
            $errors[] = $file[0] . ' feldolgozása nem sikerült.';
            continue;
          }
          $original_path = $new_path;
        }

        usleep(100);

        $photo = [
          'artpiece_id' => $artpiece['id'],
          'artpieces' => _json_encode([(string)$artpiece['id']], false, false),
          'rank' => $rank,
          'status_id' => 5,
          'created' => time(),
          'modified' => time(),
          'approved' => time(),
          'filename' => $filename,
          'slug' => $slug,
          'original_slug' => $original_slug,
          'user_id' => static::$user['id'],
          'license_type_id' => static::$user['license_type_id'],
          'exif_json' => _json_encode(@$image_data['exif']),
          'exif_taken' => @$image_data['taken'] > 0
            || @$image_data['taken'] > strtotime('+12 hours') ? $image_data['taken'] : 0,
          'exif_coordinates' => @$image_data['exif_coordinates'] != '' ? _json_encode($image_data['exif_coordinates']) : '',
          'filesize' => @$image_data['size'] > 0 ? $image_data['size'] : 0,
          'width' => $width > 0 ? $width : 0,
          'height' => $height > 0 ? $height : 0,
          'added' => $artpiece['user_id'] != static::$user['id'] ? 1 : 0,
          'receiver_users' => _json_encode([(string)$artpiece['user_id']], false, false),
          'before_shared' => in_array($artpiece['status_id'], [2,5]) ? 0 : 1,
        ];

        if ($this->data['photo_upload_type'] == 2) {
          // Archívok
          $photo['license_type_id'] = 6;
          $photo['special_license'] = 1;
          $photo['archive'] = 1;
          $photo['archive_locked'] = 1;
        }

        if ($this->data['photo_upload_type'] == 3) {
          $photo['joy'] = 1;
        }

        // Beszúrom a képet
        $photo_id = $this->DB->insert('photos', $photo);

        if ($photo_id > 0) {
          $inserts++;
          // Beszúrom a watermark, resize feladatot
          $this->Mongo->insert('jobs', [
            'class' => 'photos',
            'action' => 'handle',
            'options' => [
              'id' => $photo_id,
              'watermark' => true
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
          // Ez a borcsi
          if ($rank == 1) {
            $cover = [
              'photo_id' => $photo_id,
              'photo_slug' => $slug,
            ];
          }

          $event_photos[] = ['id' => $photo_id, 'slug' => $slug];
        }
      }

      if ($inserts > 0) {

        $top_photo_count = $artpiece['top_photo_count'] > 0 ? $artpiece['top_photo_count'] : false;

        $photos_json = $this->Artpieces->update_photos_field($artpiece['id'], false, $top_photo_count);

        $updates = [
          'photo_count' => count(_json_decode($photos_json)),
          'photos' => $photos_json,
          'updated' => time(),
          'modified' => time(),
          'cached' => 0, // hogy generálja újra mindenképp
        ];
        if (isset($cover) && @$cover['photo_id'] > 0) {
          $updates = array_merge($updates, $cover);
        }
        // Ha több vagy kevesebb a top fotó, mint a

        // Ha már rég frissült, akkor long_update is
        if ($artpiece['updated'] < strtotime('-30 days')) {
          $updates['long_updated'] = time();
          $updates['long_updated_photos'] = time();
        }
        $this->DB->update('artpieces', $updates, $artpiece['id']);

        $this->Artpieces->generate($artpiece['id']);

        if (in_array($artpiece['status_id'], [2,5])) {
          $this->Events->create(6, [
            'user_id' => static::$user['id'],
            'artpiece_id' => $artpiece['id'],
            'photo_count' => $inserts,
            'photos' => $event_photos,
            'related_users' => [$artpiece['user_id']],
          ]);
        }

        if ($artpiece['user_id'] != static::$user['id']) {
          $this->Notifications->create($artpiece['user_id'], $inserts . ' új fotó "' . $artpiece['title'] . '" műlapodra', static::$user['name'] . ' ' . $inserts . ' új fotót töltött műlapodhoz.', [
            'link' => $this->Html->link_url('', ['artpiece' => $artpiece]),
            'type' => 'artpieces',
          ]);
        }
      }

      /**
       * Sajnos itt Session flash-t kell használnom...
       * emiatt ez az API nem szép. De a JS/URL flash átadás nem megy az URL hash logika miatt,
       * ami a szerk. felületen van. Az egészet újra kellene gondolni.
       */
      if (count($errors) > 0) {
        $this->Session->set_message($inserts . ' kép feltöltése járt sikerrel.<br /><br />Az alábbi állományok mentése nem sikerült:<br />' . implode('<br />-', $errors), 'danger', 'div');
      } else {
        if ($inserts > 0) {
          $this->Session->set_message('Sikeresen feltöltöttük a kiválasztott ' . $inserts . ' képfájlt.', 'success', 'div');
        } else {
          $this->Session->set_message('Nem történt feltöltés.', 'info', 'div');
        }
      }

      $this->send(['success' => true]);
    }

    $this->send(['errors' => ['Válassz ki legalább 1 képfájlt.']]);
  }


  /**
   * Másolás vagy áthelyezés; mindent pakol vagy egyet
   */
  public function photo_copy() {
    if ($this->data['photo_id'] == 'all') {
      $photos = $this->DB->find('photos', [
        'conditions' => ['artpiece_id' => $this->data['source_artpiece_id']],
        'order' => 'rank ASC',
      ]);
    } else {
      $photos = $this->DB->find('photos', [
        'conditions' => $this->data['photo_id'],
      ]);
    }

    $source_artpiece_id = (int)$this->data['source_artpiece_id'];
    if (($this->data['target_artpiece_id'] > 0) == false && $this->data['target_artist_id'] > 0
      && $this->data['photo_id'] > 0 && $this->data['delete'] == 1) {
      // Alkotó a cél és csak egy fotót akarunk áthelyezni
      $target_artist_id = (int)$this->data['target_artist_id'];
      $target_artist = $this->DB->find_by_id('artists', $target_artist_id, ['fields' => ['user_id']]);
      $target_artpiece_id = false;
      $target_artpiece = false;
    } else {
      $target_artpiece_id = (int)$this->data['target_artpiece_id'];
      $target_artpiece = $this->DB->find_by_id('artpieces', $target_artpiece_id, [
        'fields' => ['user_id', 'photo_count']
      ]);
    }

    $error = false;
    $i = 0;

    foreach ($photos as $photo) {
      if ($this->Users->owner_or_head($photo, static::$user)) {

        $i++;

        $receiver_users = _json_decode($photo['receiver_users']);

        $artpieces = _json_decode($photo['artpieces']);

        // Ezeket a fotókat frissítjük
        $photo_ids = [$photo['id']];

        if ($this->data['delete'] == 1) {
          // ÁTHELYEZÉS

          if ($target_artpiece) {
            // Műlap
            // Pakolás
            $artpieces = $this->Photos->artpiece_switch($photo, $source_artpiece_id, $target_artpiece_id, false, $artpieces);

            // Átrakjuk
            $this->DB->update('photos', [
              'artpiece_id' => $target_artpiece_id,
              'rank' => ($target_artpiece['photo_count'] + $i),
            ], $photo['id']);

            $receiver_users = [(string)$target_artpiece['user_id']];
          } else {
            // Alkotó
            $this->DB->update('photos', [
              'artpieces' => '',
              'artpiece_id' => 0,
              'rank' => 0,
              'artist' => 0,
              'artist_id' => 0,
              'portrait' => 1,
              'portrait_artist_id' => $target_artist_id,
            ], $photo['id']);
            $receiver_users = [(string)$target_artist['user_id']];
          }

          // Ha a régi helyen cover volt, akkor újracoverálunk
          if ($this->data['source_cover'] == 1) {
            $this->Artpieces->update_cover($source_artpiece_id);
          }

        } elseif ($this->data['delete'] == 0) {

          // MÁSOLÁS
          // hozzáadjuk az új műlap az-t a meglévő fotóhoz
          $artpieces = $this->Photos->artpiece_add($photo, $target_artpiece_id, false, $artpieces);

          // létrehozzuk az új fotópéldányt, ami ugyanaz, mint a mostani,
          // csak a target-re kerül
          $new_photo = array_merge(_unset($photo, ['id']), [
            'modified' => time(),
            'approved' => time(),
            'artpiece_id' => $target_artpiece_id,
            'rank' => ($target_artpiece['photo_count'] + $i),
          ]);
          $new_photo_id = $this->DB->insert('photos', $new_photo);
          $receiver_users[] = (string)$target_artpiece['user_id'];
          $photo_ids[] = $new_photo_id;
        }

        $this->DB->update('photos', [
          'artpieces' => _json_encode(array_values($artpieces), false, false),
          'receiver_users' => _json_encode(array_unique(array_values($receiver_users)), false, false),
        ], [
          'id' => $photo_ids
        ]);

        // Frissítjük mindkét műlap photos mezejét
        $this->Artpieces->update_photos_field($source_artpiece_id);
        if ($target_artpiece) {
          // Cél műlap csak, ha volt és nem alkotóra pakoltuk
          $this->Artpieces->update_photos_field($target_artpiece_id);
        }

      } else {
        $error = true;
        break;
      }
    }

    if (!$error) {
      $this->Artpieces->generate($source_artpiece_id);
      if ($target_artpiece) {
        $this->Artpieces->generate($target_artpiece_id);
      } else {
        $this->Cache->delete('cached-view-artists-view-' . $target_artist_id);
      }
      $this->send(['success' => true]);
    }

    $this->send([]);
  }


  /**
   * Fotó törlése lokálisan és S3-ról is, minden méretben
   */
  public function photo_delete() {
    $photo = $this->DB->first('photos', $this->data['photo_id'], [
      'fields' => ['id', 'slug', 'original_slug', 'user_id', 'artpieces']
    ]);

    if ($this->Users->owner_or_head($photo, static::$user)) {

      $artpiece_id = (int)$this->data['artpiece_id'];

      if ($this->data['target'] == 'all') {
        // Mindent törölni kell
        $success = $this->Photos->delete($photo, static::$user);

      } elseif ($this->data['target'] == 'this') {
        // Adott műlaporól kell csak levenni

        // Milyen lapokon van fent
        $artpieces = _json_decode($photo['artpieces']);
        if (count($artpieces) > 1) {
          // Ha több lapon van fent, akkor végigpörgetjük
          // a példányokat, és mindegyiknél kiszedjük ezt a műlapot
          $same_photos = $this->DB->find('photos', [
            'conditions' => [
              'slug' => $photo['slug'],
              'id <>' => $photo['id'],
            ],
            'fields' => ['id', 'artpieces']
          ]);
          foreach ($same_photos as $same_photo) {
            $this->Photos->artpiece_remove($same_photo, $artpiece_id);
          }
          $success = $this->DB->delete('photos', $photo['id']);
        } else {
          // Ha csak egy lapon van fent, akkor fizikai törlés is van
          $success = $this->Photos->delete($photo, static::$user);
        }
      }

      if ($this->data['cover'] == 1) {
        $this->Artpieces->update_cover($artpiece_id);
      }

      if ($success) {
        $this->Events->delete_with_photo($photo['id']);
        $this->Artpieces->generate($artpiece_id);
        $this->Artpieces->update_photos_field($artpiece_id);
        $this->send(['success' => true]);
      }
    }

    $this->send([]);
  }


  /**
   * Műlap infók, amiket nem cache-elünk, hanem mindig megjelenítéskor húzzuk le
   */
  public function artpage() {
    $array = [];

    // Műlap akt tömbje
    $artpiece = $this->DB->first('artpieces', $this->data['id']);

    if ($artpiece) {

      // Nézettség ne legyen nulla
      $artpiece['view_total'] = $artpiece['view_total'] == 0 ? 1 : $artpiece['view_total'];
      $artpiece['view_week'] = $artpiece['view_week'] == 0 ? 1 : $artpiece['view_week'];
      $artpiece['view_day'] = $artpiece['view_day'] == 0 ? 1 : $artpiece['view_day'];

      // Műlap
      $array['artpiece'] = $artpiece;

      // Hasonló alkotások
      $array['similars'] = $this->Artpieces->get_similars($artpiece, [
        'fields' => [
          'id AS i',
          'title AS t',
          'photo_slug AS p',
        ]
      ]);

      // Szavazatok
      $array['votes'] = $this->Mongo->find_array('artpiece_votes', [
        'artpiece_id' => (int)$artpiece['id']
      ]);

      // A látogató, ha van, hogy ügyködhessen
      if (static::$user) {
        $array['user'] = [
          'id' => static::$user['id'],
          'name' => static::$user['name'],
        ];
      }
    }

    $this->send($array);
  }


  /**
   * Műlap objektum ellenőrzései
   */
  public function check() {
    if (isset($this->data['artpiece'])) {
      $validation = $this->Artpieces->check($this->data['artpiece'], static::$user);
      $this->send($validation);
    }

    return [];
  }



  public function votes () {
    $response = [];

    $type_settings = is_array(sDB['artpiece_vote_types'][@$this->data['type']])
      ? sDB['artpiece_vote_types'][@$this->data['type']] : false;

    if (!isset($this->data['type']) || !isset($this->data['id']) || !isset($this->data['cancel']) // valami nem jött
      || !$type_settings // nincs ilyen szavazat
      || ($type_settings[2] == 1 && !$this->Users->is_head(static::$user))) { // nincs joga ebben szavazni
      $this->send(400);
    }

    $type = $this->data['type'];
    $artpiece_id = $this->data['id'];
    $cancel = $this->data['cancel'] == 1 ? true : false;

    $artpiece = $this->DB->first('artpieces', $artpiece_id);


    $voter = $this->DB->find_by_id('users', static::$user['id'], [
      'fields' => ['score', 'name']
    ]);

    // Van-e ilyenem
    $this_type_vote = $this->Mongo->first('artpiece_votes', [
      'type_id' => (int)$type_settings[0],
      'artpiece_id' => (int)$artpiece_id,
      'user_id' => (int)static::$user['id'],
    ]);


    // Neki se álljunk, ha nem lehet / kell
    switch ($type) {
      case 'publish':
        // Publikus vagy blokkolt
        if ($artpiece['status_id'] == 5
          || (!$cancel && $artpiece['publish_pause'] == 1)) {
          $this->send(['refresh' => 0]);
        }
        break;

      case 'publish_pause':
        if (!$cancel && $artpiece['publish_pause'] == 1) {
          $this->send(['refresh' => 0]);
        }
        break;
    }

    // A szavazat
    $score = (int)$voter['score'];

    // Szavazat mentés
    $saved = false;
    if (!$cancel && !$this_type_vote) {
      // Szavazat rögzítése, ha még nincs
      $saved = $this->Mongo->insert('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'artpiece_id' => (int)$artpiece_id,
        'user_id' => (int)static::$user['id'],
        'user_name' => static::$user['name'],
        'score' => (int)$score,
        'created' => time(),
      ]);
    } elseif ($cancel && $this_type_vote) {
      // Szavazat visszavonása, ha volt ilyen
      $saved = $this->Mongo->delete('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'artpiece_id' => (int)$artpiece_id,
        'user_id' => (int)static::$user['id'],
      ]);
    } elseif ($cancel && in_array($type, ['question', 'publish_pause', 'harvest', 'underline'])) {
      // Nyitott kérdés visszavonása
      $saved = $this->Mongo->delete('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'artpiece_id' => (int)$artpiece_id,
      ]);
    }

    if (!$saved) {
      $this->send(['error' => true]);
    }

    // Mentés utáni műveletek
    switch ($type) {
      case 'publish':
        if (!$cancel) {
          $published = false;

          $votes = $this->Mongo->find_array('artpiece_votes', [
            'type_id' => (int)$type_settings[0],
            'artpiece_id' => (int)$artpiece_id,
          ]);

          if (count($votes) > 0) {
            $total_score = 0;
            foreach ($votes as $vote) {
              $total_score += $vote['score'];
            }

            if ($total_score >= $type_settings[3]) {
              $published = $this->Artpieces->publish($artpiece_id, false, true);
              switch ($published) {
                case 1:
                  $response['message'] = 'A műlapot ezzel a szavazattal publikáltuk.';
                  break;

                case 2:
                  $response['message'] = 'A szükséges szavazat megvan, de a feltöltő elérte a heti limitjét, ezért hétfő hajnalban automatikusan publikáljuk a műlapot.';
                  break;

                case 3:
                  $response['message'] = 'A szükséges szavazat megvan, de a lap még nem publikálható, mert nem teljesít minden publikálási feltételt.';
                  break;
              }
            }
          }

          if ($published) {
            $response['refresh'] = 2; // 2 mp múlva
          }
        }
        break;

      case 'publish_pause':
        $this->DB->update('artpieces', [
          'publish_pause' => $cancel ? 0 : 1,
        ], $artpiece_id);
        $this->Artpieces->generate($artpiece_id);
        $response = ['refresh' => 0];
        break;

      case 'praise':
        // szólunk
        if (!$cancel) {
          $this->Notifications->create($artpiece['user_id'], '"' . $artpiece['title'] . '" szép munka!', 'Műlapod "Szép munka!" jelölést kapott ' . $voter['name'] . ' tagunktól. Gratulálunk! :)', [
            'link' => '/' . $artpiece['id'],
            'type' => 'artpieces',
          ]);
        } else {
          $this->Notifications->create($artpiece['user_id'], 'Szép munka! jelölés visszavonása', '"' . $artpiece['title'] . '" műlapodra kapott "Szép munka!" jelölést ' . $voter['name'] . ' visszavonta. Lehet, hogy véletlenül nyomta meg? :)', [
            'link' => '/' . $artpiece['id'],
            'type' => 'artpieces',
          ]);
        }
        $this->Artpieces->generate($artpiece_id);
        break;

      case 'question':
        $this->DB->update('artpieces', [
          'open_question' => $cancel ? 0 : 1,
        ], $artpiece_id);

        $this->Artpieces->generate($artpiece_id);

        $response = ['refresh' => 0];
        break;

      case 'harvest':
        $this->DB->update('artpieces', [
          'harvested' => $cancel ? 0 : 1,
          'harvested_time' => $cancel ? 0 : time(),
        ], $artpiece_id);

        $this->Artpieces->generate($artpiece_id);
        $this->Artpieces->delete_artpieces_cache();

        $response = ['refresh' => 0];
        break;

      case 'underline':
        $this->DB->update('artpieces', [
          'underlined' => $cancel ? 0 : 1,
          'underlined_time' => $cancel ? 0 : time(),
        ], $artpiece_id);

        $this->Artpieces->generate($artpiece_id);
        $this->Artpieces->delete_artpieces_cache();

        $response = ['refresh' => 0];
        break;
    }

    $this->send($response);
  }


  /**
   * Szerkesztés megszavazása
   */
  public function edit_votes() {
    $response = [];

    $type_settings = sDB['artpiece_vote_types']['edit_accept'];

    if (!isset($this->data['id'])) {
      $this->send(400);
    }

    $edit_id = $this->data['id'];
    $cancel = $this->data['cancel'] == 1 ? true : false;

    $edit = $this->Mongo->first('artpiece_edits', ['_id' => $edit_id]);

    if (!$edit) {
      // Nincs
      $this->send(400);
    } elseif (!in_array($edit['status_id'], [2,3])) {
      // Már nem elfogadásra váró, hanem
      $this->send(['refresh' => 0]);
    }

    $artpiece = $this->DB->first('artpieces', $edit['artpiece_id'], [
      'fields' => ['id', 'title', 'status_id', 'user_id']
    ]);

    $voter = $this->DB->find_by_id('users', static::$user['id'], [
      'fields' => ['score', 'name']
    ]);

    // Van-e ilyenem
    $this_type_vote = $this->Mongo->first('artpiece_votes', [
      'type_id' => (int)$type_settings[0],
      'edit_id' => $edit['id'],
      'artpiece_id' => (int)$artpiece['id'],
      'user_id' => (int)static::$user['id'],
    ]);


    // A szavazat példásnál 1 = igen, 2 = nem lehet. Ezt a score-ban mentjük el.
    $score = (int)$voter['score'];

    // Szavazat mentés
    $saved = false;
    if (!$cancel && !$this_type_vote) {
      // Szavazat rögzítése, ha még nincs
      $saved = $this->Mongo->insert('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'edit_id' => $edit['id'],
        'artpiece_id' => (int)$artpiece['id'],
        'user_id' => (int)static::$user['id'],
        'user_name' => static::$user['name'],
        'score' => $score,
        'created' => time(),
      ]);
    } elseif ($cancel && $this_type_vote) {
      // Szavazat visszavonása, ha volt ilyen
      $saved = $this->Mongo->delete('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'edit_id' => $edit['id'],
        'artpiece_id' => (int)$artpiece['id'],
        'user_id' => (int)static::$user['id'],
      ]);
    }

    if (!$saved) {
      $this->send(['error' => true]);
    }


    if (!$cancel) {
      // Számoljuk
      $accepted = false;

      $votes = $this->Mongo->find_array('artpiece_votes', [
        'type_id' => (int)$type_settings[0],
        'edit_id' => $edit['id'],
        'artpiece_id' => (int)$artpiece['id'],
      ]);

      if (count($votes) > 0) {
        $total_score = 0;
        foreach ($votes as $vote) {
          $total_score += $vote['score'];
        }

        if ($total_score >= $type_settings[3]) {
          // Itt acceptálunk a robot nevében
          $accepted = $this->Artpieces->approve_edit($artpiece['id'], $edit['id'], CORE['ROBOT']);
        } else {
          $response['message'] = 'Köszönjük szavazatodat!';
        }
      }

      if ($accepted) {
        $response['message'] = 'A szerkesztést ezzel a szavazattal elfogadtuk és a műlap változásokat publikáltuk.';
        $response['refresh'] = 1; // 1 mp múlva

        $this->Artpieces->generate($artpiece['id']);

        if (in_array($artpiece['status_id'], [2,5])) {
          $this->Events->create(13, [
            'artpiece_id' => $artpiece['id'],
            'artpiece_edits_id' => $edit['id'],
            'target_user_id' => $artpiece['user_id'],
          ]);
        }
      }

    }

    $this->send($response);
  }



  /**
   *
   * Méterben megadott radius szerint ad vissza műlapokat
   *
   * @param $data
   * @return array|bool|mixed
   */
  private function _get_by_radius($data) {
    if (!isset($data['lat']) || !isset($data['lon']) || !isset($data['radius'])) {
      return [];
    }

    $lon = (float)$data['lon'];
    $lat = (float)$data['lat'];
    $radius = (float)$data['radius']; // méter!

    $artpieces_ = $this->Mongo->aggregate('artpieces', [
      [
        '$geoNear' => [
          'near' => [
            'coordinates' => [$lon, $lat] // persze, mongo fordítva szereti :]
          ],
          'distanceField' => 'distance',
          'maxDistance' => $radius,
          'spherical' => true,
          'query' => @$data['filter']
        ]
      ],
      ['$limit' => @$data['limit'] > 0 ? (int)$data['limit'] : 1000] // !
    ]);

    $artpieces = [];


    foreach ($artpieces_ as $artpiece) {
      $artpieces[] = [
        'i' => $artpiece->artpiece_id,
        'l' => $artpiece->location->coordinates,
        't' => $artpiece->title,
        'p' => $artpiece->photo_slug,
        'd' => $artpiece->distance,
      ];
    }

    return $artpieces;
  }


  /**
   *
   * Négyszögön belüli műlapokat ad vissza
   * @todo: átállás Mongo::aggregate-re, ld. _get_by_radius()
   *
   * @param $data
   * @return array|bool|mixed
   */
  private function _get_by_bounds($data) {
    // Kötelező értékek
    $nwlat = (float)$data['nwlat'];
    $nwlon = (float)$data['nwlon'];
    $selat = (float)$data['selat'];
    $selon = (float)$data['selon'];

    // Téglalap miatt kiszámolható értékek
    $nelat = isset($data['nelat']) ? (float)$data['nelat'] : $nwlat;
    $nelon = isset($data['nelon']) ? (float)$data['nelon'] : $selon;
    $swlat = isset($data['swlat']) ? (float)$data['swlat'] : $selat;
    $swlon = isset($data['swlon']) ? (float)$data['swlon'] : $nwlon;


    // Ráhagyás, ha jön paraméterben
    if (@$data['padding'] > 0 && $data['padding'] < 1) {
      $p = $data['padding'];
      $lat_adj = ($nelat - $swlat) * $p;
      $lon_adj = ($nelon - $swlon) * $p;
      $nelat += $lat_adj;
      $nelon += $lon_adj;
      $swlat -= $lat_adj;
      $swlon -= $lon_adj;
    }

    // Óriáslekérdezés lehet, szóval a find_array nem OK; az object-et kell használni
    $artpieces_ = $this->Mongo->find('artpieces',
      ['location.coordinates' =>
        ['$geoWithin' =>
          ['$polygon' =>
            [
              [$nwlon, $nwlat],
              [$nelon, $nelat],
              [$selon, $swlat],
              [$swlon, $selat],
            ]
          ]
        ]
      ],
      [
        'limit' => @$data['limit'] > 0 ? (int)$data['limit'] : 2000,
        // népszerűség szerint... ? szinkelni kell
        'sort' => ['artpiece_id' => -1]
      ]
    );


    /**
     * Eredmény összerakása
     *
     * Ha jött psess, akkor az ebben a sessionben átadott ID-ket tároljuk
     * MC-be 30 percig, és nem adjuk ugyanazt még egyszer át
     */

    if (isset($data['psess'])) {
      $stored_ids = $this->MC->get('stored_map_artpieces_' . $data['psess']);
      if (!$stored_ids || !is_array($stored_ids)) {
        $stored_ids = [];
      }
    }

    $artpieces = [];

    foreach ($artpieces_ as $artpiece) {
      if (!isset($data['psess']) || !in_array($artpiece->artpiece_id, $stored_ids)) {
        $artpieces[] = [
          'i' => $artpiece->artpiece_id,
          'l' => (array)$artpiece->location->coordinates,
          't' => $artpiece->title,
          'p' => $artpiece->photo_slug,
          'c' => @$artpiece->artpiece_condition_id,
          'l2' => @$artpiece->artpiece_location_id,
        ];
        if (isset($data['psess'])) {
          $stored_ids[] = $artpiece->artpiece_id;
        }
      }
    }

    if (isset($data['psess'])) {
      // 3 órára eltesszük az aktuális listát
      $this->MC->set('stored_map_artpieces_' . $data['psess'], $stored_ids, 3 * 60 * 60);
    }

    return $artpieces;
  }


  /**
   *
   * Vesszővel szeparált ID string alapján
   * adjuk vissza az artpieces tömböt
   *
   * @param $data
   * @return array
   */
  private function _get_by_ids($data) {
    // Kötelező értékek
    $ids = (string)$data['ids'];

    $id_array = array_map('intval', explode(',', $ids));

    // Óriáslekérdezés lehet, szóval a find_array nem OK; az object-et kell használni
    $artpieces_ = $this->Mongo->find('artpieces',
      ['artpiece_id' => ['$in' => $id_array]],
      [
        // népszerűség szerint... ? szinkelni kell
        'sort' => ['artpiece_id' => -1]
      ]
    );


    /**
     * Eredmény összerakása
     *
     */
    $artpieces = [];

    foreach ($artpieces_ as $artpiece) {
      $artpieces[] = [
        'i' => $artpiece->artpiece_id,
        'l' => (array)$artpiece->location->coordinates,
        't' => $artpiece->title,
        'p' => $artpiece->photo_slug,
        'c' => @$artpiece->artpiece_condition_id,
        'l2' => @$artpiece->artpiece_location_id,
      ];
    }

    return $artpieces;
  }
}