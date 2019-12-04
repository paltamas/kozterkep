<?php
class AutocompletesApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();

    $this->allowed_models = ['users', 'places', 'artists', 'artpieces'];
  }

  public function get() {
    if (in_array(@$this->data['m'], $this->allowed_models)) {
      // Javítások
      // pont után szóköz
      $this->data['v'] = preg_replace('/(?<!\.)\.(?!(\s|$|\,|\w\.))/', '. ', @$this->data['v']);
      $action_name = 'get_' . $this->data['m'];
      $list = $this->$action_name();
      $this->send($list);
    }

    $this->send([]);
  }


  private function get_artpieces() {
    $v = $this->data['v'];
    // Kihagyott ID
    $excluded_id = @$this->data['ex'] > 0 ? "AND id <> " . (int)$this->data['ex'] : '';

    $user_filter = static::$user ? "OR user_id = " . static::$user['id'] : '';

    $list = $this->DB->find('artpieces', [
      'fields' => [
        'id',
        "CONCAT(title, ' (', id, ')') AS label",
        "title AS value"
      ],
      'conditions' => "(title LIKE '%" . $v . "%'
      OR title_en LIKE '%" . $v . "%'
      OR title_alternatives LIKE '%" . $v . "%') 
      AND (status_id IN (2,5) " . $user_filter . ")" . $excluded_id,
      'limit' => 100,
      'order' => 'title ASC'
    ]);

    if (is_numeric($v)) {
      $artpiece_by_id = $this->DB->first('artpieces', $v, ['fields' => [
        'id',
        "CONCAT(title, ' (', id, ')') AS label",
        "title AS value"
      ]]);

      if ($artpiece_by_id) {
        array_unshift($list, $artpiece_by_id);
      };
    }

    return $list;
  }

  private function get_artists() {
    $list = $this->DB->find('artists', [
      'fields' => [
        'id',
        "CONCAT(name, IF(born_date <> '', CONCAT(' (', SUBSTRING(born_date,1,4), ')'), '')) AS label",
        //"name AS label",
        "name AS value"
      ],
      'conditions' => "(id=" . (int)$this->data['v'] . " "
          . " OR REPLACE(" . $this->data['f'] . ", '.', '') LIKE '%" . $this->data['v'] . "%' "
          . " OR " . $this->data['f'] . " LIKE '%" . $this->data['v'] . "%' "
          . " OR artist_name LIKE '%" . $this->data['v'] . "%' "
          . " OR alternative_names LIKE '%" . $this->data['v'] . "%' "
          . ") AND (artpiece_count > 0 OR checked = 1 "
          . " OR creator_user_id = " . static::$user['id'] . ")",
        /*[
        'OR' => [
          'id' => is_numeric($this->data['v']) ? $this->data['v'] : 0, // csak ha egész szám, akkor ID
          $this->data['f'] . ' LIKE'  =>  $this->data['v'] . '%',
          'artist_name LIKE'  =>  $this->data['v'] . '%',
          'alternative_names LIKE'  =>  $this->data['v'] . '%',
        ],
        'artpiece_count >' => 0
      ],*/
      'limit' => 100,
      'order' => 'name ASC'
    ]);

    return $list;
  }

  private function get_places() {
    $list = $this->DB->find('places', [
      'fields' => [
        'id',
        "IF(original_name = '', CONCAT(name, ' (', country_code, ')'), CONCAT(name, ' (', original_name, ', ', country_code, ')')) AS label",
        "name AS value"
      ],
      'conditions' => "(id=" . (int)$this->data['v'] . " "
        . " OR " . $this->data['f'] . " LIKE '%" . $this->data['v'] . "%' "
        . " OR original_name LIKE '%" . $this->data['v'] . "%' "
        . " OR alternative_names LIKE '%" . $this->data['v'] . "%' "
        . ") AND (artpiece_count > 0 OR checked = 1 "
        . " OR creator_user_id = " . static::$user['id'] . ")",

        /*[
        'OR' => [
          'id' => is_numeric($this->data['v']) ? $this->data['v'] : 0, // csak ha egész szám, akkor ID
          $this->data['f'] . ' LIKE'  => '%' . $this->data['v'] . '%',
          'original_name LIKE'  =>  '%' . $this->data['v'] . '%',
          'alternative_names LIKE'  =>  '%' . $this->data['v'] . '%',
        ],
        'artpiece_count >' => 0,
      ],*/
      'limit' => 100,
      'order' => 'name ASC'
    ]);

    return $list;
  }

}