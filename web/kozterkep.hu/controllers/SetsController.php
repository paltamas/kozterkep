<?php
use Kozterkep\AppBase as AppBase;

class SetsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Műlapok',
      '_active_submenu' => 'Gyűjtemények',
      '_sidemenu' => true,
    ]);
  }



  public function index() {

    $latest_common_sets = $this->Mongo->find_array('sets',
      ['set_type_id' => 1],
      [
        'sort' => ['updated' => -1],
        'limit' => 5
      ]
    );

    $latest_user_sets = $this->Mongo->find_array('sets',
      ['set_type_id' => 2],
      [
        'sort' => ['updated' => -1],
        'limit' => 5
      ]
    );

    $top_common_sets = $this->Mongo->aggregate('sets',[
      ['$match' => ['set_type_id' => 1]],
      ['$unwind' => '$artpieces'],
      ['$group' => [
        '_id' => '$_id',
        'artpieces' => ['$push' => '$artpieces'],
        'name' => ['$first' => '$name'],
        'description' => ['$first' => '$description'],
        'user_id' => ['$first' => '$user_id'],
        'updated' => ['$first' => '$updated'],
        'set_type_id' => ['$first' => '$set_type_id'],
        'size' => ['$sum' => 1]],
      ],
      ['$sort' => ['size' => -1]],
      ['$limit' => 10]
    ]);

    $this->set([
      '_title' => 'Gyűjtemények',
      '_bookmarkable' => false,

      'common_set_count' => $this->Mongo->count('sets', ['set_type_id' => 1]),
      'user_set_count' => $this->Mongo->count('sets', ['set_type_id' => 2]),
      'latest_common_sets' => $latest_common_sets,
      'latest_user_sets' => $latest_user_sets,
      'top_common_sets' => $top_common_sets,
    ]);

  }



  public function search() {

    $filters = [];

    if (@$this->params->query['kulcsszo'] != '') {
      $filters['$and'][] = ['name' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']];
    }

    if (@$this->params->query['tag'] > 0 && @$this->params->query['tipus'] != 'kozos') {
      $filters['$and'][] = ['user_id' => (int)$this->params->query['tag']];
      $filters['$and'][] = ['set_type_id' => 2];
    }

    if (@$this->params->query['tipus'] != '') {
      $filters['$and'][] = [
        'set_type_id' => $this->params->query['tipus'] == 'kozos' ? 1 : 2
      ];
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => 50
    ];

    //debug($filters);

    $sets = $this->Mongo->find_array(
      'sets',
      $filters,
      [
        'sort' => ['name' => 1],
        'limit' => $pagination['limit'],
        'skip' => ($pagination['page']-1) * $pagination['limit']
      ]
    );

    $this->set([
      '_title' => 'Gyűjtemények keresése',

      'pagination' => $pagination,
      'sets' => $sets,
    ]);

  }



  public function my() {
    $this->users_only();

    $sets = $this->Mongo->find_array(
      'sets',
      [
        'set_type_id' => 2,
        'user_id' => $this->user['id']
      ],
      ['sort' => ['name' => 1]]
    );

    if ($this->params->is_post) {
      $this->Validation->process($this->params->data, [
        'name' => 'not_empty',
      ], 'sets', [
        'defaults' => [
          'set_type_id' => 2,
          'user_id' => $this->user['id'],
          'created' => time(),
          'description' => '',
          'artpieces' => [],
          'updated' => 0,
        ],
        'redirect' => [
          '/gyujtemenyek/szerkesztes/{id}', 'A gyűjtemény létrejött. Ezen az oldalon módosíthatod az adatait.'
        ],
        'db' => 'mongo'
      ]);
    }

    $this->set([
      '_title' => 'Saját gyűjteményeim',
      '_shareable' => false,
      '_bookmarkable' => false,

      'sets' => $sets
    ]);

  }



  public function view() {
    if (is_numeric($this->params->id)) {
      $set = $this->Mongo->first('sets', ['tag_id' => (int)$this->params->id]);
    } else {
      $set = $this->Mongo->first('sets', $this->params->id);
    }

    if (!$set) {
      $this->redirect('/gyujtemenyek', [texts('hibas_url'), 'warning']);
    }

    /**
     * Összegyűjtjük a bepakolt műlapokat.
     * Alapból bepakolás szerinti csökkenőbe tesszük őket
     * de gyűjtjük az év-kulcsos tömböt is az idővonalhoz
     */
    $artpieces = [];
    $artpieces_by_time = [];
    if (count($set['artpieces']) > 0) {
      $set['artpieces'] = _sort($set['artpieces'], 'created', 'desc');
      foreach ($set['artpieces'] as $a) {
        $artpiece = $this->MC->t('artpieces', $a['artpiece_id']);
        if (@$artpiece['status_id'] == 5) {
          $artpieces[] = $artpiece;
          $year = $this->Artpieces->get_artpiece_year($artpiece['dates'], ['only_last_year' => true]);
          if ($year > 0) {
            $artpieces_by_time[$year] = $artpiece;
          }
        }
      }

      if (count($artpieces_by_time) > 0) {
        ksort($artpieces_by_time);
      }
    }

    $this->set([
      'set' => $set,
      'artpieces' => $artpieces,
      'artpieces_by_time' => $artpieces_by_time,

      '_title' => '"' . $set['name'] . '" műlap-gyűjtemény',
      '_sidemenu' => false,
      '_model' => 'sets',
      '_model_id' => $set['id'],
      '_shareable' => true,
      '_followable' => $set['user_id'] != $this->user['id'] ? true : false,
      '_editable' => '/gyujtemenyek/szerkesztes/' . $set['id'],
    ]);

  }



  public function edit() {
    $this->users_only();

    $set = $this->Mongo->first('sets', $this->params->id);

    if (!$set || !$this->Users->owner_or_head($set, $this->user)) {
      $this->redirect('/gyujtemenyek', [texts('hibas_url'), 'warning']);
    }

    if ($this->params->is_post) {
      $this->Validation->process($this->params->data, [
        'save_settings' => 'unset',
        'name' => 'not_empty',
        'place_name' => 'unset',
        'place_id' => '',
        'description' => 'string',
        'cover_artpiece_id' => 'string',
      ], 'sets', [
        'defaults' => [
          '_id' => $set['id'],
          'modified' => time(),
        ],
        'redirect' => [
          '/gyujtemenyek/szerkesztes/' . $set['id'], texts('sikeres_mentes')
        ],
        'cache' => 'cached-view-sets-view-' . $set['id'],
        'db' => 'mongo'
      ]);
    }

    $tabs = [
      'list' => [
        /*'Megtekintés' => [
          'link' => '/gyujtemenyek/megtekintes/' . $set['id'],
          'icon' => 'tag',
        ],*/
        'Gyűjtemény beállítások' => [
          'hash' => 'beallitasok',
          'icon' => 'edit',
        ],
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'beallitasok',
        'class' => ''
      ]
    ];

    $this->set([
      'set' => $set,
      '_title' => $set['name'],
      '_tabs' => $tabs,
      '_viewable' => '/gyujtemenyek/megtekintes/' . $set['id'],
      '_bookmarkable' => false,
      '_shareable' => false,
    ]);

  }

}