<?php
use Kozterkep\AppBase as AppBase;

class PagesController extends AppController {

  public $_cache = [
    'roviden-rolunk',
    'idovonal'
  ];

  public function __construct() {
    AppBase::__construct(APP);

    $this->user = self::$_user;
    $this->params = self::$_params;

    $page = $this->DB->find_by_path('pages', $this->Request->path());
    $this->page = $page;
    if ($page) {
      $this->set([
        '_active_menu' => 'Miez?',
        '_active_submenu' => $page['title'],
        'page' => $page,
        '_title' => $page['title'],
      ]);
      if ($page['layout'] != '') {
        $this->layout($page['layout']);
      }
    }
  }

  /**
   * Kezdőlap
   */
  public function index() {
    $underlineds = $this->DB->find('artpieces', [
      'conditions' => [
        'underlined' => 1,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 7,
    ]);
    $underlined_ids = [];
    foreach ($underlineds as $key => $underlined) {
      $underlined_ids[] = $underlined['id'];
      $underlineds[$key]['descriptions'] = $this->Mongo->find_array('artpiece_descriptions',
        ['artpieces' => $underlined['id']],
        ['sort' => [/*'main' => -1,*/ 'lang' => -1, 'approved' => 1]]
      );
    }

    $harvesteds = $this->DB->query("SELECT * FROM artpieces WHERE (harvested = 1 OR underlined = 1) AND id NOT IN (" . implode(',', $underlined_ids) . ") AND status_id = 5 ORDER BY published DESC LIMIT 24");

    $blog_friends = $this->Mongo->find_array('blogfriends', [
      'last_post_time' => ['$gt' => strtotime('-8 weeks')],
    ], [
      'sort' => ['last_post_time' => -1],
      'limit' => 2,
      'projection' => [
        'feed_id' => 1,
        'last_post' => 1,
      ]
    ]);

    $latests = $this->DB->find('artpieces', [
      'conditions' => [
        'harvested' => 0,
        'underlined' => 0,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 36,
    ]);

    $random_place = $this->DB->first('places', [
      'checked' => 1,
      'artpiece_count >' => 4,
      'artpiece_count <' => 700,
    ], [
      'order' => 'RAND()'
    ]);
    $map_artpieces = [];
    $artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'place_id' => (int)$random_place['id'],
        'status_id' => 5,
      ],
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);
    foreach ($artpieces as $artpiece) {
      $map_artpieces[] = $artpiece['id'];
    }

    $highlighted_user = $this->DB->first('users', [], [
      'order' => 'highlighted DESC'
    ]);
    $highlighted_user_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'user_id' => $highlighted_user['id'],
        'status_id' => 5,
      ],
      'limit' => 4,
      'order' => 'published DESC',
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'user_id', 'published'],
    ]);
    $top_users_latest = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'harakiri' => 0,
      ],
      'order' => 'points_latest DESC',
      'limit' => 10,
    ], ['name' => 'top_10_latest_users_by_points']);


    $latest_places = $this->DB->find('places', [
      'conditions' => [
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'order' => 'checked_time DESC',
      'limit' => 10,
    ]);

    $latest_artists = $this->DB->find('artists', [
      'conditions' => [
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'order' => 'created DESC',
      'limit' => 10,
    ]);

    $artist_births = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(born_date,6,5)' => date('m-d')
      ],
      'order' => 'name',
    ]);

    $artist_deaths = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(death_date,6,5)' => date('m-d')
      ],
      'order' => 'name',
    ]);

    $artist_photos = $this->DB->find('photos', array(
      'conditions' => [
        'OR' => [
          'artist_id >' => 0,
          'portrait_artist_id >' => 0,
        ]
      ],
      'limit' => 3,
      'order' => 'approved DESC',
      'debug' => false,
    ));

    $latest_unveils = $this->DB->query("SELECT *, replace(replace(replace(replace(replace(replace(replace(replace(replace(last_date,
'-1-', '-01-'),'-2-', '-02-'),'-3-', '-03-'),'-4-', '-04-'),'-5-', '-05-'),'-6-', '-06-'),'-7-', '-07-'),'-8-', '-08-'),'-9-', '-09-')
as mdate from artpieces where last_date > 0 and last_date <= '" . date('Y-m-d') . "' and artpiece_condition_id = 1 and last_date not like '%-0-0%' and status_id = 5 order by mdate desc limit 9");

    $this->set([
      'harvesteds' => $harvesteds,
      'underlineds' => $underlineds,
      'latests' => $latests,
      'latest_unveils' => $latest_unveils,
      'latest_artists' => $latest_artists,
      'artist_photos' => $artist_photos,
      'artist_births' => $artist_births,
      'artist_deaths' => $artist_deaths,
      'latest_places' => $latest_places,
      'random_place' => $random_place,
      'map_artpieces' => $map_artpieces,
      'highlighted_user' => $highlighted_user,
      'highlighted_user_artpieces' => $highlighted_user_artpieces,
      'top_users_latest' => $top_users_latest,

      'total_count' => $this->DB->count('artpieces', ['status_id' => 5]),
      'total_photos' => $this->DB->count('photos', ['artpiece_id >' => 0]),
      'user_count' => $this->DB->count('users', [[
        'artpiece_count >' => 0,
        'photo_count >' => 0,
        'edit_other_count >' => 0,
      ]]),
      'blog_friends' => $blog_friends,

      '_title' => '',
      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_title_row' => false,
    ]);
  }


  /**
   *
   * Ezzel hívjuk a sima szöveges oldalakat
   *
   * @param $name
   * @param array $arguments
   */
  public function __call($name, $arguments = []) {
    if (!$this->page) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }
  }



  /**
   * Szöveges oldalak extra funkciókkal
   */

  public function contact_us () {

    $data = null;

    if ($this->Request->is('post')) {
      $data = $this->params->data;

      $processable = true;

      if (!$this->user) {
        if ($data['name'] == '' || $data['message'] == '' || $data['subject'] == ''
          || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
          || @$data['disclaimer'] != 1) {
          $this->flash('Kérjük, minden adatot adj meg és fogadd el az adatkezelésünket.', 'warning');
          $processable = false;
        } elseif (!$this->Form->check_captcha($data)) {
          $this->flash('Az ellenőrző mező értéke nem volt helyes, kérjük próbáld újra.', 'warning');
          $processable = false;
        }
      } else {
        if ($data['message'] == '' || $data['subject'] == '') {
          $this->flash('Kérjük, minden adatot adj meg.', 'warning');
          $processable = false;
        }
      }

      if ($processable) {

        $email_data = [
          'member' => $this->user ? '<a href="' . CORE['BASE_URL'] . '/kozosseg/profil/' . $this->user['link'] . '">' . $this->user['name'] . '</a>' : 'Látogató',
          'url' => isset($data['url']) ? $data['url'] : '-',
          'name' => $this->user ? $this->user['name'] : $data['name'],
          'email' => $this->user ? $this->user['email'] : $data['email'],
          'subject' => $data['subject'],
          'message' => $data['message'],
        ];

        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'template' => 'system',
            'to' => CORE['ADMIN_EMAIL'],
            'reply_to' => $email_data['email'],
            'subject' => 'KT-kapcs: ' . html_entity_decode($data['subject']),
            'body' => texts('emails/contact_form', $email_data)
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect('/', ['<strong>Köszönjük üzenetedet!</strong> Amint lehet, visszajelzünk neked.', 'success']);
      }
    }


    $this->set([
      'data' => $data,

      '_title' => 'Kapcsolatfelvétel'
    ]);
  }


  public function user_guides () {
    $category_id = 11;

    $postcategory = sDB['post_categories'][$category_id];

    $posts_highlighted = $this->DB->find('posts', [
      'conditions' => [
        'highlighted' => 1,
        'postcategory_id' => $category_id,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
    ]);

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'highlighted' => 0,
        'postcategory_id' => $category_id,
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 26,
    ]);

    $this->set([
      '_title' => 'Felhasználói segédletek',

      'posts_highlighted' => $posts_highlighted,
      'posts' => $posts,
      'postcategory' => $postcategory,
      'video_guides' => $this->Arrays->sort_by_key(sDB['video_guides'], 'rank'),
    ]);
  }


  public function about_us() {
    $this->set([
      'total_count' => $this->DB->count('artpieces', ['status_id' => 5]),
      'total_photos' => $this->DB->count('photos', ['artpiece_id >' => 0]),
      'user_count' => $this->DB->count('users', [[
        'artpiece_count >' => 0,
        'photo_count >' => 0,
        'edit_other_count >' => 0,
      ]]),

      '_title' => 'Röviden rólunk',
    ]);
  }

  public function contribution_terms() {
    $this->set([
      '_title' => 'Működési elvek',
    ]);
  }

  public function legal_terms() {
    $this->set([
      '_title' => 'Jogi nyilatkozat',
    ]);
  }

  public function movement() {
    $this->set([
      '_title' => 'Köztérkép Mozgalom',
    ]);
  }

  public function privacy_policy() {
    $this->set([
      '_title' => 'Adatkezelési szabályzat',
    ]);
  }

  public function donate_us() {
    $this->set([
      'total_count' => $this->DB->count('artpieces', ['status_id' => 5]),
      'total_photos' => $this->DB->count('photos', ['artpiece_id >' => 0]),
      'user_count' => $this->DB->count('users', [[
        'artpiece_count >' => 0,
        'photo_count >' => 0,
        'edit_other_count >' => 0,
      ]]),
      'donations' => $this->DB->find('donations', [
        'order' => 'date DESC'
      ]),
      '_title' => 'Támogass minket!',
    ]);
  }

  public function impressum() {
    $headitors = $this->DB->find('users', [
      'conditions' => [
        'headitor' => 1,
      ],
      'order' => 'id DESC'
    ]);
    $headitor_were = $this->DB->find('users', [
      'conditions' => [
        'headitor_was' => 1,
        'headitor' => 0,
      ],
      'order' => 'name ASC'
    ]);

    $this->set([
      'headitors' => $headitors,
      'headitor_were' => $headitor_were,

      '_title' => 'Impresszum',
    ]);
  }



  /////////////////////// KÖZÖS FUNKCIÓK


  /**
   * Oldal szerkesztése
   * egyelőre csak adminoknak érhető el
   */
  public function _edit() {
    $this->users_only();

    $page = $this->DB->first('pages', $this->params->id);
    if (!$page) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if ($this->user['admin'] == 1 && $this->Request->is('post')) {
      $data = $this->params->data_;

      $content = htmlentities($data['content']);
      $data['content'] = $content;

      if ($this->DB->update('pages', [
          'title' => $data['title'],
          'content' => $data['content'],
          'user_id' => $this->user['id'],
        ], $this->params->id)) {

        // Történet beszúrása változás esetén
        if ($page['content'] !== $data['content']) {
          // Módosítás, csak változó kontentnél
          if ($this->DB->update('pages', ['modified' => time()], $this->params->id));
          $this->Mongo->insert('page_edits', [
            'page_id' => (int)$page['id'],
            'previous' => [
              'content' => $page['content'],
            ],
            'actual' => [
              'content' => $data['content'],
            ],
            'created' => time(),
            'user_id' => $this->user['id'],
          ]);
          $this->flash('A változástörténetet bővítettük.', 'info', 'div', APP['sessions']['alert_remove']);
        }

        $this->flash(texts('sikeres_mentes'), 'info', 'div', APP['sessions']['alert_remove']);
      } else {
        $this->flash(texts('mentes_hiba'), 'danger');
      }

      $this->redirect('referer');
    }

    $this->set([
      'page' => $page,
      '_title' => '"' . $page['title'] . '" szerkesztése',
    ]);
  }


  /**
   * Oldal szerkesztési történet
   * közös, minden oldal használja
   */
  public function _history() {
    $this->users_only();

    $page = $this->DB->first('pages', $this->params->id);
    if (!$page) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $edits = $this->Mongo->find_array('page_edits', [
      'page_id' => $page['id']
    ],
    ['sort' => ['created' => -1]]
    );

    $this->set([
      'edits' => $edits,
      'page' => $page,
      '_title' => '"' . $page['title'] . '" szerkesztési története',
    ]);
  }


}