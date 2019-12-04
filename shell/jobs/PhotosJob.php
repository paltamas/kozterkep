<?php

class PhotosJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   * Feltöltött kép kezelő logika
   */
  public function handle() {
    $options = self::$_options;

    $photo_sizes = sDB['photo_sizes'];

    if (is_numeric($options['id'])) {
      $photo = $this->DB->first('photos', $options['id'], ['fields' => ['id', 'original_slug', 'slug', 'copied', 'artpiece_id', 'artpieces', 'artist_id', 'sign_artist_id', 'portrait_artist_id']]);

      if (!$photo) {
        // Törölték, vagy migrációs izé; tehát nem kell foglalkozni
        // vele és ne akadjon be ezen a job
        return true;
      }


      // Felmásolás és mindenféle dolog vele
      if (@$photo['copied'] === 0) {

        // Kell-e forgatás
        $orientation_correction = @$options['rotate'] == false ? false : true;

        $source_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg';
        require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'ImageResize.php');
        $image = new \Gumlet\ImageResize($source_path, $orientation_correction);

        if (!$image) {
          $this->DB->update('photos', [
            'image_error' => 1,
          ], $photo['id']);
          return false;
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


        $copy_error = false;
        // Eredeti kép
        if (!$this->File->s3_copy($source_path, 'originals/' . $photo['original_slug'] . '.jpg')) {
          $copy_error = true;
        }
        foreach ($targets as $size => $source) {
          $s3_target = 'photos/' . $photo['slug'] . '_' . $size . '.jpg';
          if (!$this->File->s3_copy($source, $s3_target)) {
            $copy_error = true;
          }
        }
        if (!$copy_error) {
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


}