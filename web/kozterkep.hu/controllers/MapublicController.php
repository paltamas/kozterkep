<?php
use Kozterkep\AppBase as AppBase;

class MapublicController extends AppController {

  private $allowed_domains = [
    'http://localhost',
    'http://localhost:3000',
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

    $this->serveRoute();

    /*if ($this->MapublicLogic->auth($this->allowed_domains)) {
      $this->serveRoute();
    } else {
      $this->response([], 401);
    }*/
  }


  ///////////////////// VÉGPONTOK ---> /////////////////////


  // @todo: Szüret és kiemeltek
  public function highlighteds() {
    $response = [];
    $this->response($response);
  }

  // @todo: get_artpieces_bounds és get_artpieces_radius kiszolgálása
  public function byLocation() {
    $response = [];

    if (!$this->MapublicLogic->checkParams($this->query, ['lat', 'lon', 'radius'])) {
      $this->response(['error' => 'Kötelező paraméterek: nwlat, nwlon, selat, selon'], 400);
    }

    $data = $this->query;

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


    foreach ($artpieces_ as $artpiece) {
      $response[] = [
        'i' => $artpiece->artpiece_id,
        'l' => $artpiece->location->coordinates,
        't' => $artpiece->title,
        'p' => $artpiece->photo_slug,
        'd' => $artpiece->distance,
      ];
    }

    $this->response($response);
  }

  // @todo: get_artpieces_bounds és get_artpieces_radius kiszolgálása
  public function byBounds() {
    $response = [];

    if (!$this->MapublicLogic->checkParams($this->query, ['nwlat', 'nwlon', 'selat', 'selon'])) {
      $this->response(['error' => 'Kötelező paraméterek: nwlat, nwlon, selat, selon'], 400);
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

    $this->response($response);
  }

  // @todo: keresés, de picit részletesebben (keyword, title, artist, place - id vagy string; és ezek tényleg tudják)
  public function search() {
    $response = [];
    $this->response($response);
  }

  // @todo: listázás néhány paraméterrel
  // orderBy: uploaded,unveiled; sortBy: asc,desc
  public function list() {
    $response = [];
    $this->response($response);
  }

  // @todo: műlap ID alapján
  public function artpiece() {
    $response = [];
    $this->response($response);
  }



  ///////////////////// ---> VÉGPONTOK /////////////////////




  private function serveRoute() {
    if (method_exists($this, $this->params->action)) {
      $this->{$this->params->action}();
    } else {
      $this->response([], 404);
    }
  }

  private function response($data, $http_status_code = 200) {
    http_response_code($http_status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    die();
  }
}