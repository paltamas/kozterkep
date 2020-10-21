<?php
use Kozterkep\AppBase as AppBase;

class ArtistsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Alkotók',
    ]);
  }

  public function index() {

    $artist_count = $this->DB->count('artists');

    $random_artist = $this->DB->first('artists', [
      'checked' => 1,
      'artpiece_count >' => 4,
      'artpiece_count <' => 100,
    ], [
      'order' => 'RAND()',
    ]);

    $top_artists = $this->DB->find('artists', [
      'order' => 'artpiece_count DESC',
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

    $births = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(born_date,6,5)' => date('m-d')
      ],
      'order' => 'name',
    ]);

    $deaths = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(death_date,6,5)' => date('m-d')
      ],
      'order' => 'name',
    ]);

    $posts = $this->DB->find('posts', [
      'conditions' => [
        'status_id' => 5,
        'artist_id >' => 0,
      ],
      'order' => 'published DESC',
      'limit' => 3,
    ]);

    $photos = $this->DB->find('photos', array(
      'conditions' => [
        'OR' => [
          'artist_id >' => 0,
          'portrait_artist_id >' => 0,
        ]
      ],
      'limit' => 6,
      'order' => 'approved DESC',
      'debug' => false,
    ));

    $this->set([
      '_title' => 'Alkotók',
      '_active_submenu' => 'Áttekintés',
      '_bookmarkable' => true,
      '_sidemenu' => true,
      '_breadcrumbs_menu' => true,
      '_title_row' => true,


      'random_artist' => $random_artist,
      'births' => $births,
      'deaths' => $deaths,
      'artist_count' => $artist_count,
      'top_artists' => $top_artists,
      'latest_artists' => $latest_artists,
      'photos' => $photos,
      'posts' => $posts,
    ]);
  }


  public function search() {
    $query = $this->params->query;
    $query = _unset($query, ['oldal', 'r', 'elem', 'sorrend', 'kereses']);

    $conditions = [];

    if (@$query['ellenorizetlen'] == 1) {
      $conditions += ['checked' => 0];
    } elseif (@$query['ellenorizetlen'] === '0') {
      $conditions += ['checked' => 1];
    }

    if (@$query['kulcsszo'] != '') {
      $conditions = $this->Artists->build_keyword_condition($query, $conditions);
    }

    if (@$query['muveszeti_ag'] > 0) {
      $conditions += ['profession_id' => $query['muveszeti_ag']];
    }

    if (@$query['kovetettek'] == 1 && $this->user) {
      $me = $this->Mongo->first('users', [
        'user_id' => $this->user['id']
      ]);
      $followeds = _json_decode(@$me['follow_artists']);
      if (count($followeds) > 0) {
        $conditions += ['id' => $followeds];
      }
    }

    if (@$query['nemzetiseg'] == 'magyar') {
      $conditions += ['english_form' => 0];
    } elseif (@$query['nemzetiseg'] == 'nem_magyar') {
      $conditions += ['english_form' => 1];
    }

    if (@$query['ev_eddig'] > 0) {
      $conditions += ['SUBSTR(born_date,1,4) <=' => $query['ev_eddig']];
      $conditions += ['born_date NOT LIKE' => ''];
    }
    if (@$query['ev_ettol'] > 0) {
      $conditions += ['SUBSTR(death_date,1,4) >=' => $query['ev_ettol']];
      $conditions += ['born_date NOT LIKE' => ''];
    }

    if (@$query['alkoto_besorolas'] == 'szemelyek') {
      $conditions += [
        'artistgroup' => 0,
        'corporation' => 0,
      ];
    } elseif (@$query['alkoto_besorolas'] == 'alkotocsoportok') {
      $conditions += [
        'artistgroup' => 1,
      ];
    } elseif (@$query['alkoto_besorolas'] == 'tarsasagok') {
      $conditions += [
        'corporation' => 1,
      ];
    }

    $total_count = $this->DB->count('artists', $conditions);

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
      'mulap-csokkeno' => 'Műlap, csökkenő',
      'mulap-novekvo' => 'Műlap, növekvő',
      'nezettseg-csokkeno' => 'Nézettség, csökkenő',
      'rogzites-csokkeno' => 'Rögzítés, csökkenő',
      'rogzites-novekvo' => 'Rögzítés, növekvő',
    ];

    $order = $this->Search->build_order($this->params->query, 'last_name ASC, first_name ASC', [
      'abc' => 'last_name ASC, first_name ASC'
    ]);

    //$order = $this->Search->build_order($this->params->query);

    $artists = $this->DB->find('artists', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => $order,
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Alkotók keresése',
      '_active_submenu' => 'Alkotók keresése',
      '_bookmarkable' => true,
      '_sidemenu' => true,
      '_breadcrumbs_menu' => true,
      '_title_row' => true,

      'artists' => $artists,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'query' => $query,
      'order_options' => $order_options,
    ]);

  }


  public function view() {

    $artist = $this->DB->first('artists', $this->params->id);

    if (!$artist) {
      $this->redirect('/alkotok', [texts('hibas_url'), 'warning']);
    }

    $photos = $this->DB->find('photos', [
      'conditions' => [
        'OR' => [
          'artist_id' => $artist['id'],
          'portrait_artist_id' => $artist['id'],
        ]
      ],
      'order' => 'approved ASC'
    ]);

    $sign_photos = $this->DB->find('photos', [
      'conditions' => [
        'sign_artist_id' => $artist['id'],
      ],
      'order' => 'approved ASC'
    ]);

    $top_artpieces = $this->DB->find('artpieces', [
      'conditions' => "status_id = 5 AND JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')",
      'order' => 'view_total DESC',
      'limit' => 2,
    ]);

    $latest_artpieces = $this->DB->find('artpieces', [
      'conditions' => "status_id = 5 AND JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')",
      'order' => 'published DESC',
      'limit' => 14,
    ]);

    $map_artpieces = [];
    $artpieces_by_time = [];

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => "status_id = 5 AND JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')",
      'fields' => ['id', 'title', 'photo_id', 'photo_slug', 'dates']
    ]);

    if ($artpieces) {

      foreach ($artpieces as $artpiece) {
        if ($artist['artpiece_count'] <= APP['map']['max_id']) {
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
        'artist_id' => $artist['id'],
      ],
      'order' => 'published DESC',
      'limit' => 5,
    ]);


    $artist_descriptions = $this->Mongo->find_array('artist_descriptions',
      ['artist_id' => $artist['id']],
      ['sort' => ['approved' => 1]]
    );

    $tabs = [
      'list' => [
        'Adatlap' => [
          'hash' => 'adatlap',
          'icon' => 'user',
        ],
        'Fotóalbum' => [
          'hash' => 'fotolista',
          'icon' => 'images',
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
      'artist' => $artist,
      'photos' => $photos,
      'sign_photos' => $sign_photos,
      'artist_descriptions' => $artist_descriptions,
      'top_artpieces' => $top_artpieces,
      'latest_artpieces' => $latest_artpieces,
      'map_artpieces' => $map_artpieces,
      'artpieces_by_time' => $artpieces_by_time,
      'comment_count' => $this->Mongo->count('comments', [
        'artist_id' => $artist['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      'posts' => $posts,

      '_title' => strip_tags($this->Artists->name($artist)),
      '_active_submenu' => 'Alkotók keresése',
      '_sidemenu' => false,
      '_breadcrumbs_menu' => ['Alkotók' => '/alkotok/attekintes'],
      '_tabs' => $tabs,
      '_model' => 'artists',
      '_model_id' => $artist['id'],
      '_shareable' => true,
      '_followable' => true,
      '_editable' => '/alkotok/szerkesztes/' . $artist['id'],
    ]);
  }


  /**
   * Szerkkomm ajaxdiv
   */
  public function view_editcom() {
    $artist = $this->DB->first('artists', $this->params->id);

    if (!$artist) {
      $this->redirect('/alkotok', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      'artist' => $artist,
      'comment_count' => $this->Mongo->count('comments', [
        'artist_id' => $artist['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      '_title' => strip_tags($this->Artists->name($artist)) . ' SzerkKomm',
    ]);
  }


  public function anniversaries() {

    $day = strtotime('today 00:00');
    $date_end = ' a mai napon';

    if (isset($this->params->query['honap']) && isset($this->params->query['nap'])) {
      $month = $this->params->query['honap'];
      $day = $this->params->query['nap'];
      $date = _cdate(date('Y') . '-' . $month . '-' . $day) . ' 00:00';

      if (strtotime($date) > 0) {
        $day = strtotime($date);
        $date_end = ' ' . _time($date, 'Y.m.d.') . ' napon';
      }
    }

    if (isset($this->params->query['leptetes']) && is_numeric($this->params->query['leptetes'])) {
      $step = $this->params->query['leptetes'];
      $new_date = $day + ($step*24*60*60);
      $this->redirect('/alkotok/evfordulok?honap=' . date('n', $new_date) . '&nap=' . date('j', $new_date));
    }

    $births = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(born_date,6,5)' => date('m-d', $day)
      ],
      'order' => 'name',
    ]);

    $deaths = $this->DB->find('artists', [
      'conditions' => [
        'SUBSTR(death_date,6,5)' => date('m-d', $day)
      ],
      'order' => 'name',
    ]);

    $this->set([
      '_title' => 'Évfordulók' . $date_end,
      '_active_submenu' => 'Évfordulók',

      'births' => $births,
      'deaths' => $deaths,
      'day' => $day,
    ]);

  }


  public function photosearch() {
    $query = $this->params->query;

    $artist_conditions = [];
    $artists = [];

    // Alkotókra keresünk, ha keresés van, és az innen nyert ID-ket engedjük rá a fotókeresésre
    if (@$query['kulcsszo'] != '') {
      $artist_conditions = $this->Artists->build_keyword_condition($query, $artist_conditions);
    }

    if (@$query['muveszeti_ag'] > 0) {
      $artist_conditions += ['profession_id' => $query['muveszeti_ag']];
    }

    if (count($artist_conditions) > 0) {
      $artists = $this->DB->find('artists', [
        'type' => 'list',
        'conditions' => $artist_conditions,
      ]);
    }


    // Keresés
    $conditions = [];

    $conditions[] = [
      'before_shared' => 0,
    ];

    if (count($artist_conditions) > 0) {
      if (count($artists) > 0) {
        if (@$query['kep_tipus'] == 'portrek') {
          $conditions['OR'] = [
            'artist_id' => array_keys($artists),
            'portrait_artist_id' => array_keys($artists),
          ];
        } elseif (@$query['kep_tipus'] == 'szignok') {
          $conditions['OR'] = ['sign_artist_id' => array_keys($artists)];
        } else {
          $conditions['OR'] = [
            'artist_id' => array_keys($artists),
            'sign_artist_id' => array_keys($artists),
            'portrait_artist_id' => array_keys($artists),
          ];
        }
      } else {
        // Ne találjunk majd semmit a fotóknál
        $conditions = ['artist' => 2];
      }
    } else {
      if (@$query['kep_tipus'] == 'portrek') {
        $conditions['OR'] = [
          'artist_id >' => 0,
          'portrait_artist_id >' => 0,
        ];
      } elseif (@$query['kep_tipus'] == 'szignok') {
        $conditions['OR'] = ['sign_artist_id >' => 0];
      } else {
        $conditions['OR'] = [
          'artist_id >' => 0,
          'sign_artist_id >' => 0,
          'portrait_artist_id >' => 0,
        ];
      }
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $this->DB->count('photos', $conditions)
    ];

    $photos = $this->DB->find('photos', array(
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'approved DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ));

    $this->set([
      '_title' => 'Alkotó portrék és szignók keresése',
      '_active_submenu' => 'Képkereső',
      '_sidemenu' => false,

      'photos' => $photos,
      'artists' => $artists,
      'pagination' => $pagination,
    ]);
  }


  public function edit() {
    $this->users_only();

    $artist = $this->DB->first('artists', $this->params->id);

    if (!$artist || !$this->Users->owner_or_head($artist, $this->user)) {
      $this->redirect('/alkotok', [texts('hibas_url'), 'warning']);
    }


    if (@$this->params->query['foto_torles'] > 0) {
      $deleted = $this->Photos->delete($this->params->query['foto_torles'], $this->user);
      if ($deleted) {
        $this->redirect('/alkotok/szerkesztes/' . $artist['id'] . '#fotok', texts('sikeres_torles'));
      } else {
        $this->redirect('/alkotok/szerkesztes/' . $artist['id'], [texts('varatlan_hiba'), 'danger']);
      }
    }

    if ($this->params->is_post) {

      // BEOLVASZTÁS
      if (isset($this->params->data['merge'])) {
        if ($this->params->data['target_artist_id'] > 0) {
          $merge = $this->Artists->merge($this->user, $artist['id'], $this->params->data['target_artist_id']);
          if ($merge) {
            $this->redirect('/alkotok/szerkesztes/' . $this->params->data['target_artist_id'],
              'Sikeresen lezajlott a beolvasztás. ' . (int)$merge['artpieces'] . ' műlapon és ' . (int)$merge['waiting_edits'] . ' várakozó szerkesztésben cseréltünk alkotót. A beolvasztott alkotót töröltük.');
          } else {
            $this->redirect('/alkotok/szerkesztes/' . $artist['id'], [texts('varatlan_hiba'), 'danger']);
          }
        }
      }

      // ADALÉK szerkesztés
      if (isset($this->params->data['save_description'])) {
        $updated = $this->Mongo->update('artist_descriptions', [
          'text' => $this->params->data['text'],
          'modified' => time(),
        ], ['_id' => $this->params->data['description_id']]);
        if ($updated) {
          $this->redirect('/alkotok/szerkesztes/' . $artist['id'] . '#adalekok', texts('sikeres_mentes'));
        } else {
          $this->redirect('/alkotok/szerkesztes/' . $artist['id'] . '#adalekok', [texts('varatlan_hiba'), 'danger']);
        }
      }

      // KÉPEK szerkesztése
      if (isset($this->params->data['save_photos'])) {
        foreach ($this->params->data['photolist'] as $photo_data) {
          $this->DB->update('photos', [
            'text' => $photo_data['text'],
            'source' => $photo_data['source'],
            'modified' => time(),
          ], $photo_data['id']);
        }
        $this->redirect('/alkotok/szerkesztes/' . $artist['id'] . '#fotok', texts('sikeres_mentes'));
      }


      // SIMA mentés
      if (isset($this->params->data['save_settings'])) {
        // Most lett ellenőrzött
        if ($artist['checked'] == 0 && $this->params->data['checked'] == 1) {
          $this->params->data['checked_time'] = time();
        }

        if ($this->params->data['born_date_year'] > 0) {
          $this->params->data['born_date'] = _cdate(
            $this->params->data['born_date_year'] . '-' .
            $this->params->data['born_date_month'] . '-' .
            $this->params->data['born_date_day']
          );
        }

        if ($this->params->data['death_date_year'] > 0) {
          $this->params->data['death_date'] = _cdate(
            $this->params->data['death_date_year'] . '-' .
            $this->params->data['death_date_month'] . '-' .
            $this->params->data['death_date_day']
          );
        }


        /**
         * Név módosulásoknál műlap cache-t törlünk
         */
        if ($artist['name'] != $this->params->data['name']
          || $artist['artist_name'] != $this->params->data['artist_name']
          || $artist['first_name'] != $this->params->data['first_name']
          || $artist['last_name'] != $this->params->data['last_name']
          || $artist['before_name'] != $this->params->data['before_name']
          || $artist['english_form'] != $this->params->data['english_form']) {
          $artpieces = $this->DB->find('artpieces', [
            'conditions' => "JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')",
            'fields' => ['id'],
          ]);
          if (count($artpieces)) {
            foreach ($artpieces as $artpiece) {
              $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
            }
          }
        }

        $this->params->data = _unset($this->params->data, ['born_date_year', 'born_date_month', 'born_date_day', 'death_date_year', 'death_date_month', 'death_date_day']);

        $this->Validation->process($this->params->data, [
          'save_settings' => 'unset',
          'name' => 'not_empty',
          'artist_name' => '',
          'alternative_names' => '',
          'first_name' => '',
          'last_name' => 'not_empty',
          'website_url' => '',
          'admin_memo' => '',
          'inner_memo' => '',
          'born_date' => '',
          'born_place_name' => '',
          'death_date' => '',
          'death_place_name' => '',
          'english_form' => 'tinyint',
          'corporation' => 'tinyint',
          'artistgroup' => 'tinyint',
          'artpeople_id' => 'numeric',
          'profession_id' => 'numeric',
          'before_name' => 'numeric',
          'checked' => 'tinyint',
          'checked_time' => 'numeric',
        ], 'artists', [
          'defaults' => [
            'id' => $artist['id'],
            'modified' => time(),
          ],
          'redirect' => [
            '/alkotok/szerkesztes/' . $artist['id'], texts('sikeres_mentes')
          ],
          'cache' => 'cached-view-artists-view-' . $artist['id'],
        ]);
      }
    }

    $tabs = [
      'list' => [
        /*'Megtekintés' => [
          'link' => '/alkotok/megtekintes/' . $artist['id'],
          'icon' => 'user',
        ],*/
        'Szerkesztés' => [
          'hash' => 'szerkesztes',
          'icon' => 'edit',
        ],
        'Adalékok' => [
          'hash' => 'adalekok',
          'icon' => 'paragraph',
        ],
        'Fotók' => [
          'hash' => 'fotok',
          'icon' => 'images',
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

    $photos = $this->DB->find('photos', [
      'conditions' => [
        'portrait_artist_id' => $artist['id'],
        'artpiece_id' => 0,
      ],
      'order' => 'approved ASC'
    ]);

    $artpiece_photos = $this->DB->find('photos', [
      'conditions' => [
        'OR' => [
          'artist_id' => $artist['id'],
          'sign_artist_id' => $artist['id'],
        ],
        'artpiece_id >' => 0
      ],
      'order' => 'approved ASC'
    ]);

    $similars = $this->DB->find('artists', [
      'conditions' => [
        'OR' => [
          'name LIKE' => '%' . $artist['name'] . '%',
          'alternative_names LIKE' => '%' . $artist['name'] . '%',
        ],
        'id <>' => $artist['id'],
      ]
    ]);

    $this->set([
      'artist' => $artist,
      'artist_descriptions' => $this->Mongo->find_array('artist_descriptions',
        ['artist_id' => $artist['id']],
        ['sort' => ['approved' => 1]]
      ),
      'photos' => $photos,
      'artpiece_photos' => $artpiece_photos,
      'similars' => $similars,

      '_active_submenu' => 'Alkotók keresése',
      '_active_sidemenu' => '/alkotok/kereses',
      '_title' => '"' . $artist['name'] . '" szerkesztése',
      '_tabs' => $tabs,
      '_viewable' => '/alkotok/megtekintes/' . $artist['id'],
      '_bookmarkable' => false,
      '_sidemenu' => false,
      '_shareable' => false,
    ]);
  }


  public function edit_description() {
    if ($this->Request->is('ajax')
      && ($this->Users->is_head($this->user) || $this->user['id'] == CORE['USERS']['artists'])) {

      $description = $this->Mongo->first('artist_descriptions', ['_id' => $this->params->id]);
      $this->set([
        'description' => $description,
      ]);
    } else {
      $this->redirect('/');
    }
  }


}