<?php
use Kozterkep\AppBase as AppBase;

class ToolsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_sidemenu' => false,
      '_title' => false,
      '_breadcrumbs_menu' => false
    ]);
  }


  /**
   * Profil fotóra dob át
   */
  public function user_image() {
    $user = $this->MC->t('users', $this->params->id);
    $size = @$this->params->query['s'] > 0 ? $this->params->query['s'] : 2;
    if (@$user['profile_photo_filename'] != '') {
      // Van profil fotó
      $this->redirect('/tagok/' . $user['profile_photo_filename'] . '_' . $size . '.jpg');
    } elseif ($user) {
      // Nincs profil fotó
      $this->redirect('/img/kt-tag-ikon_' . $size . '.jpg');
    } else {
      // Nincs user
      $this->redirect('/');
    }
  }

  public function user_tooltip() {
    $user = $this->DB->first('users', $this->params->id, [
      //'fields' => ['id', 'name', 'nickname', 'profile_photo_filename', 'last_here', 'introduction'],
    ]);

    $this->cache_header(300);

    $this->set([
      'user' => $user
    ]);
  }

  public function artpiece_tooltip() {
    $artpiece = $this->DB->first('artpieces', $this->params->id, []);

    $this->cache_header(300);

    $this->set([
      'artpiece' => $artpiece
    ]);
  }

  public function artist_tooltip() {
    $artist = $this->DB->first('artists', $this->params->id, [
      //'fields' => ['id', 'name', 'profile_photo_filename', 'last_here', 'introduction'],
    ]);

    $this->cache_header(300);

    $this->set([
      'artist' => $artist
    ]);
  }

  public function place_tooltip() {
    $place = $this->DB->first('places', $this->params->id, [
      //'fields' => ['id', 'name', 'profile_photo_filename', 'last_here', 'introduction'],
    ]);

    $this->cache_header(300);

    $this->set([
      'place' => $place
    ]);
  }

  public function set_tooltip() {
    $set = $this->Mongo->first('sets', ['_id' => $this->params->id]);

    $this->cache_header(300);

    $this->set([
      'set' => $set
    ]);
  }

  public function photo_tooltip() {
    $photo = $this->DB->first('photos', $this->params->id, [
      //'fields' => ['id', 'name', 'profile_photo_filename', 'last_here', 'introduction'],
    ]);

    $this->cache_header(300);

    $this->set([
      'photo' => $photo
    ]);
  }

  public function cookie_consent() {
    $cookies = $_COOKIE;
    $own_cookies = sDB['cookie_descriptions'];

    $this->set([
      'cookies' => $cookies,
      'own_cookies' => $own_cookies,

      '_title' => 'Süti hozzájárulás',
    ]);
  }

  public function only_users() {
    $this->set([
      '_title' => 'Csak bejelentkezéssel érhető el',
    ]);
  }


  public function image_editor() {

    $this->users_only();
    $photo = false;
    if (isset($this->params->query['foto'])) {
      $photo = $this->DB->first('photos', $this->params->query['foto']);
      if (!$photo || !$this->Users->owner_or_right($photo, $this->user)) {
        $this->redirect('/', ['hibas_url', 'warning']);
      } elseif ($photo['missing_original'] == 1) {
        $this->redirect('/', ['Nincs eredeti, vízjel nélküli fájl, így a kép nem szerkeszthető. 2012. előtt nem mentettük az eredeti fájlt, csak az átméretezetteket.', 'warning']);
      }
    }

    if ($this->Request->is('post')) {

      // Megvan-e lokálisan az eredeti kép?
      $original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg';
      if (!is_file($original_path)) {
        $this->File->s3_get('originals/' . $photo['original_slug'] . '.jpg', $original_path);
      }

      if (is_file($original_path)) {

        $image = imagecreatefromjpeg($original_path);
        $rotate = imagerotate($image, 360-(int)$this->params->data['angle'], 0);

        $prefix = 'u' . $photo['user_id'] . '-a' . $photo['artpiece_id'] . '-';
        $slug = $prefix . uniqid() . '-' . bin2hex(random_bytes(12));
        $new_original_slug = $prefix . uniqid() . '-' . sha1(uniqid());

        $new_original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $new_original_slug . '.jpg';
        $rotated = imagejpeg($rotate, $new_original_path);

        if ($rotated) {
          $this->DB->update('photos', [
            'slug' => $slug,
            'original_slug' => $new_original_slug,
            'copied' => 0,
          ], $photo['id']);

          // Hogy a műlap még a job lefutása előtt is jót mutasson (orig. képpel)
          $this->Cache->delete('cached-view-artpieces-view-' . $photo['artpiece_id']);

          $this->Mongo->insert('jobs', [
            'class' => 'photos',
            'action' => 'handle',
            'options' => [
              'id' => $photo['id'],
              'watermark' => true,
              'rotate' => false,
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);

          $this->redirect('/mulapok/szerkesztes/' . $photo['artpiece_id'] . '#szerk-fotok', 'A forgatási feladatot elkezdtük. Előkerestük az eredeti, vízjel nélküli képet és elforgattuk. Most újra elkészítjük az alternatív képméreteket és a vízjelezést.');
        }
      }

      $this->redirect('/eszkozok/kepkezelo/?foto=' . $photo['id'] . '&forgatas=' . $this->params->data['angle'], [texts('varatlan_hiba'), 'warning']);
    }

    $this->set([
      'photo' => $photo,
      '_title' => 'Képkezelő',
      '_shareable' => false,
    ]);
  }


  public function photo_download($id) {
    $photo = $this->DB->first('photos', $id);

    // Van-e joga (ide jön majd az, aki engedélyt kapott rá)
    if ($this->Users->owner_or_head($photo, $this->user)) {
      if ($photo) {
        $original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg';
      }

      if (is_file($original_path)) {
        header('Content-Type: image/jpeg');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 45)) . ' GMT');
        readfile($original_path);
        exit;
      } else {
        if ($photo && $photo['copied'] > 0) {
          $this->redirect(C_WS_S3['url'] . 'originals/' . $photo['original_slug'] . '.jpg');
        }
      }
    }

    exit;
  }


  public function photo_fetch($id) {
    $photo = $this->DB->first('photos', $id);

    if (!$photo) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $size = @$this->params->query['meret'] > 0 ? (int)$this->params->query['meret'] : 4;
    $size = min($size, 8);

    $path = $photo['slug'] . '_' . $size . '.jpg';
    $local_path = CORE['PATHS']['DATA'] . '/tmp/' . $path;

    if (!is_file($local_path)) {
      // Nincs meg lokálisan, visszatöltjük TMP-be
      $this->File->s3_get('photos/' . $path, $local_path);
    }

    header('Content-Type: image/jpeg');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 45)) . ' GMT');
    readfile($local_path);
    exit;
  }


  /**
   *
   * Átmeneti képmutatás, amíg nincs S3-on
   * simán kiolvassuk az eredetit.
   * Ha már nincs meg, akkor 100%, hogy másolt,
   * akkor továbbdobjuk permanent redirekttel.
   *
   * @param string $filename
   */
  public function temporary_photo($slug = '') {
    $original_path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $slug . '.jpg';
    if (is_file($original_path)) {
      header('Content-Type: image/jpeg');
      header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24*45)) . ' GMT');
      readfile($original_path);
      exit;
    } else {
      $photo = $this->DB->first('photos', ['original_slug' => $slug], ['fields' => ['slug', 'copied']]);
      if ($photo && $photo['copied'] > 0) {
        $this->redirect(C_WS_S3['url'] . 'photos/' . $photo['slug'] . '_1.jpg', false, 301);
      }
    }
  }

}