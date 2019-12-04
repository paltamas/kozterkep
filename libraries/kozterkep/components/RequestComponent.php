<?php
namespace Kozterkep;

class RequestComponent {

  private $app_config;

  public function __construct($app_config) {
    $this->Session = new SessionComponent($app_config);
    $this->app_config = $app_config;
    $this->routes = $app_config['routes'];
    //$this->get();
  }

  public function get() {
    return (object) [
      'url' => $this->url(),
      'domain' => $this->url(false),
      'here' => $this->here(),
      'path' => $this->here(true),
      'controller' => $this->controller(),
      'action' => $this->action(),
      'id' => $this->id(),
      'query' => $this->query_params(),
      'data' => $this->request_data() + $this->files(false, true),
      'data_' => $this->request_data(false) + $this->files(false, true),
      'files' => $this->files(),
      'referer' => $this->referer(),
      'user_agent' => $this->user_agent(),
      'user_ip' => $this->user_ip(),
      'is_ajax' => $this->is('ajax'),
      'is_get' => $this->is('get'),
      'is_post' => $this->is('post'),
    ];
  }

  /*
   * Hogy ne kelljen a params mindenhol
   */
  public function query() {
    return $this->query_params();
  }
  public function path() {
    return $this->here(true);
  }
  public function data() {
    return $this->request_data() + $this->files(false, true);
  }
  public function data_() {
    return $this->request_data(false) + $this->files(false, true);
  }


  public function is($what = false) {
    if (!$what) {
      return $_SERVER['REQUEST_METHOD'];
    }
    switch ($what) {
      case 'post':
      case 'get':
      case 'put':
      case 'patch':
      case 'delete':
      case 'options':
        return $_SERVER['REQUEST_METHOD'] === strtoupper($what) ?: false;
        break;

      case 'ajax':
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ?: false;
        break;

      case 'mobile':
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
          return true;
        } else {
          return false;
        }
        break;

      default:
        return true;
        break;
    }
  }

  public function url($full = true) {
    if (isset($_SERVER['HTTPS']) &&
      ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
      isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
      $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
      $protocol = 'https://';
    }
    else {
      $protocol = 'http://';
    }
    $host = $_SERVER['HTTP_HOST'];
    $uri = $full ? $_SERVER['REQUEST_URI'] : '';
    $url = $protocol . $host . $uri;
    return $url;
  }

  public function here($only_path = false) {
    $uri = $_SERVER['REQUEST_URI'];
    if ($only_path) {
      $uri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $uri);
    }
    return $uri;
  }

  public function controller() {
    if (is_numeric($this->uri_level(1))) {
      $name = 'mulapok';
    } else {
      $name = $this->uri_level(1) == '' ? $this->app_config['default_controller'] : $this->uri_level(1);
    }
    $controller_name = isset($this->routes[$name][0]) ? $this->routes[$name][0] : $name;

    return $controller_name;
  }

  public function action() {
    $c = $this->uri_level(1) == '' ? $this->app_config['default_controller'] : $this->uri_level(1);

    if (is_numeric($this->uri_level(1))) {
      $name = 'view';
    } else {
      if ($this->uri_level(2) == '') {
        $name = $this->app_config['default_index_action'];
      } elseif (is_numeric($this->uri_level(2))) {
        $name = $this->app_config['default_view_action'];
      } else {
        $name = $this->uri_level(2);
      }
    }
    $action_name = isset($this->routes[$c][1][$name]) ? $this->routes[$c][1][$name] : $name;

    return $action_name;
  }

  public function id() {
    if (is_numeric($this->uri_level(1))) {
      $id = $this->uri_level(1);
    } elseif (is_numeric($this->uri_level(2))) {
      $id = $this->uri_level(2);
    } else {
      $id = $this->uri_level(3);
    }

    return $id;
  }

  public function uri_level($level) {
    $uri_parts = explode('/', $this->here(true));
    return isset($uri_parts[$level]) ? $uri_parts[$level] : '';
  }

  private function query_params() {
    parse_str($_SERVER['QUERY_STRING'], $array);
    return $this->clean_inputs($array);
  }

  /**
   *
   * POST és PUT data feldolgozás
   * ha jön _token, akkor összevetjük a CSRF-tokennel
   * és átdobjuk a biztonsági hiba oldalra, ha kell.
   * A PUT picit viccesebb.
   *
   * @return array|mixed
   */
  private function request_data($clean = true) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      unset($_POST['submit']);
      if (isset($_POST['_token'])) {
        // CSRF ellenőrzés itt
        $token = $this->Session->get('csrf_token');
        if (!$token || $_POST['_token'] !== $token) {
          header("Location: " . $this->app_config['security']['black_hole'], false, 302);
          exit;
        }
      }

      if (isset($_POST['_redirect_hash'])) {
        $this->Session->set('_redirect_hash', $_POST['_redirect_hash']);
        unset($_POST['_redirect_hash']);
      }

      if ($clean) {
        return $this->clean_inputs($_POST);
      } else {
        return $_POST;
      }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      // Ez azért kell, különben elszállunk a fájl feltöltéskor!
      // fájl feltöltés PUT-tal így talán jobb
      ini_set('memory_limit', '500M');
      parse_str(file_get_contents("php://input"), $data);

      // Itt nem engedünk tisztítatlan küldést
      return $this->clean_inputs($data);
    } else {
      return [];
    }
  }

  public function files($key = false, $wrapped = false) {
    if (count($_FILES) == 0) {
      return [];
    }
    if ($key) {
      $response = array_key_exists($key, $_FILES) ? $_FILES[$key] : null;
    } else {
      $response = $_FILES;
    }
    return $wrapped ? ['files' => $response] : $response;
  }

  public function referer() {
    return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
  }

  //@todo: nem szép,
  // 1. array walk kell
  // 2. HTML-t be kellene engedni, ha mondják meghíváskor (végigvezetni)
  public function clean_inputs($data) {
    if (is_array($data)) {
      $clean_array = array();
      foreach ($data as $key => $value) {
        if (is_array($value)) {
          $clean_array[$key] = $this->clean_inputs($value);
        } elseif (is_numeric($value)) {
          $clean_array[$key] = $value;
        } else {
          $clean_array[$key] = trim(filter_var($value, FILTER_SANITIZE_STRING));
        }
      }
      return $clean_array;
    } else {
      return filter_var($data, FILTER_SANITIZE_STRING);
    }
  }


  /**
   * Sima IP megadás
   * @return string
   */
  public function user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
  }

  /**
   * Sima agent megadás
   * @return string
   */
  public function user_agent() {
    $default_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.0.9) Gecko/20061206 Firefox/1.5.0.9';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : $default_agent;
    return $user_agent;
  }

}
