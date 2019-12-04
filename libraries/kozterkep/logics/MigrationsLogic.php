<?php
namespace Kozterkep;

class MigrationsLogic {

  private $kt_url;
  private $db_url;
  private $db_url_pw;
  private $Arrays;
  private $DB;
  public $hugs_locally;

  public function __construct($app_name = false) {
    $this->kt_url = 'https://www.kozterkep.hu';
    $this->db_url = $this->kt_url . '/api/pulldb/';
    $this->db_url_pw = CORE['OLD_KT_API_SECRET'];
    $this->Arrays = new ArraysHelper();
    $this->DB = new DatabaseComponent('kt');
    $this->MC = new MemcacheComponent();
    $this->Mongo = new MongoComponent();
    $this->File = new FileHelper($this->DB, $this->Mongo);
    $this->Text = new TextHelper();

    // True = localhostról jön a hug, egyébként zazen-ről
    $this->hugs_locally = true;

    $this->new_parameters = $this->DB->find('parameters', [
      'type' => 'list',
      'key' => 'old_id',
      'fields' => ['old_id', 'id']
    ]);
  }

  /*
   * Szinkron futás előtti műveletek
   */

  public function prepare($table, $only_alter = false) {
    if (!$only_alter) {
      $this->DB->query("TRUNCATE " . $table);
    }
    $this->DB->query("ALTER TABLE " . $table . " MODIFY COLUMN id int(11) NOT NULL;");
  }

  /*
   * Szinkron futás utáni műveletek
   */

  public function aftermath($table) {
    $this->DB->query("ALTER TABLE " . $table . " MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT FIRST;");
  }

  public function get_model($model_name, $options = false, $conditions = []) {
    if ($model_name == 'Hug' && $this->hugs_locally) {
      if (isset($options['order_by'])) {
        $options['order'] = $options['order_by'];
        unset($options['order_by']);
      }
      $query_options = $options ? $options : [];
      $query_options['conditions'] = $conditions;
      return $this->DB->find('hugs', $query_options);
    }

    if ($model_name == 'ZazenHug') {
      $model_name = 'Hug';
    }

    $query = '';

    if ($options == 'count') {
      $query .= '&count';
      $conditions = [];
    } elseif (is_numeric($options)) {
      $query .= '&limit=' . $options;
    } elseif (is_array($options) && count($options) > 0) {
      $query = '&';
      $query .= http_build_query($options);
    }

    if (count($conditions) > 0) {
      $query .= $this->build_url_conditions($conditions);
    }

    $url = $this->db_url . $model_name . '?' . $this->db_url_pw . $query;
    $json = file_get_contents($url);

    if ($options == 'count') {
      return $json;
    } else {
      $array = json_decode($json, true);
      return is_array($array) && count($array) > 0 ? $array : false;
    }
  }

  /*
   * hugok lekérése feltétel alapján
   * sokszor kell, kiszerveztem
   */

  public function get_hugs($conditions = [], $options = []) {
    $options['order_by'] = isset($options['order_by']) ? $options['order_by'] : 'id';
    $array = $this->get_model('Hug', $options, $conditions);
    if (is_array($array) && count($array) > 0) {
      $hugs = $this->Arrays->skip_cake_model('Hug', $array);
      return $hugs;
    } else {
      return false;
    }
  }

  /*
   * Kiolvassa a HUG-hoz tartozó artpiece-tag-eket
   * és JSON-ben adja vissza
   * át is forgatja őket az új tag ID-be
   * 
   */

  public function get_hug_tags($hug_id) {
    $atags = $this->get_model('ArtpiecesTag', [], ['hug_id' => $hug_id]);
    if ($atags) {
      $array = [];
      foreach ($atags as $atag) {
        $array[] = $this->new_parameters[$atag['ArtpiecesTag']['tag_id']]['id'];
      }
      return json_encode($array);
    } else {
      return '';
    }
  }

  /*
   * Régi hugok átvételekor hülyeségek vannak.
   * int default 0 mezőkben nullok meg üres értékek
   */

  public function clear_old_hug_rows($rows) {
    $hugs = [];
    foreach ($rows as $row) {
      $hugs[] = $this->clear_old_hug($row);
    }
    return $hugs;
  }

  public function clear_old_hug($row) {
    $int_types = ['rank', 'created', 'modified', 'updated', 'approved', 'vote_count', 'followed_last_hot', 'it_was_comment', 'answered_haveread', 'artist_contributor', 'unveil_date_cca', 'anniversary', 'local_importance', 'not_artistic', 'parameters_changed', 'copy', 'reconstruction', 'initial', 'valid_now', 'haveread', 'editorial', 'counted', 'hidden', 'extrashit', 'exowner', 'waiting', 'highlighted', 'user_id', 'owner_user_id'];
    foreach ($row as $key => $value) {
      if (
        (strpos($key, '_id') !== false || in_array($key, $int_types)) && $value == ''
      ) {
        $row[$key] = 0;
      }
    }
    if ($row['text'] != '') {
      $row['text'] = html_entity_decode($row['text']);
    }
    return $row;
  }

  /*
   * Megépíti a kapott JSON válaszból mezőnként az új tömböt
   * keep: megtartja, ahogy van
   * rename: átnevezi új mezőnévvé
   * int: integerré alakítja
   * custom: egyedi értéket ad a mezőnek
   */

  public function build_model_fields($row, $methods = []) {
    $fields = [];

    /*
     * Ezek egy-az-egyben átjönnek
     */
    if (isset($methods['keep']) && count($methods['keep']) > 0) {
      foreach ($methods['keep'] as $field_name) {
        $fields[$field_name] = $row[$field_name];
      }
    }

    /**
     * Ezek is, csak trimmelünk
     */
    if (isset($methods['trim']) && count($methods['trim']) > 0) {
      foreach ($methods['trim'] as $field_name) {
        $fields[$field_name] = trim($row[$field_name]);
      }
    }

    /*
     * Ezeket kell számmá alakítani, mert sztringként akaródnak bejönni
     */
    if (isset($methods['int']) && count($methods['int']) > 0) {
      foreach ($methods['int'] as $field_name) {
        $fields[$field_name] = (int) $row[$field_name];
      }
    }

    /*
     * Ezeket új megnevezésű mezőbe pakkantjuk
     */
    if (isset($methods['rename']) && count($methods['rename']) > 0) {
      foreach ($methods['rename'] as $field_name => $new_field_name) {
        if (strpos($new_field_name, 'int:') !== false) {
          $fields[str_replace('int:', '', $new_field_name)] = (int)$row[$field_name];
        } else {
          $fields[$new_field_name] = $row[$field_name];
        }
      }
    }

    /*
     * Ezek tök egyedi dolgok; jellemzőn valami függvényt, vagy más 
     * átszabást engedek rá.
     */
    if (isset($methods['custom']) && count($methods['custom']) > 0) {
      foreach ($methods['custom'] as $field_name => $custom_value) {
        $fields[$field_name] = $custom_value;
      }
    }

    return $fields;
  }

  /*
   * Kapott tömbből épít mongo adattömböt
   * építi, ami kell, néha átnevez, ha tömb a kulcs
   */

  public function build_mongo_data($item, $needed_fields) {
    $data = [];
    // Megépítem
    foreach ($needed_fields as $key => $value) {
      if (is_numeric($key)) {
        $old_field = $value;
        $new_field = $value;
      } else {
        // Átnevezés
        $old_field = $key;
        $new_field = $value;
      }

      if (_is_float($item[$old_field])) {
        $item_value = (float) $item[$old_field];
      } elseif (_is_int($item[$old_field])) {
        $item_value = (int) $item[$old_field];
      } elseif (in_array($item[$old_field], array('NULL', 'null', NULL, null))) {
        // a nullok...
        $item[$old_field] = '';
      } else {
        // szövegre rá kell ezt ereszteni, mert gáz karakterek vannak
        // de sajnos szétfagyasztja a 7-es PHP-t.
        //$item_value = new \MongoDB\BSON\Regex($item[$old_field]);
        $item_value = $item[$old_field];
      }

      $data[$new_field] = $item_value;
    }
    return $data;
  }

  /*
   * Kapott tömb számait (int)-té teszi
   */

  public function numeric_int($array) {
    foreach ($array as $key => $value) {
      if (is_numeric($value)) {
        $array[$key] = (int) $value;
      }
    }
    return $array;
  }

  /*
   * Kapott $source tömbből ad hozzá a $target tömbhöz, 
   * $needed_fields-ből, de csak ha nem nullák vagy üresek
   */

  public function add_data_if_not_null($source, $target, $needed_fields) {
    foreach ($needed_fields as $old_field => $new_field) {
      $value = $source[$old_field];
      if (is_numeric($value) && $value > 0) {
        $target[$new_field] = $value;
      } elseif (!is_numeric($value) && $value != '') {
        $target[$new_field] = $value;
      }
    }
    return $target;
  }

  /*
   * Régi tag_id-ből új parameter ID-t csinál
   * 
   */

  public function tag_to_parameter($old_tag_id) {
    $parameter = $this->DB->first('parameters', ['old_id' => $old_tag_id]);
    return $parameter ? $parameter['id'] : false;
  }

  /*
   * Beszúrja a megadott modellhez a statokat
   * ezekre kell: 
   *  - artpieces
   *  - artists
   *  - places
   *  - users
   *  - sets
   *  - photos
   *  - folders
   *  - files
   */


  /*
   * URL conditions építő tömbből
   * így kell a KT API-nak: &conditions=id:1|email=paltamas@gmail.com
   */

  private function build_url_conditions($conditions = []) {
    $string = '';
    if (count($conditions) > 0) {
      $string .= '&conditions=';
      $cond_ = [];
      foreach ($conditions as $field => $value) {
        if (is_array($value)) {
          $values = implode(',', $value);
          $conds_[] = $field . ':' . $values;
        } else {
          $conds_[] = $field . ':' . $value;
        }
      }
      $string .= implode('|', $conds_);
    }
    return $string;
  }


  /*
 * Igen.
 * Kösz KT! Kösz PT! Kösz VG! :D
 */
  public function old_text($str, $check_echo = false) {
    // és akkor...
    $str = htmlentities($str);

    // Ellenőrzési céllal, ha kell, hogy lássam
    if ($check_echo) {
      return $str;
    }

    // Elrejtem a hülyén dekódolódottakat, és van, amit simán átírok
    $from = array('/&acirc;&euro;/', '/&acirc;&euro;/',
      '/&Atilde;&ndash;/', '/&Atilde;&ldquo;/', '/&Aring;&lsquo;/', '/&Atilde;&oelig;/',
      '/&Atilde;&permil;/', '/&Atilde;&scaron;/', '/&acirc;&sbquo;&not;/');
    $to = array('"', '"',
      '{XxX-O..}', '{XxX-O-}', '{XxX-o--}', '{XxX-U..}',
      '{XxX-E-}', 'XxX-U-}', 'XxX-e-}');
    $str = preg_replace($from, $to, $str);

    // Ráeresztem, ami valamennyire OK
    $str = utf8_decode(html_entity_decode(trim($str)));

    // Visszahozom az eldugdosottakat :)
    $from = array('/{XxX-O..}/', '/{XxX-O-}/', '/{XxX-o--}/', '/{XxX-U..}/', '/{XxX-E-}/', '/{XxX-U-}/', '/{XxX-e-}/');
    $to = array('Ö', 'Ó', 'ő', 'Ü', 'É', 'Ú', 'é');
    return preg_replace($from, $to, $str);
  }


  /**
   *
   * Üzenet jelölések; jajj, de katyvasz.
   *
   * @param $message
   * @param $read
   * @param $favored
   * @param $archived
   * @return array
   */
  public function message_flags($message, $read, $favored, $archived) {
    if ($message['sender'] == 1) {
      // A küldöt rögtön betesszük az olvasók közé
      if (!in_array($message['sender_user_id'], $read)) {
        $read[] = $message['sender_user_id'];
      }
      if ($message['starred'] == 1 && !in_array($message['sender_user_id'], $favored)) {
        $favored[] = $message['sender_user_id'];
      }
      if ($message['archived'] == 1 && !in_array($message['sender_user_id'], $archived)) {
        $archived[] = $message['sender_user_id'];
      }
    } else {
      if ($message['haveread'] == 1 && !in_array($message['receiver_user_id'], $read)) {
        $read[] = $message['receiver_user_id'];
      }
      if ($message['starred'] == 1 && !in_array($message['receiver_user_id'], $favored)) {
        $favored[] = $message['receiver_user_id'];
      }
      if ($message['archived'] == 1 && !in_array($message['receiver_user_id'], $archived)) {
        $archived[] = $message['receiver_user_id'];
      }
    }

    return [$read, $favored, $archived];
  }


  public function message_file($message, $message_id, $old_file_id) {
    // Itt a fájl
    $file_url = str_replace('.jpg', '_1.jpg', $this->kt_url . '/user/' . $message['sender_user_id'] . '/' . $message['file_filename']);

    $original_name = $message['file_filename'];
    $ext = $this->File->get_ext($original_name);
    $ext = $ext == 'jpeg' ? 'jpg' : $ext;
    $original_name = str_replace(['.' . $ext, strtoupper('.' . $ext)], '', $original_name);
    $filename = md5($old_file_id) . '-' . sha1($original_name);

    // Mentés fájlként
    $path = CORE['PATHS']['DATA'] . '/s3gate/files/' . $filename . '.' . $ext;

    // Leszedjük
    if (copy($file_url, $path)) {

      $type = mime_content_type($path);

      $data = [
        'id' => $old_file_id,
        'folder' => 'files',
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => (int)$message['sending_time'],
        'copied' => 0,
        'user_id' => (int)$message['sender_user_id'],
        'permissions' => '"' . $message['sender_user_id'] . '","' . $message['receiver_user_id'] . '"',
        'conversation_message_id' => $message_id,
        'size' => filesize($path),
      ];

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->File->read_image_data($path);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      $inserted_id = $old_file_id;

      if (!$this->DB->first('files', $data['id'])) {
        $this->DB->insert('files', $data);

        // Szólok a forevernek, hogy meló van
        $this->Mongo->insert('jobs', [
          'class' => 'files',
          'action' => 'handle',
          'options' => ['id' => $inserted_id],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      return $original_name;
    }

    // Feldolgozzuk
  }


  public function folder_file($file, $folder_id) {
    // Itt a fájl
    $file_url = $this->kt_url . '/user/' . $file['user_id'] . '/' . $file['original_slug'];

    $original_name = $file['title'] != '' ? $file['title'] : $file['id'];
    $ext = $this->File->get_ext($file['original_slug']);
    $ext = $ext == 'jpeg' ? 'jpg' : $ext;
    $filename = md5($file['id']) . '-' . sha1($original_name);

    // Mentés fájlként
    $path = CORE['PATHS']['DATA'] . '/s3gate/files/' . $filename . '.' . $ext;

    // Leszedjük
    if (copy($file_url, $path)) {

      $type = mime_content_type($path);

      $data = [
        'id' => (int)$file['id'],
        'folder' => 'files',
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => (int)$file['created'],
        'rank' => (int)$file['rank'],
        'cover' => (int)$file['cover'],
        'text' => $file['text'],
        'source' => $file['source'],
        'copied' => 0,
        'user_id' => (int)$file['user_id'],
        'folder_id' => (int)$folder_id,
        'size' => filesize($path),
        'license_type_id' => (int)$file['license_type_id'],
        'special_license' => (int)$file['special_license'],
      ];

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->exif_json($file['exif_json']);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      $inserted_id = (int)$file['id'];

      if (!$this->DB->first('files', $data['id'])) {
        $this->DB->insert('files', $data);

        // Szólok a forevernek, hogy meló van
        $this->Mongo->insert('jobs', [
          'class' => 'files',
          'action' => 'handle',
          'options' => [
            'id' => $inserted_id,
            'onesize' => true,
            'watermark' => true,
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      return $inserted_id;
    }
    // Feldolgozzuk
  }



  public function book_file($file, $book_id) {
    // Itt a fájl
    $file_url = $this->kt_url . '/user/' . $file['user_id'] . '/' . $file['original_slug'];

    $original_name = $file['title'] != '' ? $file['title'] : $file['id'];
    $ext = $this->File->get_ext($file['original_slug']);
    $ext = $ext == 'jpeg' ? 'jpg' : $ext;
    $filename = md5($file['id']) . '-' . sha1($original_name);

    // Mentés fájlként
    $path = CORE['PATHS']['DATA'] . '/s3gate/files/' . $filename . '.' . $ext;

    // Leszedjük
    if (copy($file_url, $path)) {

      $type = mime_content_type($path);

      $data = [
        'id' => (int)$file['id'],
        'folder' => 'files',
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => (int)$file['created'],
        'copied' => 0,
        'user_id' => (int)$file['user_id'],
        'size' => filesize($path),
        'license_type_id' => (int)$file['license_type_id'],
        'book_id' => $book_id,
      ];

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->exif_json($file['exif_json']);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      $inserted_id = (int)$file['id'];

      if (!$this->DB->first('files', $data['id'])) {
        $this->DB->insert('files', $data);

        // Szólok a forevernek, hogy meló van
        $this->Mongo->insert('jobs', [
          'class' => 'files',
          'action' => 'handle',
          'options' => [
            'id' => $inserted_id,
            'onesize' => true,
            'watermark' => false,
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      return $inserted_id;
    }
    // Feldolgozzuk
  }


  public function comment_file($file, $comment_id) {
    // Itt a fájl
    $file_url = $this->kt_url . '/user/' . $file['user_id'] . '/' . $file['original_slug'];

    $original_name = $file['title'] != '' ? $file['title'] : $file['id'];
    $ext = $this->File->get_ext($file['original_slug']);
    $ext = $ext == 'jpeg' ? 'jpg' : $ext;
    $filename = md5($file['id']) . '-' . sha1($original_name);

    // Mentés fájlként
    $path = CORE['PATHS']['DATA'] . '/s3gate/files/' . $filename . '.' . $ext;

    // Leszedjük
    if (copy($file_url, $path)) {

      $type = mime_content_type($path);

      $data = [
        'id' => (int)$file['id'],
        'folder' => 'files',
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => (int)$file['created'],
        'copied' => 0,
        'user_id' => (int)$file['user_id'],
        'size' => filesize($path),
        'license_type_id' => (int)$file['license_type_id'],
        'comment_id' => $comment_id,
      ];

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->exif_json($file['exif_json']);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      $inserted_id = (int)$file['id'];

      if (!$this->DB->first('files', $data['id'])) {
        $this->DB->insert('files', $data);

        // Szólok a forevernek, hogy meló van
        $this->Mongo->insert('jobs', [
          'class' => 'files',
          'action' => 'handle',
          'options' => [
            'id' => $inserted_id,
            'onesize' => true,
            'watermark' => false,
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      return $inserted_id;
    }
    // Feldolgozzuk
  }


  public function comment_photo($file, $comment_id) {
    // Itt a fájl
    $file_url = $this->kt_url . '/user/' . $file['user_id'] . '/' . $file['original_slug'];

    $original_name = $file['title'] != '' ? $file['title'] : $file['id'];
    $ext = $this->File->get_ext($file['original_slug']);
    $ext = $ext == 'jpeg' ? 'jpg' : $ext;
    $filename = md5($file['id']) . '-' . sha1($original_name);

    // Mentés fájlként
    $path = CORE['PATHS']['DATA'] . '/s3gate/files/' . $filename . '.' . $ext;

    // Leszedjük
    if (copy($file_url, $path)) {

      $type = mime_content_type($path);

      $data = [
        'id' => (int)$file['id'],
        'folder' => 'files',
        'name' => $filename,
        'original_name' => $original_name,
        'extension' => $ext,
        'type' => $type,
        'created' => (int)$file['created'],
        'copied' => 0,
        'user_id' => (int)$file['user_id'],
        'size' => filesize($path),
        'license_type_id' => (int)$file['license_type_id'],
        'comment_id' => $comment_id,
      ];

      // Kép adatok, ha kép
      if (_contains($type, ['jpg', 'jpeg'])) {
        $image_data = $this->exif_json($file['exif_json']);
        if (count($image_data) > 0) {
          $data['exif_json'] = json_encode($image_data);
          if (isset($image_data['taken'])) {
            $data['exif_taken'] = $image_data['taken'] < strtotime('2002-04-01')
              || $image_data['taken'] > strtotime('+12 hours') ? 0 : $image_data['taken'];
          }
        }
      }

      $inserted_id = (int)$file['id'];

      if (!$this->DB->first('files', $data['id'])) {
        $this->DB->insert('files', $data);

        // Szólok a forevernek, hogy meló van
        $this->Mongo->insert('jobs', [
          'class' => 'files',
          'action' => 'handle',
          'options' => [
            'id' => $inserted_id,
            'onesize' => true,
            'watermark' => false,
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }

      return $inserted_id;
    }
    // Feldolgozzuk
  }


  /**
   *
   * Korábban JSON-ban tárolt EXIF infókat nyerünk ki és dolgozunk fel
   *
   * Így jön: {"make":"Apple","model":"iPhone SE","exposuretime":"1\/17","fnumber":"11\/5","isospeedratings":320,"datetime":"2017:05:31 11:01:03","lat":0,"lng":0}
   *
   * @param $exif_json
   * @return array
   */
  public function exif_json ($exif_json) {
    $image_data = [];

    if (!in_array($exif_json, ['', '-'])) {
      $exif_array = json_decode($exif_json, true);
      $exif_cleaned = [];
      foreach (['make', 'model', 'exposuretime', 'fnumber', 'isospeedratings'] as $param) {
        if (@$exif_array[$param] != '') {
          $exif_cleaned[$param] = trim($exif_array[$param]);
        }
      }

      if (count($exif_cleaned) > 0) {
        $image_data['exif'] = $exif_cleaned;
      }

      // Paradigmaváltás, ezt olvastam:
      // https://gis.stackexchange.com/questions/24690/difference-between-lon-and-lng
      // nem, mintha a leaflet nem lng-nek hívná... ;]
      if (isset($exif_array['lng'])) {
        $exif_array['lon'] = $exif_array['lng'];
        unset($exif_array['lng']);
      }

      if (@$exif_array['lat'] != '' && @$exif_array['lon'] != '') {
        $image_data['coordinates'] = [
          'lat' => $exif_array['lat'],
          'lon' => $exif_array['lon'],
        ];
      }

      if (@$exif_array['datetime'] != '') {
        $p = explode(' ', $exif_array['datetime']);
        $image_data['taken'] = strtotime(str_replace(':', '-', $p[0]) . ' ' . $p[1]);
      }
    }

    return $image_data;
  }
}
