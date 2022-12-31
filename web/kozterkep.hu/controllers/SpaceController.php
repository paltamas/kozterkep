<?php
use Kozterkep\AppBase as AppBase;

class SpaceController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);

    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->users_only();

    $this->set([
      '_active_menu' => 'Köztér',
      //'_fluid_layout' => true,
      '_shareable' => false,
    ]);
  }

  public function index() {
    $latests = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 42,
      'cached' => ['name' => __METHOD__ . '::latests'],
    ]);

    $updated_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'published <' => strtotime('-1 year'),
      ],
      'order' => 'long_updated DESC',
      'limit' => 16,
    ]);

    $admin_posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id' => [1, 11],
        'status_id' => 5,
        'published >' => strtotime('-2 weeks'),
      ],
      'order' => 'highlighted DESC, published DESC',
      'limit' => 2,
      //'cache' => ['name' => __METHOD__ . '::admin_posts'],
    ]);

    /*$posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id NOT' => [1, 11],
        'status_id' => 5,
        'published >' => strtotime('-1 months'),
      ],
      'order' => 'published DESC',
      'limit' => 5,
      //'cache' => ['name' => __METHOD__ . '::posts'],
    ]);*/

    $comment_filter = [];

    $editorial_forum_topics = $this->DB->find('forum_topics', [
      'conditions' => ['editorial' => 1],
      'type' => 'fieldlist',
      'fields' => ['id'],
    ], ['name' => 'editorial_forum_topics']);

    $comment_filter['forum_topic_id'] = ['$nin' => $editorial_forum_topics];
    // Ezzel kiszedjük a műlapokhoz adott szerkesztések első kommentjeit
    $comment_filter['no_wall'] = ['$exists' => false];

    if ($this->user['headitor'] != 1) {
      $comment_filter['hidden'] = ['$ne' => 1];
    }


    $comments = $this->Mongo->find_array('comments', $comment_filter + [
      'artpiece_edit' => ['$ne' => 1],
      'artpiece_edits_id' => ['$exists' => false],
      'artist_id' => ['$exists' => false],
      'place_id' => ['$exists' => false],
    ], [
      'sort' => ['created' => -1],
      'limit' => 30,
    ]);

    if ($this->user['headitor'] == 1) {
      $editcomments = $this->Mongo->find_array('comments', $comment_filter + [
          '$or' => [
            ['artpiece_edit' => 1],
            ['artpiece_edits_id' => ['$exists' => true]],
            ['artist_id' => ['$gt' => 0]],
            ['place_id' => ['$gt' => 0]],
          ]
        ], [
        'sort' => ['created' => -1],
        'limit' => 30,
      ]);

      $headitorcomments = $this->Mongo->find_array('comments', [
          'forum_topic_id' => 6
        ], [
        'sort' => ['created' => -1],
        'limit' => 30,
      ]);
    } else {
      $editcomments = $headitorcomments = [];
    }

    $invitations = $this->DB->find('artpieces', [
      'conditions' => [
        'invited_users LIKE' => '%"' . $this->user['id'] . '"%'
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'user_id']
    ]);
    $edits_for_me = $this->Mongo->find('artpiece_edits', [
      'receiver_user_id' => $this->user['id'],
      'user_id' => ['$ne' => $this->user['id']],
      'status_id' => 2,
    ], [
      'sort' => ['created' => -1]
    ]);

    $this->set([
      '_title' => 'Köztér',
      '_title_row' => false,
      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_bookmarkable' => false,
      '_active_submenu' => 'Köztér',

      'comments' => $comments,
      'editcomments' => $editcomments,
      'headitorcomments' => $headitorcomments,
      'latests' => $latests,
      'updated_artpieces' => $updated_artpieces,
      'admin_posts' => $admin_posts,
      //'posts' => $posts,
      'edits_for_me' => $edits_for_me,
      'invitations' => $invitations,
      'video_guides' => $this->Arrays->sort_by_key(sDB['video_guides'], 'time', -1),
    ]);
  }

  public function index_editorbox() {
    if ($this->user['headitor'] == 0 /*&& $this->user['admin'] == 0*/) {
      $this->redirect('/');
    }
    $submissions = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 2,
      ],
      'order' => 'submitted ASC',
    ]);

    $artpiece_ids = array_column($submissions, 'id');

    $waiting_edits = $this->Mongo->find('artpiece_edits', [
      'status_id' => ['$in' => [2,3]],
      '$or' => [
        ['receiver_user_id' => ['$in' => $this->Users->list('not_managing_artpieces', ['only_ids' => true])]],
        ['created' => ['$lt' => strtotime('-' . sDB['limits']['edits']['wait_days'] . ' days')]],
      ],
      'invisible' => ['$ne' => 1],
    ], [
      'sort' => ['created' => 1],
    ]);

    $edit_ids = [];
    foreach ($waiting_edits as $waiting_edit) {
      $edit_ids[] = (string)$waiting_edit->_id;
    }

    $votes_ = $this->Mongo->find('artpiece_votes', [
      '$or' => [
        ['artpiece_id' => ['$in' => $artpiece_ids]],
        ['edit_id' => ['$in' => $edit_ids]],
      ],
      'type_id' => ['$in' => [1,2,5,6]],
    ]);

    $votes = [];
    foreach ($votes_ as $vote) {
      if (isset($vote->artpiece_id)
        && in_array($vote->artpiece_id, $artpiece_ids)) {
        if (!isset($votes[$vote->artpiece_id])) {
          $votes[$vote->artpiece_id] = [];
        }
        $votes[$vote->artpiece_id][] = (array)$vote;
      }
      if (isset($vote->edit_id)
        && in_array($vote->edit_id, $edit_ids)) {
        if (!isset($votes[$vote->edit_id])) {
          $votes[$vote->edit_id] = [];
        }
        $votes[$vote->edit_id][] = (array)$vote;
      }
    }

    $this->set([
      'waiting_edits' => $waiting_edits,
      'submissions' => $submissions,
      'votes' => $votes,
      'vote_types' => sDB['artpiece_vote_types'],

      '_title' => 'Köztér Szerkdoboz',
    ]);
  }



  /**
   * Ajax betöltéses div-ben nem működik a kép logika normálisan
   */
  public function index_comments() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/kozter');
    }

    $comment_filter = [];
    // Editorial forum topikok leszűrése a nem admin/nem headitor userek számára
    if ($this->user['headitor'] == 0 /*&& $this->user['admin'] == 0*/) {
      $comment_filter['forum_topic_id'] = ['$nin' => $this->DB->find('forum_topics', [
        'conditions' => ['editorial' => 1],
        'type' => 'fieldlist',
        'fields' => ['id'],
      ], ['name' => 'editorial_forum_topics'])];
      $cache_name = 'latest_comments_head';
    } else {
      $cache_name = 'latest_comments_public';
    }

    $comments = $this->Mongo->find_array('comments', $comment_filter, [
      'sort' => ['created' => -1],
      'limit' => 30,
      //'cache' => ['name' => $cache_name],
    ]);

    $this->set([
      '_title' => 'Friss hozzászólások',
      'comments' => $comments,
    ]);
  }

  public function index_events() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/kozter');
    }

    $events = $this->Mongo->find_array('events', [
      'type_id' => ['$nin' => [7,8]] // érintést és kapszulát nem tesszük ide,
      // csak a "komoly" munka van :D
    ], [
      'sort' => ['created' => -1],
      'limit' => 30,
      'cache' => ['name' => 'latest_events'],
    ]);

    $this->set([
      '_title' => 'Friss események',
      'events' => $events,
    ]);
  }

  public function index_photos() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/kozter');
    }

    // Frissek
    $ids = $this->Users->list('not_managing_artpieces', ['only_ids' => true]);
    $id_list = '\'["' . implode('"]\',\'["', $ids) . '"]\'';
    $level_1_users = $this->Users->list('level_1', ['only_ids' => true]);
    $photos_to_check = $this->DB->find('photos', array(
      'fields' => array('id', 'slug', 'artpiece_id', 'user_id', 'approved'),
      'conditions' => "approved > " . strtotime('-30 days') . " AND user_id <> REPLACE(REPLACE(receiver_users, '[\"', ''), '\"]', '') "
        . " AND receiver_users IN (" . $id_list . ")"
        . " AND user_id NOT IN (" . implode(',', $level_1_users) . ")",
      'order' => 'approved DESC',
      'limit' => 50,
      'debug' => false,
    ));

    $this->set([
      '_title' => 'Friss fotók',
      'photos_to_check' => $photos_to_check,
    ]);
  }

  public function forum() {

    $forum_topic_list = $this->DB->find('forum_topics', [
      'conditions' => [
        'editorial' => 0,
        'classic' => 1
      ],
      'type' => 'list',
      'fields' => [
        "CONCAT('forum-', id) AS id",
        "CONCAT('Fórum: ', title) AS name"
      ],
      'order' => 'name',
    ], ['name' => 'public_forum_topics_list']);

    if ($this->user['headitor'] == 1 || $this->user['admin'] == 1) {
      $forum_topic_list = ['forum-6' => 'FőszerkSzoba'] + $forum_topic_list;
    }


    $filters = [];

    if ($this->user['headitor'] != 1) {
      $filters['hidden'] = ['$ne' => 1];
    }

    $filters['no_wall'] = ['$exists' => false];
    // Editorial kommentek és Editorial forum topikok leszűrése a nem admin/nem headitor userek számára
    if ($this->user['headitor'] == 0 && $this->user['admin'] == 0) {
      $filters['forum_topic_id'] = ['$nin' => $this->DB->find('forum_topics', [
        'conditions' => ['editorial' => 1],
        'type' => 'fieldlist',
        'fields' => ['id'],
      ], ['name' => 'editorial_forum_topics'])];
      $filters['artpiece_edits_id'] = ['$exists' => false];
    }

    // URL-ben jövő szűrések
    $search_filters = $this->Comments->build_filters($this->params->query, @$this->user['id']);
    if (count($search_filters) > 0) {
      $filters['$and'] = $search_filters;
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => APP['comments']['thread_count']
    ];


    $latest_comments = $this->Mongo->find_array('comments', $filters, [
      'sort' => ['created' => -1],
      'limit' => $pagination['limit'],
      'skip' => ($pagination['page']-1) * $pagination['limit'],
    ]);

    $this->set([
      '_title' => 'Fórum',
      '_active_submenu' => 'Fórum',

      'pagination' => $pagination,
      'latest_comments' => $latest_comments,
      'forum_topic_list' => $forum_topic_list,
    ]);

  }


  public function forum_topic() {
    $forum_topic = $this->DB->first('forum_topics', @$this->params->id);

    // Jog és létezés
    if (!$forum_topic ||
      ($this->user['headitor'] == 0 && $this->user['admin'] == 0
        && $forum_topic['editorial'] == 1)) {
      $this->redirect('/kozter/forum');
    }

    $filters = ['$and' => []];

    // Fórum támában vagyunk
    $filters['$and'][] = ['forum_topic_id' => (int)$this->params->id];

    $search_filters = $this->Comments->build_filters($this->params->query, @$this->user['id']);
    if (count($search_filters) > 0) {
      $filters['$and'] = array_merge($filters['$and'], $search_filters);
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => APP['comments']['thread_count']
    ];

    $latest_comments = $this->Mongo->find_array('comments', $filters, [
      'sort' => ['created' => -1],
      'limit' => $pagination['limit'],
      'skip' => ($pagination['page']-1) * $pagination['limit'],
    ]);

    $this->set([
      '_title' => $forum_topic['title'] . ' fórum',
      '_active_submenu' => 'Fórum',

      'forum_topic' => $forum_topic,
      'pagination' => $pagination,
      'latest_comments' => $latest_comments,
    ]);
  }


  public function comment_threads() {
    $threads = [];

    $limit = @$this->params->query['mennyi'] > 0
      ? (int)$this->params->query['mennyi'] : 5;

    $filters = [];
    $filters['parent_answered_id'] = ['$exists' => true];

    // Editorial kommentek leszűrése a nem admin/nem headitor userek számára
    if ($this->user['headitor'] == 0 && $this->user['admin'] == 0) {
      $filters['artpiece_edits_id'] = ['$exists' => false];
      $filters['$or'] = [
        ['hidden' => 0],
        ['hidden' => ['$exists' => false]]
      ];
    }


    // Ha fórumtéma ID-t dobtunk be
    if ($this->params->id > 0) {
      $filters['forum_topic_id'] = (int)$this->params->id;
    } else {
      // Főszerk párbeszédet nyitna nem főszerk
      if (!$this->Users->is_head($this->user)) {
        $filters['forum_topic_id'] = ['$ne' => 6];
      }
    }

    $filters['user_id'] = ['$gt' => 0];

    // Legutolsó 5 különböző ősös komment
    $latest_comments = $this->Mongo->aggregate('comments', [
      ['$match' => $filters],
      ['$group' => [
        '_id' => '$parent_answered_id', // csak ebbe lehet betenni az ős ID-t
        'last_id' => ['$last' => '$_id'],
        'text' => ['$last' => '$text'],
        'created' => ['$last' => '$created'],
        'user_id' => ['$last' => '$user_id'],
        'user_name' => ['$last' => '$user_name'],
      ]],
      ['$sort' => ['created' => -1]],
      ['$limit' => $limit],
    ]);

    foreach ($latest_comments as $key => $latest_comment) {
      $latest_comment = (array)$latest_comment;
      $key = $latest_comment['created'];

      $threads[$key]['latest_comment'] = $latest_comment;
      // Kiszedem, mennyi komment van a folyamban
      $threads[$key]['comment_count'] = $this->Mongo->count('comments', [
        'parent_answered_id' => $latest_comment['_id']
      ]);
      // Kiszedem az őst is
      $parent = $this->Mongo->first('comments', [
        '_id' => $latest_comment['_id']
      ]);
      if ($parent) {
        $threads[$key]['parent_comment'] = $parent;
      } else {
        // Nincs ős, töröljük a párbeszédet, mert ez valami hiba
        unset($threads[$key]);
      }
    }

    krsort($threads);

    $this->set([
      '_title' => 'Párbeszédek',
      '_active_submenu' => 'Fórum',

      'threads' => $threads,
    ]);
  }


  public function comment_thread() {

    $filters = [];

    $filters['$and'][] = ['$or' => [
      ['parent_answered_id' => $this->params->id],
      ['_id' => $this->params->id],
    ]];

    $comments = $this->Mongo->find_array('comments', $filters, [
      'sort' => ['created' => -1],
    ]);

    // Főszerk párbeszédet nyitna nem főszerk
    if (!$this->Users->is_head($this->user) && @$comments[0]['forum_topic_id'] == 6) {
      $this->redirect('/kozter/forum');
    }

    $this->set([
      '_title' => 'Párbeszéd',
      '_active_submenu' => 'Fórum',

      'comments' => $comments,
    ]);
  }


  public function comment() {
    $comment = $this->Mongo->first('comments', ['_id' => $this->params->id]);

    if (!$comment && !$this->Request->is('ajax')) {
      $this->redirect('/', ['Nincs ilyen hozzászólás', 'danger']);
    }

    // Ha nem ajax kérés, akkor kitaláljuk, hova kell dobni
    if (!$this->Request->is('ajax')) {
      $url = $this->Comments->thread_url($comment);
      if ($url) {
        $this->redirect($url);
      }
    }

    if (@$comment['artpiece_id'] > 0 && (@$comment['forum_topic_id'] > 0) === false) {
      $commented_artpiece = $this->DB->find_by_id('artpieces', $comment['artpiece_id'], [
        'fields' => ['user_id']
      ]);
    } else {
      $commented_artpiece = false;
    }

    $this->set([
      '_title' => 'Hozzászólás kezelése',
      'comment' => $comment,
      'commented_artpiece' => $commented_artpiece,
    ]);
  }


  public function my_artpieces() {

    $invitations = $this->DB->find('artpieces', [
      'conditions' => [
        'invited_users LIKE' => '%"' . $this->user['id'] . '"%'
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'user_id']
    ]);


    $this->set([
      '_title' => 'Műlapjaim',
      '_active_submenu' => 'Műlapjaim',

      'invitations' => $invitations,
      'work_artpieces' => $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => [1,2,3,4,6],
          'user_id' => $this->user['id']
        ],
        'order' => 'modified DESC',
        'cached' => ['name' => __METHOD__ . '::' . $this->user['id'] . '_work_artpieces'],
      ]),
      'modified_artpieces' => $this->Artpieces->get_modified_list($this->user['id'], 12),
      'latest_artpieces' => $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => 5,
          'user_id' => $this->user['id'],
        ],
        'order' => 'published DESC',
        'limit' => 6,
        'cached' => ['name' => __METHOD__ . '::' . $this->user['id'] . '_latest_artpieces'],
      ]),

      'top_artpieces' => $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => 5,
          'user_id' => $this->user['id'],
          'view_week >' => 0,
        ],
        'fields' => ['id', 'photo_slug', 'title', 'modified', 'view_day', 'view_week', 'view_total'],
        'order' => 'view_week DESC',
        'limit' => 10,
        'cached' => ['name' => __METHOD__ . '::' . $this->user['id'] . '_top_artpieces'],
      ]),
    ]);
  }

  public function edits() {
    $edits_by_me = $this->Mongo->find('artpiece_edits', [
      'user_id' => $this->user['id'],
      'receiver_user_id' => ['$ne' => $this->user['id']],
      'status_id' => 2,
      'before_shared' => ['$ne' => 1],
    ], [
      'sort' => ['created' => -1]
    ]);

    $edits_for_me = $this->Mongo->find('artpiece_edits', [
      'receiver_user_id' => $this->user['id'],
      'user_id' => ['$ne' => $this->user['id']],
      'status_id' => 2,
    ], [
      'sort' => ['created' => -1]
    ]);

    $waiting_edits = $this->Mongo->find('artpiece_edits', [
      'status_id' => ['$in' => [2,3]],
      'before_shared' => ['$ne' => 1],
      '$or' => [
        ['receiver_user_id' => ['$in' => $this->Users->list('not_managing_artpieces', ['only_ids' => true])]],
        ['created' => ['$lt' => strtotime('-' . sDB['limits']['edits']['wait_days'] . ' days')]],
      ],
      'invisible' => ['$ne' => 1],
    ], [
      'sort' => ['created' => 1],
    ]);


    $filters = ['$and' => []];

    $filters['$and'][] = ['invisible' => ['$ne' => 1]];
    $filters['$and'][] = ['own_edit' => ['$ne' => 1]];
    $filters['$and'][] = ['before_shared' => ['$ne' => 1]];

    if (@$this->params->query['statusz'] > 0) {
      $filters['$and'][] = ['status_id' => (int)$this->params->query['statusz']];
    }
    if (@$this->params->query['tag'] > 0) {
      $filters['$and'][] = ['user_id' => (int)$this->params->query['tag']];
    } elseif (@$this->params->query['tag'] == 'altalam' && $this->user['id'] > 0) {
      $filters['$and'][] = ['user_id' => $this->user['id']];
    } elseif (@$this->params->query['tag'] == 'nekem' && $this->user['id'] > 0) {
      $filters['$and'][] = [
        'user_id' => ['$ne' => $this->user['id']],
        'receiver_user_id' => $this->user['id'],
      ];
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => 25
    ];

    //debug($filters);

    if (@$this->params->query['rendezes'] == 'novekvo') {
      $sort = ['created' => 1];
    } elseif (@$this->params->query['rendezes'] == 'csokkeno') {
      $sort = ['created' => -1];
    } elseif (@$this->params->query['rendezes'] == 'jovahagyas') {
      $sort = ['approved' => -1];
    } else {
      $sort = ['created' => -1];
    }

    $edits = $this->Mongo->find('artpiece_edits', $filters, [
      'sort' => $sort,
      'limit' => $pagination['limit'],
      'skip' => ($pagination['page']-1) * $pagination['limit'],
    ]);

    //$edits_total = $this->Mongo->count('artpiece_edits', $filters);

    $this->set([
      '_title' => 'Szerkesztések',
      '_active_submenu' => 'Szerkesztések',

      'edits' => $edits,
      //'edits_total' => $edits_total,
      'pagination' => $pagination,
      'edits_by_me' => $edits_by_me,
      'edits_for_me' => $edits_for_me,
      'waiting_edits' => $waiting_edits,
    ]);
  }


  public function events() {

    $filters = ['$and' => []];

    $filters['$and'][] = ['type_id' => [
      '$nin' => sDB['events_hidden_from_artpage_history']
    ]];

    if (@$this->params->query['engem'] > 0) {
      $filters['$and'][] = ['related_users' => $this->user['id']];
    }

    if (@$this->params->query['tipus'] > 0) {
      $filters['$and'][] = ['type_id' => (int)$this->params->query['tipus']];
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => 25
    ];

    //debug($filters);

    $events = $this->Mongo->find_array(
      'events',
      $filters,
      [
        'sort' => ['created' => -1],
        'limit' => $pagination['limit'],
        'skip' => ($pagination['page']-1) * $pagination['limit']
      ]
    );

    $this->set([
      '_title' => 'Laptörténet',
      '_active_submenu' => 'Laptörténet',

      'pagination' => $pagination,
      'events' => $events,
      '_sidemenu' => true,
    ]);

  }

  public function headitorium() {
    $this->users_only('headitor');

    $this->redirect('/kozter', ['A FőszerkSzoba megszűnt, a funkcióit a Köztér kezdőlapon találod.', 'warning']);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => [2,5],
        // Hogy a régi nemszavazottak ne keveredjenek ide
        'published >' => strtotime('-6 months')
      ],
      'order' => 'published ASC',
    ]);

    $artpiece_ids = array_column($latest_artpieces, 'id');

    $votes_ = $this->Mongo->find('artpiece_votes', [
      'artpiece_id' => ['$in' => $artpiece_ids],
      'type_id' => ['$in' => [4]],
    ]);

    $votes = [];
    foreach ($votes_ as $vote) {
      if (!isset($votes[$vote->artpiece_id])) {
        $votes[$vote->artpiece_id] = [];
      }
      $votes[$vote->artpiece_id][] = (array)$vote;
    }


    $this->set([
      '_title' => 'FőszerkSzoba',
      '_active_submenu' => 'FőszerkSzoba',

      'events' => $this->Mongo->find_array('events', [
        'type_id' => ['$in' => [1,2,3,4,5,9]],
      ], [
        'sort' => ['created' => -1],
        'limit' => 50,
      ]),

      'publish_pauseds' => $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => [2],
          'publish_pause' => 1
        ],
        'order' => 'submitted DESC'
      ]),

      'open_questions' => $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => [2,5],
          'open_question' => 1
        ],
        'order' => 'published ASC'
      ]),

      'possible_publishers' => $this->DB->find('users', [
        'conditions' => [
          'active' => 1,
          'blocked' => 0,
          'harakiri' => 0,
          'user_level' => 0,
          'artpiece_count >' => sDB['user_scores']['settings']['artpiece_limit'],
          'last_here >' => strtotime('-3 months'),
        ],
        'order' => 'artpiece_count DESC'
      ]),

      'latest_artpieces' => $latest_artpieces,
      'old_artpieces' => $old_artpieces,
      'votes' => $votes,

    ]);
  }

}