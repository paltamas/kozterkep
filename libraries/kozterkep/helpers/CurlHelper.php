<?php
namespace Kozterkep;

class CurlHelper {

  public function get_info($url) {
    $ch = curl_init();

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLINFO_HEADER_OUT => true,
      CURLOPT_NOBODY => true
    );
    curl_setopt_array($ch, $options);
    curl_exec($ch);

    $info = curl_getinfo($ch);
    return $info;
  }

  public function get_headers($url, $params = array()) {
    $url = $url . '?' . http_build_query($params, '', '&');
    $ch = curl_init();

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    //$response = explode(' ', $response);
    return $response;
  }

  /**
   * Performs a get request on the chosen link and the chosen parameters
   * in the array.
   *
   * @param string $url
   * @param array $params
   *
   * @return string returns the content of the given url
   */
  public static function get($url, $params = array()) {
    $url = $url . '?' . http_build_query($params, '', '&');
    $ch = curl_init();

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 60,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_AUTOREFERER => true,
      CURLOPT_SSL_VERIFYPEER => false
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

  /**
   * Performs a post request on the chosen link and the chosen parameters
   * in the array.
   *
   * @param string $url
   * @param array $fields
   *
   * @return string returns the content of the given url after post
   */
  public static function post($url, $fields = array()) {
    $ch = curl_init();

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_POSTFIELDS => $fields,
      CURLOPT_POST => true,
      CURLOPT_USERAGENT => "SMVC Agent",
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

  /**
   * Performs a put request on the chosen link and the chosen parameters
   * in the array.
   *
   * @param string $url
   * @param array $fields
   *
   * @return string with the contents of the site
   */
  public static function put($url, $fields = array()) {
    $post_field_string = http_build_query($fields);
    $ch = curl_init($url);

    $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CUSTOMREQUEST => "PUT",
      CURLOPT_POSTFIELDS => $post_field_string
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

}
