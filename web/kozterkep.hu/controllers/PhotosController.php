<?php
use Kozterkep\AppBase as AppBase;

class PhotosController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
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
  public function temporary($slug = '') {
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



  public function search() {
    // Keresés
    $conditions = [];

    $conditions['before_shared'] = 0;

    if (@$this->params->query['tag'] > 0) {
      $conditions['user_id'] = (int)$this->params->query['tag'];
    }

    if (@$this->params->query['kihez'] == 'mashoz') {
      $conditions['added'] = 1;
    } elseif (@$this->params->query['kihez'] == 'magahoz') {
      $conditions['added'] = 0;
    }

    if (@$this->params->query['hova'] == 'mulap') {
      $conditions['artpiece_id >'] = 0;
    } elseif (@$this->params->query['hova'] == 'alkoto') {
      $conditions['portrait_artist_id >'] = 0;
    }

    if (@$this->params->query['archiv'] == 1) {
      $conditions['archive'] = 1;
    }


    if (@$this->params->query['adalek'] == 1) {
      $conditions['other'] = 1;
    }

    if (@$this->params->query['elmenyfoto'] == 1) {
      $conditions['joy'] = 1;
    }

    if (@$this->params->query['mas_helyrol'] == 1) {
      $conditions['other_place'] = 1;
    }


    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $this->DB->count('photos', $conditions)
    ];

    $photos = $this->DB->find('photos', array(
      'fields' => array('id', 'slug', 'artpiece_id', 'user_id', 'approved', 'created', 'portrait_artist_id'),
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'approved DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ));

    $this->set([
      '_title' => 'Fotók listája',
      '_active_menu' => 'Adattár',
      '_active_submenu' => 'Fotók',
      '_sidemenu' => false,

      'filtered' => count($conditions) > 1 ? true : false,
      'photos' => $photos,
      'pagination' => $pagination,
    ]);
  }

}