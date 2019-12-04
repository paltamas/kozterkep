<?php

namespace Kozterkep;

class FileHelper {

  public function __construct($DB, $Mongo) {
    $this->DB = $DB;
    $this->Mongo = $Mongo;
    $this->Log = new LogComponent();

    $this->Arrays = new ArraysHelper();

    require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'S3.php');
    $this->S3 = new \S3(C_WS_S3['access_key'], C_WS_S3['secret_key']);
  }


  /**
   *
   * Tárolt EXIF tömbből értelmezhető dolgot csinál
   *
   * @param $exif_array
   * @param array $options
   * @return string
   */
  public function exif_info($exif_array, $options = []) {
    $options = (array)$options + [
      'separator' => ' &bull; ',
      'class' => '',
    ];

    $s = '';

    $values = [];

    if (@count($exif_array) > 0) {

      foreach ($exif_array as $key => $value) {
        if ($value != '') {
          switch ($key) {
            case 'model':
              if ($exif_array['make'] != '') {
                $s .= ucfirst($exif_array['make']) . ' / ';
              }
              $s .= $value;
              break;

            case 'fnumber':
              $values[0] = 'ƒ' . $value;
              break;

            case 'exposuretime':
              $p = explode('/', $value);
              if (@$p[0] > 0 && @$p[1] > 0) {
                $p1 = 1;
                $p2 = round($p[1] / $p[0]);
                $value = $p1 . '/' . $p2;
              }
              $values[1] = $value;
              break;

            case 'focallength':
              $p = explode('/', $value);
              if (@$p[0] > 0 && @$p[1] > 0) {
                $values[2] = round($p[0] / $p[1], 1) . 'mm';
              }
              break;

            case 'isospeedratings':
              $values[3] = 'ISO' . $value;
              break;
          }
        }
      }

      ksort($values);

      if (count($values) > 0) {
        $s .= '<br /><span class="text-muted"><span class="far fa-sliders-h mr-2"></span>' . implode($options['separator'], $values) . '</span>';
      }

      if ($s != '') {
        $uid = uniqid();
        $s = '<div class="' . $options['class'] . '">'
        . '<a href="#exif-container-' . $uid . '" data-toggle="collapse" class="text-muted">EXIF információk...</a>'
        . '<div class="collapse" id="exif-container-' . $uid . '"><span class="far fa-camera mr-2"></span>' . $s . '</div>'
        . '</div>';
      }
    }

    return $s != '' ? $s : '';
  }



  public function license_info($id = 0, $options = []) {
    $options = (array)$options + [
      'name' => true,
      'class' => '',
    ];

    $s = '';
    $license_infos = sDB['license_infos'];
    $license_types = sDB['license_types'];

    if ($id > 0 && isset($license_infos[$id])) {
      $info = $license_infos[$id];
      $name = $license_types[$id];

      $s .= '<div class="' . $options['class'] . '" data-toggle="tooltip" title="' . $name . '">';

      if ($options['name']) {
        $s .= '<div class="mb-1"><strong>' . $name . '</strong>';
        if ($info[1] != '') {
          $s .= '<a href="' . $info[1] . '" target="_blank" data-toggle="tooltip" title="További részletek a licenszről">';
          $s .= '<span class="far fa-external-link ml-2"></span>';
          $s .= '</a>';
        }
        $s .= '</div>';
      }

      foreach ($info[0] as $icon) {
        $s .= '<span class="' . $icon . ' fa-2x mr-1 mt-2 text-muted"></span>';
      }
      $s .= '</div>';
    }

    return $s;
  }


  /**
   *
   * POSTban kapott fájlokat feldolgozza
   *  - DB-ben tárolja
   *  - s3gate dir-be teszi
   *  - beszúrja az átméretező és s3 feldolgozó feladatot options-szel
   *
   * @param $folder - ide tesszük lokálisan
   * @param array $post_data - post, amiből kiszedjük a képeket
   * @param array $custom_data - egyedi adatok, amiket még mentünk
   * @param array $job_options - a jobnak szóló opciók: onesize, sizes
   * @param array $options - egyéb kusztom opciók
   * @return array
   */
  public function upload_posted($folder, $post_data = [], $custom_data = [], $job_options = [], $options = []) {
    $inserted_ids = [];

    if (!isset($post_data['_files']) || @count(@$post_data['_files']) == 0) {
      return [];
    }

    $i = 0;

    // Rendezem fájlnév szerint
    $post_data['_files'] = $this->Arrays->sort_by_key($post_data['_files'], 0);

    foreach ($post_data['_files'] as $key => $file) {
      $i++;
      $original_name = $file[0];
      $ext = $this->get_ext($original_name);
      $original_name = str_replace(['.' . $ext, strtoupper('.' . $ext)], '', $original_name);
      $data = $file[1];
      $filename = uniqid() . '-' . sha1(uniqid());

      // Mentés fájlként
      $path = CORE['PATHS']['DATA'] . '/s3gate/' . $folder . '/' . $filename . '.' . $ext;
      $type = $this->save_base64_data($data, $path);

      usleep(100);

      // Letárolom a megadott adatokkal; itt dől el, hogy hova kell másolni S3-on
      $data = [
        'folder' => $folder,
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => time(),
        'copied' => 0,
      ];

      if (isset($options['rankstart'])) {
        $data['rank'] = $options['rankstart'] + $i;
      }

      // Default permission: mindenki
      if (!isset($custom_data['permissions'])) {
        $data['permissions'] = '';
      }

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->read_image_data($path);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      if (_contains($type, ['image'])) {
        list($width, $height, $type, $attr) = getimagesize($path);
        $data['width'] = $width > 0 ? $width : 0;
        $data['height'] = $height > 0 ? $height : 0;
      }

      $inserted_id = $this->DB->insert('files', array_merge($data, $custom_data));

      // Options építés, hogy mit kell csinálnia a jobnak
      // itt jonnek a onesize, sizes parancsok
      $job_options_ = (array)$job_options + ['id' => $inserted_id];

      // Szólok a forevernek, hogy meló van
      $this->Mongo->insert('jobs', [
        'class' => 'files',
        'action' => 'handle',
        'options' => $job_options_,
        'created' => date('Y-m-d H:i:s'),
      ]);

      $inserted_ids[] = [$inserted_id, $original_name];

      usleep(100);
    }

    return $inserted_ids;
  }

  /**
   *
   * Kapott base64 adatot kitisztít és elment megadott helyre
   * @param $data
   * @param $path
   */
  public function save_base64_data($data, $path) {
    // Kiszedjük a típust
    $parts = explode(';base64,', $data);
    // Mime type
    $type = strtolower(str_replace('data:', '', $parts[0]));
    // Kitisztítjuk az adatot
    $data = substr($data, strpos($data, ',') + 1);
    $data = base64_decode($data);
    if (file_put_contents($path, $data)) {
      return $type;
    }

    return false;
  }


  /**
   *
   * S3-ra másoló logika
   *
   * @param $image_path
   * @param $content_title
   * @param $bucket_name
   * @return bool
   */
  public function s3_copy($image_path, $content_title, $bucket_name = C_WS_S3['bucket_name']) {
    if (!is_readable($image_path)) {
      $this->Log->write('ennemment: ' . $image_path);
      return false;
    }

    if ($this->S3->putObjectFile(
      $image_path,
      $bucket_name,
      C_WS_S3['folder_prefix'] . $content_title,
      'public-read'
    )) {
      return true;
    } else {
      return false;
    }
  }


  public function s3_get($content_title, $save_target = false, $bucket_name = C_WS_S3['bucket_name']) {
    $result = $this->S3->getObject($bucket_name, C_WS_S3['folder_prefix'] . $content_title);
    if ($save_target && @$result->error === false) {
      $result = $this->write($save_target, $result->body, 'w+');
    }
    return $result;
  }

  public function s3_delete($content_title, $bucket_name = C_WS_S3['bucket_name']) {
    $result = $this->S3->deleteObject($bucket_name, C_WS_S3['folder_prefix'] . $content_title);
    return $result;
  }


  /**
   *
   * Fájl törlése lokálisan és s3-on is
   *
   * @param $id
   * @param bool $user_to_check - ha van és nem az övé, akkor VISZLÁT! :)
   * @return bool
   */
  public function delete($id, $user_id_to_check = false) {
    $file = $this->DB->first('files', $id);

    if ($user_id_to_check && $user_id_to_check != $file['user_id']) {
      return false;
    }

    if ($file) {
      // Mindenképpen ellenőrizzük lokálisan
      $path = CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $file['name'] . '.' . $file['extension'];
      if (is_file($path)) {
        unlink($path);
      }
      // onesize törlés...
      if (@$file['onesize'] != '') {
        $path = CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $file['onesize'] . '.' . $file['extension'];
        if (is_file($path)) {
          unlink($path);
        }
      }
      // @todo: sizes

      if ($file['copied'] > 0) {
        // Már feltöltöttök
        $this->s3_delete($file['folder'] . '/' . $file['name'] . '.' . $file['extension']);

        // onesize
        if (@$file['onesize'] != '') {
          $path = CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $file['onesize'] . '.' . $file['extension'];
          $this->s3_delete($file['folder'] . '/' . $file['onesize'] . '.' . $file['extension']);
        }

        // @todo: sizes
      }
    }

    return true;
  }


  /**
   *
   * Kiterjesztés kisbetűvel
   *
   * @param $filename
   * @return string
   */
  public function get_ext($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  }


  /**
   *
   * A böngészőben megmutatja a fájlt.
   * Ha nem PDF vagy kép, akkor letöltést nyom
   *
   * A webes path-t csak kivételes esetben érdemes használni, mert lassú.
   * Pl ha a teljes méreretet akarjuk megszerezni.
   *
   * @param bool $readfile
   * @param $file
   * @param $type
   * @param $download_name
   */
  public function display($path, $type, $download_name) {
    if (!_contains($path, 'http') && !is_readable($path)) {
      mydie('Fájl megnyitási hiba.');
    }

    if (_contains($type, ['image', 'pdf'])) {
      header("Content-Type: " . $type);
    } else {
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream");
      header("Content-Type: application/download");
      header("Content-Disposition: attachment; filename=\"" . $download_name . "\"");
      header("Pragma: public");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    }
    ob_clean();
    if (_contains($path, 'http')) {
      echo file_get_contents($path);
      exit;
    } else {
      readfile($path);
    }
    exit;
  }


  public function write($filename, $content, $mode = "w") {
    $file = new \SplFileObject($filename, $mode);
    if (!$file->fwrite($content)) {
      debug('Fájl írási hiba: ' . $filename);
      $this->Log->write('Fájl írási hiba: ' . $filename);
    } else {
      return true;
    }
  }


  public function read($filename) {
    if (is_readable($filename)) {
      $file = new \SplFileObject($filename, "r");
    } else {
      $file = false;
    }
    if (!$file) {
      debug('Fájl olvasási hiba: ' . $filename);
      $this->Log->write('Fájl olvasási hiba: ' . $filename);
    } else {
      $file->fread($file->getSize());
    }
  }


  public function scan_dir($target) {
    $result = [];
    foreach (scandir($target) as $filename) {
      if ($filename[0] === '.') continue;
      $filePath = $target . '/' . $filename;
      if (is_dir($filePath)) {
        foreach ($this->scan_dir($filePath) as $childFilename) {
          $result[] = $filename . '/' . $childFilename;
        }
      } else {
        $result[] = $filename;
      }
    }
    return $result;
  }


  /**
   *
   * Egy állomány útvonalát adja vissza.
   * Méretezett képeknél csak a méretezett verziót adja.
   * Figyeli, hogy S3-on van-e a cucc
   *
   * @param $file
   * @return string
   */
  public function get_file_path($file) {
    // Méretezett kép, a méretnevet adjuk meg
    if (@$file['onesize'] != '' || @$file['sizes'] != '') {
      $filename = @$file['onesize'] != '' ? $file['onesize'] : $file['sizes'];
    } else {
      $filename = $file['name'];
    }

    if ($file['copied'] > 0 && $file['copied'] < strtotime('-' . C_WS_S3['delay'] . ' seconds')) {
      $path = C_WS_S3['url'] . C_WS_S3['folder_prefix'] . $file['folder'] . '/' . $filename . '.' . $file['extension'];
    } else {
      $path = '/mappak/fajl_mutato/' . $file['id'];
    }

    return $path;
  }


  /**
   *
   * Kép adatok kiolvasása egy fájlból
   *
   * @param $path
   * @return array
   */
  public function read_image_data($path) {
    if ((!_contains($path, 'http') && is_readable($path)) || _contains($path, 'http')) {
      $exif = @exif_read_data($path);

      if (!$exif || count($exif) == 0 || $exif == null) {
        return [];
      }

      $exif_cleaned = [
        'make' => isset($exif['Make']) ? trim($exif['Make']) : '',
        'model' => isset($exif['Model']) ? trim($exif['Model']) : '',
        'exposuretime' => isset($exif['ExposureTime']) ? trim($exif['ExposureTime']) : '',
        'fnumber' => isset($exif['FNumber']) ? trim($exif['FNumber']) : '',
        'isospeedratings' => isset($exif['ISOSpeedRatings']) ? trim($exif['ISOSpeedRatings']) : '',
        'focallength' => isset($exif['FocalLength']) ? trim($exif['FocalLength']) : '',
      ];

      $data = [
        'size' => filesize($path),
        'exif' => $exif_cleaned
      ];

      if (isset($exif["GPSLongitude"])) {
        $data['coordinates'] = [
          'lon' => $this->get_exif_gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']),
          'lat' => $this->get_exif_gps($exif["GPSLatitude"], $exif['GPSLatitudeRef'])
        ];
      }

      if (isset($exif['DateTimeOriginal'])) {
        // ilyen: 2018:04:04 17:24:35, muhaha
        $p = explode(' ', $exif['DateTimeOriginal']);
        $data['taken'] = strtotime(str_replace(':', '-', $p[0]) . ' ' . $p[1]);

        /**
         * Ha túl régi az exif időbélyeg, akkor nem hisszük el,
         * mert régen nem voltak még olyan fényképezőgépek, amik tudtak exifelni.
         * https://hu.wikipedia.org/wiki/Exif 2002 április előtt nem valós és
         * ez kb. korrekt, mert én 2003-ban vettem az első Nikon Coolpix gépemet,
         * és abban már volt exif, habár elég szegényes.
         * Ez pl. azzal készült: https://www.facebook.com/photo.php?fbid=10150348493524886&set=pb.753619885.-2207520000.1550952171.&type=3&theater
         * :D :D
         *
         * HA itt jársz, másold már ide a te is legelső online is elérhető fotód URL-jét!
         * Nagyon izgalmas játék lesz ez. Több évtizedig ;]
         *
         */
        $data['taken'] = $data['taken'] < strtotime('2002-04-01') ? 0 : $data['taken'];
        //
      }

      return $data;
    }
    return [];
  }


  /**
   *
   * Vízjel hozzáadás
   *
   * @param $target
   * @return bool
   */
  public function add_watermark($target) {
    $image_width = imagesx($target);
    $image_height = imagesy($target);
    if (max($image_width, $image_height) < 800) {
      $watermark = CORE['PATHS']['DATA'] . DS . 'etc' . DS . 'kozterkep-watermark-small.png';
      $padding = 10;
    } else {
      $watermark = CORE['PATHS']['DATA'] . DS . 'etc' . DS . 'kozterkep-watermark.png';
      $padding = 20;
    }
    $logo = imagecreatefrompng($watermark);
    $logo_width = imagesx($logo);
    $logo_height = imagesy($logo);
    $image_x = $image_width - $logo_width - $padding;
    $image_y = $image_height - $logo_height - $padding;
    return imagecopy($target, $logo, $image_x, $image_y, 0, 0, $logo_width, $logo_height);
  }



  public function convert_to_jpg($source_path, $target_path, $options) {
    $options = (array)$options + [
      'quality' => 75,
      'delete_source' => false,
    ];

    $extension = pathinfo($source_path, PATHINFO_EXTENSION);
    switch ($extension) {
      case 'jpg':
      case 'jpeg':
        $image = imagecreatefromjpeg($source_path);
        break;
      case 'gif':
        $image = imagecreatefromgif($source_path);
        break;
      case 'png':
        $image = imagecreatefrompng($source_path);
        break;
    }

    $created = imagejpeg($image, $target_path, $options['quality']);

    if ($created) {
      if ($options['delete_source']) {
        unlink($source_path);
      }
      return true;
    }

    return false;
  }



  /**
   *
   * Valid GPS koordinátává alakítás kapott EXIF koordinátából
   *
   * @param $exifCoord
   * @param $hemi
   * @return float|int
   */
  private function get_exif_gps($exifCoord, $hemi) {
    $degrees = count($exifCoord) > 0 ? $this->gps2num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? $this->gps2num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? $this->gps2num($exifCoord[2]) : 0;
    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;
    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
  }

  /**
   *
   * EXIF GPS-hez segéd
   *
   * @param $coordPart
   * @return float|int
   */
  private function gps2num($coordPart) {
    $parts = explode('/', $coordPart);
    if (count($parts) <= 0) {
      return 0;
    }
    if (count($parts) == 1) {
      return $parts[0];
    }
    return floatval($parts[0]) / floatval($parts[1]);
  }

}
