<?php
namespace Kozterkep;

class SessionComponent {

  private $app_config;

  public function __construct($app_config) {

    $this->app_config = $app_config;

    if (session_status() == PHP_SESSION_NONE) {
      ini_set('session.gc_maxlifetime', $this->app_config['sessions']['lifetime']);
      ini_set('session.cookie_lifetime', $this->app_config['sessions']['lifetime']);
      ini_set('session.hash_function', 'whirlpool');
      ini_set('session.cookie_secure', true);
      ini_set('session.cookie_httponly', true);
      ini_set('session.cookie_samesite', 'strict');
      session_name($this->app_config['sessions']['cookie_name']);
      session_start();
    }

  }


  /**
   *
   * SET
   *
   * @param $key
   * @param $value
   */
  public function set($key, $value) {
    $_SESSION[$key] = $value;
  }


  /**
   *
   * GET, de beállítja, ha kell
   *
   * @param $key
   * @param bool $set_if_not
   * @return bool
   */
  public function get($key, $set_if_not = false) {
    $value = isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    if (!$value && $set_if_not) {
      $this->set($key, $set_if_not);
      $value = $set_if_not;
    }
    return $value;
  }


  /**
   *
   * DELETE
   *
   * @param $key
   * @return bool
   */
  public function delete($key) {
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
      return true;
    } else {
      return false;
    }
  }

  /**
   * MINDENT töröl, kapcsolódó cookie-t is!
   */
  public function delete_all() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
      );
    }

    session_destroy();
  }


  /**
   *
   * Flash message beállítás
   *
   * @param $message
   * @param string $type
   * @param string $format
   */
  public function set_message($message, $type = 'info', $format = '', $remove_after = 0) {
    if (is_array($message)) {
      $message = '<ul class="m-0"><li>' . implode('</li><li>', $message) . '</li></ul>';
    }
    $messages = $this->get($this->app_config['sessions']['message_name']);
    if (is_array($messages)) {
      $messages[] = array($message, $type, $format, $remove_after);
    } else {
      $messages = array(0 => array($message, $type, $format, $remove_after));
    }
    $this->set($this->app_config['sessions']['message_name'], $messages);
  }


  /**
   *
   * Flash message lekérdezés
   *
   * @return string
   */
  public function get_messages() {
    $string = '[]'; // Nincs, ez megy vissza; JS dolgozza fel
    $messages = $this->get($this->app_config['sessions']['message_name']);

    if (is_array($messages) && count($messages) > 0) {
      $string = json_encode($messages);
      $this->delete($this->app_config['sessions']['message_name']);
    }

    return $string;
  }

}
