<?php

class FilesJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   * Feltöltött fájl kezelő logika
   *  - foldertől függően felteszi S3-ba
   *  - photos folder esetén resize/crop és watermark is megy
   */
  public function handle() {
    $options = self::$_options;

    if (@$options['id'] > 0) {
      $file = $this->DB->first('files', $options['id']);

      if ($file) {

        $source_path = CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $file['name'] . '.' . $file['extension'];

        // A fájlhoz mentendő infók
        $updates = [];

        // S3-ra másolási feladatok
        // az alap fájllal kezdünk
        $copy_array = [];
        $copy_array[$source_path] = $file['folder'] . '/' . $file['name'] . '.' . $file['extension'];



        // Kép dolgok
        if (strpos($file['type'], 'image') !== false) {
          // Kép resize class meghívása, ha kell
          if (@$options['onesize'] == true || @$options['sizes'] == true) {
            require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'ImageResize.php');
            $image = new \Gumlet\ImageResize($source_path);
          }

          // Egy resize + watermark kell csak
          if (@$options['onesize'] == true) {

            $resize_name = uniqid() . '-' . sha1($file['name']);
            $target = CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $resize_name . '.' . $file['extension'];
            $image->quality_jpg = 70;
            $image->resizeToShortSide('1200');

            if (@$options['watermark'] == true) {
              $image->addFilter(function ($target) {
                $this->File->add_watermark($target);
              });
            }

            $image->save($target);
            $copy_array[$target] = $file['folder'] . '/' . $resize_name . '.' . $file['extension'];
            $updates['onesize'] = $resize_name;

          } elseif (@$options['sizes'] == true) {
            // Egyelőre nem kell, a onesize-t használjuk
          }
        }

        if (count($copy_array) > 0) {
          $copy_error = false;
          foreach ($copy_array as $source => $target) {
            if (!$this->File->s3_copy($source, $target)) {
              $copy_error = true;
            }
          }
          if (!$copy_error) {
            $updates['copied'] = time();
            $this->DB->update('files', $updates, $file['id']);
            if (@$file['artpiece_id'] > 0) {
              $this->Artpieces->generate($file['artpiece_id']);
              $this->DB->update('artpieces', ['cached' => 0], $file['artpiece_id']);
            }
            if (@$file['folder_id'] > 0) {
              $this->Cache->delete('cached-view-folders-view-' . $file['folder_id']);
            }
            return true;
          }
        }
      }
    }

    return false;
  }
}