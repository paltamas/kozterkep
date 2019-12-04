<?php
namespace Kozterkep;

class UsersLogic {

  private $DB;
  private $app_config;

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();
    $this->Email = new EmailHelper($app_config);
    $this->Html = new HtmlHelper($app_config);
    $this->Text = new TextHelper();
    $this->File = new FileHelper($DB, $this->Mongo);
    $this->Notification = new NotificationsLogic($app_config, $DB);
  }

  public function top_members($count = 50) {
    //$this->DB->delete_cache('users_top_members_' . $count);
    return $this->DB->find('users', [
      'conditions' => [
        'activated >' => 0,
        'blocked' => 0,
        'harakiri' => 0,
      ],
      'fields' => ['id', 'name', 'link'],
      'order' => 'points DESC',
      'limit' => $count
    ], [
      'name' => 'users_top_members_' . $count
    ]);
  }


  public function list($type = 'actives', $options = []) {
    $options = (array)$options + [
      'only_ids' => false,
    ];

    $conditions = [
      'activated >' => 0,
      'name <>' => '',
    ];

    switch ($type) {
      case 'creators':
        $conditions = $conditions + [
          'harakiri' => 0,
          'artpiece_count >' => 0
        ];
        $fields = ['id', "CONCAT(name, ' (', artpiece_count,')') AS name"];
        break;

      case 'made_edits':
        $conditions = $conditions + [
          'harakiri' => 0,
          'edit_other_count >' => 0
        ];
        break;

      case 'not_managing_artpieces':
        $conditions = $conditions + ['artpiece_count >' => 0];
        $conditions['OR'] = [
          'managing_on' => 0,
          'passed_away' => 1,
          'blocked' => 1,
          'harakiri' => 1,
          'last_here <' => strtotime('-' . sDB['limits']['edits']['inactive_after_months'] . ' months')
        ];
        break;

      case 'commenters':
        $user_ids = $this->Mongo->group('comments', 'user_id');
        if (count($user_ids) > 0) {
          $conditions[] = ['id' => $user_ids];
        }
        break;

      case 'photoers':
        $conditions = $conditions + ['photo_count >' => 0];
        break;

      case 'folderers':
        $conditions = $conditions + ['folder_count >' => 0];
        break;

      case 'setters':
        $conditions = $conditions + ['set_count >' => 0];
        break;

      case 'bloggers':
        $conditions = $conditions + ['post_count >' => 0];
        break;

      case 'level_0':
        $conditions = $conditions + ['user_level' => 0];
        break;

      case 'level_1':
        $conditions = $conditions + ['user_level' => 1];
        break;

      case 'headitors':
        $conditions = $conditions + ['headitor' => 1];
        break;

      case 'headitors_ever':
        $conditions['OR'] = [
          'headitor' => 1,
          'headitor_was' => 1,
        ];
        break;

      case 'all':
        break;

      case 'actives':
      default:
        $conditions = $conditions + [
          'blocked' => 0,
          'harakiri' => 0,
        ];
        break;
    }

    if (!isset($fields)) {
      $fields = ['id', 'name'];
    }

    $users = $this->DB->find('users', [
      'type' => 'list',
      'conditions' => $conditions,
      'fields' => $fields,
      'order' => 'name ASC',
      'debug' => false,
    ], ['name' => 'users_list_' . $type]);

    if ($options['only_ids']) {
      return array_keys($users);
    }

    return $users;
  }


  public function name($user_id, $options = []) {
    $user = is_array($user_id) && @$user_id['id'] > 0
      ? $user_id : @$this->MC->t('users', $user_id);

    if (!$user) {
      return texts('torolt_tag_neve');
    }

    $options = (array)$options + [
      'tooltip' => true,
      'link' => true,
      'class' => '',
      'image' => false,
    ];

    $attributes = [];

    if ($options['tooltip']) {
      $attributes = array_merge($attributes, [
        'ia-tooltip' => 'tag',
        'ia-tooltip-id' => $user['id'],
        'class' => $options['class'],
      ]);
    }

    $s = $user['name'];

    if ($options['link']) {
      $s = $this->Html->link($s, '/kozosseg/profil/' . $user['link'], $attributes);
    } else {
      $attributes = $this->Html->parse_attributes($attributes);
      $s = '<span' . $attributes . '>' . $s . '</span>';
    }

    if ($options['image']) {
      $size = is_numeric($options['image']) && $options['image'] > 0 ? $options['image'] : 4;
      $profile_image = $this->profile_image($user, $size);
      if ($profile_image == '') {
        $profile_image = '<span class="far fa-user-circle fa-lg text-muted mr-2"></span>';
      }
      $s = $profile_image . $s;
    }

    return $s;
  }

  public function profile_image($user, $size = 4, $options = []) {
    $options = (array)@$options + [
      'class' => 'img-fluid rounded-circle mr-1',
      'tooltip' => false,
      'only_path' => false,
    ];

    $attributes = [
      'class' => $options['class'],
    ];

    if (!is_array($user) && is_numeric($user)) {
      $user = $this->MC->t('users', $user);
      if (!$user) {
        return '';
      }
    }

    if ($options['tooltip']) {
      $attributes = array_merge($attributes, [
        'ia-tooltip' => 'tag',
        'ia-tooltip-id' => $user['id'],
        'class' => $options['class'],
      ]);
    }

    $attributes = $this->Html->parse_attributes($attributes);

    if ($user['profile_photo_filename'] != '') {
      $image_path = '/tagok/' . $user['profile_photo_filename'] . '_' . $size . '.jpg';
    } else {
      $image_path = '/img/kt-tag-ikon_' . $size . '.jpg';
    }

    if ($options['only_path']) {
      return $image_path;
    } else {
      return '<img src="' . $image_path . '" ' . $attributes . '>';
    }
  }

  public function email_change($user, $old_email, $new_email) {
    // Elmentjük az új emailcímet és kiküldjük a leveleket
    if ($this->DB->update('users', ['email_to_confirm' => $new_email], $user['id'])) {

      $this->Mongo->insert('jobs', [
        'class' => 'emails',
        'action' => 'send',
        'options' => [
          'template' => 'system',
          'user_id' => $user['id'],
          'subject' => 'Email módosítási értesítő',
          'body' => texts('emails/emailchange_old', [
            'name' => $user['name'],
            'new_email' => $new_email,
            'contact_form' => $this->app_config['url'] . '/oldalak/kapcsolat'
          ])
        ],
        'created' => date('Y-m-d H:i:s'),
      ]);

      $this->Mongo->insert('jobs', [
        'class' => 'emails',
        'action' => 'send',
        'options' => [
          'template' => 'system',
          'user_id' => $user['id'],
          'to' => $new_email,
          'subject' => 'Hagyd jóvá új emailcímedet!',
          'body' => texts('emails/emailchange_new', [
            'name' => $user['name'],
            'link' => $this->app_config['url'] . '/tagsag/email-modositas/' . sha1($new_email),
            'contact_form' => $this->app_config['url'] . '/oldalak/kapcsolat'
          ])
        ],
        'created' => date('Y-m-d H:i:s'),
      ]);

      return true;
    }

    return false;
  }

  /**
   * Vakáció-válasz küldése értesítőben;
   * hacsak nem szerepel a sender_id már a user.auto_replied_today mezején
   *
   * @param $user_id - aki nemvan-e
   * @param $sender_id - aki ír a vakációzónak és kell neki az infó
   */
  public function auto_reply($user_id, $sender_id) {
    $user = $this->DB->find('users', [
      'conditions' => "id = " . $user_id . " AND out_of_work = 1 AND (auto_replied_today NOT LIKE '%user-" . $sender_id . "%' OR auto_replied_today IS NULL)",
      'fields' => ['id', 'name', 'auto_reply', 'auto_replied_today'],
      'debug' => false
    ]);

    $user = count($user) == 1 ? $user[0] : false;

    if ($user) {

      $who_is_you = $user_id == $sender_id ? ' (aki te vagy) ' : '';

      $custom_message = $user['auto_reply'] != '' ? ' Üzenete:' . PHP_EOL . $user['auto_reply'] : '';
      $this->Notification->create(
        $sender_id,
        $user['name'] . $who_is_you . ' vakáción van',
        'Emiatt lehet, hogy nem olvassa a leveleket.' . $this->Text->format($custom_message, [
          'nl2br' => false,
          'format' => false
        ])
      );

      $auto_replied_today = _json_decode($user['auto_replied_today']);
      $auto_replied_today = !is_array($auto_replied_today) ? [] : $auto_replied_today;
      $auto_replied_today[] = 'user-' . $sender_id;

      $this->DB->update('users', [
        'auto_replied_today' => json_encode($auto_replied_today)
      ], $user_id);
    }
  }


  /**
   *
   * Az adott dolog tulaja vagy headitor vagy admin vagy meghívott benne
   *
   * @param $thing
   * @param $user
   * @return bool
   */
  public function owner_or_head_or_invited($thing, $user) {
    if ($thing && (strpos(@$thing['invited_users'], '"' . $user['id'] . '"') !== false
        || $thing['user_id'] == $user['id'] || $user['headitor'] == 1 || $user['admin'] == 1)) {
      return true;
    }
    return false;
  }

  /**
   *
   * Az adott dolog tulaja vagy headitor vagy admin
   *
   * @param $thing
   * @param $user
   * @return bool
   */
  public function owner_or_head($thing, $user) {
    if ($thing && ($thing['user_id'] == $user['id'] || $user['headitor'] == 1 || $user['admin'] == 1)) {
      return true;
    }
    return false;
  }


  /**
   *
   * Az adott dolog tulaja vagy van publikálási joga
   *
   * @param $thing
   * @param $user
   * @return bool
   */
  public function owner_or_right($thing, $user) {
    if ($thing && ($thing['user_id'] == $user['id'] || $user['user_level'] == 1)) {
      return true;
    }
    return false;
  }


  /**
   *
   * Főszerk vagy admin
   *
   * @param $user
   * @return bool
   */
  public function is_head($user) {
    if ($user['headitor'] == 1 || $user['admin'] == 1) {
      return true;
    }
    return false;
  }

  /**
   *
   * Vétó jogú főszerkesztő
   *
   * @param $user
   * @return bool
   */
  public function is_vetohead($user) {
    if (in_array($user['id'], CORE['USERS']['headveto'])) {
      return true;
    }
    return false;
  }



  /**
   *
   * Sime user név lista
   *
   * @param $user_ids
   * @param string $separator
   * @return string
   */
  public function namelist ($user_ids, $options = []) {
    $options = (array)$options + [
      'separator' => ', ',
      'link' => true,
    ];

    $s = '';

    if (!is_array($user_ids)) {
      $user_ids = _json_decode($user_ids);
    }

    if (is_array($user_ids) && count($user_ids) > 0) {
      foreach ($user_ids as $user_id) {
        if ($user = $this->MC->t('users', $user_id)) {
          $name = $user['name'];
          if ($options['link']) {
            $name = $this->Html->link($name, '', [
              'user' => $user,
              'ia-tooltip' => 'tag',
              'ia-tooltip-id' => $user['id'],
            ]);
          }
          $s .= $name . $options['separator'];
        }
      }
      $s = rtrim($s, $options['separator']);
    }

    return $s;
  }



  /**
   *
   * Profilbeállításkor feltöltött / módosított képek kezelése
   *
   * @param $data
   * @param $user
   */
  public function manage_settings_images($data, $user) {
    $error_messages = [];

    $users_folder = CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/tagok/';

    // Fotó törlések
    // Profil fotó
    if (@$data['delete_profile_photo'] == 1) {
      for ($i = 1; $i <= 5; $i++) {
        $path = $users_folder . $user['profile_photo_filename'] . '_' . $i . '.jpg';
        if (is_file($path)) {
          unlink($path);
        }
      }
      $this->DB->update('users', ['profile_photo_filename' => ''], $user['id']);
    }
    // Fejléckép fotó
    if (@$data['delete_header_photo'] == 1) {
      $path = $users_folder . $user['header_photo_filename'];
      if (is_file($path)) {
        unlink($path);
      }
      $this->DB->update('users', ['header_photo_filename' => ''], $user['id']);
    }

    if (isset($data['files']['header_photo']) && $data['files']['header_photo']['name'] != '') {
      $file = $data['files']['header_photo'];
      $target_filename = 'header-' . uniqid() . '-' . md5(uniqid()) . '.jpg';
      if (_contains(strtolower($file['type']), ['jpg', 'jpeg'])) {
        $path = $users_folder . $target_filename;
        if (move_uploaded_file($file['tmp_name'], $path)) {

          require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'ImageResize.php');
          $image = new \Gumlet\ImageResize($path);
          $image->quality_jpg = 70;
          $image->resizeToWidth('2400');
          $image->save($path);

          $data['header_photo_height'] = getimagesize($path)[1];
          $data['header_photo_filename'] = $target_filename;

        } else {
          $error_messages[] = 'A feltöltés nem sikerült, próbáld újra.';
        }
      } else {
        $error_messages[] = 'A kép csak JPG típusú lehet. Típusa: ' . $file['type'];
      }
    }

    if (isset($data['files']['profile_photo']) && $data['files']['profile_photo']['name'] != '') {
      $file = $data['files']['profile_photo'];
      $target_filename = uniqid() . '-' . md5(uniqid());
      if (_contains($file['type'], ['jpg', 'jpeg'])) {
        $original_path = $users_folder . $target_filename . '.jpg';
        if (move_uploaded_file($file['tmp_name'], $original_path)) {

          require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'manual' . DS . 'ImageResize.php');

          $sizes = [
            1 => 360,
            2 => 120,
            3 => 48,
            4 => 32,
            5 => 18,
          ];

          $size_path = $users_folder . $target_filename;

          // Ebből indulunk
          $base_image = $size_path . '_600.jpg';
          $image = new \Gumlet\ImageResize($original_path);
          $image->quality_jpg = 70;
          $image->resizeToLongSide(600);
          $image->save($base_image);

          foreach ($sizes as $key => $size) {
            $image = new \Gumlet\ImageResize($base_image);
            $image->quality_jpg = 70;
            $image->crop($size, $size, true, 6);
            $image->save($size_path . '_' . $key . '.jpg');
          }

          unlink($original_path);
          unlink($base_image);

          $data['profile_photo_filename'] = $target_filename;

        } else {
          $error_messages[] = 'A feltöltés nem sikerült, próbáld újra.';
        }
      } else {
        $error_messages[] = 'A kép csak JPG típusú lehet. Típusa: ' . $file['type'];
      }
    }

    return [$data, $error_messages];
  }


  /**
   *
   * Edit, Follow link kipakolásához
   * ettől persze még nem teheti, mert a linken túl is
   * ott a csekkolás.
   *
   * @param $what
   * @param $params
   * @param $user
   * @return bool
   */
  public function can_do($what, $params, $user) {

    $can = false;

    if (in_array($params->controller, ['artpieces', 'artists', 'places', 'sets', 'folders', 'community', 'posts'])) {
      $model = $params->controller == 'community' ? 'users' : $params->controller;

      // Blog oldal, ez teljesen külön
      // Blog, itt nincs modell
      if ($params->controller == 'posts' && $params->action == 'member'
        && $params->id == @$user['link']) {
        return true;
      }


      if ($model == 'users') {
        // Mivel a paramban az ID mezőben link van
        $item = $this->MC->t('users_by_link', $params->id);
      } else {
        $item = $this->MC->t($model, $params->id);
      }

      if (!$item) {
        return false;
      }

      if ($what == 'edit') {
        if ($model == 'artpieces') {
          // Bárki, aki user
          $can = true;
        } elseif ($model == 'users' && $item['id'] == $user['id'] ) {
          // Csak a user maga magát
          $can = true;
        } elseif (in_array($model, ['folders']) && $item['user_id'] == $user['id']) {
          // Csak a tulajdonos
          $can = true;
        } elseif (in_array($model, ['sets'])) {
          // Közöst tulaj, admin, headitor
          if ($item['set_type_id'] == 1
            && ($item['user_id'] == $user['id'] || $user['admin'] == 1 || $user['headitor'] == 1)) {
            $can =  true;
          } elseif ($item['set_type_id'] == 2 && $item['user_id'] == $user['id']) {
            $can =  true;
          }
        } elseif (in_array($model, ['artists', 'places', 'posts'])) {
          // Tulaj, Admin, Headitor
          if ($item['user_id'] == $user['id'] || $user['admin'] == 1 || $user['headitor'] == 1) {
            $can = true;
          }
        }
      }


      if ($what == 'follow') {
        $can = true;
        if ($model == 'users' && $item['id'] == $user['id']) {
          // User, magát, nem
          $can = false;
        } elseif (in_array($model, ['artpieces', 'folders']) && $item['user_id'] == $user['id']) {
          // műlapot, mappát tulaja nem
          $can = false;
        } elseif ($model == 'sets' && $item['set_type_id'] == 2 && $item['user_id'] == $user['id']) {
          // tagi gyűjteményt tulaja nem
          $can = false;
        }
      }

    }

    return $can;
  }



  /**
   *
   * Kapcsolatfelvételi link, különböző opciókkal
   *
   * @param $user
   * @param array $options
   * @return string|void
   */
  public function contact_link($user, $options = []) {
    $options = (array)@$options + [
      'text' => '',
      'link_options' => [],
      'artpiece_id' => 0,
      'photo_id' => 0,
      'file_id' => 0,
      'div' => '',
    ];

    if (is_numeric($user)) {
      $user = $this->MC->t('users', $user);
    }

    if ($user['active'] == 0 || @$user['blocked'] == 1
      || @$user['harakiri'] == 1 || @$user['passed_away'] == 1) {
      return;
    }

    $url = '/beszelgetesek/inditas?tag=' . $user['id'];

    if ($options['artpiece_id'] > 0) {
      $url .= '&mulap_az=' . $options['artpiece_id'];
    }
    if ($options['photo_id'] > 0) {
      $url .= '&foto_az=' . $options['photo_id'];
    }
    if ($options['file_id'] > 0) {
      $url .= '&fajl_az=' . $options['file_id'];
    }

    $s = $this->Html->link($options['text'], $url, $options['link_options']);

    if ($options['div'] != '') {
      $s = '<div class="' . $options['div'] . '">' . $s . '</div>';
    }

    return $s;
  }



  /**
   *
   * Beadott user, photo vagy file esetén visszaadja
   * a választható licenszek listáját, figyelembe véve azt, hogy miből mibe
   * lehet váltani, és hogy keményebbe nem.
   *
   * @param $thing
   * @return mixed
   */
  public function licenses_selectable($thing) {
    $licenses = sDB['license_types'];
    $license_transmissions = sDB['license_transmissions'];
    foreach ($licenses as $key => $licens) {
      if ($thing['license_type_id'] != $key
        && !in_array($key, $license_transmissions[$thing['license_type_id']]['children'])) {
        unset($licenses[$key]);
      }
    }
    return $licenses;
  }



  /**
   * Licensz váltás
   * @param $user_id
   * @return bool
   */
  public function license_change($user_id, $new_license_id) {
    $license_transmissions = sDB['license_transmissions'];

    // A legaktuálisabb
    $user = $this->DB->first('users', $user_id);

    if ($user
      && in_array($new_license_id, $license_transmissions[$user['license_type_id']]['children'])) {
      // Válthatunk-e így? igen,

      // frissítjük a users
      $this->DB->update('users', [
        'license_type_id' => $new_license_id
      ], $user['id']);

      // nyomjuk a nem speclicenszes képeket
      $this->DB->update('photos', [
        'license_type_id' => $new_license_id
      ], [
        'user_id' => $user['id'],
        'special_license' => 0,
      ]);

      // nyomjuk a nem speclicenszes fájlokat
      $this->DB->update('files', [
        'license_type_id' => (int)$new_license_id
      ], [
        'user_id' => (int)$user['id'],
        'special_license' => 0,
      ]);

      return true;
    }

    return false;
  }


  /**
   *
   * Egy user nemkezelő-e?
   *
   * @param $user
   * @return bool
   */
  public function not_managing($user) {
    if (is_numeric($user)) {
      $user = $this->MC->t('users', $user);
    }

    if ($user && ($user['managing_on'] == 0 || $user['blocked'] == 1
        || $user['passed_away'] == 1 || $user['harakiri'] == 1
      || $user['last_here'] <= strtotime('-' . sDB['limits']['edits']['inactive_after_months'] . ' months'))) {
      return true;
    }
    return false;
  }

}

