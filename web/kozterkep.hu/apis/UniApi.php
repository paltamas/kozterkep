<?php
class UniApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }


  /**
   * Ez a legizgalmasabb API végpont, mert
   * itt _bármit_ le lehet kérni.
   *
   * Az az elv, hogy csak azt, amit egyébként is
   * elérhet publikus felületen.
   *
   * Tehát pl.:
   *  - nem belépett user ne érje el a nem publikus műlapokat
   *  - belépett user érje el az ellenőrzésre küldött sajátokat,
   * de ne érje el a nem saját szerk. alattiakat, hacsak nem headitor || admin
   *  - vannak modellek, ahol csak bizonyos mezőket adunk ki
   *
   * Tehát látszólag szimpla, de minden modellhez különféle
   * szabályok és így feltételrendszer tartozik.
   */
  public function get() {
    if (!isset($this->data['model']) || !isset($this->data['id'])) {
      $this->send([]);
    }

    $user = static::$user;
    $model = $this->data['model'];
    $id = $this->data['id'];

    // A továbbiakban használt data, mert ezekkel esetleg szűrhetünk még
    $query = _unset($this->data, ['model', 'id', '_']);

    // Műlap szerkesztések
    if ($model == 'artpiece_edits') {
      $results = $this->Mongo->first('artpiece_edits', $id);
    }

    // Műlap szavazatok
    if ($model == 'artpiece_votes') {
      $filters = ['artpiece_id' => (int)$id];
      if (@$query['edit_id'] != '') {
        $filters['edit_id'] = $query['edit_id'];
      }
      $results = $this->Mongo->find_array('artpiece_votes', $filters);
    }


    $response = $results && is_array($results) ? ['results' => $results] : ['results' => []];

    // A látogató, ha van, hogy ügyködhessen
    if (static::$user) {
      $response['user'] = [
        'id' => static::$user['id'],
        'name' => static::$user['name'],
      ];
    }

    $this->send($response);

  }

  public function post() {
    // Ezt nem akarjuk
  }

  public function put() {
    // Ezt nem akarjuk
  }

}