<?php
class UpdatesApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

    // Belépett, de még nem fogadta el a disclaimert
    if (static::$user['disclaimer'] == 0) {
      $this->send([]);
    }

    $filters = ['user_id' => static::$user['id']];

    $results = [];

    $results['notifications'] = $this->Mongo->find_array('notifications',
      [
        'user_id' => static::$user['id'],
        'unread' => 1,
      ],
      [
        'sort' => ['created' => 1],
        'limit' => 100
      ]
    );

    $results['conversations'] = $this->Mongo->find_array('conversations',
      [
        'users' => static::$user['id'],
        'read' => ['$ne' => static::$user['id']],
        'archived' => ['$nin' => [static::$user['id']]],
        'trashed' => ['$nin' => [static::$user['id']]],
        'deleted' => ['$nin' => [static::$user['id']]],
      ],
      [
        'sort' => ['updated' => 1],
        'limit' => 100
      ]
    );

    // Hogy itt jártunk
    $this->DB->update('users', ['last_here' => time()], ['id' => static::$user['id']]);

    $this->send($results);
  }



  public function post() {

  }

  public function put() {

  }

}