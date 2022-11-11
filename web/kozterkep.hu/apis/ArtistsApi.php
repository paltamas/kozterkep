<?php
class ArtistsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

  }

  // Új alkotó létrehozása
  public function post() {
    if (@$this->data['name'] != '') {
      $p = explode(' ', $this->data['name']);
      $first_name = end($p);
      $last_name = trim(str_replace($first_name, '', $this->data['name']));

      $data = [
        'name' => $this->data['name'],
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_id' => CORE['USERS']['artists'],
        'creator_user_id' => static::$user['id'],
        'creator_artpiece_id' => @$this->data['artpiece_id'],
        'created' => time(),
        'modified' => time(),
        'artpiece_count' => 1,
      ];

      if ($id = $this->DB->insert('artists', $data)) {
        $this->Mongo->insert('jobs', [
          'class' => 'cache',
          'action' => 'tables',
        ]);
        $this->send(['success' => true, 'id' => $id]);
      }
    }
    $this->send([]);
  }


  public function put() {

  }


  public function photos() {

    if (isset($this->data['_files']) && count($this->data['_files']) > 0) {
      $artist = $this->DB->first('artists', $this->data['artist_id']);

      // Ha nem saját, akkor csak közteres vagy publikus lap lehet
      if (!$artist) {
        $this->send(['errors' => [texts('mentes_hiba')]]);
      }

      $photo_count = $this->DB->count('photos', [
        'OR' => [
          'artist_id' => $artist['id'],
          'portrait_artist_id' => $artist['id'],
        ]
      ]);

      $min_size = sDB['limits']['photos']['archive_min_size'];

      $i = 0;
      $inserts = 0;
      $errors = [];

      $event_photos = [];

      foreach ($this->data['_files'] as $key => $file) {
        $i++;

        $rank = $photo_count + $i;

        // Eredeti kép mentése
        $filename = $file[0];
        $ext = $this->File->get_ext($file[0]);
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;
        $prefix = 'u' . static::$user['id'] . '-ar' . $artist['id'] . '-';
        $slug = $prefix . uniqid() . '-' . bin2hex(random_bytes(12));
        $original_slug = $prefix . uniqid() . '-' . sha1(uniqid());
        $original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $original_slug . '.' . $ext;
        $type = $this->File->save_base64_data($file[1], $original_path);

        // Licensz és egyedi-e
        $license_type_id = $this->data['license_type_id'];
        $special_license = $license_type_id != static::$user['license_type_id'] ? 1 : 0;

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
          'portrait' => 1,
          'portrait_artist_id' => $artist['id'],
          'rank' => $rank,
          'text' => $this->data['text'],
          'source' => $this->data['source'],
          'status_id' => 5,
          'created' => time(),
          'modified' => time(),
          'approved' => time(),
          'filename' => $filename,
          'slug' => $slug,
          'original_slug' => $original_slug,
          'user_id' => static::$user['id'],
          'license_type_id' => $license_type_id,
          'special_license' => $special_license,
          'exif_json' => _json_encode(@$image_data['exif']),
          'exif_taken' => @$image_data['taken'] > 0
            || @$image_data['taken'] > strtotime('+12 hours') ? $image_data['taken'] : 0,
          'exif_coordinates' => @$image_data['exif_coordinates'] != '' ? _json_encode($image_data['exif_coordinates']) : '',
          'filesize' => @$image_data['size'] > 0 ? $image_data['size'] : 0,
          'width' => $width > 0 ? $width : 0,
          'height' => $height > 0 ? $height : 0,
          'added' => 1,
          'receiver_users' => _json_encode([(string)CORE['USERS']['artists']], false, false),
        ];

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

          if (static::$user['id'] != CORE['USERS']['artists']) {
            $this->Notifications->create(CORE['USERS']['artists'], 'Új fotó ' . $artist['name'] . ' adatlapjára', static::$user['name'] . ' új fotót töltött az alkotó adatlapjára.', [
              'link' => $this->Html->link_url('', ['artist' => $artist]),
              'type' => 'things',
            ]);
          }

          $this->Events->create(28, [
            'user_id' => static::$user['id'],
            'artist_id' => $artist['id'],
            'photo_count' => 1,
            'photos' => [['id' => $photo_id, 'slug' => $slug]],
            'related_users' => [CORE['USERS']['artists']],
          ]);
        }
      }

      if ($inserts > 0) {
        $this->Cache->delete('cached-view-artists-view-' . $artist['id']);
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

    $this->send(['errors' => ['Válassz ki egy képfájlt.']]);
  }

}