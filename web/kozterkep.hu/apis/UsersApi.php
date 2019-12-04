<?php
class UsersApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }


  public function get() {
    $conditions = [
      'active' => 1,
      'blocked' => 0,
      'harakiri' => 0,
    ];
    $result = [];

    if (@$this->data['id'] > 0) {
      $conditions['id'] = $this->data['id'];
    }

    if (@$this->data['name'] != '') {
      $conditions['LOWER(name) LIKE'] = '%' . mb_strtolower($this->data['name']) . '%';
    }

    $order = 'name ASC';

    if (in_array(@$this->data['order'], ['has_photo'])) {
      $order = 'has_photo DESC, name';
    }

    if (count($conditions) > 0) {

      $result = $this->DB->find('users', [
        'conditions' => $conditions,
        'fields' => [
          'id',
          'name',
          'link',
          'profile_photo_filename',
          "IF(profile_photo_filename = '', 0, 1) AS has_photo",
        ],
        'order' => $order,
        'debug' => false
      ]);
    }

    $this->send($result);
  }


  // Beállítások
  public function put() {
    // UI settings
    if (@$this->data['tiny_settings'] == 1) {
      $data = $this->data;

      // Fluid view toggle most
      if (isset($data['view_toggle'])) {
        $data['fluid_view'] = @_json_decode(static::$user['tiny_settings'])['fluid_view'] == 1 ? 0 : 1;
      }

      // Ezeket nem mentjük soha
      $data = _unset($data, ['tiny_settings', 'view_toggle']);

      // Beleírjuk a meglévőbe a mentendő változásokat
      $data = (array)$data + (array)(_json_decode(static::$user['tiny_settings']));

      // Mentünk
      $this->DB->update('users', [
        'tiny_settings' => $data
      ], static::$user['id']);
    }

    // Ez mindenképp kell, hogy a user érezze odaát
    $this->Auth->resession();

    $this->send([]);
  }
}