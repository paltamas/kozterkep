<?php
namespace Kozterkep;

// API
// minden1ben

/**
 *
 * @todo
 * A session üzeneteket kiiktatni, ha partner hív. Ez csak nekünk van.
 * Igazából ki kellene innen venni őket.
 *
 *
 */

// Alapok
define('C_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('DS', DIRECTORY_SEPARATOR);
require_once C_PATH . DS . 'bootstrap' . DS . 'bootstrap.php';
require_once dirname(dirname(__FILE__)) . DS . 'config' . DS . 'app_init.php';


use Kozterkep\AppBase as AppBase;

class Api extends AppBase {

  private $user_actions;

  public $class_name;
  public $action_name;
  public $action_id = false;
  public static $user = false;
  public $data;

  public $params;

  public function __construct() {
    parent::__construct(APP);

    $this->params = $this->Request->get();

    /*
     * Egyelőre csak saját domain-en ismerjük meg
     * a usereinket a session alapján.
     * Ezt kell bővíteni majd, ha a kliensek is
     * be tudják autentikálni a usereiket.
     */
    static::$user = $this->Auth->user();

    // Csak KT userrel elérhető akciók
    $this->user_actions = [
      'UsersApi' => ['get', 'put'],
      'NotificationsApi' => ['get', 'put', 'read_all'],
      'ConversationsApi' => ['get', 'post', 'put', 'empty_trash'],
      'CommentsApi' => ['get', 'post', 'latests', 'story_convert', 'artist_description_convert', 'artist_description_convert_back', 'delete', 'highlight_toggle'],
      'UpdatesApi' => ['get'],
      'FollowsApi' => ['info', 'toggle'],
      'FoldersApi' => ['put'],
      'ArtpiecesApi' => ['put', 'post', 'check', 'photos', 'photo_delete'],
      'ArtistsApi' => ['photos', 'photo_delete'],
      'GamesApi' => ['add_hug', 'add_spacecapsule'],
    ];

    // Class / Action kinyerése a hívásból
    if ($this->Request->uri_level(3)) {
      // VAN megnevezett action
      $this->class_name = ucfirst($this->Request->uri_level(2)) . 'Api';
      $this->action_name = $this->Request->uri_level(3);
      if ($this->Request->uri_level(4)) {
        $this->action_id = $this->Request->uri_level(4);
      }
    } else {
      // Nincs action (így ID-t sem adhatunk át!)
      // get, put, post action
      $this->class_name = ucfirst($this->params->action) . 'Api';
      $this->action_name = strtolower($this->Request->is());
    }

    // Ellenőrizzük az elérési jogosultságot
    $this->check();

    if ($this->Request->is() == 'GET') {
      $this->data = $this->params->query;
    } else {
      $this->data = $this->params->data;
    }
  }


  /**
   *
   * API jogosultsági ellenőrzés
   *
   * CSRF token
   */
  private function check() {
    $headers = apache_request_headers();
    if (!in_array($this->Session->get('csrf_token'), [@$headers['X-Csrf-Token'], $this->Cookie->get('KT-csrf')])
      || (isset($this->user_actions[$this->class_name]) && in_array($this->action_name, $this->user_actions[$this->class_name]) && !self::$user)) {
      http_response_code(401);
      exit;
    }
  }


  /**
   *
   * Feltétel építő
   *
   * @param array $defaults - ez mindenképp bekerül
   * @param array $allowed_keys - engedélyezett mezők konverzióval, ami validál is
   * @param array $allowed_source - innen jöhetnek engedélyezett mező => érték párok
   * @return array
   */
  public function conditions(array $defaults, array $allowed_keys, array $allowed_source) {
    $conditions = [] + $defaults;

    // POST vagy GET lehet ebben, és
    // ami engedélyezett, azt beépítjük a feltételekbe
    foreach ($allowed_source as $key => $value) {
      if (isset($allowed_keys[$key])) {
        switch ($allowed_keys[$key]) {
          case 'int':
            $conditions[$key] = (int)$value;
            break;

          case 'float':
            $conditions[$key] = (float)$value;
            break;

          case 'string':
            $conditions[$key] = (string)$value;
            break;
        }

      }
    }

    return $conditions;
  }


  /**
   *
   * Egyszerű tömb kiíró
   *
   * @param $result
   */
  public function send($result = false) {
    if (is_int($result)) {
      http_response_code($result);
      exit;
    } else {
      http_response_code(200);
    }
    if (is_array($result) && count($result) > 0) {
      echo json_encode($result);
    } else {
      echo json_encode([]);
    }
    exit;
  }
}


// Röff.
$api = new Api();

// Behúzzuk az egyes API-kat
$base_folder = CORE['PATHS']['WEB'] . DS . APP['path'] . DS . 'apis/';
$lib_dirs = array_slice(scandir($base_folder), 2);
foreach ($lib_dirs as $class_file) {
  $path = $base_folder . $class_file;
  require_once($path);
}

// Meghívjuk
$action_api = new $api->class_name();

// Mehet
if (is_callable([$action_api, $api->action_name])) {
  /**
   * Létezik és hívható a metódus, meghívjuk; ID-vel.
   */
  $action_api->{$api->action_name}($api->action_id);
} else {
  /**
   * Hibás URL formátum || nem létező / nem hívható metódus hívása
   */

  http_response_code(406);
  exit;
}