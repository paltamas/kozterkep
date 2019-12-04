<?php
namespace Kozterkep;

class NotificationsLogic {

  private $DB;

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
  }


  /**
   *
   * Értesítő beszúrása
   * lehetne lokálisan is egy sima mongo insert, de esetleg kifelejtek valamit;
   * meg remélhetőleg ez a logika még bővül majd.
   *
   * @param $user_id
   * @param $title
   * @param string $content
   * @param array $extra_fields
   * @return bool
   */
  public function create($user_id, $title, $content = '', $extra_fields = []) {
    if ($user_id == 0 || !$user_id || $title == '') {
      return false;
    }

    if (is_array($user_id) && count($user_id) > 0) {
      foreach ($user_id as $id) {
        $user = $this->MC->t('users', $id);
        $result = $this->_insert($user, $title, $content, $extra_fields);
      }
    } else {
      $user = $this->MC->t('users', $user_id);
      $result = $this->_insert($user, $title, $content, $extra_fields);
    }

    return $result ? true : false;
  }


  /**
   *
   * A beszúrás maga
   *
   * @param $user_id
   * @param $title
   * @param string $content
   * @param array $extra_fields
   * @return array|string
   */
  private function _insert($user, $title, $content = '', $extra_fields = []) {
    $data = [
      'user_id' => $user['id'],
      'unread' => 1,
      'created' => time(),
      'title' => $title,
      'content' => $content
    ];

    // Nem kezelők ne kapjanak artpieces típusú alerteket
    if (@$extra_fields['type'] == 'artpieces') {
      if (@$user['managing_on'] == 0) {
        return true;
      }
    }

    // Játékról leiratkozottak se kapjanak értesítéseket
    if (@$extra_fields['type'] == 'games') {
      if (@$user['game_notifications_pause'] == 1) {
        return true;
      }
    }

    if (@$extra_fields['link'] != '') {
      $data['link'] = $extra_fields['link'];
    }

    if (@$extra_fields['type'] != '') {
      $data['type'] = $extra_fields['type'];
    } else {
      $data['type'] = 'others';
    }

    return $this->Mongo->insert('notifications', $data);
  }
}

