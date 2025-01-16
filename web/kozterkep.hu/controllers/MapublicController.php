<?php
use Kozterkep\AppBase as AppBase;

class MapublicController extends AppController {

  // Egyelőre nincs bevezetve
  private $allowed_domains = [
    'http://localhost',
    'https://localhost',
    'http://localhost:3000',
    'https://localhost:3000',
    'https://app.kozterkep.hu',
    'https://dev.kozterkep.hu',
  ],
    $allowed_domain = false,
    $allowed_request = false;

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
    $this->data = $this->Request->data();
    $this->query = $this->Request->query();
    $this->MapublicLogic = new \Kozterkep\MapublicLogic();

    // Egyelőre nincs bevezetve
    $this->origin = isset($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : '*';

    if ($this->MapublicLogic->auth('*')) {
      $this->serveRoute();
    } else {
      $this->response([], 401);
    }
  }


  ///////////////////// VÉGPONTOK ---> /////////////////////
  public function parameters() {
    $parameters = sDB;
    unset($parameters['bots'], $parameters['user_roles'], $parameters['cookie_descriptions'], $parameters['limits'], $parameters['photo_quality'], $parameters['photo_sizes'], $parameters['hidden_edit_fields'], $parameters['artpiece_fields_empties'], $parameters['forum_topics'], $parameters['bookmark_types'], $parameters['set_types'], $parameters['set_types_public'], $parameters['user_scores'], $parameters['artpiece_vote_types'], $parameters['events_hidden_from_artpage_history'], $parameters['notification_types'], $parameters['model_parameters'], $parameters['license_transmissions'], $parameters['similar_excludes'], $parameters['video_guides'], $parameters['blog_friends'], $parameters['ww_parameter_types']);
    $parameters['parameters'] = $this->MC->t('parameters');
    $this->response($parameters);
  }


  // @todo: Szüret és kiemeltek
  public function highlighteds() {
    $harvesteds = $this->DB->query("SELECT * FROM artpieces WHERE (harvested = 1 OR underlined = 1) AND status_id = 5 ORDER BY published DESC LIMIT 12");

    $response = [];

    foreach ($harvesteds as $harvested) {
      $response[] = $this->buildArtpieceData($harvested, 2);
    }

    $this->response($response);
  }

  // @todo: get_artpieces_bounds és get_artpieces_radius kiszolgálása
  public function byLocation($query = false, $simple = false) {
    if (!$query) {
      $query = $this->query;
    }

    $response = [];

    if (!$this->MapublicLogic->checkParams($query, ['lat', 'lon', 'radius'])) {
      $this->response(['error' => 'Hiányos lekérdezés'], 400);
    }

    $data = $query;

    $lon = (float)$data['lon'];
    $lat = (float)$data['lat'];
    $radius = (float)$data['radius']; // méter!

    $artpieces_ = $this->Mongo->aggregate('artpieces', [
      [
        '$geoNear' => [
          'near' => [
            'coordinates' => [$lon, $lat] // persze, mongo fordítva szereti :]
          ],
          'distanceField' => 'distance',
          'maxDistance' => $radius,
          'spherical' => true,
          'query' => @$data['filter']
        ]
      ],
      ['$limit' => @$data['limit'] > 0 ? (int)$data['limit'] : 1000] // !
    ]);

    if (is_countable($artpieces_) && count($artpieces_) > 0) {
      foreach ($artpieces_ as $artpiece) {
        $item = [
          'i' => $artpiece->artpiece_id,
          'l' => $artpiece->location->coordinates,
          't' => $artpiece->title,
        ];
        if (!$simple) {
          $item = array_merge($item, [
            'p' => $artpiece->photo_slug,
            'd' => $artpiece->distance,
          ]);
        }

        $response[] = $item;
      }
    }

    $this->response($response);
  }

  // @todo: get_artpieces_bounds és get_artpieces_radius kiszolgálása
  public function byBounds() {
    $response = [];

    if (!$this->MapublicLogic->checkParams($this->query, ['nwlat', 'nwlon', 'selat', 'selon'])) {
      $this->response(['error' => 'Hiányos lekérdezés'], 400);
    }

    $data = $this->query;

    // Hiányzó paraméterek
    foreach ([
      'nelat' => 'nwlat',
      'nelon' => 'selon',
      'swlat' => 'selat',
      'swlon' => 'nwlon',
    ] as $param => $alter_param) {
      if (!array_key_exists($param, $data)) {
        $data[$param] = $data[$alter_param];
      }
    }

    // Ráhagyás, ha jön paraméterben
    if (@$data['padding'] > 0 && $data['padding'] < 1) {
      $p = $data['padding'];
      $lat_adj = ($data['nelat'] - $data['swlat']) * $p;
      $lon_adj = ($data['nelon'] - $data['swlon']) * $p;
      $data['nelat'] += $lat_adj;
      $data['nelon'] += $lon_adj;
      $data['swlat'] -= $lat_adj;
      $data['swlon'] -= $lon_adj;
    }

    // Óriáslekérdezés lehet, szóval a find_array nem OK; az object-et kell használni
    $artpieces_ = $this->Mongo->find('artpieces',
      ['location.coordinates' =>
        ['$geoWithin' =>
          ['$polygon' =>
            [
              [(float)$data['nwlon'], (float)$data['nwlat']],
              [(float)$data['nelon'], (float)$data['nelat']],
              [(float)$data['selon'], (float)$data['swlat']],
              [(float)$data['swlon'], (float)$data['selat']],
            ]
          ]
        ]
      ],
      [
        'limit' => @$data['limit'] > 0 ? (int)$data['limit'] : 2000,
        'sort' => ['artpiece_id' => -1]
      ]
    );

    if (is_countable($artpieces_) && count($artpieces_) > 0) {
      foreach ($artpieces_ as $artpiece) {
        $response[] = [
          'i' => $artpiece->artpiece_id,
          'l' => (array)$artpiece->location->coordinates,
          't' => $artpiece->title,
          'p' => $artpiece->photo_slug,
          'c' => @$artpiece->artpiece_condition_id,
          'l2' => @$artpiece->artpiece_location_id,
        ];
      }
    }

    $this->response($response);
  }

  // @todo: keresés, de picit részletesebben (keyword, title, artist, place - id vagy string; és ezek tényleg tudják)
  public function search() {
    if (!isset($this->query['keyword'])
      && !isset($this->query['title'])
      && !isset($this->query['artist'])
      && !isset($this->query['place'])) {
      $this->response(['error' => 'Hiányos lekérdezés'], 400);
    }

    $order = 'published DESC';

    $base_conditions = 'status_id = 5';

    if (isset($this->query['keyword']) || isset($this->query['title'])) {
      $q = $this->query['keyword'] ?? $this->query['title'];
      $or_conditions = [
        "title LIKE '%" . $q . "%'",
        "title_alternatives LIKE '%" . $q . "%'",
      ];
    }

    if (isset($this->query['keyword']) || isset($this->query['artist'])) {
      $q = $this->query['keyword'] ?? $this->query['artist'];
      $artists = _array($this->DB->find('artists', [
        'conditions' => ['name LIKE' => $q . '%'],
        'fields' => ['id'],
        'limit' => 10,
      ]), ['value_field' => 'id']);
      if (is_countable($artists) && count($artists) > 0) {
        foreach ($artists as $artis_id) {
          $or_conditions[] = "artists LIKE '%\"id\":" . $artis_id . "%'";
        }
      }
    }

    if (isset($this->query['keyword']) || isset($this->query['place'])) {
      $q = $this->query['keyword'] ?? $this->query['place'];
      $places = _array($this->DB->find('places', [
        'conditions' => ['name LIKE' => $q . '%'],
        'fields' => ['id'],
        'limit' => 10,
      ]), ['value_field' => 'id']);
      if (is_countable($places) && count($places) > 0) {
        foreach ($places as $place_id) {
          $or_conditions[] = "place_id = " . $place_id;
        }
      }
    }

    $conditions = $base_conditions . ' AND (' .  implode(' OR ', $or_conditions) . ')';

    $artpieces = $this->DB->find('artpieces', [
      'conditions' => $conditions,
      'order' => $order,
      'limit' => 500,
    ]);

    $response = [];

    foreach ($artpieces as $artpiece) {
      $response[] = $this->buildArtpieceData($artpiece, 1);
    }

    $this->response($response);
  }

  // @todo: listázás néhány paraméterrel
  // orderBy: uploaded,unveiled; sortBy: asc,desc
  // kiváltja a search
  public function list() {
    $response = [];
    $this->response($response);
  }

  // @todo: műlap ID alapján
  public function artpiece() {
    if (!$this->MapublicLogic->checkParams($this->query, ['id'])) {
      $this->response(['error' => 'Kötelező paraméterek: id'], 400);
    }

    $artpiece = $this->DB->first('artpieces', [
      'id' => (int)$this->query['id'],
      'status_id' => 5,
    ]);

    if (!$artpiece) {
      $this->response(['error' => 'Nem létező műlap'], 404);
    }

    $this->response($this->buildArtpieceData($artpiece, 2));
  }


  public function osm () {
    switch (true) {
      case (isset($this->query['list']) && isset($this->query['page'])):
        $response = [];
        $per_page = 5000;
        $artpieces = $this->Mongo->find('artpieces',
          [
            'artpiece_id' => [
              '$gte' => (int)$this->query['page'] * $per_page,
              '$lt' => ((int)$this->query['page']+1) * $per_page,
            ],
            'artpiece_condition_id' => ['$in' => [1,4,8,9]], // meglévők és láthatók
          ],
          [
            //'skip' => $per_page * (int)$this->query['page'],
            //'limit' => $per_page,
            'sort' => ['artpiece_id' => 1],
            'projection' => [
              'artpiece_id' => 1,
              'location' => 1,
              'title' => 1,
              'artist' => 1,
              'first_date' => 1,
              'last_date' => 1,
            ],
          ]
        );

        if (is_countable($artpieces) && count($artpieces) > 0) {
          foreach ($artpieces as $artpiece) {
            $response[] = [
              'i' => $artpiece->artpiece_id,
              'l' => (array)$artpiece->location->coordinates,
              't' => $artpiece->title,
              'a' => $artpiece->artist,
              'd' => [
                'fd' => $this->correctDate($artpiece->first_date),
                'ld' => $this->correctDate($artpiece->last_date),
              ],
            ];
          }
        }

        $this->response($response);
        break;


      case isset($this->query['id']):
        $artpiece = $this->DB->first('artpieces', [
          'id' => (int)$this->query['id'],
          'status_id' => 5,
        ]);
        if (!$artpiece) {
          $this->response(['error' => 'Nem létező műlap'], 404);
        }
        $this->response($this->buildArtpieceData($artpiece, 0));
        break;


      case isset($this->query['lat']) &&  isset($this->query['lon']):
        $this->response($this->byLocation(array_merge($this->query, ['radius' => 100]), true));
        break;
    }

    $this->response([], 404);
  }



  ///////////////////// ---> VÉGPONTOK /////////////////////




  private function serveRoute() {
    if (method_exists($this, $this->params->action)) {
      if (strtolower(@$_SERVER['REQUEST_METHOD']) == 'options') {
        $this->response(false);
      } else {
        $this->{$this->params->action}();
      }
    } else {
      $this->response([], 404);
    }
  }

  private function response($data, $http_status_code = 200) {
    http_response_code($http_status_code);
    header('Access-Control-Allow-Origin: ' . $this->origin);
    header('Access-Control-Allow-Headers: *');
    header('Content-Type: application/json; charset=utf-8');
    echo $data
      ? json_encode($data, JSON_FORCE_OBJECT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
      : '';
    die();
  }

  private function buildArtpieceData($artpiece, $complexity = 0) {
    if (is_numeric($artpiece)) {
      $artpiece = $this->MC->t('artpieces', $artpiece);
    }

    $artists = [];
    if (json_validate($artpiece['artists'])) {
      $artists_ = json_decode($artpiece['artists'], true);
      foreach ($artists_ as $artist) {
        $artists[] = [
          'id' => $artist['id'],
          'name' => $this->MC->t('artists', $artist['id'])['name'],
          'contributor' => $artist['contributor'],
          'profession' => [
            'id' => $artist['profession_id'],
            'name' => sDB['artist_professions'][$artist['profession_id']][0],
          ],
        ];
      }
    }

    $item = [
      'id' => $artpiece['id'],
      'titles' => [
        'main' => $artpiece['title'],
        'alternatives' => $artpiece['title_alternatives'],
        'english' => $artpiece['title_en'],
      ],
      'country' => [
        'id' => $artpiece['country_id'],
        'name' => @sDB['countries'][$artpiece['country_id']][1],
      ],
      'county' => [
        'id' => $artpiece['county_id'],
        'name' => @sDB['counties'][$artpiece['county_id']][0],
      ],
      'place' => [
        'id' => $artpiece['place_id'],
        'name' => @$this->MC->t('places', $artpiece['place_id'])['name'],
      ],
      'district' => [
        'id' => $artpiece['district_id'],
        'name' => @sDB['districts'][$artpiece['district_id']][0],
      ],
      'coordinates' => [
        'lat' => $artpiece['lat'],
        'lng' => $artpiece['lon'],
      ],
      'users' => [
        'created' => [
          'id' => $artpiece['creator_user_id'],
          'name' => @$this->MC->t('users', $artpiece['creator_user_id'])['name'],
        ],
        'managing' => [
          'id' => $artpiece['user_id'],
          'name' => @$this->MC->t('users', $artpiece['user_id'])['name'],
        ],
      ],
      'published' => $artpiece['published'],
    ];

    if ($complexity >= 1) {
      $item = array_merge($item, [
        'cover_photo' => [
          'id' => $artpiece['photo_id'],
          'slug' => $artpiece['photo_slug'],
        ],
        'artists' => $artists,
        'dates' => [
          'first' => $this->correctDate($artpiece['first_date']),
          'last' => $this->correctDate($artpiece['last_date']),
        ],
      ]);
    }

    if ($complexity >= 2) {
      $descriptions = [];
      $desc_rows = $this->Mongo->find_array('artpiece_descriptions',
        [
          'artpieces' => $artpiece['id'],
          'status_id' => 5,
        ],
        ['sort' => [
          'lang' => -1,
          'approved' => 1
        ]]
      );

      foreach ($desc_rows as $desc_row) {
        $descriptions[] = [
          'id' => $desc_row['id'],
          'user' => [
            'id' => $desc_row['user_id'],
            'name' => @$this->MC->t('users', $desc_row['user_id'])['name'],
          ],
          'text' => $desc_row['text'],
          'source' => $desc_row['source'],
          'approved' => $desc_row['approved'],
        ];
      }


      $parameters = [];
      foreach ((array)json_decode(@$artpiece['parameters'], true) as $parameter) {
        $pitem = @$this->MC->t('parameters', $parameter);
        $parameters[] = [
          'id' => (int)$parameter,
          'name' => $pitem['name'],
          'group' => [
            'id' => $pitem['parameter_group_id'],
            'name' => sDB['parameter_groups'][$pitem['parameter_group_id']][0],
          ],
        ];
      }

      $item = array_merge($item, [
        'address' => $artpiece['address'],
        'place_description' => $artpiece['place_description'],
        'descriptions' => $descriptions,
        'artpiece_condition' => [
          'id' => $artpiece['artpiece_condition_id'],
          'name' => sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']][0],
        ],
        'artpiece_location' => [
          'id' => $artpiece['artpiece_location_id'],
          'name' => sDB['artpiece_locations'][$artpiece['artpiece_location_id']],
        ],
        'not_public_type' => [
          'id' => $artpiece['not_public_type_id'],
          'name' => @sDB['not_public_types'][$artpiece['not_public_type_id']][0],
        ],
        'photos' => json_decode($artpiece['photos'], true),
        'photo_count' => $artpiece['photo_count'],
        'parameters' => $parameters,
        'temporary' => $artpiece['temporary'],
        'anniversary' => $artpiece['anniversary'],
        'local_importance' => $artpiece['local_importance'],
        'national_heritage' => $artpiece['national_heritage'],
        'copy' => $artpiece['copy'],
        'reconstruction' => $artpiece['reconstruction'],
        'view_stats' => [
          'total' => $artpiece['view_total'],
          'this_week' => $artpiece['view_week'],
          'today' => $artpiece['view_day'],
        ],
      ]);
    }

    return $item;
  }


  private function correctDate($string) {
    $date = $string;

    $p = explode('-', $date);
    if (isset($p[1]) && (int)$p[1] < 10 && strlen($p[1]) == 1) {
      $date = $p[0] . '-0' . $p[1] . '-' . $p[2];
    }

    $p = explode('-', $date);
    if (isset($p[2]) && (int)$p[2] < 10 && strlen($p[2]) == 1) {
      $date = $p[0] . '-' . $p[1] . '-0' . $p[2];
    }

    return $date;
  }
}
