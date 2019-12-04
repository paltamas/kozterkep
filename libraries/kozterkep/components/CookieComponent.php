<?php
namespace Kozterkep;

class CookieComponent {

  private $domain;
  private $cookie_options;

  public function __construct($app_config) {
    $this->app_config = $app_config;
    $this->cookie_options = $this->app_config['cookies'];
  }

  public function get($key) {
    if (isset($_COOKIE[$key])) {
      $value = $_COOKIE[$key];

      if (strpos($value, '|') !== false) {
        return explode('|', $value);
      }

      return $value;
    }
    return false;
  }

  public function set($key, $value, $encryption = false, $httponly = true) {
    if ($encryption) {
      $value = $this->_encrypt_values($value);
    }

    return setcookie(
      $key, $value, time() + $this->cookie_options['expiration'], '/', $this->cookie_options['domain'], $this->cookie_options['secure'], $httponly // httponly
    );
  }

  public function delete($key) {
    if (isset($_COOKIE[$key])) {
      unset($_COOKIE[$key]);
      setcookie(
        $key, false, -1, '/', $this->cookie_options['domain'], $this->cookie_options['secure']
      );
    }
    return true;
  }

  private function _encrypt_values($values, $delimiter = '|') {
    $encryted_value = '';

    if (is_array($values)) {
      $encrypteds = [];
      foreach ($values as $item) {
        $encrypteds[] = md5($this->app_config['security']['salt'] . $item);
      }
      $encryted_value = implode($delimiter, $encrypteds);
    } else {
      $encryted_value = md5($this->app_config['security']['salt'] . $values);
    }

    return $encryted_value;
  }

}
