<?php

class PhotosJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }

  /**
   * Nem feltöltött képek ellenőrzése
   * és job direkt futtatása; egyelőre rejtély, miért nem fut le alapból
   * @todo: stabilizálni a job alapján futást
   */
  public function copy_check () {
    $photos = $this->DB->find('photos', [
      'conditions' => ['copied' => 0],
      'fields' => ['id'],
      'limit' => 10,
    ]);

    if (count($photos) > 0) {
      foreach ($photos as $photo) {
        $this->handle([
          'id' => $photo['id'],
          'watermark' => true
        ]);
      }
    }
  }

  /**
   *
   * Feltöltött kép kezelő logika
   *
   * @param false $direct_options
   * @return bool
   * @throws Exception
   */
  public function handle($direct_options = false) {
    if ($direct_options) {
      $options = $direct_options;
    } else {
      $options = self::$_options;
    }

    $photo_sizes = sDB['photo_sizes'];

    if (is_numeric($options['id'])) {
      $photo = $this->DB->first('photos', $options['id'], ['fields' => ['id', 'original_slug', 'slug', 'copied', 'artpiece_id', 'artpieces', 'artist_id', 'sign_artist_id', 'portrait_artist_id', 'auto_rotated']]);

      if (!$photo) {
        // Törölték, vagy migrációs izé; tehát nem kell foglalkozni
        // vele és ne akadjon be ezen a job
        return true;
      }

      // Felmásolás és mindenféle dolog vele
      if (@$photo['copied'] == 0) {

        // Kell-e forgatás (kell, vagy már forgattunk)
        $orientation_correction = @$options['rotate'] == false || $photo['auto_rotated'] == 1
          ? false : true;

        $source_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg';

        if (is_file($source_path)) {
          require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'ImageResize.php');
          $image = new \Gumlet\ImageResize($source_path, $orientation_correction);
        } else {
          $image = false;
        }

        if (!$image) {
          $this->DB->update('photos', [
            'image_error' => 1,
          ], $photo['id']);
          return false;
        }

        // Ha már forgattunk, rájegyezzük,
        // nehogy még egyszer ráfutáskor még egyszer forgassunk
        if ($orientation_correction) {
          $this->DB->update('photos', [
            'auto_rotated' => 1,
          ], $photo['id']);
        }

        // cél hely; méret AZ és kiterjesztés nélkül!
        $target_path = CORE['PATHS']['DATA'] . '/s3gate/photos/' . $photo['slug'];
        $targets = [];

        // 1. méret
        $targets[1] = $target_path . '_1.jpg';
        $image->quality_jpg = sDB['photo_quality'];
        $image->resizeToShortSide($photo_sizes[1]);

        $target_1 = $targets[1];

        if (@$options['watermark'] == true) {
          $image->addFilter(function ($target_1) {
            $this->File->add_watermark($target_1);
          });
        }
        $image->save($targets[1]);

        // 2 1-ből jön, simán átméretezve a vízjellel
        $image = new \Gumlet\ImageResize($targets[1], $orientation_correction);
        $targets[2] = $target_path . '_2.jpg';
        $image->resizeToShortSide($photo_sizes[2]);
        $image->save($targets[2]);

        // 3 1-ből jön, simán átméretezve a vízjellel
        $image = new \Gumlet\ImageResize($targets[1], $orientation_correction);
        $targets[3] = $target_path . '_3.jpg';
        $image->resizeToShortSide($photo_sizes[3]);
        $image->save($targets[3]);


        // 4-8 => crop, itt az eredetiből csináljuk, vízjel nélkül
        $photo_sizes = _unset($photo_sizes, [1,2,3]);
        $crop_sizes = $photo_sizes;

        for ($i=4; $i<=8; $i++) {
          $image = new \Gumlet\ImageResize($source_path, $orientation_correction);
          $image->resizeToShortSide($crop_sizes[$i]);
          $image->crop($crop_sizes[$i], $crop_sizes[$i], false, 6);
          $targets[$i] = $target_path . '_' . $i . '.jpg';
          $image->save($targets[$i]);
        }

        echo 'resize kesz';

        $copy_error = false;
        // Eredeti kép
        if (!$this->File->s3_copy($source_path, 'originals/' . $photo['original_slug'] . '.jpg')) {
          $copy_error = true;
          echo PHP_EOL . 'hiba';
        }

        foreach ($targets as $size => $source) {
          $s3_target = 'photos/' . $photo['slug'] . '_' . $size . '.jpg';
          if (!$this->File->s3_copy($source, $s3_target)) {
            $copy_error = true;
            echo PHP_EOL . ' hiba: ' . $size;
          }
        }
        if (!$copy_error) {
          echo PHP_EOL . 'siker';
          // Műlap cache törlés, ha végzett
          $this->DB->update('photos', ['copied' => time()], $photo['id']);
          if ($photo['artpiece_id'] > 0) {
            $this->Artpieces->generate($photo['artpiece_id']);
          }
          // Ha alkotó van a képen valahogy, akkor ott is cache-törlés
          foreach (['artist_id', 'sign_artist_id', 'portrait_artist_id'] as $field) {
            if ($photo[$field] > 0) {
              $this->Cache->delete('cached-view-artists-view-' . $photo[$field]);
            }
          }
          return true;
        }
      } else {
        return true;
      }
    }
    return false;
  }

  /**
   *
   * Kép szerkesztés.
   * 0.) megnézzük, megvan-e még lokálisan a kép, ha nincs...
   * 1.) letöltjük a képet az S3-ról, ha megvan ott
   * 2.) elvégezzük a műveleteket [...]
   * 3.) beszúrjuk azonos slugokkal feldolgozásra, így nem változik semmi se lent, se fent
   *
   * @return bool
   */
  public function edit() {
    $options = self::$_options;
    return false;
  }

  /**
   *
   * Direktbe futtatható cacheelő logika
   *  - letölti a képet S3-ról
   *  - törli a jobot
   *
   * @return true
   */
  public function cache() {
    $photos = $this->Mongo->find_array(
      'cacheimages',
      [],
      [
        'limit' => 100,
        'sort' => ['request' => 1]
      ]
    );

    if (is_countable($photos) && count($photos) > 0) {
      foreach ($photos as $photo) {
        if (file_put_contents(
          CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/imgcache/' . $photo['filename'],
          file_get_contents($photo['s3_url'])
        )) {
          $this->Mongo->delete('cacheimages', ['filename' => $photo['filename']]);
        }
      }
    }

    return true;
  }
}