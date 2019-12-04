<?php
use Kozterkep\AppBase as AppBase;

class CommunityController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Közösség',
    ]);
  }

  public function index() {

    $highlighted_user = $this->DB->first('users', [], [
      'order' => 'highlighted DESC'
    ]);
    $highlighted_user_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $highlighted_user['id'],
        'status_id' => 5,
      ],
      'limit' => 5,
      'order' => 'published DESC',
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'user_id', 'published'],
    ]);

    $top_users = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'harakiri' => 0,
      ],
      'order' => 'points DESC',
      'limit' => 10,
    ], ['name' => 'top_10_users_by_points']);

    $top_users_latest = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'harakiri' => 0,
      ],
      'order' => 'points_latest DESC',
      'limit' => 10,
    ], ['name' => 'top_10_latest_users_by_points']);

    $top_users_edits = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'harakiri' => 0,
      ],
      'fields' => ['id', 'name', 'link', 'points', 'points_latest', 'edit_other_count', 'description_other_count', 'edit_other_count_latest', 'description_other_count_latest', '(edit_other_count + description_other_count) AS community_count',],
      'order' => 'community_count DESC',
      'limit' => 10,
    ], ['name' => 'top_10_latest_users_by_edits']);

    $admin_posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id' => [1,10,11],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 3,
    ]);

    $user_posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id NOT' => [1,10,11],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 9,
    ]);

    $folders = $this->DB->find('folders', [
      'conditions' => [
        'public' => 1,
      ],
      'order' => 'updated DESC',
      'limit' => 5,
    ]);

    $sets = $this->Mongo->find_array('sets',
      [
        'set_type_id' => 2,
        'artpieces' => ['$ne' => []]
      ],
      [
        'sort' => ['updated' => -1],
        'limit' => 10,
      ]
    );

    $this->set([
      '_active_submenu' => 'Mi',
      '_title' => 'A Köztérképet építő közösség',
      '_sidemenu' => false,

      'top_users' => $top_users,
      'top_users_latest' => $top_users_latest,
      'top_users_edits' => $top_users_edits,
      'highlighted_user' => $highlighted_user,
      'highlighted_user_artpieces' => $highlighted_user_artpieces,
      'user_posts' => $user_posts,
      'admin_posts' => $admin_posts,
      'folders' => $folders,
      'sets' => $sets,
    ]);

  }

  public function index_wall() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/kozosseg');
    }

    // Élményképek
    $photos = $this->DB->find('photos', [
      'conditions' => ['joy' => 1],
      'order' => 'approved DESC',
      'limit' => 6,
    ]);
    // Feltört kapszulákok
    // ..

    $events = $this->Mongo->find_array('events',
      ['public' => 1,],
      [
        'limit' => 12,
        'sort' => ['created' => -1]
      ]
    );

    $this->set([
      'photos' => $photos,
      'events' => $events,
    ]);
  }

  public function members() {

    $query = $this->params->query;
    $query = _unset($query, ['oldal', 'r', 'elem', 'sorrend', 'kereses']);

    $conditions = [];

    if (@$query['kulcsszo'] != '') {
      if (is_numeric($query['kulcsszo'])) {
        $conditions += ['id' => $query['kulcsszo']];
      } elseif (@$query['eleje'] == 1) {
        $conditions += ['OR' => [
          'name LIKE' => $query['kulcsszo'] . '%',
          'link LIKE' => $query['kulcsszo'] . '%',
          'nickname LIKE' => $query['kulcsszo'] . '%',
        ]];
      } else {
        $conditions += ['OR' => [
          'name LIKE' => '%' . $query['kulcsszo'] . '%',
          'link LIKE' => '%' . $query['kulcsszo'] . '%',
          'nickname LIKE' => '%' . $query['kulcsszo'] . '%',
        ]];
      }
    }

    if (@$query['mulaposok'] == 1) {
      $conditions += ['artpiece_count >' => 0];
    }

    if (@$query['fotosok'] == 1) {
      $conditions += ['photo_count >' => 0];
    }

    if (@$query['kovetettek'] == 1 && $this->user) {
      $me = $this->Mongo->first('users', [
        'user_id' => $this->user['id']
      ]);
      $followeds = _json_decode(@$me['follow_users']);
      if (count($followeds) > 0) {
        $conditions += ['id' => $followeds];
      }
    }

    $total_count = $this->DB->count('users', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count,
    ];

    $order_options = [
      'abc-novekvo' => 'Név szerint',
      'aktivitas-csokkeno' => 'Aktivitás, csökkenő',
      'aktivitas-novekvo' => 'Aktivitás, növekvő',
      'mulap-csokkeno' => 'Műlap, csökkenő',
      'mulap-novekvo' => 'Műlap, növekvő',
      'foto-csokkeno' => 'Fotó, csökkenő',
      'foto-novekvo' => 'Fotó, növekvő',
      'szerkesztes-csokkeno' => 'Szerkesztés, csökkenő',
      'szerkesztes-novekvo' => 'Szerkesztés, növekvő',
    ];

    $order = $this->Search->build_order($this->params->query, 'points DESC', [
      'abc' => 'name'
    ]);

    $users = $this->DB->find('users', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => $order,
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Tagjaink',
      '_active_submenu' => 'Tagjaink',
      '_bookmarkable' => true,
      '_sidemenu' => true,
      '_breadcrumbs_menu' => true,
      '_title_row' => true,

      'users' => $users,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'query' => $query,
      'order_options' => $order_options,
    ]);
  }

  public function statistics() {
    // Kiolvassuk a napi user regeket
    $registrations = $this->DB->find('users', [
      'conditions' => ['active' => 1],
      'order' => 'activated ASC',
      'fields' => ['activated'],
    ]);

    // Havi bontásba rendezzük
    $registers_data = [];
    foreach ($registrations as $item) {
      if (!isset($registers_data[date('y.m.', $item['activated'])])) {
        $registers_data[date('y.m.', $item['activated'])] = 0;
      }
      $registers_data[date('y.m.', $item['activated'])] += 1;
    }
    // Sorba tesszük ASC szerint
    ksort($registers_data);


    // Kiolvassuk a napi kommenteket
    $aggregated = $this->Mongo->aggregate('comments', [
      ['$group' => ['_id' => '$_id', 'created' => ['$first' => '$created']]],
      ['$sort' => ['created' => 1]]
    ]);

    // Havi bontásba rendezzük
    $comments_data = [];
    foreach ($aggregated as $item) {
      if (!isset($comments_data[date('y.m.', $item->created)])) {
        $comments_data[date('y.m.', $item->created)] = 0;
      }
      $comments_data[date('y.m.', $item->created)] += 1;
    }
    // Sorba tesszük ASC szerint
    ksort($comments_data);


    // Kiolvassuk a napi műlapokat
    $aggregated = $this->Mongo->aggregate('artpieces', [
      ['$group' => ['_id' => '$_id', 'published' => ['$first' => '$published']]],
      ['$sort' => ['published' => 1]]
    ]);

    // Havi bontásba rendezzük
    $artpieces_data = [];
    foreach ($aggregated as $item) {
      if ($item->published == 0 || $item->published > time()
        || $item->published < APP['szoborlap_birth']) {
        continue;
      }
      if (!isset($artpieces_data[date('y.m.', $item->published)])) {
        $artpieces_data[date('y.m.', $item->published)] = 0;
      }
      $artpieces_data[date('y.m.', $item->published)] += 1;
    }
    // Sorba tesszük ASC szerint
    ksort($artpieces_data);

    $users = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'harakiri' => 0,
      ],
      'order' => 'points DESC',
      'limit' => 300,
    ]);

    $this->set([
      '_active_submenu' => 'Statisztikák',
      '_title' => 'Közösségi statisztikák',

      'users' => $users,
      'registers_data' => $registers_data,
      'comments_data' => $comments_data,
      'artpieces_data' => $artpieces_data,
    ]);

  }


  public function member_statistics($link) {
    $user = $this->DB->find_by_link('users', $link);

    if (!$user) {
      $this->redirect('/kozosseg/statisztikak', [texts('hibas_url'), 'warning']);
    }


    $artpieces_daily = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'view_day DESC',
      'limit' => 18,
    ]);

    $artpieces_weekly = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'view_week DESC',
      'limit' => 18,
    ]);


    $artpieces_total = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'view_total DESC',
      'limit' => 18,
    ]);


    // Kiolvassuk a napi kommenteket
    $aggregated = $this->Mongo->aggregate('comments', [
      ['$match' => ['user_id' => (int)$user['id']]],
      ['$group' => [
        '_id' => '$_id',
        'created' => ['$first' => '$created']
      ]],
      ['$sort' => ['created' => 1]]
    ]);

    // Havi bontásba rendezzük
    $comments_data = [];
    foreach ($aggregated as $item) {
      if (!isset($comments_data[date('y.m.', $item->created)])) {
        $comments_data[date('y.m.', $item->created)] = 0;
      }
      $comments_data[date('y.m.', $item->created)] += 1;
    }
    // Sorba tesszük ASC szerint
    ksort($comments_data);


    // Kiolvassuk a napi műlapokat
    $aggregated = $this->Mongo->aggregate('artpieces', [
      ['$match' => ['user_id' => (int)$user['id']]],
      ['$group' => ['_id' => '$_id', 'published' => ['$first' => '$published']]],
      ['$sort' => ['published' => 1]]
    ]);

    // Havi bontásba rendezzük
    $artpieces_data = [];
    foreach ($aggregated as $item) {
      if ($item->published == 0 || $item->published > time()
        || $item->published < APP['szoborlap_birth']) {
        continue;
      }
      if (!isset($artpieces_data[date('y.m.', $item->published)])) {
        $artpieces_data[date('y.m.', $item->published)] = 0;
      }
      $artpieces_data[date('y.m.', $item->published)] += 1;
    }
    // Sorba tesszük ASC szerint
    ksort($artpieces_data);

    $this->set([
      'user' => $user,
      'artpieces_daily' => $artpieces_daily,
      'artpieces_weekly' => $artpieces_weekly,
      'artpieces_total' => $artpieces_total,
      'comments_data' => $comments_data,
      'artpieces_data' => $artpieces_data,

      '_title' => $user['name'] . ' tagunk statisztikái'
    ]);
  }




  public function profile($link = false) {
    if (strpos($link, 'id:') !== false) {
      $p = explode(':', $link);
      $user = $this->DB->first('users', $p[1]);
      if ($user) {
        $this->redirect('/kozosseg/profil/' . $user['link']);
      }
    }

    if ($link == 'koztergep') {
      $this->redirect('/kozosseg');
    }

    $user = $this->DB->find_by_link('users', $link);

    if (!$user) {
      $this->redirect('/', ['Nincs ilyen felhasználó', 'warning']);
    }

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 18,
    ]);

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 3,
    ]);

    $folders = $this->DB->find('folders', [
      'conditions' => [
        'user_id' => $user['id'],
        'public' => 1,
      ],
      'order' => 'updated DESC',
      'limit' => 5,
    ]);

    $places = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'fields' => ['place_id', 'COUNT(id) AS artpiece_count'],
      'order' => 'artpiece_count DESC, place_id ASC',
      'group' => 'place_id',
      'limit' => 10,
    ]);


    $event_filters = [
      'user_id' => $user['id'],
      'public' => 1,
    ];
    if ($user['hide_location_events'] == 1) {
      $event_filters['type_id'] = ['$nin' => [7,8]];
    }

    $events = $this->Mongo->find_array('events',
      $event_filters,
      [
        'limit' => 30,
        'sort' => ['created' => -1]
      ]
    );

    $sets = $this->Mongo->find_array('sets',
      [
        'user_id' => $user['id'],
        'set_type_id' => 2,
        'artpieces' => ['$ne' => []]
      ],
      [
        'sort' => ['updated' => -1]
      ]
    );

    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'user_id' => $user['id'],
        'view_week >' => 0,
      ],
      'fields' => ['id', 'photo_slug', 'title', 'modified', 'view_day', 'view_week', 'view_total'],
      'order' => 'view_week DESC',
      'limit' => 10,
      'cached' => ['name' => __METHOD__ . '::' . $user['id'] . '_top_artpieces'],
    ]);

    $this->set([
      '_title' => $user['name'],
      '_title_row' => false,
      '_sidemenu' => false,
      '_model' => 'users',
      '_model_id' => $user['id'],
      '_followable' => $user['id'] == @$this->user['id'] ? false : true,
      '_editable' => $user['id'] == @$this->user['id'] ? '/tagsag/beallitasok' : '',

      'user' => $user,
      'artpieces' => $artpieces,
      'top_artpieces' => $top_artpieces,
      'posts' => $posts,
      'events' => $events,
      'sets' => $sets,
      'folders' => $folders,
      'places' => $places,
    ]);
  }

}