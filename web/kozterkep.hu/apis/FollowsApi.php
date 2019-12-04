<?php

class FollowsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

  }

  public function post() {

  }

  public function put() {

  }


  public function info() {
    $results = [];

    if (@count(@$this->data['data']) > 0) {
      foreach ($this->data['data'] as $follow) {
        $model = $follow[0];
        $model_id = is_numeric($follow[1]) ? (int)$follow[1] : $follow[1];
        $found = $this->Mongo->first('users', [
          'user_id' => static::$user['id'],
          'follow_' . $model => $model_id
        ]);

        if ($found) {
          $results[] = [$model, $model_id];
        }
      }
    }

    $this->send($results);
  }



  /**
   * Bekövetés, kikövetés
   */
  public function toggle() {

    // Követés
    $m = $this->data['model'];
    $m_i = is_numeric($this->data['model_id'])
      ? (int)$this->data['model_id'] : $this->data['model_id'];

    // Megnézzük, létezik-e a model/id és ha igen, töröljük
    // ha nem, hozzáadjuk
    $userthings = $this->Mongo->first('users', [
      'user_id' => static::$user['id']
    ]);

    if (!is_array(@$userthings['follow_' . $m])) {
      $userthings['follow_' . $m] = [];
    }

    if (!in_array($m_i, $userthings['follow_' . $m])) {
      // Follow ON
      $follow = 1;
      $userthings['follow_' . $m] = array_merge(
        $userthings['follow_' . $m],
        [$m_i]
      );

      if ($m == 'users') {
        $this->Notifications->create($m_i, static::$user['name'] . ' elkezdett követni', 'Nézd meg új követőd profilját!', [
          'link' => '/kozosseg/profil/' . static::$user['link'],
          'type' => 'others',
        ]);
      }

    } else {
      // Follow OFF
      $follow = 0;
      $key = array_search($m_i, $userthings['follow_' . $m]);
      unset($userthings['follow_' . $m][$key]);
    }

    $userthings['follow_' . $m] = (array)$userthings['follow_' . $m];

    $this->Mongo->update('users', $userthings, ['_id' => $userthings['id']]);

    // Itt küldünk infót, nem kell resession
    $this->send(['success' => $follow]);
  }

}