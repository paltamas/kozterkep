<?php
use Kozterkep\AppBase as AppBase;

class AppController extends AppBase {

  // View adatok
  public static $view_data = [];
  public static $layout_name = 'default';

  public static $_user = false;
  public static $_params;

  public function __construct() {
    parent::__construct(APP);

    // Ezek mennek a többi controllerbe
    static::$_user = $this->Auth->user();
    static::$_params = $this->Request->get();


    $this->inits();

    $this->old_things();

    if (!$this->Cookie->get(APP['cookies']['webstat_name'])) {
      $this->Cookie->set(APP['cookies']['webstat_name'], uniqid(rand(10000,99999), true));
    }

    $this->user_check();

    $this->redirect_flash();

    $this->set([
      'title' => APP['title'],
      '_user' => static::$_user,
      '_params' => static::$_params,
      '_header' => true,
      '_simple_mobile' => false,
      '_mobile_header' => true,
      '_sidemenu' => true,
      '_active_menu' => '',
      '_active_submenu' => '',
      '_active_sidemenu' => '',
      '_title_row' => true,
      '_tabs' => false,
      '_breadcrumbs_menu' => true,
      '_footer' => true,
    ]);

    $this->render_view($this->load_action());
  }


  /**
   *
   * Layout beállítása Controllerből
   *
   * @param $name
   */
  public function layout($name) {
    static::$layout_name = $name;
  }


  /**
   * Kell-e cache-elni az adott view-t; vars.php-ben konfigoljuk
   * @return bool
   */
  public function cacheable() {
    $c = APP['cacheables'];
    return isset($c[static::$_params->controller][static::$_params->action])
      ? $c[static::$_params->controller][static::$_params->action] : false;
  }

  /**
   *
   * Van-e cache-ünk az aktuális view-hoz
   *
   * @return bool|mixed
   */
  private function have_cache() {
    if (!$this->have_to_cache() && $this->cacheable() && $cache = $this->Cache->get($this->cache_name())) {
      return $cache;
    }
    return false;
  }

  /**
   *
   * Kell-e épp cache-t írni az aktuális view-hoz
   *
   * @return bool
   */
  private function have_to_cache() {
    if ($this->cacheable() && (isset(static::$_params->query['regen']) || !$this->Cache->get($this->cache_name()))) {
      return $this->cacheable();
    }
    return false;
  }





  /**
   *
   * View változók beállítása Controllerből
   *
   * @param null $data
   */
  public function set($data = null) {
    if ($data !== null) {
      static::$view_data = array_merge(static::$view_data, $data);
    }
  }


  /**
   *
   * Element meghívása; View-ból
   *
   * @param $view_name
   * @param array $passed_data
   */
  public function element($view_name, $passed_data = [], $echo = false) {
    $element_name = '_elements' . DS . $view_name;
    $view_file = $this->views_dir . str_replace('/', DS , $element_name) . '.php';
    $this->set($passed_data);
    if ($echo) {
      echo $this->fetch_simple($view_file, true);
      return;
    } else {
      return $this->fetch_simple($view_file, true);
    }
  }


  /**
   *
   * Action betöltése itt, rendeléshez
   *
   * @return string
   */
  private function load_action() {
    // Controller és action visszafejtés
    // A params-ban már visszafordul szépen a magyar route-név
    $controller_name = ucfirst(static::$_params->controller . 'Controller');
    $action_name = static::$_params->action;
    $id = static::$_params->id;


    // Csak ha nem cache-elendő, vagy épp kell cache-t írni
    if (!$this->cacheable() || $this->have_to_cache()) {

      // Controller, action betöltés, ha van ilyen
      if (class_exists($controller_name)) {
        $controller = new $controller_name;

        if (method_exists($controller, $action_name)) {
          // Ha van, akkor meghívjuk, hogy állítsa, amit akar
          $controller->$action_name($id);
        } elseif (method_exists($controller, '__call')) {
          // Nincs action, hátha van call
          $controller->__call($id);
          $action_name = '_default';
        }
      }

    }

    // Visszaadjuk a controller actiont, amit be kell tölteni,
    // fenti eredménytől függetlenül. Így controller / action nélkül is
    // meghívhatók sima statikus view-k.
    return static::$_params->controller . '/' . $action_name;
  }


  /**
   *
   * View fájl összerakása layoutban
   *
   * @param $view
   */
  public function render_view($view) {
    // Amennyiben layout_name = false, akkor nincs renderelés
    if (!static::$layout_name) {
      return;
    }

    // Van view renderelés

    // Ajax hívásnál ajax layout
    if (static::$_params->is_ajax) {
      static::$layout_name = 'ajax';
    } elseif (isset(static::$_params->query['teljeskepernyosmod'])) {
      static::$layout_name = 'fullscreen';
    }

    // Layout file
    $layout_file = $this->views_dir . '_layouts' . DS . static::$layout_name . '.php';
    if (!is_file($layout_file)) {
      debug('Nincs ilyen layout: ' . static::$layout_name);
      $layout_file = $this->views_dir . '_layouts' . DS . APP['default_layout'];
    }


    // View fájl útvonala
    $view_file = $this->views_dir . str_replace('/', DS, $view) . '.php';

    /**
     * Akkor olvassuk be a view fájlt, ha nem kell cacheelni,
     * vagy kell és épp lejárt, vagy épp regenerálás van
     */

    if ($cached_view_data = $this->have_cache()) {
      // View datahoz hozzáadjuk a cache-ben tároltat, ezzel felülírunk néhányat
      static::$view_data = array_merge(static::$view_data, $cached_view_data);
    } else {
      // Generálás idejét beállítjuk
      static::$view_data['_generated'] = time();
      // View beolvasás
      $view_content = $this->fetch_view($view_file);
      // Layoutnak átadjuk a változókat, benne a view tartalommal
      static::$view_data['_view_content'] = $view_content;
    }

    // Layout beolvasás, átadott view tartalommal
    // az egész kiírása
    $view_content = $this->fetch_simple($layout_file);

    // Ha blokkolva van a cache írás, akkor töröljük (mert megírta fentebb)
    // sajna nem tudjuk előbb, csak a fetch simple-nél, aminek viszont
    // nem tudjuk paraméterként átadni. na, így.
    if (@static::$view_data['_block_caching'] === true) {
      $this->Cache->delete($this->cache_name());
    }

    echo $view_content;
  }


  /**
   *
   * Layout és Element, vagy más fetch,
   * minden extra logika nélkül;
   * Az $erase_options = true esetén az elementeknél használt opciós tömböt
   * törli render után, így az nem öröklődik a sorban következő elementeknek
   * @todo kellene, hogy legyen ennek a kikerülése, csak ez asszem generikus
   * szervezési probléma most.
   *
   * @param $view_file
   * @param array $erase_options
   * @return string
   */
  public function fetch_simple($view_file, $erase_options = false) {
    $app = $this;
    ob_start();

    if (is_file($view_file)) {
      extract(static::$view_data);
      require($view_file);

      if ($erase_options && isset(static::$view_data['options'])) {
        unset(static::$view_data['options']);
      }
    }

    return ob_get_clean();
  }


  /**
   *
   * Oldalak view-inak feccselése
   * 404 és cache logikával
   *
   * @param $view_file
   * @param bool $page
   * @return string
   */
  public function fetch_view($view_file) {
    // Ez itt nagyon fontos
    $app = $this;
    ob_start();

    if (!is_file($view_file)) {
      // 404, ha nincs a view
      static::$view_data['_title'] = 'Az oldal nem található';
      static::$view_data['_sidemenu'] = false;
      http_response_code(404);
      $view_file = $this->error_4xx . '.php';
    }

    extract(static::$view_data);

    require($view_file);


    /**
     * Cache írás, ha tárolandó, és nincs meg a cache fájl,
     * vagy épp most kell újra letárolni.
     */
    if ($expiry = $this->have_to_cache()) {
      $cache_vars = [];
      foreach (APP['cache']['view_vars'] as $var) {
        $cache_vars[$var] = @static::$view_data[$var];
      }
      $cache_vars['_view_content'] = ob_get_contents();
      $cache_vars['_generated'] = time();
      $this->Cache->set($this->cache_name(), $cache_vars, $expiry);
    }

    return ob_get_clean();
  }



  /**
   * Belépett user ellenőrzések
   */
  private function user_check() {
    if (static::$_user) {

      // Nem elfogadott új szabályzatok
      if (static::$_user['disclaimer'] == 0
        && !in_array(static::$_params->here, [
          '/oldalak/jogi-nyilatkozat',
          '/oldalak/adatkezelesi-szabalyzat',
          '/oldalak/mukodesi-elvek',
          '/oldalak/kapcsolat',
          '/tagsag/szabalyzatok-elfogadasa',
          '/tagsag/profil-torlese',
          '/tagsag/kilepes'
        ])
      ) {
        $this->redirect('/tagsag/szabalyzatok-elfogadasa', ['Kérjük, fogadd el az új szabályzatokat az oldal böngészéséhez.', 'info']);
      }
    }
  }


  /**
   *
   * User tiny_settings-t ad vissza
   * ha jön key, azt, egyébként a teljes tömböt
   *
   * @param bool $key
   * @return array|mixed
   */
  public function ts($key = false) {
    $settings = [];
    if (static::$_user) {
      $settings = _json_decode(static::$_user['tiny_settings']);
      if ($key) {
        return @$settings[$key];
      }
    }
    return $settings;
  }


  /**
   * Ha nincs user, kirakjuk
   * Belépés nélkül nem elérhető action-ökre
   * és teljes controllerekre használjuk.
   */
  public function users_only($type = 'basic') {
    $auth = false;

    switch ($type) {

      case 'basic':
        $auth = static::$_user ? true : false;
        $message = texts('jelentkezz_be');
        break;

      case 'headitor':
        $auth = @static::$_user['headitor'] == 1 || @static::$_user['admin'] == 1
          ? true : false;
        $message = texts('jogosultsagi_hiba');
        break;

      case 'admin':
        $auth = @static::$_user['admin'] == 1 ? true : false;
        $message = texts('jogosultsagi_hiba');
        break;

      default:
        $auth = false;
        break;
    }

    if (!$auth) {
      $this->redirect('/tagsag/belepes?hopp=' . static::$_params->here, [
        $message,
        'warning'
      ]);
    }
  }


  /**
   *
   * View cache elnevezés
   *
   * @param string $path
   * @return string
   */
  public function cache_name($path = '') {
    $name_end = static::$_params->id != '' ? '-' . static::$_params->id : '';
    $path = $path == ''
      ? static::$_params->controller . '-' . static::$_params->action . $name_end : $path;
    return APP['cache']['view_prefix'] . $path;
  }


  /**
   *
   * URL-ben kapott ?flash= változóból session flash-t csinál és
   * redirecteli a látogatót az url var nélküli útvonalra
   */
  private function redirect_flash() {
    if (@static::$_params->query['flash'] != '') {
      $json = urldecode(html_entity_decode(static::$_params->query['flash']));
      $array = json_decode($json, true);
      $this->redirect(static::$_params->path, $array);
    }
  }


  private function inits() {
    if (!$this->Request->is('ajax')) {
      $visited_pages = (int)$this->Session->get('visited_pages') + 1;
      $this->Session->set('visited_pages', $visited_pages);
    }
  }


  private function old_things() {
    // Régi Cake és GA követő kukik törlése, ha még vannak
    // Ez csak akkor fog futni, ha beüzemelődik a domain itt, mert
    // egyébként nincs jogunk a törléshez.
    if (isset($_COOKIE['Kozterkep']) || isset($_COOKIE['_ga'])) {
      foreach ([
        'CakeCookie[autoLogin]',
        'CakeCookie[search_artpiece_ids]',
        'CakeCookie[visitor]',
        'PHPSESSID',
        'Kozterkep',
        '__unam',
        '_ga',
        '_gid',
      ] as $cookie_name) {
        unset($_COOKIE[$cookie_name]);
        setcookie($cookie_name, null, -1, '/');
      }
    }

    // Régi tagi fájlok linkjei; ezt ki kell találni
    if (static::$_params->controller == 'tagok'
      && static::$_params->action != '') {
      $p = explode('.', static::$_params->action);
      $file = $this->DB->find_by_old_filename('files', $p[0]);
      if ($file) {
        $this->redirect('/mappak/megtekintes/' . $file['folder_id'] . '#vetito=' . $file['id']);
      }
    }

    // Szoborlapos(!!) fotó URL-ek, csak műlap ID következtethető már ki
    // => műlapra dobjuk, mert sztem régi képlopók ezek
    if (static::$_params->controller == 'szobrok'
      && strpos(static::$_params->action, 'fotok_') !== false) {
      $p = explode('_', static::$_params->id);
      $this->redirect('/' . @$p[0]);
    }

    // Szoborlapos blog
    if (static::$_params->controller == 'community'
      && static::$_params->action == 'blogoszfera') {
      $p = explode('_', static::$_params->id);
      $this->redirect('/blogok/megtekintes/' . @$p[0]) . '/' . @$p[1];
    }

    // Régi köztérképes keresések
    if (static::$_params->controller == 'artpieces'
      && _contains(static::$_params->here, [
        'text=',
        'city=',
        'artist=',
        'city_id=',
        'artist_id=',
      ])) {
      $vars = static::$_params->query;
      $var_names = [
        'text' => 'kulcsszo',
        'city' => 'hely',
        'artist' => 'alkoto',
        'city_id' => 'hely_az',
        'artist_id' => 'alkoto_az',
      ];
      $new_query = [];
      foreach ($vars as $var => $value) {
        if (isset($var_names[$var])) {
          $new_query[$var_names[$var]] = $value;
        }
      }
      $this->redirect('/kereses?' . http_build_query($new_query));
    }

    // Régi szoborlapos (...) keresés
    if (static::$_params->controller == 'search'
      && static::$_params->action == 'egyszeru_kereses'
      && strpos(static::$_params->here, 'keresoszo=') !== false) {
      $vars = static::$_params->query;
      $var_names = [
        'keresoszo' => 'kulcsszo',
        'telepules' => 'hely',
        'alkoto' => 'alkoto',
      ];
      $new_query = [];
      foreach ($vars as $var => $value) {
        if (isset($var_names[$var])) {
          $new_query[$var_names[$var]] = $value;
        }
      }
      $this->redirect('/kereses?' . http_build_query($new_query));
    }

    // Redirektek
    if (isset(static::$_params->query['regi-re'])) {
      $url = str_replace('?regi-re', '', static::$_params->here);
      $this->redirect($url, ['<strong>A kért aloldal új változatára irányítottunk.</strong> A Köztérkép megújult, ezért ez az oldal is új webcímen érhető el.', 'info']);
    }
  }
}