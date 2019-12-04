<?php

class AlertsJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();

    /*
     * Ennyi percenként futunk. Legyen ugyanannyi,
     * mint amit a job intervallumnál megadsz! Hejj.
     */
    $this->interval = 30;
  }


  /**
   * Összegyűjti és egy üzenetbe pakolja az olvasatlan üzeneteket és értesítéseket
   * azoknál, akik nem jöttek az utolsó alert-küldés óta és nincsenek pauzén
   * és már aktiválták magukat az új KT-n.
   *
   * 2 percet hozzáadtam, hogy garantált legyen.
   */
  public function create() {

    $conditions = [];
    $conditions[] = 'JSON_EXTRACT(alert_settings, "$.conversations") = 1';
    foreach (sDB['notification_types'] as $key => $type) {
      $conditions[] = 'JSON_EXTRACT(alert_settings, "$.notifications_' . $key . '") = 1';
    }

    $users = $this->DB->find('users', [
      'fields' => ['id', 'name', 'email', 'alert_settings'],
      'conditions' => '(' . implode(' OR ', $conditions) . ')'
        . ' AND pause = 0 AND kt2 > 0 AND last_here < ' . strtotime('-' . $this->interval+2 . ' minutes'),
    ]);

    if (count($users) == 0) {
      return;
    }

    foreach ($users as $user) {

      $alert_settings = _json_decode($user['alert_settings']);

      $alerts = [];
      $subjectparts = [];

      if (@$alert_settings['conversations'] == 1) {
        $conversations = $this->_get_latest_conversations($user['id']);
        if (count($conversations) > 0) {
          $subjectparts[] = count($conversations) . ' új üzenet';
          $alerts = array_merge($alerts, $conversations);
        }
      }


      $notification_types = [];
      foreach (sDB['notification_types'] as $key => $type) {
        if (@$alert_settings['notifications_' . $key] == 1) {
          $notification_types[] = $key;
        }
      }
      if (count($notification_types) > 0) {
        $notifications = $this->_get_latest_notifications($user['id'], $notification_types);
        if (count($notifications) > 0) {
          $subjectparts[] = count($notifications) . ' értesítés';
          $alerts = array_merge($alerts, $notifications);
        }
      }

      // tyiha. az.
      if (count($alerts) > 0) {
        $this->_email($user, $alerts, $subjectparts);
      }
    }

    return true;
  }


  /**
   *
   * Összeszedi az intervallumon belül érkezett új üzeneteket
   *
   * @param $user_id
   * @return array
   */
  private function _get_latest_conversations($user_id) {
    $alerts = [];

    $items = $this->Mongo->find_array(
      'conversations',
      [
        'updated' => ['$gt' => strtotime('-' . $this->interval . ' minutes')],
        'users' => $user_id,
        'read' => ['$nin' => [$user_id]],
        'archived' => ['$nin' => [$user_id]],
        'trashed' => ['$nin' => [$user_id]],
        'deleted' => ['$nin' => [$user_id]],
      ],
      ['sort' => ['updated' => -1]]
    );

    if (count($items) > 0) {
      foreach ($items as $item) {
        $alerts[] = 'Új üzenet: <strong><a href="' . CORE['BASE_URL'] . '/beszelgetesek/folyam/' . $item['id'] . '">' . $item['subject'] . '</a></strong>'
          . '<br /><strong>' . end($item['messages'])['user_name'] . '</strong> @ ' . date('H:i:s', end($item['messages'])['created'])
          . '<br />' . $this->Text->format(end($item['messages'])['body']);
      }
    }

    return $alerts;
  }


  /**
   *
   * Összeszedi az adott intervallumon belüli értesítőket
   * a megadott típusok alatt
   *
   * @param $user_id
   * @param array $types
   * @return arrayű
   */
  private function _get_latest_notifications($user_id, $types = []) {
    $alerts = [];

    $items = $this->Mongo->find_array(
      'notifications',
      [
        'created' => ['$gt' => strtotime('-' . $this->interval . ' minutes')],
        'user_id' => $user_id,
        'unread' => 1,
        'type' => ['$in' => $types]
      ],
      ['sort' => ['updated' => -1]]
    );

    if (count($items) > 0) {
      foreach ($items as $item) {
        $notif_types = sDB['notification_types'];

        if (@$item['link'] != '') {
          $link = $item['link'];
        } else {
          $link = '/tagsag/ertesitesek';
        }

        $alerts[] = 'Új értesítő: <strong><a href="' . CORE['BASE_URL'] . $link . '">' . $item['title'] . '</a></strong>'
          . ' (' . date('H:i:s', $item['created']) . ')'
          . '<br />' . $this->Text->format($item['content']);
      }
    }

    return $alerts;
  }


  /**
   *
   * Kiküldi a listával az emailt
   *
   * @param $user
   * @param $alerts
   */
  private function _email($user, $alerts = [], $subjectparts = []) {
    $ui = $this->interval;
    if ($ui <= 60) {
      $interval = $ui . ' percben';
    } elseif ($ui < 1140) {
      $interval = round($ui/60) . ' órában';
    } else {
      $interval = floor($ui/1440) . ' napban';
    }

    $this->Mongo->insert('jobs', [
      'class' => 'emails',
      'action' => 'send',
      'options' => [
        'user_id' => $user['id'],
        'subject' => implode(', ', $subjectparts) . ' a Köztérképen',
        'body' => texts('emails/alerts', [
          'name' => $user['name'],
          'interval' => $interval,
          'alerts' => implode('<br /><br /><hr /><br />', $alerts),
        ])
      ],
      'created' => date('Y-m-d H:i:s'),
    ]);
  }

}