<?php
class GamesApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

  }

  public function post() {

  }


  public function put() {

  }


  public function add_hug() {
    $artpiece = $this->DB->first('artpieces', [
      'id' => (int)$this->data['artpiece_id'],
      'status_id' => 5,
    ]);

    if ($artpiece && sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']][6] == 1) {
      $my_lat = $this->data['my_lat'];
      $my_lon = $this->data['my_lon'];
      $ap_lat = $artpiece['lat'];
      $ap_lon = $artpiece['lon'];

      // Biztosan elég közel vagyunk még mindig?
      $distance = $this->Location->distance($my_lat, $my_lon, $ap_lat, $ap_lon, 'm');
      if ($distance <= sDB['limits']['games']['hug_distance']) {
        // Ha nincs érintés mostanában, akkor berögzítjük
        $hug = $this->Mongo->first('artpiece_hugs', [
          'id' => $artpiece['id'],
          'user_id' => static::$user['id'],
          'created' => ['$gt' => strtotime('-' . sDB['limits']['games']['hug_days'] . ' days')]
        ]);

        if (!$hug) {
          // Érintés
          $this->Mongo->insert('artpiece_hugs', [
            'id' => $artpiece['id'],
            'user_id' => static::$user['id'],
            'created' => time(),
          ]);
          // Esemény
          $this->Events->create(7, [
            'user_id' => static::$user['id'],
            'target_user_id' => $artpiece['user_id'],
            'artpiece_id' => $artpiece['id'],
            'created' => time(),
          ]);
          // Érintő statja
          $this->DB->update('users', [
            'hug_count' => $this->Mongo->count('artpiece_hugs', ['user_id' => static::$user['id']])
          ], static::$user['id']);
          // Létrehozó értesítése
          if ($artpiece['user_id'] != static::$user['id']) {
            $this->Notifications->create($artpiece['user_id'], static::$user['name'] . ' érintett', '"' . $artpiece['title'] . '" c. műlapon szereplő alkotást ' . static::$user['name'] . ' megérintette.', [
              'link' => '/' . $artpiece['id'],
              'type' => 'games',
            ]);
          }
          // Műlap kess, az esemény miatt
          $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
          // A siker.
          $this->send(['success' => true]);
        }
      } else {
        $this->send(['too_far' => true]);
      }
    }

    $this->send([]);
  }

  public function add_spacecapsule() {

    $this->send($this->data);
    $this->send([]);
  }
}