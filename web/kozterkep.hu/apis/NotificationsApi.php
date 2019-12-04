<?php
class NotificationsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }


  public function get() {
    $filters = ['user_id' => static::$user['id']];
    if (in_array(@$this->data['unread'], [0, 1])) {
      $filters['unread'] = (int)$this->data['unread'];
    }

    $this->DB->update('users', ['last_here' => time()], ['id' => static::$user['id']]);

    $result = $this->Mongo->find_array('notifications', $filters, ['sort' => ['created' => 1], 'limit' => 100]);
    $this->send($result);
  }


  public function post() {

  }


  public function put($id) {
    $filters = [
      'user_id' => static::$user['id'],
      '_id' => $id
    ];

    $notification = $this->Mongo->first(
      'notifications',
      $filters
    );

    if (!$notification) {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    }

    // Egyelőre csak olvasásra van
    if (isset($this->data['read_toggle'])) {
      $data = [
        'unread' => $notification['unread'] == 0 ? 1 : 0,
        'read' => $notification['unread'] == 0 ? '' : time()
      ];
      $result = $this->Mongo->update('notifications', $data, $filters);
      $this->send(['success' => true]);
    }
  }


  public function read_all() {
    $result = $this->Mongo->update('notifications', [
      'unread' => 0,
      'read' => time(),
    ], [
      'unread' => 1,
      'user_id' => static::$user['id'],
    ]);
    if ($result) {
      $this->send(['success' => true]);
    } else {
      $this->send(['errors' => [texts('mentes_hiba')]]);
    }
  }

}