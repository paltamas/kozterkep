<?php
/**
 * DB kapcsolódás
 */
function _db() {
  $link = mysqli_connect('localhost', C_DB['user'], C_DB['pass'], C_DB['name']);
  mysqli_set_charset($link, C_DB['encoding']);
  return $link;
}


/**
 * Dev környezet egyedisége
 */
if (C_ENV['level'] == 'dev') {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
}


/**
 * Session indítás
 */
function _session_start() {
  if (session_status() == PHP_SESSION_NONE) {
    if (C_SESSION['base_expiry']) {
      ini_set('session.gc_maxlifetime', C_SESSION['base_expiry']);
      ini_set('session.cookie_lifetime', C_SESSION['base_expiry']);
    }
    ini_set('session.hash_function', 'whirlpool');
    ini_set('session.cookie_secure', true);
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_samesite', 'strict');
    session_name(C_SESSION['base']);
    session_start();
  }
}


/**
 *
 * Debug
 *
 * @param $thing
 * @param bool $forced_debug
 */
function debug($thing, $forced_debug = false) {
  $backtrace = debug_backtrace();

  if (php_sapi_name() === 'cli') {
    var_dump($thing);
    return;
  }

  if (C_ENV['level'] == 'dev' || $forced_debug) {
    echo '<html><head><meta charset="utf-8"></head><body>';
    echo '<pre style="border: 2px solid #ccc; padding: 15px; border-radius: 3px;">';
    var_dump($thing);
    //echo '<hr />';
    //var_dump($backtrace);
    echo '</pre></body></html>';
  }
}


/**
 * Mert nincs
 */
if(!function_exists('apache_request_headers') ) {
  function apache_request_headers() {
    $arh = array();
    $rx_http = '/\AHTTP_/';
    foreach($_SERVER as $key => $val) {
      if( preg_match($rx_http, $key) ) {
        $arh_key = preg_replace($rx_http, '', $key);
        $rx_matches = array();
        // do some nasty string manipulations to restore the original letter case
        // this should work in most cases
        $rx_matches = explode('_', $arh_key);
        if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
          foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
          $arh_key = implode('-', $rx_matches);
        }
        $arh[$arh_key] = $val;
      }
    }
    return( $arh );
  }
}


function date_convert ($day_count) {
  $date_string = date('Y-m-d', strtotime('0000-00-00 + ' . $day_count . ' days'));
  return $date_string > '1900-00-00' && $date_string < date('Y-m-d')
    ? $date_string : '0000-00-00';
}