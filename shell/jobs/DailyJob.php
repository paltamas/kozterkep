<?php
class DailyJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   *
   * Mindenféle dolgok, amik naponta futnak.
   * Törlések, takarítások, visszaállítások stb.
   *
   * @return bool
   */
  public function things () {
    /**
     * Napi autó válasz-kapók
     * Töröljük, hogy másnap újra megkapják az autó-válaszukat. Nehogy elfelejtsék, hogy
     * szabin van a címzett ;]
     */
    $this->DB->update('users', ['auto_replied_today' => ''], ['auto_replied_today <>' => '']);


    /**
     * Legalább 90 napos olvasott értesítések törlése
     */
    $this->Mongo->delete('notifications', [
      'unread' => 0,
      'read' => ['$lt' => strtotime('-90 days')]
    ]);


    /**
     * Minden résztvevő által törölt beszélgetések törlése
     */
    $result = $this->Mongo->find_array('conversations', [
      'every_user_deleted' => 1
    ]);
    if (count($result) > 0) {
      foreach ($result as $item) {
        // Fájlok!
        if (@count(@$item['files']) > 0) {
          foreach ($item['files'] as $messages) {
            foreach ($messages as $file) {
              $this->File->delete($file[0]);
            }
          }
        }
        // Maga a dumi
        $this->Mongo->delete('conversations', ['_id' => $item['id']]);
      }
    }


    /**
     * Legalább 1 hete copied fájlok lokális törlése
     */
    /*$result = $this->DB->find('files', [
      'conditions' => [
        'copied <' => strtotime('-7 days'),
        'copied >' => strtotime('-60 days'),
      ],
      'fields' => ['folder', 'name', 'extension'],
    ]);
    if (count($result) > 0) {
      foreach ($result as $item) {
        $path = CORE['PATHS']['DATA'] . '/s3gate/'
          . $item['folder'] . '/' . $item['name'] . '.' . $item['extension'];
        if (is_file($path)) {
          unlink($path);
        }
      }
    }*/


    /**
     * Legalább 1 hete copied fotók lokális törlése
     */
    /*$result = $this->DB->find('photos', [
      'conditions' => [
        'copied >' => strtotime('-7 days'),
        'copied <' => strtotime('-60 days'),
      ],
      'fields' => ['id', 'slug', 'original_slug']
    ]);
    if (count($result) > 0) {
      foreach ($result as $item) {
        // Eredeti
        $path = CORE['PATHS']['DATA'] . '/s3gate/originals/' . $item['original_slug'] . '.jpg';
        if (is_file($path)) {
          unlink($path);
        }
        // Méretek
        for ($i=1; $i<=8; $i++) {
          $path = CORE['PATHS']['DATA'] . '/s3gate/photos/' . $item['slug'] . '_' . $i . '.jpg';
          if (is_file($path)) {
            unlink($path);
          }
        }
      }
    }*/



    /**
     * Napi nézettségek törlése
     * hétfőn a heti is
     */
    $tables = ['artpieces', 'artists', 'places', 'users', 'folders', 'posts'];
    foreach ($tables as $table) {
      $this->DB->update($table, ['view_day' => 0], ['id >' => 0]);
      // hétfő van
      if (date('N') == 1) {
        $this->DB->update($table, ['view_week' => 0], ['id >' => 0]);
      }
    }


    /**
     * X hónap után kiemelés levétele a kommentről
     */
    $highlighted_comments = $this->Mongo->find('comments', [
      'highlighted' => [
        '$lt' => strtotime('-' . sDB['limits']['comments']['highlight_months'] . ' months 00:00'),
        '$gt' => 0,
      ]
    ]);
    if (count($highlighted_comments) > 0) {
      foreach ($highlighted_comments as $highlighted_comment) {
        $this->Mongo->update('comments', [
          'highlighted' => 0,
          'highlighted_was' => (int)$highlighted_comment->highlighted, // Ha később valamire kellene
        ], ['_id' => (string)$highlighted_comment->_id]);
      }
    }



    /**
     * Legalább X hónapja be nem lépettek => álljanak át nem kezelőre
     * és kapjanak emailt róla
     */
    $users = $this->DB->find('users', [
      'conditions' => 'managing_on = 1 '
        . ' AND last_here < ' . strtotime('-' . sDB['limits']['edits']['inactive_after_months'] . ' months')
    ]);
    if (count($users) > 0) {
      foreach ($users as $user) {
        $this->DB->update('users', ['managing_on' => 0], $user['id']);

        $alert_settings = _json_decode($user['alert_settings']);
        if (@$alert_settings['work'] == 1 && $user['artpiece_count'] > 0) {
          // Email, ha kért ilyet és van műlapja
          $this->Mongo->insert('jobs', [
            'class' => 'emails',
            'action' => 'send',
            'options' => [
              'user_id' => $user['id'],
              'subject' => 'Rég láttunk...',
              'body' => texts('emails/inactive_notice', [
                'name' => $user['name'],
                'month' => sDB['limits']['edits']['inactive_after_months'],
              ])
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
        } else {
          $this->Notifications->create($user['id'], 'Inaktivitási értesítő', sDB['limits']['edits']['inactive_after_months'] . ' havi inaktivitásod miatt mostantól a műlapjaidra érkező szerkesztéseket a közösség kezeli. Ha módosítanád: Beállítások / Közös munka alatt tudod.');
        }
      }
    }

    // Megvan.
    return true;
  }
}