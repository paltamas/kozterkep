<?php
namespace Kozterkep;

class ArraysHelper {

  public function __construct() {
    // ..
  }

  /*
   * Beteszi a $field_array tömbbe a $needed_fields tömbben
   * megadottak közül azokat a kulcsokat,
   * amelyek a $from_array tömbben nullánál nagyobb értékkel bírnak
   */

  public function push_if_not_null($from_array, $field_array, $needed_fields) {
    foreach ($from_array as $field => $value) {
      if (in_array($field, $needed_fields) && $value > 0) {
        $field_array[] = $field;
      }
    }
    return $field_array;
  }


  /*
   * Skippelem a model nevet, mert a cake-től 2 dimenzóban jön
   */
  public function skip_cake_model($model_name, $rows) {
    if (isset($rows[0][$model_name])) {
      $new_rows = [];
      foreach ($rows as $row) {
        $new_rows[] = $row[$model_name];
      }
      return $new_rows;
    } else {
      return $rows;
    }
  }


  /*
   * A numerikus mezőket (int)-té alakítom
   * decimalra/float-ra nem OK!
   */
  public function numeric_int($array) {
    array_walk_recursive($array, function(&$value, &$key) {
      if (is_numeric($value)) {
        $value = (int) $value;
      }
    });
    return $array;
  }


  /**
   *
   * Kapott szövegből, tömbből vagy objektumból
   * visszaadjuk az összes szót egy tömbben.
   * Ha tömb és kulcsot is kapunk, akkor az elemek azon kulcsának
   * értékéből vesszük a stringeket.
   *
   * @param bool $source
   * @param bool $key
   * @return array|mixed
   */
  public function words_array($source = false, $key = false) {
    if (!$source) {
      return [];
    }
    if (is_array($source) || is_object($source)) {
      $source = is_object($source) ? (array)$source : $source;
      $words = '';
      foreach ($source as $item) {
        $words .= $key ? ' ' . $item[$key] : ' ' . $item;
      }
    } elseif (is_string($source)) {
      $words = $source;
    }
    return str_word_count($words, 1);
  }


  /**
   *
   * Multi array kulcs/érték szerinti rendezése
   *
   * @param $array
   * @param $order_by
   * @param int $direction
   * @return mixed
   */
  public function sort_by_key($array = [], $order_by, $direction = 1) {
    if (!is_array($array) || count($array) == 0) {
      return $array;
    }
    $sort_array = [];
    foreach($array as $item){
      foreach($item as $key => $value){
        if (!isset($sort_array[$key])){
          $sort_array[$key] = array();
        }
        $sort_array[$key][] = $value;
      }
    }
    $sort = $direction == 1 ? SORT_ASC : SORT_DESC;
    array_multisort($sort_array[$order_by], $sort ,$array);
    return $array;
  }


  /**
   *
   * Tömb elemei közt JSON-t is tömbbé alakít
   *
   * @param $array
   * @return mixed
   */
  public function json_to_array($array) {
    foreach ($array as $key => $value) {
      $possible_array = _json_decode($value, false);
      if (is_array($possible_array)) {
        $array[$key] = $possible_array;
      }
    }
    return $array;
  }


  /**
   *
   * kapott JSON-ból elemlistát csinál
   * a $value_array-ban található megfeleltetés szerint
   *
   * @param $json
   * @param $value_array
   * @param $value_array_field
   * @param array $options
   * @return array|string
   */
  public function json_list($json, $value_array, $value_array_field, $options = []) {
    $items = [];

    $options = (array)@$options + [
      'return_array' => false,
    ];
    $array = _json_decode($json);

    foreach ($array as $array_item) {
      if (isset($value_array[$array_item])) {
        $items[] = $value_array[$array_item][$value_array_field];
      }
    }


    // Nincs elem
    if (count($items) == 0) {
      return $options['return_array'] ? [] : '';
    }

    if ($options['return_array']) {
      return $items;
    } else {
      return implode(', ', $items);
    }
  }


  /**
   *
   * Rekurzív array_diff, kavart kulcsokkal is
   * .. nem szimmetrikus,
   * csak 1. elemeit keressük 2. tömbben
   *
   * @param $array_1 - ennek elemeit keressük
   * @param $array_2 - ebben
   * @return array
   */
  public function array_diff_recursive($array_1, $array_2) {
    $changes = [];
    foreach ($array_1 as $key => $value) {
      if (!isset($array_2[$key])) {
        $changes[$key] = $value;
        continue;
      }

      if (is_array($value)) {
        if (is_array(@$array_2[$key]) && count($value) !== count($array_2[$key])) {
          $changes[$key] = $value;
          continue;
        }

        if (count($value) == 0 && is_array(@$array_2[$key]) && count($array_2[$key]) > 0) {
          $changes[$key] = $value;
          continue;
        }

        $inside_changes = $this->array_diff_recursive($value, $array_2[$key]);
        if (count($inside_changes) > 0) {
          $changes[$key] = $value;
        }

      } elseif(trim(@htmlentities($value)) != trim(@htmlentities($array_2[$key]))) {
        $changes[$key] = $value;

      } elseif (is_string($value)) {
        if ($value == '' && is_array(@$array_2[$key]) && count($array_2[$key]) > 0) {
          $changes[$key] = $value;
          continue;
        }
      }
    }

    return $changes;
  }


  /**
   *
   * Kapott tömbből egy szimpla key=>value típusú tömböt csinál
   * pl. select-hez jó
   *
   * @param $array
   * @param $value_field
   * @param array $options
   * @return array
   */
  public function id_list($array, $value_field = 0, $options = []) {
    $options = (array)$options + [
      'sort' => false, // value szerinti sort
      'excluded_keys' => [], // ezeket a kulcsokat kihagyjuk
    ];
    $list = [];
    foreach ($array as $key => $item) {
      if (!in_array($key, $options['excluded_keys'])) {
        $list[$key] = $item[$value_field];
      }
    }
    if ($options['sort']) {
      if ($options['sort'] == 'ASC') {
        asort($list);
      }
      if ($options['sort'] == 'DESC') {
        arsort($list);
      }
    }
    return $list;
  }
}