<?php
use Kozterkep\AppBase as AppBase;

class GamesController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_sidemenu' => false,
      '_active_menu' => 'Játék',
    ]);
  }

  public function index() {


    $this->set([
      '_active_submenu' => 'Mire játszunk?',
      '_title' => 'Mire játszunk?',
    ]);

  }

  public function race() {

    $this->set([
      '_active_submenu' => 'Havi futam',
      '_title' => 'Havi futam',
    ]);

  }

  public function hugs() {
    $photos = $this->DB->find('photos', [
      'conditions' => ['joy' => 1],
      'order' => 'approved DESC',
      'limit' => 9,
    ]);

    $events = $this->Mongo->find_array('events',
      [
        'public' => 1,
        'type_id' => ['$in' => [7,8]]
      ],
      [
        'limit' => 20,
        'sort' => ['created' => -1]
      ]
    );

    $top_huggers = $this->DB->find('users', [
      'conditions' => [
        'hug_count >' => 0,
        'active' => 1,
        'harakiri' => 0,
      ],
      'order' => 'hug_count DESC',
      'limit' => 20,
    ]);

    $hugs_30 = $this->Mongo->aggregate('artpiece_hugs', [
      ['$match' => ['created' => ['$gt' => strtotime('-30 days')]]],
      ['$group' => ['_id' => '$user_id', 'count' => ['$sum' => 1]]],
      ['$sort' => ['count' => -1]],
      ['$limit' => 10],
    ]);

    $hugs_360 = $this->Mongo->aggregate('artpiece_hugs', [
      ['$match' => ['created' => ['$gt' => strtotime('-365 days')]]],
      ['$group' => ['_id' => '$user_id', 'count' => ['$sum' => 1]]],
      ['$sort' => ['count' => -1]],
      ['$limit' => 10],
    ]);

    $this->set([
      '_active_submenu' => 'Érintő',
      '_title' => 'Érintő',

      'photos' => $photos,
      'events' => $events,
      'top_huggers' => $top_huggers,
      'hugs_30' => $hugs_30,
      'hugs_360' => $hugs_360,
    ]);

  }

  public function spacecapsules() {

    $this->set([
      '_active_submenu' => 'Térkapszulák',
      '_title' => 'Térkapszulák',
    ]);

  }

}