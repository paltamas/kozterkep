<?php
use Kozterkep\AppBase as AppBase;

class PlacesController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Helyek',
      '_sidemenu' => true,
    ]);
  }

  public function index() {

    $countries_ = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'country_id >' => 0,
      ],
      'fields' => ['DISTINCT country_id'],
    ]);
    $country_count = count($countries_);

    $place_count = $this->DB->count('places');

    $random_place = $this->DB->first('places', [
      'checked' => 1,
      'artpiece_count >' => 4,
      'artpiece_count <' => 100,
    ], [
      'order' => 'RAND()',
    ]);

    $top_places = $this->DB->find('places', [
      'order' => 'artpiece_count DESC',
      'limit' => 10,
    ]);

    $latest_places = $this->DB->find('places', [
      'conditions' => [
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'order' => 'checked_time DESC',
      'limit' => 10,
    ]);

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'status_id' => 5,
        'place_id >' => 0,
      ],
      'order' => 'published DESC',
      'limit' => 7,
    ]);

    $countries = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'country_id >' => 0,
      ],
      'fields' => ['country_id', 'COUNT(id) AS artpiece_count'],
      'order' => 'artpiece_count DESC',
      'group' => 'country_id',
      'limit' => 10,
    ]);


    $this->set([
      '_title' => 'Helyek',
      '_active_submenu' => 'Áttekintés',

      'random_place' => $random_place,
      'country_count' => $country_count,
      'place_count' => $place_count,
      'top_places' => $top_places,
      'latest_places' => $latest_places,
      'countries' => $countries,
      'posts' => $posts,
    ]);

  }


  public function search() {
    $query = $this->params->query;
    $query = _unset($query, ['oldal', 'r', 'elem', 'sorrend', 'kereses']);

    $conditions = [];

    if (@$query['ellenorizetlen'] == 1) {
      $conditions += ['checked' => 0];
    } elseif (@$query['ellenorizetlen'] === 0) {
      $conditions += ['checked' => 1];
    }

    if (@$query['kulcsszo'] != '') {
      if (is_numeric($query['kulcsszo'])) {
        $conditions += ['id' => $query['kulcsszo']];
      } elseif (@$query['eleje'] == 1) {
        $conditions += ['OR' => [
          'name LIKE' => $query['kulcsszo'] . '%',
          'original_name LIKE' => $query['kulcsszo'] . '%',
          'alternative_names LIKE' => $query['kulcsszo'] . '%',
        ]];
      } else {
        $conditions += ['OR' => [
          'name LIKE' => '%' . $query['kulcsszo'] . '%',
          'original_name LIKE' => '%' . $query['kulcsszo'] . '%',
          'alternative_names LIKE' => '%' . $query['kulcsszo'] . '%',
        ]];
      }
    }

    if (@$query['megye'] > 0) {
      $conditions += ['county_id' => $query['megye']];
    }

    if (@$query['orszag'] > 0) {
      $conditions += ['country_id' => $query['orszag']];
    }

    if (@$query['kovetettek'] == 1 && $this->user) {
      $me = $this->Mongo->first('users', [
        'user_id' => $this->user['id']
      ]);
      $followeds = _json_decode(@$me['follow_places']);
      if (count($followeds) > 0) {
        $conditions += ['id' => $followeds];
      }
    }

    $total_count = $this->DB->count('places', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count
    ];

    $order_options = [
      'abc-novekvo' => 'Név szerint',
      'mulap-csokkeno' => 'Műlap, csökkenő',
      'mulap-novekvo' => 'Műlap, növekvő',
      'nezettseg-csokkeno' => 'Nézettség, csökkenő',
      'rogzites-csokkeno' => 'Rögzítés, csökkenő',
      'rogzites-novekvo' => 'Rögzítés, növekvő',
    ];

    $order = $this->Search->build_order($this->params->query, 'name ASC');

    $places = $this->DB->find('places', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => $order,
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Települések keresése',
      '_active_submenu' => 'Települések keresése',
      '_bookmarkable' => true,
      '_sidemenu' => true,
      '_breadcrumbs_menu' => true,
      '_title_row' => true,

      'places' => $places,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'query' => $query,
      'order_options' => $order_options,
    ]);
  }

  public function view() {

    $place = $this->DB->first('places', $this->params->id);

    if (!$place) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }

    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'place_id' => $place['id'],
      ],
      'order' => 'view_total DESC',
      'limit' => 2,
    ]);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'place_id' => $place['id'],
      ],
      'order' => 'published DESC',
      'limit' => 14,
    ]);

    $map_artpieces = [];
    $artpieces_by_time = [];

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'place_id' => (int)$place['id'],
        'status_id' => 5,
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);

    if ($artpieces) {

      foreach ($artpieces as $artpiece) {
        if ($place['artpiece_count'] <= APP['map']['max_id']) {
          $map_artpieces[] = $artpiece['id'];
        }
        $year = $this->Artpieces->get_artpiece_year($artpiece['dates'], ['only_last_year' => true]);
        if ($year > 0) {
          $artpieces_by_time[$year] = $artpiece;
        }
      }
    }

    if (count($artpieces_by_time) > 0) {
      ksort($artpieces_by_time);
    }

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'status_id' => 5,
        'place_id' => $place['id'],
      ],
      'order' => 'published DESC',
    ]);


    $tabs = [
      'list' => [
        'Adatlap' => [
          'hash' => 'adatlap',
          'icon' => 'map-pin',
        ],
        'SzerkKomm' => [
          'hash' => 'szerkkomm',
          'icon' => 'comment-edit',
        ],
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'adatlap',
        'class' => '',
      ]
    ];

    $this->set([
      'place' => $place,
      'top_artpieces' => $top_artpieces,
      'latest_artpieces' => $latest_artpieces,
      'map_artpieces' => $map_artpieces,
      'artpieces_by_time' => $artpieces_by_time,
      'comment_count' => $this->Mongo->count('comments', [
        'place_id' => $place['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      'posts' => $posts,

      '_title' => strip_tags($this->Places->name($place)) . ' alkotásai',
      '_active_submenu' => 'Települések keresése',
      '_sidemenu' => false,
      '_breadcrumbs_menu' => $this->Places->get_breadcrumbs_menu($place),
      '_tabs' => $tabs,
      '_model' => 'places',
      '_model_id' => $place['id'],
      '_shareable' => true,
      '_followable' => true,
      //'_editable' => $this->Users->owner_or_head($place, $this->user) ? '/helyek/szerkesztes/' . $place['id'] : false,
      '_editable' => '/helyek/szerkesztes/' . $place['id'],
    ]);

  }


  /**
   * Szerkkomm ajaxdiv
   */
  public function view_editcom() {
    $place = $this->DB->first('places', $this->params->id);

    if (!$place) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      'place' => $place,
      'comment_count' => $this->Mongo->count('comments', [
        'place_id' => $place['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      '_title' => strip_tags($this->Places->name($place)) . ' SzerkKomm',
    ]);
  }




  public function bp_districts() {

    // Itt lehet rendezni 2 esetben is szépen
    $order = isset($this->params->query['szam'])
      ? 'district_id ASC' : 'artpiece_count DESC, district_id ASC';

    $districts = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'district_id >' => 0,
        'place_id' => 110,
      ],
      'fields' => ['district_id', 'COUNT(id) AS artpiece_count'],
      'order' => $order,
      'group' => 'district_id',
    ], ['name' => 'places_districts_' . $this->Text->slug($order)]);

    $selected_tab = 1;

    if (isset($this->params->query['szam'])) {
      $selected_tab = 2;
    }

    if (isset($this->params->query['rata'])) {
      $districts_ = [];
      foreach ($districts as $district) {
        $persons = max(1, (sDB['districts'][$district['district_id']][1]*1000));
        $rate = ($district['artpiece_count'] / $persons);
        $districts_[(string)$rate] = $district;
      }
      krsort($districts_);
      $districts = $districts_;
      $selected_tab = 3;
    }


    $this->set([
      '_title' => 'Budapesti kerületek alkotásai',
      '_active_submenu' => 'BP kerületek',
      'districts' => $districts,
      'selected_tab' => $selected_tab,
    ]);

  }


  public function bp_district() {
    $district_id = (int)$this->params->id;
    $districts = sDB['districts'];

    if (!$districts[$district_id]) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }


    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'district_id' => $district_id,
      ],
      'order' => 'view_total DESC',
      'limit' => 2,
    ]);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'district_id' => $district_id,
      ],
      'order' => 'published DESC',
      'limit' => 8,
    ]);

    $map_artpieces = [];
    $artpieces_by_time = [];

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'district_id' => $district_id,
        'status_id' => 5,
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);

    if ($artpieces) {

      foreach ($artpieces as $artpiece) {
        if (count($artpieces) <= APP['map']['max_id']) {
          $map_artpieces[] = $artpiece['id'];
        }
        $year = $this->Artpieces->get_artpiece_year($artpiece['dates'], ['only_last_year' => true]);
        if ($year > 0) {
          $artpieces_by_time[$year] = $artpiece;
        }
      }
    }

    if (count($artpieces_by_time) > 0) {
      ksort($artpieces_by_time);
    }

    $this->set([
      '_title' => $districts[$district_id][0] . ' alkotásai',
      '_active_sidemenu' => '/helyek/budapesti-keruletek',
      '_active_submenu' => 'BP kerületek',
      '_breadcrumbs_menu' => [
        'Helyek' => '/helyek/attekintes',
        'Budapesti kerületek' => '/helyek/budapesti-keruletek'
      ],

      'district_id' => $district_id,
      'top_artpieces' => $top_artpieces,
      'latest_artpieces' => $latest_artpieces,

      'map_artpieces' => $map_artpieces,
      'artpieces_by_time' => $artpieces_by_time,
      'artpiece_count' => count($artpieces),
    ]);
  }



  public function counties() {

    $counties = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'county_id >' => 0,
        'country_id' => 101,
      ],
      'fields' => ['county_id', 'COUNT(id) AS artpiece_count'],
      'order' => 'artpiece_count DESC, county_id ASC',
      'group' => 'county_id',
    ], ['name' => 'places_counties']);

    $selected_tab = 1;

    if (isset($this->params->query['abc'])) {
      $counties_ = [];
      foreach ($counties as $county) {
        $counties_[$this->Text->slug(sDB['counties'][$county['county_id']][0])] = $county;
      }
      ksort($counties_);
      $counties = $counties_;
      $selected_tab = 2;
    }


    if (isset($this->params->query['rata'])) {
      $counties_ = [];
      foreach ($counties as $county) {
        $rate = ($county['artpiece_count'] / (sDB['counties'][$county['county_id']][1]*1000));
        $counties_[(string)$rate] = $county;
      }
      krsort($counties_);
      $counties = $counties_;
      $selected_tab = 3;
    }


    $this->set([
      '_title' => 'Magyarország megyéinek alkotásai',
      '_active_submenu' => 'Vármegyék',
      'counties' => $counties,
      'selected_tab' => $selected_tab,
    ]);

  }

  public function county() {

    $county_id = (int)$this->params->id;
    $counties = sDB['counties'];

    if (!$counties[$county_id]) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }


    $place_count = $this->DB->count('places', [
      'county_id' => $county_id,
      'artpiece_count >' => 0
    ]);

    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'county_id' => $county_id,
      ],
      'order' => 'view_total DESC',
      'limit' => 2,
    ]);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'county_id' => $county_id,
      ],
      'order' => 'published DESC',
      'limit' => 8,
    ]);

    $top_places = $this->DB->find('places', [
      'conditions' => ['county_id' => $county_id],
      'order' => 'artpiece_count DESC',
      'limit' => 5,
    ]);

    $latest_places = $this->DB->find('places', [
      'conditions' => [
        'county_id' => $county_id,
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'order' => 'created DESC',
      'limit' => 5,
    ]);

    $map_artpieces = [];
    $artpieces_by_time = [];

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'county_id' => $county_id,
        'status_id' => 5,
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);

    if ($artpieces) {

      foreach ($artpieces as $artpiece) {
        if (count($artpieces) <= APP['map']['max_id']) {
          $map_artpieces[] = $artpiece['id'];
        }
        $year = $this->Artpieces->get_artpiece_year($artpiece['dates'], ['only_last_year' => true]);
        if ($year > 0) {
          $artpieces_by_time[$year] = $artpiece;
        }
      }
    }

    if (count($artpieces_by_time) > 0) {
      ksort($artpieces_by_time);
    }

    $this->set([
      '_title' => $counties[$county_id][0] . ' alkotásai',
      '_active_submenu' => 'Vármegyék',
      '_active_sidemenu' => '/helyek/megyek',
      '_breadcrumbs_menu' => [
        'Helyek' => '/helyek/attekintes',
        'Magyarország' => '/orszagok/megtekintes/101/magyarorszag',
      ],

      'county_id' => $county_id,
      'top_artpieces' => $top_artpieces,
      'latest_artpieces' => $latest_artpieces,
      'place_count' => $place_count,
      'top_places' => $top_places,
      'latest_places' => $latest_places,
      'map_artpieces' => $map_artpieces,
      'artpieces_by_time' => $artpieces_by_time,
      'artpiece_count' => count($artpieces),
    ]);

  }



  public function countries() {

    $countries = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'country_id >' => 0,
      ],
      'fields' => ['country_id', 'COUNT(id) AS artpiece_count'],
      'order' => 'artpiece_count DESC, country_id ASC',
      'group' => 'country_id',
    ], ['name' => 'places_countries']);


    $this->set([
      '_title' => 'Országok alkotásai',
      '_active_submenu' => 'Országok',
      'countries' => $countries,
    ]);

  }



  public function country() {

    $country_id = (int)$this->params->id;
    $countries = sDB['countries'];

    if (!$countries[$country_id]) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }


    $place_count = $this->DB->count('places', [
      'country_id' => $country_id,
      'artpiece_count >' => 0
    ]);

    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'country_id' => $country_id,
      ],
      'order' => 'view_total DESC',
      'limit' => 2,
    ]);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'country_id' => $country_id,
      ],
      'order' => 'published DESC',
      'limit' => 8,
    ]);

    $top_places = $this->DB->find('places', [
      'conditions' => ['country_id' => $country_id],
      'order' => 'artpiece_count DESC',
      'limit' => 5,
    ]);

    $latest_places = $this->DB->find('places', [
      'conditions' => [
        'country_id' => $country_id,
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'order' => 'created DESC',
      'limit' => 5,
    ]);

    $map_artpieces = [];
    $artpieces_by_time = [];

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'country_id' => $country_id,
        'status_id' => 5,
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);

    if ($artpieces) {

      foreach ($artpieces as $artpiece) {
        if (count($artpieces) <= APP['map']['max_id']) {
          $map_artpieces[] = $artpiece['id'];
        }
        $year = $this->Artpieces->get_artpiece_year($artpiece['dates'], ['only_last_year' => true]);
        if ($year > 0) {
          $artpieces_by_time[$year] = $artpiece;
        }
      }
    }

    if (count($artpieces_by_time) > 0) {
      ksort($artpieces_by_time);
    }

    $this->set([
      '_title' => $countries[$country_id][1] . ' alkotásai',
      '_active_submenu' => 'Országok',
      '_active_sidemenu' => '/helyek/orszagok',
      '_breadcrumbs_menu' => ['Helyek' => '/helyek/attekintes'],

      'country_id' => $country_id,
      'top_artpieces' => $top_artpieces,
      'latest_artpieces' => $latest_artpieces,
      'place_count' => $place_count,
      'top_places' => $top_places,
      'latest_places' => $latest_places,
      'map_artpieces' => $map_artpieces,
      'artpieces_by_time' => $artpieces_by_time,
      'artpiece_count' => count($artpieces),
    ]);

  }


  public function discoverables() {

    $this->set([
      '_title' => '',
    ]);

  }



  public function edit() {
    $this->users_only();

    $place = $this->DB->first('places', $this->params->id);

    if (!$place || !$this->Users->owner_or_head($place, $this->user)) {
      $this->redirect('/helyek', [texts('hibas_url'), 'warning']);
    }


    if ($this->params->is_post) {


      // BEOLVASZTÁS
      if (isset($this->params->data['merge'])) {
        if ($this->params->data['target_place_id'] > 0) {
          $merge = $this->Places->merge($this->user, $place['id'], $this->params->data['target_place_id']);
          if ($merge) {
            $this->redirect('/helyek/szerkesztes/' . $this->params->data['target_place_id'], 'Sikeresen lezajlott a beolvasztás. ' . (int)$merge['artpieces'] . ' műlapon és ' . (int)$merge['edits'] . ' szerkesztésben cseréltünk települést. A beolvasztott települést töröltük.');
          } else {
            $this->redirect('/helyek/szerkesztes/' . $place['id'], [texts('varatlan_hiba'), 'danger']);
          }
        }
      }


      /**
       * Megye vagy ország módosításnál rájegyezzük
       * a kapcsolódó műlapokra az új ID-t
       * név módosulásoknál műlap cache-t törlünk
       */
      if ($place['country_id'] != $this->params->data['country_id']
        || $place['county_id'] != $this->params->data['county_id']
        || $place['name'] != $this->params->data['name']
        || $place['original_name'] != $this->params->data['original_name']
        || $place['alternative_names'] != $this->params->data['alternative_names']) {
        $artpieces = $this->DB->find('artpieces', [
          'conditions' => [
            'place_id' => $place['id'],
          ],
          'fields' => ['id', 'country_id', 'county_id'],
        ]);
        if (count($artpieces)) {
          foreach ($artpieces as $artpiece) {
            if ($this->params->data['country_id'] != $artpiece['country_id']
              || $this->params->data['county_id'] != $artpiece['county_id']) {
              $this->DB->update('artpieces', [
                'country_id' => (int)$this->params->data['country_id'],
                'county_id' => (int)$this->params->data['county_id'],
              ], $artpiece['id']);
            }
            $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
          }
        }
      }


      // SIMA mentés

      // Most lett ellenőrzött
      if ($place['checked'] == 0 && $this->params->data['checked'] == 1) {
        $this->params->data['checked_time'] = time();
      }

      $this->Validation->process($this->params->data, [
        'save_settings' => 'unset',
        'name' => 'not_empty',
        'original_name' => '',
        'alternative_names' => '',
        'county_id' => 'int',
        'country_id' => 'int',
        'admin_memo' => '',
        'inner_memo' => '',
        'checked' => 'tinyint',
        'checked_time' => 'int',
      ], 'places', [
        'defaults' => [
          'id' => $place['id'],
          'modified' => time(),
        ],
        'redirect' => [
          '/helyek/szerkesztes/' . $place['id'], texts('sikeres_mentes')
        ],
        'cache' => 'cached-view-places-view-' . $place['id'],
      ]);
    }


    $similars = $this->DB->find('places', [
      'conditions' => [
        'OR' => [
          'name LIKE' => '%' . $place['name'] . '%',
          'alternative_names LIKE' => '%' . $place['name'] . '%',
          'original_name LIKE' => '%' . $place['name'] . '%',
        ],
        'id <>' => $place['id'],
      ]
    ]);


    $tabs = [
      'list' => [
        /*'Megtekintés' => [
          'link' => '/helyek/megtekintes/' . $place['id'],
          'icon' => 'map-pin',
        ],*/
        'Szerkesztés' => [
          'hash' => 'szerkesztes',
          'icon' => 'edit',
        ],
        'SzerkKomm' => [
          'hash' => 'szerkkomm',
          'icon' => 'comment-edit',
        ],
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'szerkesztes',
        'class' => ''
      ]
    ];

    $this->set([
      'place' => $place,
      'similars' => $similars,
      '_active_submenu' => 'Települések keresése',
      '_active_sidemenu' => '/helyek/kereses',
      '_title' => '"' . $place['name'] . '" szerkesztése',
      '_tabs' => $tabs,
      '_viewable' => '/helyek/megtekintes/' . $place['id'],
      '_bookmarkable' => false,
      '_shareable' => false,
    ]);

  }
}