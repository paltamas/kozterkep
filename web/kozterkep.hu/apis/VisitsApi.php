<?php
class VisitsApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

  }

  public function post() {
    if (isset($_SERVER['HTTP_USER_AGENT']) &&
      !preg_match('/' . implode('|', sDB['bots']) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
      // Elvileg ember
      if ($this->data['visit'] == 1) {
        // Látogatás

        $this->Mongo->insert('webstat', [
          's' => session_id(), // session ID az egyediséghez
          'v' => $this->Cookie->get(APP['cookies']['webstat_name']), // session ID az egyediséghez
          'p' => $this->data['path'], // tisztított path query és hash nélkül
          'fp' => $this->data['full_path'], // teljes path
          'r' => $this->data['referrer'], // referrer
          'vp' => $this->data['vp'], // visit page: minősített oldalak
          'vi' => (int)$this->data['vi'], // visit ID: minősített oldal ID-k
          't' => time(), // itt jött ide
          'tt' => time(), // eddig nézte
          'd' => (int)$this->data['vi'] == 0 ? 1 : 0, // done, feldolgozott: csak a visit ID esetén kell dolgozni vele
        ]);
      } else {
        // Még mindig nézi a lapot
        // Kiszedem azt, ami "mostanában"* nézve és ez
        // és azt update-elem. Ha nincs, akkor valami gebasz van, nem csinálok frissítést.
        // * - ha a böngi ablak nem aktív és visszajön fél órán belül, akkor még ő és ez
        $visit = $this->Mongo->first('webstat', [
          's' => session_id(),
          'fp' => $this->data['full_path'],
          'tt' => ['$gt' => strtotime('-30 minutes')]
        ]);

        if ($visit) {
          $this->Mongo->update('webstat', ['tt' => time()], ['_id' => $visit['id']]);
        }
      }
      $this->send(['success']);
    } else {
      // Bot
      $this->send(['success' => 'szia, te kis botka! :]']);
    }
  }


  public function put() {

  }


  /**
   * Tetszőleges, view-statozható modellek elemeinek
   * megtekintési adatait szedi le
   */
  public function view_stats() {
    $result = [];
    $models = APP['models'];
    $model = $this->data['model'];
    $id = (int)$this->data['model_id'];

    if (isset($models[$model]) && $id > 0) {

      $item = $this->DB->first($model, $id, [
        'fields' => ['view_week', 'view_total']
      ]);

      if ($item) {
        $result = [
          'view_week' => max($item['view_week'], 1),
          'view_total' => max($item['view_total'], 1),
        ];
      }
    }

    $this->send($result);
  }

}