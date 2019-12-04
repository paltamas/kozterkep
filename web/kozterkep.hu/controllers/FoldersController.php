<?php
use Kozterkep\AppBase as AppBase;

class FoldersController extends AppController {

  public $_cache = [
    'view' => 'forever',
  ];

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_sidemenu' => true,
      '_active_menu' => 'Adattár',
      '_active_submenu' => 'Mappák',
    ]);
  }


  /**
   * Mappa kezdőlap a frissekkel
   */
  public function index() {

    $folder_count = $this->DB->count('folders', ['public' => 1]);

    $latest_folders = $this->DB->find('folders', [
      'conditions' => [
        'public' => 1,
      ],
      'limit' => 8,
      'order' => 'updated DESC',
      'debug' => false,
    ]);

    $top_folders = $this->DB->find('folders', [
      'conditions' => [
        'public' => 1,
      ],
      'limit' => 8,
      'order' => 'view_week DESC',
      'debug' => false,
    ]);

    $biggest_folders = $this->DB->find('folders', [
      'conditions' => [
        'public' => 1,
      ],
      'limit' => 8,
      'order' => 'file_count DESC',
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Mappák',
      'latest_folders' => $latest_folders,
      'top_folders' => $top_folders,
      'biggest_folders' => $biggest_folders,
      'folder_count' => $folder_count,
    ]);
  }


  /**
   * Publikus mappák listája
   * ezt máshova kell tenni
   */
  public function search() {
    $query = $this->params->query;
    $query = _unset($query, ['oldal', 'elem', 'sorrend', 'kereses']);

    $conditions = [];
    $conditions['public'] = 1;

    if (@$query['tag'] > 0) {
      $conditions['user_id'] = $query['tag'];
    }
    if (@$query['kulcsszo'] != '') {
      $conditions['name LIKE'] = '%' . $query['kulcsszo'] . '%';
    }

    $total_count = $this->DB->count('folders', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count
    ];

    $folders = $this->DB->find('folders', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'updated DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Publikus mappák keresése',
      'folders' => $folders,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'query' => $query,
    ]);
  }

  /**
   * Saját mappalistám létrehozási és egyéb funkciókkal
   */
  public function my() {
    $this->users_only();

    $folders = $this->DB->find('folders', [
      'conditions' => [
        'user_id' => $this->user['id']
      ],
      'order' => 'modified DESC'
    ]);

    if ($this->params->is_post) {
      $this->Validation->process($this->params->data, [
        'name' => 'not_empty',
      ], 'folders', [
        'defaults' => [
          'auth_key' => uid(),
          'user_id' => $this->user['id'],
          'created' => time(),
          'modified' => time(),
        ],
        'redirect' => [
          '/mappak/szerkesztes/{id}', 'A mappa létrejött. Ezen az oldalon módosíthatod a beállításokat és kezelheted a fájlokat.'
        ]
      ]);
    }

    $this->set([
      'folders' => $folders,
      '_title' => 'Saját mappák kezelése',
      '_shareable' => false,
    ]);
  }

  /**
   * Mappa megtekintő / szerkesztő nézete
   */
  public function edit() {
    $this->users_only();

    $folder = $this->DB->first('folders', [
      'id' => $this->params->id,
      'user_id' => $this->user['id']
    ]);

    if (!$folder) {
      $this->redirect('/mappak/sajat', [texts('hibas_url'), 'warning']);
    }

    $files = $this->DB->find('files', [
      'conditions' => [
        'folder_id' => $folder['id']
      ],
      'order' => 'rank ASC'
    ]);

    if ($this->params->is_post) {
      $this->Cache->delete($this->cache_name('/mappak/megtekintes/' . $folder['id']));

      if (isset($this->params->data['save_files'])) {
        foreach ($this->params->data['files'] as $file) {
          if (!$this->DB->first('files', [
            'id' => $file['id'],
            'user_id' => $this->user['id']
          ])) {
            $this->redirect('/mappak/szerkesztes/' . $folder['id'], [texts('jogosultsagi_hiba'), 'danger']);
          }

          $file['special_license'] = $file['license_type_id'] != $this->user['license_type_id'] ? 1 : 0;

          $success = $this->Validation->process($file, [
            'rank' => 'numeric',
            'original_name' => 'not_empty',
            'text' => 'string',
            'source' => 'string',
            'license_type_id' => 'numeric',
            'special_license' => 'tinyint',
          ], 'files', [
            'defaults' => [
              'modified' => time(),
            ],
            'cache' => [
              'cached-view-folders-index',
              'cached-view-folders-view-' . $folder['id'],
            ]
          ]);
          if (!$success) {
            $this->redirect('/mappak/szerkesztes/' . $folder['id']);
          }
        }
        $this->redirect('/mappak/szerkesztes/' . $folder['id'], [texts('sikeres_mentes'), 'success']);
      }

      if (isset($this->params->data['save_settings'])) {
        $this->Validation->process($this->params->data, [
          'save_settings' => 'unset',
          'name' => 'not_empty',
          'description' => 'string',
          'public' => 'tinyint',
        ], 'folders', [
          'defaults' => [
            'id' => $folder['id'],
            'modified' => time(),
          ],
          'redirect' => [
            '/mappak/szerkesztes/' . $folder['id'], texts('sikeres_mentes')
          ],
          'cache' => [
            'cached-view-folders-index',
            'cached-view-folders-view-' . $folder['id'],
          ]
        ]);
      }
    }

    $tabs = [
      'list' => [
        /*'Megtekintés' => [
          'link' => '/mappak/megtekintes/' . $folder['id'],
          'icon' => 'images',
        ],*/
        'Fájlok kezelése' => [
          'hash' => 'fajlok',
          'icon' => 'upload',
        ],
        'Mappa beállítások' => [
          'hash' => 'beallitasok',
          'icon' => 'edit',
        ],
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'fajlok',
        'class' => ''
      ]
    ];

    $this->set([
      'folder' => $folder,
      'files' => $files,
      '_title' => $folder['name'],
      '_tabs' => $tabs,
      '_viewable' => '/mappak/megtekintes/' . $folder['id'],
      '_bookmarkable' => false,
      '_shareable' => false,
    ]);
  }

  /**
   * Mappa megtekintő nézete
   */
  public function view() {
    $folder = $this->DB->first('folders', [
      'id' => $this->params->id
    ]);

    if ($folder['public'] == 0 && $folder['user_id'] != $this->user['id']) {
      $this->redirect('/mappak', [texts('hibas_url'), 'warning']);
    }

    $files = $this->DB->find('files', [
      'conditions' => [
        'folder_id' => $folder['id']
      ],
      'order' => 'rank'
    ]);

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'folder_id' => $folder['id'],
        'status_id' => 5,
      ],
      'order' => 'published DESC'
    ]);

    $this->set([
      'folder' => $folder,
      'files' => $files,
      'posts' => $posts,

      '_sidemenu' => false,
      '_title' => $folder['name'],
      '_model' => 'folders',
      '_model_id' => $folder['id'],
      '_shareable' => $folder['public'] == 1 ? true : false,
      '_followable' => true,
      '_editable' => '/mappak/szerkesztes/' . $folder['id'],
    ]);
  }


  /**
   * Fájl megnyitása, jogokat és méret logikákat is figyel
   * tud még típust és letöltési nevet is
   * Ha már amazonon van, redirekteljük simán
   */
  public function display_file() {
    $id = $this->params->id;
    $file = $this->DB->first('files', $id);

    if (!$file
      || ($file['permissions'] != '' && strpos($file['permissions'], '"' . $this->user['id'] . '"') === false)) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if (isset($this->params->query['type'])) {
      echo @$file['type'];
      exit;
    }

    //debug($file); exit;

    // A mutatandó fájlnév
    if (@$file['onesize'] != '' || @$file['sizes'] != '') {
      $filename = @$file['onesize'] != '' ? $file['onesize'] : $file['sizes'];
    } else {
      $filename = $file['name'];
    }

    /*
     * Kell egy kis késleltetésne azonnal mutassuk a másoltakat,
     * mert ugyed a replikát használjuk, oda picit később kerül át.
     */
    if ($file['copied'] > 0 && $file['copied'] < strtotime('-' . C_WS_S3['delay'] . ' seconds')) {
      $path = C_WS_S3['url'] . C_WS_S3['folder_prefix'] . $file['folder'] . '/' . $filename . '.' . $file['extension'];
      $this->redirect($path);
    } else {
      $path = realpath(CORE['PATHS']['DATA'] . '/s3gate/' . $file['folder'] . '/' . $filename . '.' . $file['extension']);
      $this->File->display($path, $file['type'], $file['original_name'] . '.' . $file['ext']);
    }
  }
}