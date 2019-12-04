<?php
/**
 *
 * Legfontosabb, mindenhonnan hívható szuperfüggfények.
 *
 */


/**
 *
 * Ez is kell
 *
 * @return string
 * @throws Exception
 */
function uid() {
  return uniqid(bin2hex(random_bytes(16)));
}


/**
 *
 * Igen, pontosan
 *
 * @param string $class
 * @param string $text
 * @return string
 */
function _loading($class = 'text-muted', $text = '') {
  $text = $text != '' ? '<span class="text-muted ml-2">' . $text . '</span>' : '';
  return '<span class="far fa-compass fa-spin ' . $class . '"></span>' . $text;
}


/**
 *
 * Debug
 * ha a DEBUG_LEVEL > 0
 *
 * @param $thing
 * @param bool $detailed: részletes
 * @param bool $forced_debug: DEBUG_LEVEL-től függetlenül írunk
 * @return bool
 */
function debug($thing, $forced_debug = false) {
  $backtrace = debug_backtrace();

  if (php_sapi_name() === 'cli') {
    var_dump($thing);
    return;
  }

  if (CORE['DEBUG_LEVEL'] || $forced_debug) {
    echo '<html><head><meta charset="utf-8"></head><body>';
    echo '<pre style="border: 2px solid #ccc; padding: 15px; border-radius: 3px;">';
    var_dump($thing);
    if (CORE['DEBUG_LEVEL'] > 1) {
      echo '<hr />';
      var_dump($backtrace);
    }
    echo '</pre></body></html>';
  }
}

// Na azért már! ;]
function _d($thing) {
  return debug($thing);
}


/**
 * Bármikor bevethető, ha só-megállító hibák vannak.
 * @param $message
 */
function mydie($message, $subinfo = true) {
  echo '<html><head><meta charset="utf-8"></head><body>';
  echo '<div style="font-family: consolas;"><h3>' . $message . '</h3>';
  echo $subinfo ? '<span style="color:#777;">Ha úgy érzed, hogy itt nem ennek kellene lennie, jelezd a hello@kozterkep.hu címen.</span>' : '';
  echo '</div>';
  echo '</body></html>';
  exit;
}


/**
 *
 * Konstans szövegek behelyettesített
 * változókkal akár.
 * A szövegek jöhetnek:
 *  a.) könyvtárakból (1 szöveg = 1 fájl), vagy
 *  b.) a texts.php-ből, ahol simán key-value párokat
 * vezetek rövid formázatlan szövegeknek.
 *  c.) $text_passed_to_merge = true esetén a szöveget adtuk ét,
 * változó becserélés céljából
 *
 * @param $key
 * @param array $vars
 * @param $text_passed_to_merge
 * @return bool|mixed|string
 */
function texts ($key, $vars = [], $text_passed_to_merge = false) {
  $s = '';

  if ($text_passed_to_merge) {
    $s = $key;
  } elseif (strpos($key, '/') !== false) {
    $path = CORE['PATHS']['DATA'] . DS . 'texts' . DS . str_replace('/', DS, $key) . '.html';
    if (is_readable($path)) {
      $s = file_get_contents($path);
    } else {
      mydie('Kritikus fájl kiolvasási hiba.');
    }
  } else {
    $texts = include CORE['PATHS']['DATA'] . DS . 'texts' . DS . 'texts.php';
    $s = isset($texts[$key]) ? $texts[$key] : 'Upsz!';
  }

  if (count($vars) > 0) {
    foreach ($vars as $k => $v) {
      $s = str_replace('{' . $k . '}', $v, $s);
    }
  }

  return $s;
}



/**
 *
 * Dátum akármiből
 *
 * @param $time
 * @param string $format
 * @return false|string
 */
function _date($time, $format = 'Y.m.d.') {
  if (strpos($time, '-') !== false || !is_integer($time)) {
    $timestamp = strtotime($time);
  } else {
    $timestamp = $time;
  }
  return date($format, $timestamp);
}



/**
 *
 * Bedobunk valami értelmezhetetlen dolgot, és kijön
 * egy dátum. Ha nincs hónap, csak évet, ha nincs nap, csak hónapig mutatjuk.
 *
 * @param $string
 * @return mixed
 */
function _lazydate($string) {
  $date = '';
  if (strpos($string, '-') !== false) {
    // Valami értelmezhetőnek TŰNŐ dátumot kaptunk
    $p = explode('-', $string);
    $date .= @$p[0] > 0 ? (int)$p[0] : '';
    if (@(int)$p[1] > 0) {
      $month = (int)$p[1] > 9 ? $p[1] : '0' . (int)$p[1];
      $date .= '.' . $month . '.';
    }
    if (isset($month) && @(int)$p[2] > 0) {
      $day = (int)$p[2] > 9 ? $p[2] : '0' . (int)$p[2];
      $date .= $day . '.';
    }
  } elseif (!is_numeric($string)) {
    // Talán időszöveget kaptunk
    $timestamp = strtotime($string);
    if ($timestamp > 0) {
      $date = date('Y.m.d.', $timestamp);
    }
  } elseif ($string > 0 && $string < date('Y')) {
    // Évet kaptunk
    return (int)$string;
  } elseif ($string > 0) {
    // Reméljük értelmezhető unix időt kaptunk
    $date = date('Y.m.d.', $string);
  }
  return $date;
}


/**
 *
 * Mindenféle kötőjeles dátumból
 * új kötőjeleset csinál
 *
 * @param $date_string - valamilyen dátum formátum
 * @param string $what - mit kérek vissza
 * @return mixed|string
 */
function _cdate($date_string, $what = 'full') {
  $full_date = '';
  $date = [];
  $time_string = '';
  if ($date_string == '') {
    $date['y'] = '0000';
    $date['m'] = 0;
    $date['d'] = 0;
  } else {
    if (strpos($date_string, ' ') !== false) {
      $dp = explode(' ', $date_string);
      $date_string = $dp[0];
      $time_string = $dp[1];
    }
    $p = explode('-', $date_string);
    // Full forma esetén az év csak így jó 0050-0-0
    // (ez i.sz. 50); máshogy nem lehet rendezni
    if ($what == 'full') {
      $y = $p[0] == '' ? '0000' : (int)$p[0];
      if ($y > 99 && $y < 1000) {
        $date['y'] = '0' . $y;
      } elseif ($y > 0 && $y < 100) {
        $date['y'] = '00' . $y;
      } else {
        $date['y'] = $y;
      }
    } else {
      $date['y'] = $p[0] == '' || (int)$p[0] == 0 ? '0000' : (int)$p[0];
    }
    $date['m'] = $p[1] == '' ? 0 : (int)$p[1];
    $date['d'] = $p[2] == '' ? 0 : (int)$p[2];
  }
  $full_date = implode('-', $date);

  if ($what == 'full') {
    $s = $full_date;
    $s .= $time_string != '' ? ' ' . $time_string : '';
    return $s;
  } else {
    return @$date[$what];
  }
}


/**
 *
 * Saját idő kiírás. Formátumok:
 *  - ago: épp most ... perce, ... órája, ... napja, 7 nap után dátum óra perc
 *  - ''
 *
 * Beteszi a pontos timestamp-et ia- tagbe, hogy
 * a JS idő fgv frissíthesse.
 *
 * @param $timestamp
 * @param string $format
 * @return false|string
 */
function _time($timestamp, $options = [], $only_time = false) {
  $no_md = false;

  // Dátum jött
  if (strpos($timestamp, '-') !== false) {
    $date = _cdate($timestamp);
    $timestamp = strtotime($date);
  }

  if (is_string($options)) {
    $options = ['format' => $options];
  }

  $options = (array)$options + [
    'ago' => false,
    'privacy' => false,
    'format' => 'Y.m.d. H:i',
    'ifonly' => true,
  ];

  if (isset($date)) {
    if (substr($date, -4) == '-0-0' && $options['format'] == 'Y.m.d.') {
      $options['format'] = 'Y';
      $date = substr($date, 0, 4) . '-01-01';
      $timestamp = strtotime($date);
    } elseif (substr($date, -2) == '-0' && $options['format'] == 'Y.m.d.') {
      $options['format'] = 'Y.m.';
      $date = rtrim($date, '-0') . '-01';
      $timestamp = strtotime($date);
    }

    if (substr($date, 0, 4) < 1970) {
      // Formátumtól függően adjuk vissza.
      if ($options['format'] == 'Y') {
        return substr($date, 0, 4);
      } elseif ($options['format'] == 'Y.m.') {
        return str_replace('-', '.', substr($date, 0, 8));
      } else{
        return str_replace('-', '.', $date);
      }
    }
  }

  if ($options['ifonly'] && in_array($timestamp, [0, ''])) {
    return is_string($options['ifonly']) ? $options['ifonly'] : '-';
  }

  if ($options['ago'] && !$options['privacy']) {
    if ($timestamp > strtotime('-30 days')) {
      // igazi ago
      switch (true) {
        case $timestamp > strtotime('-60 seconds'):
          $ago = 'épp most';
          break;
        case $timestamp > strtotime('-60 minutes'):
          $ago = floor((time() - $timestamp) / 60) . ' perce';
          break;
        case $timestamp > strtotime('-40 hours'):
          $ago = floor((time() - $timestamp) / (60 * 60)) . ' órája';
          break;
        default:
          $ago = ceil((time() - $timestamp) / (60 * 60 * 24)) . ' napja';
          break;
      }
      $t = '<span ia-timestamp="' . $timestamp . '">' . $ago . '</span>';
    } else {
      // 7 napnál korábbi, dátumóraperc
      // de év csak ha tavalyi
      $t = $timestamp > strtotime(date('Y') . '-01-01')
        ? date('m.d. H:i', $timestamp) : date('y.m.d. H:i', $timestamp);
    }
  } elseif ($options['ago'] && $options['privacy']) {
      switch (true) {
        case $timestamp > strtotime('today 00:00'):
          $ago = 'ma';
          break;
        case $timestamp > strtotime('-7 days'):
          $ago = 'a napokban';
          break;
        case $timestamp > strtotime('-30 days'):
          $ago = 'az elmúlt hetekben';
          break;
        case $timestamp > strtotime('-6 months'):
          $ago = 'pár hónapja';
          break;
        case $timestamp > strtotime('-365 days'):
          $ago = 'egy éven belül';
          break;
        default:
          $ago = 'több, mint ' . floor((time() - $timestamp)/(365*24*60*60)) . ' éve';
          break;
      }
      $t = '<span>' . $ago . '</span>';
  } else {
    $t = date($options['format'], $timestamp);
  }

  if (!$options['privacy']) {
    $title = ' title="' . date('Y.m.d. H:i:s', $timestamp) . '"';
  } else {
    $title = '';
  }

  // Az induláskorüli üres óráim...
  if (strpos($options['format'], 'H:i') !== false
    && $timestamp < strtotime('2009-01-01')
    && date('H:i', $timestamp) == '00:00') {
    $t = rtrim($t, ' 00:00');
  }

  return $only_time ? $t : '<span class="text-nowrap"' . $title . '>' . $t . '</span>';
}


// Mennyiségek, dolgok, stb
function _q($s, $type, $options = []) {
  switch ($type) {
    case 'mb':
      $s = round($s / (1024*1024),2) . 'MB';
      break;
  }

  return $s;
}

/**
 *
 * Json -> array csináló. Ha nem jsont kaptunk,
 * a forced = true esetén akkor is üres array lesz belőle
 *
 * @param $json
 * @param bool $forced
 * @return array|mixed|string
 */
function _json_decode($json, $forced = true) {
  if ($forced && ((is_string($json) && $json == '') || !$json)) {
    $json = [];
  }
  return is_array($json) ? $json : json_decode($json, true);
}

function _json_encode($array = [], $urlencode = false, $numeric_check = true) {
  if (!$numeric_check) {
    $array = array_map('to_string', $array);
    $num = false;
  } else {
    $num = JSON_NUMERIC_CHECK;
  }
  $json = is_array($array) ? json_encode($array, $num) : '[]';
  if ($urlencode) {
    $json = urlencode($json);
  }
  return $json;
}

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


/**
 *
 * A régóta várt tömbös string tartalmaz-e stringet vizsgáló!! :)
 *
 * @param $haystack
 * @param array $array
 * @return bool
 */
function _contains($haystack, $needles, $exact = false) {
  if (is_array($needles) && count($needles) > 0) {
    foreach ($needles as $needle) {
      if ((!$exact && strpos($haystack, $needle) !== false)
        || ($exact && $haystack == $needle)) {
        return true;
      }
    }
  } elseif (strpos($haystack, $needles) !== false) {
    return true;
  }
  return false;
}


/**
 *
 * Számok, számok
 * @todo: itt még significant is kell!
 *
 * @param $number
 * @param int $decimals
 * @return string
 */
function _n($number, $decimals = 0) {
  return '<span class="small-spaces text-nowrap">' . number_format($number, $decimals, ',', ' ') . '</span>';
}


/**
 *
 * Egy tömb egyes kulcsait törli
 * ha a difference == true, akkor, hmm..
 * @todo, mi ez a diff? nem is használom
 *
 * @param array $array
 * @param array $keys
 * @param bool $difference
 * @return array
 */
function _unset($array = [], $keys = [], $difference = false) {
  // Egyet kaptunk
  if (!is_array($keys)) {
    $keys = [$keys];
  }
  if ($difference) {
    $array_ = [];
    foreach ($keys as $key) {
      $array_[$key] = $array[$key];
    }
    $array = $array_;
  } else {
    $array = array_diff_key($array, array_flip($keys));
  }
  return $array;
}


// Ez azért, mert a string típusú integereket az is_int nem figyeli
// de a mongo miatt pont ez kell
function _is_int($input) {
  return ctype_digit(strval($input));
}
function _is_float($input) {
  if (!is_scalar($input)) {
    return false;
  }

  $type = gettype($input);

  if ($type === "float") {
    return true;
  } else {
    return preg_match("/^\\d+\\.\\d+$/", $input) === 1;
  }
}


/**
 *
 * Olvasni tud MC-ből
 *
 * @param $key
 * @return mixed
 */
function _mc($key) {
  $mc = new \Memcached(); // PHP beépített memcache class
  $mc->addServer(C_MEMCACHE['host'], C_MEMCACHE['port']);
  $value = $mc->get(C_MEMCACHE['prefix'] . $key);
  return $value;
}

/**
 *
 * Tábla ID kiolvasás
 *
 * @param $name
 * @param $id
 * @return mixed
 */
function _mct($name, $id) {
  return _mc('tables.' . $name . '.' . $id);
}



/**
 *
 * A számos entitiket is visszanyomja UTF-8-ba
 *
 * A preg_replace.. innen: http://php.net/manual/en/function.html-entity-decode.php
 * @param $string
 * @return string
 */
function _html_entity_decode($string) {
  $output = preg_replace_callback("/(&#[0-9]+;)/", function($m) {
    return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
  }, $string);

  return html_entity_decode($output);
}



/**
 *
 * Tömb mező érték alapján rendezünk
 * asc vagy desc
 *
 * @param $array
 * @param $field
 * @param string $direction
 * @return mixed
 */
function _sort($array, $field, $direction = 'asc') {
  if ($direction == 'asc') {
    usort($array, function ($a, $b) use($field) {
      return $a[$field] <=> $b[$field];
    });
  } else {
    usort($array, function ($a, $b) use($field) {
      return $b[$field] <=> $a[$field];
    });
  }

  return $array;
}



/**
 *
 * az ív béla probléma megoldása is itt van
 *
 * a(z) helyett
 * éljenek a mogyorók
 * @todo számokra nem jó, mert oda regexp kell
 * pl az 1917. malac, de a 19174 kukac,
 * erről nem is beszélve: az 52 0000. jeti.
 *
 * @param $string
 * @return string
 */
function _z($string, $apostrofe = false) {
  $first_char = mb_substr($string, 0, 1);

  if (is_numeric($first_char)) {
    $s = '';
    if (strpos($string, '1.') === 0
      || strpos($string, '1 ') === 0
      || strpos($string, '5.') === 0
      || strpos($string, '1000') === 0) { // hjajj, ez hülyeség; számokkal nagyon bonyi lesz
      $s = 'z';
    }
  } else {
    if (strpos(strtolower($string), 'I.') === 0
      || strpos(strtolower($string), 'V.') === 0) {
      $s = 'z';
    } elseif (strpos(strtolower($string), 'II.') === 0
      || strpos(strtolower($string), 'III.') === 0
      || strpos(strtolower($string), 'IV.') === 0
      || strpos(strtolower($string), 'IX.') === 0) {
      $s = '';
    } elseif (preg_match_all('/[aáeéiíoóöőuúüű]/i', strtolower($first_char), $matches) > 0) {
      $s = 'z';
    } else {
      $s = '';
    }
  }

  return $apostrofe ? $s . ' "' . $string . '"' : $s . ' ' . $string;
}


/**
 *
 * array_map, array_walk kéri így
 *
 * @param $value
 * @return string
 */
function to_string($value) {
  return (string)$value;
}



/**
 *
 * Tömbben deklarált search => pattern cserélő
 *
 * @param array $patterns
 * @param $string
 * @return mixed
 */
function _replace($patterns = [], $string) {
  foreach ($patterns as $search => $replace) {
    $string = str_replace($search, $replace, $string);
  }
  return $string;
}