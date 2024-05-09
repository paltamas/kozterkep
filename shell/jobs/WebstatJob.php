<?php

class WebstatJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }

  /**
   * Kiolvassa a vi > 0 && d = 0 webstatokat
   * és a vp alatt megadott táblába rögzíti az új statokat
   * és d = 1-re állítja a jobot.
   *
   * Mivel itt is integer model_id (vi) kell, ezért is
   * csak sql-re tudunk most lőni
   *
   * Ezzel léptetjük a napi és a total látogatottságot.
   */
  public function visits() {

    $results = $this->Mongo->find_array('webstat', [
      'd' => 0,
      'st' => ['$ne' => 1],
      'vi' => ['$gt' => 0]
    ], [
      'sort' => ['t' => -1],
      'limit' => 50,
    ]);

    if (count($results) > 0) {

      // started rájegyzés
      // hogy ne csússzanak össze a lassú futások - mert megteszik
      foreach ($results as $item) {
        $this->Mongo->update('webstat', ['st' => 1], ['_id' => $item['id']]);
      }

      foreach ($results as $item) {

        // Ez a szupertrükk. Csak egyszer számoljuk az egymás utáni megtekintéseket,
        // ha azonos a vp && vi && s (tehát effötölgetett)
        // Ehhez kiolvassuk az ehhez képesti előző látogatását ennek a sessionnek
        $her_last_visit = $this->Mongo->find_array('webstat', [
          's' => $item['s'],
          't' => ['$lt' => $item['t']]
        ], [
          'sort' => ['t' => -1],
          'limit' => 1
        ]);

        if (count($her_last_visit) == 0
          || ($her_last_visit[0]['vp'] != $item['vp'] && $her_last_visit[0]['vi'] != $item['vi'])) {
          $this->DB->update($item['vp'], [
            'view_total' => 'view_total+1',
            'view_week' => 'view_week+1',
            'view_day' => 'view_day+1',
          ], (int)$item['vi']);
        }

        // Done és akkor többet nem jövünk
        $this->Mongo->update('webstat', ['d' => 1], ['_id' => $item['id']]);
      }
    }
    
    return true;
  }

  public function remove_old() {
    // Régi dolgok törlése
    $this->Mongo->delete('webstat', [
      't' => ['$lt' => strtotime('-2 years')]
    ]);
  }

}