<?php
namespace Kozterkep;

class MapublicLogic {

  public function __construct () {

  }

  public function auth($allowed_domains) {
    $allowed_domain = $allowed_request = false;

    $origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';

    if (in_array($origin, $allowed_domains)) {
      $allowed_domain = true;
    }

    if (isset($_SERVER['HTTP_SESSIONTOKEN'])) {
      foreach (C_ALLOWED_API_KEYS as $user => $pass) {
        $sha1 = sha1($user . ':' . $pass . ':' . date('Y-m-d'));
        if ($sha1 == $_SERVER['HTTP_SESSIONTOKEN']) {
          $allowed_request = true;
          break;
        }
      }
    }

    return $allowed_domain && $allowed_request;
  }

  public function checkParams($query, $required_params = []) {
    foreach ($required_params as $param) {
      if (!array_key_exists($param, $query)) {
        return false;
      }
    }

    return true;
  }

}