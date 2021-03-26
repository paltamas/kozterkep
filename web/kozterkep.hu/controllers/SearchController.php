<?php
use Kozterkep\AppBase as AppBase;

class SearchController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Műlapok',
      '_active_submenu' => 'Műlap keresés',
    ]);
  }


  /**
   * Instant keresés
   * ajaxszal meghívva
   */
  public function instant() {
    if (!$this->is('ajax')) {
      $this->redirect('/');
    }
    $query = $this->params->query;

    $limit = 150;

    $artpieces = [];
    $places = [];
    $artists = [];
    $sets = [];
    $users = [];

    if (mb_strlen(@$query['kulcsszo']) > 2) {
      // Kulcsszó javítások
      // pont után szóköz
      $query['kulcsszo'] = preg_replace('/(?<!\.)\.(?!(\s|$|\,|\w\.))/', '. ', $query['kulcsszo']);

      $conditions = [];

      if (is_numeric($query['kulcsszo']) && $query['kulcsszo'] > 16) {
        $conditions[] = "id = " . (int)$query['kulcsszo'];
      }

      $conditions[] = "title LIKE '%" . $query['kulcsszo'] . "%'";
      $conditions[] = "title_alternatives LIKE '%" . $query['kulcsszo'] . "%'";
      // Szóköz nélkül is, aka "kis királylány" szabály
      $conditions[] = "title LIKE '%" . str_replace(' ', '', $query['kulcsszo']) . "%'";
      $conditions[] = "title_alternatives LIKE '%" . str_replace(' ', '', $query['kulcsszo']) . "%'";
      // Kötőjellel is, aka "Lenin-mozaik" szabály
      $conditions[] = "title LIKE '%" . str_replace(' ', '-', $query['kulcsszo']) . "%'";
      $conditions[] = "title_alternatives LIKE '%" . str_replace(' ', '-', $query['kulcsszo']) . "%'";

      if (mb_strlen(@$query['kulcsszo']) >= 3) {
        /**
         * Alkotót és települést csak akkor nézünk, ha legalább 3*
         * betűt megadott a keresésben. Így nem lesz túl értelmetlen a lista.
         * Csak szó eleji egyezést veszünk figyelembe itt is.
         *
         * Ugyanezeket a logikákat használjunk az index actionben is.
         * Érdemes együtt módosítani, fejleszteni, bővíteni.
         *
         * Itt nem fieldlist van, mert a település és az alkotó
         * listát átadjuk a view-nak, hogy ott kattintható legyen.
         *
         *
         * hOPP!!
         * egyelőre nem tesszük bele a listába az alkotós és a településes egyezéseket,
         * mert végiggondoltam és zavaró. Kiírjuk őket listában és ez is klafa lesz.
         * * - emiatt most 3
         */

        // TELEPÜLÉSEK
        $places = $this->DB->find('places', [
          'type' => 'list',
          'conditions' => [
            'checked' => 1,
            'OR' => [
              'name LIKE' => $query['kulcsszo'] . '%',
              'alternative_names LIKE' => $query['kulcsszo'] . '%',
            ]
          ],
          'order' => 'artpiece_count DESC',
          'fields' => ['id', 'name'],
        ]);
        /*
        if (count($places) > 0) {
          $place_ids = [];
          foreach ($places as $place) {
            $place_ids[] = $place['id'];
          }
          //$conditions[] = 'place_id IN (' . implode(',', $place_ids) . ')';
        }*/

        // ALKOTÓK
        $artists = $this->DB->find('artists', [
          'type' => 'list',
          'conditions' => [
            'checked' => 1,
            'OR' => [
              "REPLACE(name, '.', '') LIKE" => $query['kulcsszo'] . "%",
              'name LIKE' => $query['kulcsszo'] . '%',
              'last_name LIKE' => $query['kulcsszo'] . '%',
              'alternative_names LIKE' => $query['kulcsszo'] . '%',
            ]
          ],
          'order' => 'artpiece_count DESC',
          'fields' => ['id', 'name'],
        ]);
        /*if (count($artists) > 0) {
          $artist_ids = [];
          foreach ($artists as $artist) {
            $or_array[] = "JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')";
          }
          //$conditions[] = '(' . implode(' OR ', $or_array) . ')';
        }*/

        // GYŰJTEMÉNYEK
        $sets = $this->Mongo->find_array('sets',
          ['name' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
          ['order' => ['name' => 1]]
        );

        if ($this->user) {
          $users = $this->DB->find('users', [
            'type' => 'list',
            'conditions' => [
              'harakiri' => 0,
              'activated >' => 0,
              'OR' => [
                'name LIKE' => $query['kulcsszo'] . '%',
                'link LIKE' => $query['kulcsszo'] . '%',
                'nickname LIKE' => $query['kulcsszo'] . '%',
              ]
            ],
            'order' => 'points DESC',
            'fields' => ['id', 'name', 'link'],
          ]);
        } else {
          $users = [];
        }
      }


      // Sajátot, vagy publikust keresünk
      if (isset($this->params->query['sajat'])) {
        // Csak saját lapok közt szűrünk, nincs státuszszűrés
        $user_status_condition = 'user_id = ' . $this->user['id'];
      } else {
        // Sajátok közt bármi, egyébként csak publikus (ha nem user, csak publikus)
        $user_status_condition = @$this->user ? '(status_id = 5 OR user_id = ' . $this->user['id'] . ')' : 'status_id = 5';
      }

      $artpieces = $this->DB->find('artpieces', [
        'conditions' => $user_status_condition . ' AND ('
          . implode(' OR ', $conditions) . ')',
        'limit' => $limit,
        'order' => 'view_total DESC',
        'debug' => false,
      ]);
    }

    $this->set([
      '_title' => 'Instant kereső',
      '_bookmarkable' => false,
      '_sidemenu' => false,
      '_breadcrumbs_menu' => false,
      '_title_row' => false,
      'artpieces' => $artpieces,
      'places' => $places,
      'artists' => $artists,
      'sets' => $sets,
      'users' => $users,
      'query' => $query,
      'limit' => $limit,
    ]);

  }


  /**
   * Kereső oldal
   */
  public function index() {
    $query = $this->params->query;
    $query = _unset($query, ['oldal', 'r', 'elem', 'sorrend', 'kereses']);

    // Ha évszámtól évszámig keresnek, akkor

    // Paraméterek; itt, mert kell a kondihoz is
    $artpiece_parameters = $this->DB->find('parameters', [
      'conditions' => ['hidden' => 0],
      'order' => 'parameter_group_id ASC, parameter_subgroup_id ASC, rank ASC'
    ]);

    $sets = $this->Mongo->find_array('sets', ['set_type_id' => 1], [
      'sort' => ['name' => 1],
      'projection' => ['name' => 1],
    ]);

    $selected_parameters = $this->Search->selected_parameters($this->params->query);

    /**
     * Itt bizony raw condition lesz a végén!
     */
    $conditions = $this->Search->build_artpiece_search_conditions($query, $artpiece_parameters, $this->user);

    // Mindenképpen fut
    $search_history = $this->_handle_search_history($query);

    // Ha nincs keresés (és oldal léptetés), akkor friss lista, és ezt cache-eljük
    $cached = false;
    if (count($this->params->query) == 0) {
      $cached = ['name' => __METHOD__];
    }

    //$total_count = $this->DB->count('artpieces', implode(' AND ', $conditions));

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
    ];

    $order_options = [
      'publikalas-csokkeno' => 'Publikálás, csökkenő',
      'publikalas-novekvo' => 'Publikálás, növekvő',
      'evszam_utolso-csokkeno' => 'Utolsó évszám, csökkenő',
      'evszam_utolso-novekvo' => 'Utolsó évszám, növekvő',
      'evszam_elso-csokkeno' => 'Első évszám, csökkenő',
      'evszam_elso-novekvo' => 'Első évszám, növekvő',
      'nezettseg-csokkeno' => 'Nézettség, csökkenő',
      'nezettseg-novekvo' => 'Nézettség, növekvő',
      'napi_nezettseg-csokkeno' => 'Napi nézettség, csökkenő',
      'napi_nezettseg-novekvo' => 'Napi nézettség, növekvő',
    ];

    if (@$this->params->query['hasonlo'] == 1 && @$this->params->query['kulcsszo'] != '') {
      $order = $this->Search->build_order($this->params->query, 'score2 DESC');

      if (!isset($this->params->query['sorrend'])) {
        $this->params->query['sorrend'] = 'hasonlosag-csokkeno';
      }

      $order_options = ['hasonlosag-csokkeno' => 'Hasonlóság szerint'] + $order_options;

      $title = $this->params->query['kulcsszo'];
      $ignorandus = sDB['similar_excludes'];
      $title = addslashes(trim(str_replace($ignorandus, '', $title)));

      // Ide komplex count kell, amit nem tudok megcsinálni a komponenssel...
      $total_count = $this->DB->query("SELECT *, MATCH(title) AGAINST ('" . $title . "*' IN BOOLEAN MODE) AS score "
        //. " MATCH(title) AGAINST ('" . $title . "*') AS score2"
        . "FROM artpieces WHERE " . implode(' AND ', $conditions) . " HAVING score > 0");

      $pagination['total_count'] = count($total_count);

      $artpieces = $this->DB->find('artpieces', [
        'conditions' => implode(' AND ', $conditions),
        'having' => 'score > 0',
        'order' => $order,
        'fields' => [
          '*',
          "MATCH(title) AGAINST ('" . $title . "*' IN BOOLEAN MODE) AS score",
          "MATCH(title) AGAINST ('" . $title . "*') AS score2",
        ],
        'limit' => $pagination['limit'],
        'page' => $pagination['page'],
        'debug' => false,
      ], $cached);

    } else {

      $order = $this->_build_order($this->params->query);

      $total_count = $this->DB->count('artpieces', implode(' AND ', $conditions));
      $pagination['total_count'] = $total_count;

      // Ha évszám szerinti növekvő sorrendben vagyunk,
      // akkor be kell tenni egy plussz feltételt,
      // különben az üres évszámosok előre kerülnek
      if (in_array($order, ['first_date ASC', 'last_date ASC'])) {
        $conditions[] = "first_date > '0000-00-00'";
      }

      $artpieces = $this->DB->find('artpieces', [
        'conditions' => implode(' AND ', $conditions),
        'limit' => $pagination['limit'],
        'order' => $order,
        'page' => $pagination['page'],
        'debug' => false,
      ], $cached);
    }


    // Lista oldal vagy keresési oldal?
    if ($this->params->path == '/kereses/lista') {
      $list_page_type = true;
      $page_title = @$this->params->query['oldalcim'] != '' ? $this->params->query['oldalcim'] : 'Műlapok listája';
    } else {
      $page_title = 'Műlapok keresése';
      $list_page_type = false;
    }

    // hasonlók sorbarendezése
    $this->set([
      '_title' => $page_title,
      '_bookmarkable' => true,
      '_sidemenu' => false,
      '_breadcrumbs_menu' => ['Műlapok' => '/mulapok/attekintes'],
      '_title_row' => true,
      'list_page_type' => $list_page_type,
      'artpieces' => $artpieces,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'search_history' => $search_history,
      'order_options' => $order_options,
      'query' => $query,
      'sets' => $sets,
      'artpiece_parameters' => $artpiece_parameters,
      'selected_parameters' => $selected_parameters,
      'creator_users' => $this->Users->list('creators'),
    ]);
  }


  /**
   *
   * Utolsó keresések tárolása
   *
   * @todo: menteni users Mongo táblába
   *
   * @param $conditions
   * @param int $max_count
   * @return array|bool
   */
  private function _handle_search_history($query, $max_count = 7) {

    // Törlés kérése volt (URL-ben jön)
    if (isset($this->params->query['elozmenyek_torlese'])) {
      if ($this->user) {
        $this->Mongo->update('users', ['search_history' => []], ['user_id' => $this->user['id']]);
      } else {
        $this->Session->delete('search_history');
      }
      $this->redirect('/kereses');
    }

    $search_history = [];


    // Kiolvassuk az előzményeket
    if ($this->user) {
      $user_ = $this->Mongo->first('users', ['user_id' => $this->user['id']]);
      if (isset($user_['search_history'])) {
        $search_history = $user_['search_history'];
      }
    } else {
      $search_history_ = $this->Session->get('search_history');
      if ($search_history_) {
        $search_history = $search_history_;
      }
    }


    // ...csak, ha volt URL-ben látható keresés és gombot is nyomtak
    // linkeléskor nincs bent az URL-ben a "kereses", így nem képződik előzmény
    if (isset($this->params->query['kereses']) && count($query) > 0) {
      // Ide már újra kell a részletes is, hogy jó fület nyissunk
      if (@$this->params->query['r'] == 1) {
        $query['r'] = 1;
      }

      $query_string = http_build_query($query);

      $last_search = [
        'name' => '',
        'created' => time(),
        'query_string' => $query_string
      ];

      // Tárolás, és ha van ilyen korábban, azt töröljük
      // így "előre jön"
      if (count($search_history) > 0) {
        foreach ($search_history as $key => $item) {
          // Kivesszük a részletes flag-et a hasonlításkor, így mindig arra vált
          // a keresés, ami épp az utolsó volt
          if (str_replace(['&r=1', 'r=1&'], '', @$item['query_string']) == str_replace(['&r=1', 'r=1&'], '', $query_string)) {
            unset($search_history[$key]);
          }
        }
      }
      $search_history[uniqid()] = $last_search;

      // Elemszám ellenőrzés (user 300, visitor 30)
      if (($this->user && count($search_history) > 300) || (!$this->user && count($search_history) > 30)) {
        reset($search_history);
        $first_key = key($search_history);
        unset($search_history[$first_key]);
      }

      // Tárolás
      if ($this->user) {
        $this->Mongo->update('users', ['search_history' => $search_history], ['user_id' => $this->user['id']]);
      } else {
        $this->Session->set('search_history', $search_history);
      }
    }

    return $search_history;
  }


  private function _build_order($query, $default_order = 'published DESC') {
    $order = $default_order;
    if (@$query['sorrend'] != '') {
      $vars = explode('-', $query['sorrend']);
      $order = '';
      switch ($vars[0]) {
        case 'publikalas':
          $order .= 'published';
          break;

        case 'evszam_elso':
          $order .= 'first_date';
          break;

        case 'evszam_utolso':
          $order .= 'last_date';
          break;

        case 'nezettseg':
          $order .= 'view_total';
          break;

        case 'napi_nezettseg':
          $order .= 'view_day';
          break;

        case 'hasonlosag':
          $order .= 'score2';
          break;
      }
      $order .= $vars[1] == 'novekvo' ? ' ASC' : ' DESC';
    }

    return $order;
  }

}