<?php
use Kozterkep\AppBase as AppBase;

class PostsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Közösség',
      '_active_submenu' => 'Blogok',
      '_sidemenu' => false,
    ]);
  }

  public function index() {
    $highlighted_posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id NOT' => [1,10,11],
        'highlighted' => 1,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 2,
    ]);

    $highlighted_ids = [];
    foreach ($highlighted_posts as $highlighted_post) {
      $highlighted_ids[] = $highlighted_post['id'];
    }

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id NOT' => [1,10,11],
        'id NOT' => $highlighted_ids,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 20,
    ]);

    $this->set([
      '_title' => 'Friss blogbejegyzések',
      '_title_row' => false,

      'highlighted_posts' => $highlighted_posts,
      'posts' => $posts,
    ]);
  }

  public function search() {
    $query = $this->params->query;

    $categories = $this->Blog->category_list(true);

    $conditions = [];

    $conditions['status_id'] = 5;

    if (@$query['kulcsszo'] != '') {
      $conditions[] = [
        'title LIKE' => '%' . $query['kulcsszo'] . '%',
        'intro LIKE' => '%' . $query['kulcsszo'] . '%',
        'text LIKE' => '%' . $query['kulcsszo'] . '%',
      ];
    }

    if (isset($query['tema']) && $query['tema'] != 'barmilyen'
      && isset($categories[$query['tema']])) {
      $conditions['postcategory_id'] = $categories[$query['tema']]['id'];
    }

    if (isset($query['tag']) && $query['tag'] != 'barki') {
      $user = $this->MC->t('users', $query['tag']);
      if ($user) {
        $conditions['user_id'] = $user['id'];
      }
    }

    $total_count = $this->DB->count('posts', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count,
    ];

    $posts = $this->DB->find('posts', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'published DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Bejegyzések keresése',

      'posts' => $posts,
      'pagination' => $pagination,
      'total_count' => $total_count,
    ]);
  }

  public function member($link) {
    $user = $this->DB->find_by_link('users', $link);
    $blog_name = $user['blog_title'] != '' ? $user['blog_title'] : $user['name'];

    if (!$user) {
      $this->redirect('/blogok', [texts('hibas_url'), 'warning']);
    }

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'user_id' => $user['id'],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 26,
    ]);

    $this->set([
      '_title' => $blog_name,
      '_editable' => '/blogok/sajat',

      'posts' => $posts,
      'user' => $user,
    ]);
  }

  public function my() {
    if (isset($this->params->query['letrehozas'])) {
      $post_id = $this->DB->insert('posts', [
        'title' => 'Egy új bejegyzés...',
        'status_id' => 1,
        'postcategory_id' => 8,
        'created' => time(),
        'modified' => time(),
        'user_id' => $this->user['id'],
      ]);
      if ($post_id){
        $this->redirect('/blogok/szerkesztes/' . $post_id, ['Létrehoztuk új blogbejegyzésedet.', 'success']);
      } else {
        $this->redirect('/blogok/sajat', [texts('varatlan_hiba'), 'warning']);
      }
    }


    $query = $this->params->query;

    $categories = $this->Blog->category_list(true);

    $conditions = [];

    if (@$query['kulcsszo'] != '') {
      $conditions[] = [
        'title LIKE' => '%' . $query['kulcsszo'] . '%',
        'intro LIKE' => '%' . $query['kulcsszo'] . '%',
        'text LIKE' => '%' . $query['kulcsszo'] . '%',
      ];
    }

    if (isset($query['tema']) && $query['tema'] != 'barmilyen'
      && isset($categories[$query['tema']])) {
      $conditions['postcategory_id'] = $categories[$query['tema']]['id'];
    }

    if (isset($query['statusz']) && $query['statusz'] != 'minden') {
      $conditions['status_id'] = (int)$query['statusz'];
    }

    $conditions['user_id'] = $this->user['id'];

    $total_count = $this->DB->count('posts', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count,
    ];

    $posts = $this->DB->find('posts', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'status_id ASC, published DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Saját blogom kezelése',

      'posts' => $posts,
      'pagination' => $pagination,
    ]);
  }

  public function category($slug) {
    $postcategory = $this->Blog->category_list(true)[$slug];

    if (!isset($postcategory['id'])) {
      $this->redirect('/blogok', [texts('hibas_url'), 'warning']);
    }

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'postcategory_id' => $postcategory['id'],
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 26,
    ]);


    $this->set([
      '_active_menu' => $postcategory['id'] == 1 ? 'Hírek' : 'Közösség',
      '_active_submenu' => $postcategory['id'] == 1 ? 'Gépház hírek' : 'Blogok',
      '_title' => 'Blogbejegyzések ' . $postcategory['name'] . ' témában',

      'posts' => $posts,
      'postcategory' => $postcategory,
    ]);
  }

  public function view() {
    $post = $this->DB->first('posts', $this->params->id);

    if (!$post || ($post['status_id'] != 5 && !$this->Users->owner_or_head($post, $this->user))) {
      $this->redirect('/blogok', [texts('hibas_url'), 'warning']);
    }

    $user = $this->DB->first('users', $post['user_id']);
    $blog_name = $user['blog_title'] != '' ? $user['blog_title'] : $user['name'];

    $this->set([
      '_title_row' => false,
      '_title' => $post['title'],
      '_breadcrumbs_menu' => [
        'Blogok '=> '/blogok',
        $blog_name => '/blogok/tag/' . $user['link'],
      ],
      '_model' => 'posts',
      '_model_id' => $post['id'], // hogy ne számoljunk view-kat
      '_shareable' => $post['status_id'] == 5 ? true : false,
      '_editable' => '/blogok/szerkesztes/' . $post['id'],
      '_block_caching' => in_array($post['status_id'], [2,5]) ? false : true,

      'post' => $post,
      'highlighted_artpiece' => $post['artpiece_id'] > 0 ? $this->DB->first('artpieces', $post['artpiece_id']) : false,
      'artist' => $post['artist_id'] > 0 ? $this->DB->first('artists', $post['artist_id']) : false,
      'place' => $post['place_id'] > 0 ? $this->DB->first('places', $post['place_id']) : false,
      'folder' => $post['folder_id'] > 0 ? $this->DB->first('folders', $post['folder_id']) : false,
      'set' => $post['set_id'] != '' ? $this->Mongo->first('sets', $post['set_id']) : false,
      'latest_posts' => $this->DB->find('posts', [
        'conditions' => [
          'user_id' => $post['user_id'],
          'status_id' => 5,
          'id <>' => $post['id'],
        ],
        'order' => 'published DESC',
        'limit' => 5,
      ]),

    ]);
  }

  public function edit() {
    $post = $this->DB->first('posts', $this->params->id);

    if (!$post || !$this->Users->owner_or_head($post, $this->user)) {
      $this->redirect('/blogok', [texts('hibas_url'), 'warning']);
    }

    if ($this->params->is_post) {

      $data = $this->params->data;

      // A html miatt a tisztítatlant kérjük be
      $data['text'] = $this->params->data_['text'];
      // ha nem htmlformatted a bejegyzés, akkor csak a 3 engedélyezett tag maradhat
      if ($post['html_formatted'] == 0) {
        $data['text'] = strip_tags($data['text'], '<strong><aimage><ffile>');
      }

      if ($this->user['admin'] == 1) {
        $this->DB->update('posts', [
          'super_high' => (int)$data['super_high'],
          'highlighted' => (int)$data['highlighted'],
          'html_formatted' => (int)@$data['html_formatted'],
          'comments_blocked' => (int)@$data['comments_blocked'],
        ], $post['id']);
        $this->Cache->delete('cached-view-pages-index');
        $this->Cache->delete('cached-view-community-index');
        $this->Cache->delete('cached-view-posts-index');
      }

      // Ez az erősebb
      if ($data['photo_id'] > 0) {
        $photo = $this->DB->first('photos', $data['photo_id']);
        if ($photo) {
          $data['photo_slug'] = $photo['slug'];
          $data['photo_artpiece_id'] = $photo['artpiece_id'];
        }
      } elseif ($data['file_id'] > 0) {
        $file = $this->DB->first('files', $data['file_id']);
        if ($file) {
          $data['file_slug'] = $file['onesize'];
          $data['file_folder_id'] = $file['folder_id'];
        }
      }

      if (in_array($data['photo_id'], [0,''])) {
        $data['photo_id'] = 0;
        $data['photo_slug'] = '';
        $data['photo_artpiece_id'] = 0;
      }
      if ($data['photo_id'] > 0 || in_array($data['file_id'], [0,''])) {
        $data['file_id'] = 0;
        $data['file_slug'] = '';
        $data['file_folder_id'] = 0;
      }

      foreach ($data as $field => $value) {
        if (in_array($field, ['artpiece_id', 'artist_id', 'place_id', 'folder_id']) && !is_numeric($value)) {
          $data[$field] = 0;
        }
      }

      $data['connected_artpieces'] = urldecode($data['connected_artpieces']);

      // Kapcsolódó műlapok

      $save = $this->Validation->process($data, [
        'files' => 'unset', // html-editor nyomja be a posztba
        'save_post' => 'unset',
        'highlighted' => 'unset',
        'super_high' => 'unset',
        'html_formatted' => 'unset',
        'comments_blocked' => 'unset',
        'new_connected_artpiece' => 'unset',
        'artpiece_title' => 'unset',
        'artist_name' => 'unset',
        'place_name' => 'unset',
        'postcategory_id' => 'numeric',
        'photo_id' => 'numeric',
        'photo_slug' => '',
        'photo_artpiece_id' => 'numeric',
        'file_id' => 'numeric',
        'file_slug' => '',
        'file_folder_id' => 'numeric',
        'artpiece_id' => 'numeric',
        'artist_id' => 'numeric',
        'place_id' => 'numeric',
        'folder_id' => 'numeric',
        'set_id' => '',
        'connected_artpieces' => '',
        'title' => 'not_empty',
        'intro' => '',
        'text' => '',
        'sources' => '',
      ], 'posts', [
        'defaults' => [
          'id' => $post['id'],
          'modified' => time(),
        ]
      ]);

      if ($save) {
        if ($post['status_id'] == 5) {
          $post = $this->DB->first('posts', $post['id']);
          $this->Blog->delete_caches($post, $this->user);
        }
        $this->redirect('/blogok/szerkesztes/' . $post['id'], texts('sikeres_mentes'));
      } else {
        $this->redirect('/blogok/szerkesztes/' . $post['id'], [texts('mentes_hiba'), 'danger']);
      }
    }

    if (isset($this->params->query['publikalas'])) {
      if (strlen($post['text']) < sDB['limits']['posts']['text_min_length']) {
        $this->redirect('/blogok/szerkesztes/' . $post['id'], ['Írj legalább ' . sDB['limits']['posts']['text_min_length'] . ' karaktert a bejegyzésbe, hogy publikálhassuk.', 'warning']);
      } elseif ($post['title'] == 'Egy új bejegyzés...') {
        $this->redirect('/blogok/szerkesztes/' . $post['id'], ['Biztosan ezzel a bejegyzés címmel szeretnél publikálni?', 'warning']);
      }
      $updates = [
        'status_id' => 5,
        'modified' => time(),
        'view_total' => 0,
        'view_day' => 0,
        'view_week' => 0,
      ];

      if ($post['published'] == 0) {
        $updates['published'] = time();
      }
      $this->DB->update('posts', $updates, $post['id']);
      $this->Blog->delete_caches($post, $this->user);
      $this->redirect('/blogok/szerkesztes/' . $post['id'], ['A bejegyzést publikáltuk!', 'success']);
    }

    if (isset($this->params->query['visszavetel'])) {
      $this->DB->update('posts', [
        'status_id' => 1,
        'modified' => time(),
      ], $post['id']);
      if ($post['status_id'] == 5) {
        $this->Blog->delete_caches($post, $this->user);
      }
      $this->redirect('/blogok/szerkesztes/' . $post['id'], ['A bejegyzést visszavettük, most már nem publikus.', 'info']);
    }

    if (isset($this->params->query['torles'])) {
      $this->DB->delete('posts', ['id' => $post['id']]);
      if ($post['status_id'] == 5) {
        $this->Blog->delete_caches($post, $this->user);
      }
      $this->redirect('/blogok/sajat', ['A bejegyzést töröltük.', 'info']);
    }


    $postcategories_ = sDB['post_categories'];
    $postcategories = [];
    $admin_categories = [];
    foreach ($postcategories_ as $key => $item) {
      if ($item[1] == 1) {
        $admin_categories[] = $key;
      }
      if ($this->user['admin'] == 0 && $item[1] == 1 && $post['postcategory_id'] != $key) {
        continue;
      }
      $postcategories[$key] = $item[0];
    }


    $connected_artpieces = [];
    $connected_artpiece_ids = _json_decode($post['connected_artpieces']);
    if (count($connected_artpiece_ids)) {
      $connected_artpieces = $this->DB->find('artpieces', [
        'conditions' => [
          'id' => array_values($connected_artpiece_ids),
          'status_id' => 5,
        ]
      ]);
    }


    $this->set([
      '_title' => 'Bejegyzés szerkesztése',
      '_shareable' => false,
      '_bookmarkable' => false,
      '_viewable' => $this->Html->link_url('', ['post' => $post]),
      '_breadcrumbs_menu' => [
        'Blogok' => '/blogok',
        'Saját blogom' => '/blogok/sajat',
      ],

      'post' => $post,
      'connected_artpieces' => $connected_artpieces,
      'postcategories' => $postcategories,
      'admin_categories' => $admin_categories,
      'folders' => $this->Arrays->id_list($this->DB->find('folders', [
        'conditions' => [
          'user_id' => $this->user['id'],
          'public' => 1,
        ],
        'order' => 'name',
        'type' => 'list',
        'fields' => ['id', 'name']
      ]), 'name'),
      'sets' => $this->Arrays->id_list($this->Mongo->find_array('sets',
        [
          'user_id' => (int)$this->user['id'],
          'set_type_id' => 2,
        ],
        [
          'sort' => ['name' => 1],
          'projection' => [
            '_id' => 1,
            'name' => 1,
          ],
          'idlist' => true,
        ]
      ), 'name'),
    ]);
  }
}