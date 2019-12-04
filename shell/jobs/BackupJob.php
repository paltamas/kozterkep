<?php
class BackupJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();

    // Ide mentjük lokálisan a DB-ket és ide is restore-olunk, ha kell
    $this->temp_dir = CORE['PATHS']['DATA'] . DS . 'backup' . DS;

    // Ez az S3 folder
    $this->s3_dir = 'backups';

    // Ennyi idő után töröljük lokálisan
    $this->old_delete = '-7 days';
  }


  /**
   *
   * Archiváló logika
   * az elején a régi lokál archívok törlésével
   *
   * @return bool
   */
  public function archive() {
    // Töröljük a legalább 7 napja írt fájlokat
    $files = array_diff(scandir($this->temp_dir), ['.', '..']);

    if (count($files) > 0) {
      foreach ($files as $file_name) {
        if (filemtime($this->temp_dir . $file_name) < strtotime($this->old_delete)) {
          unlink($this->temp_dir . $file_name);
        }
      }
    }


    $sql_up = $mongo_up = false;

    // SQL dump készítés
    $file_name = date('Ymd') . '-sql.gz';
    $temp_path = $this->temp_dir . $file_name;
    $command = "mysqldump -u " . C_MYSQL['kt']['user']
      . " -p" . C_MYSQL['kt']['pass']
      . " --single-transaction --quick --lock-tables=false "
      . C_MYSQL['kt']['name'] . " | gzip -c > " . $temp_path . "";
    exec($command, $output);
    if (file_exists($temp_path)) {
      $sql_up = $this->File->s3_copy($temp_path, $this->s3_dir . '/' . $file_name);
    }

    // Mongo dump
    $file_name = date('Ymd') . '-mongo.archive';
    $temp_path = $this->temp_dir . $file_name;
    $command = 'mongodump -d kozterkep --gzip --archive=' . $temp_path;
    exec($command, $output);
    if (file_exists($temp_path)) {
      $mongo_up = $this->File->s3_copy($temp_path, $this->s3_dir. '/' . $file_name);
    }

    if (!$sql_up) {
      $this->Log->write('Nem sikerült a MySQL DB archiválás.', 'error', 1);
    }
    if (!$mongo_up) {
      $this->Log->write('Nem sikerült a Mongo DB archiválás.', 'error', 1);
    }

    return true;
  }



  /**
   * Konzolból hívható letöltő script
   * így hívd:
   * php run.php backup restore -f=20141017.sql.gz
   * ahol az f => a fájlnév az S3-on
   *
   * !!!!!!!!!!!! csak sima s3-on megy !!!!!!!!!!!!
   * most gleccseren vagyunk, ott webes felületen kell letölteni
   *
   */
  public function restore() {
    $options = self::$_options;
    if (@$options['f'] != '') {
      $this->File->s3_get($this->s3_dir . '/' . $options['f'], $this->temp_dir . '_restore-' . $options['f']);
    }
  }
}