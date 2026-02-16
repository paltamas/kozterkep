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

      if (in_array($this->data['vp'], ['artpieces', 'artists', 'places', 'posts', 'users', 'folders'])
        && $this->data['path'] != $this->Session->get('last_page')) {
        $this->DB->update($this->data['vp'], [
          'view_total' => 'view_total+1',
          'view_week' => 'view_week+1',
          'view_day' => 'view_day+1',
        ], (int)$this->data['vi']);

        $this->Session->set('last_page', $this->data['path']);
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