<?php
class WeeklyJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   *
   * Mindenféle dolgok, amik hetente futnak.
   * Törlések, takarítások, visszaállítások stb.
   *
   * @return bool
   */
  public function things () {


    /**
     * Heti kiemelt szerkesztőnk
     */
    $users = $this->DB->find('users', [
      'conditions' => [
        'highlighted <' => strtotime('-52 weeks'),
        'artpiece_count >' => 29,
        'active' => 1,
        'blocked' => 0,
        'harakiri' => 0,
      ],
      'order' => 'RAND()',
      'limit' => 1,
    ]);

    if (count($users) == 1) {
      $user = $users[0];

      $this->DB->update('users', ['highlighted' => time()], $user['id']);

      $alert_settings = _json_decode($user['alert_settings']);
      if (@$alert_settings['work'] == 1) {
        // Email, ha kért ilyet és van műlapja
        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'user_id' => $user['id'],
            'subject' => 'Gratulálunk! :)',
            'body' => texts('emails/highlighted_user', [
              'name' => $user['name'],
              'artpiece_count' => $user['artpiece_count'],
            ])
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      } else {
        $this->Notifications->create($user['id'], 'Gratula :)', 'Ezen a héten te vagy a Köztérkép kiemelt szerkesztője!');
      }
    }


    // Heti műlapszám nullázása
    $this->DB->update('users', [
      'weekly_artpieces' => 0,
    ], [
      'weekly_artpieces >' => 0,
    ]);



    // Megvan.
    return true;
  }
}