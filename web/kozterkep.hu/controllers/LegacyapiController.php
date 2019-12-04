<?php
use Kozterkep\AppBase as AppBase;

class LegacyapiController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
    $this->data = $this->Request->data();
    $this->check();
  }


  public function router() {
    $method = $this->Request->uri_level(3);
    return $this->$method();
  }


  /**
   *
   * API jogosultsági ellenőrzés
   *
   */
  private function check() {
    $allowed_credentials = C_ALLOWED_API_KEYS;
    if (!isset($this->data['api_key'])
      || !isset($this->data['api_secret'])
      || !isset($allowed_credentials[$this->data['api_key']])
      || $allowed_credentials[$this->data['api_key']] != $this->data['api_secret']) {
      http_response_code(401);
      exit;
    }
  }


  /**
   *
   * Egyszerű tömb kiíró
   *
   * @param $result
   */
  private function send($result = false, $json = false, $trim = true) {
    http_response_code(200);
    if ($json) {
      echo $result;
    } else {
      if (is_array($result) && count($result) > 0) {
        $json = json_encode($result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        // A korábbiban sztem nem volt szabványos a JSON, amit az előző PHP ragasztott...
        // most is az kell
        echo $trim ? ltrim(rtrim($json, ']'), '[') : $json;
      } else {
        echo json_encode([]);
      }
    }
    exit;
  }


  /**
   *
   * Műlap lista radius alapján
   *
   * @return array
   */
  public function get_artpieces_radius() {
    $data = $this->data;

    if (!isset($data['lat']) || !isset($data['lon']) || !isset($data['radius'])) {
      return [];
    }

    $lon = (float)$data['lon'];
    $lat = (float)$data['lat'];
    $radius = (float)$data['radius'] * 1000; // km => méter!

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

    $artpieces = [];
    foreach ($artpieces_ as $artpiece) {
      $artpieces[(string)$artpiece->_id] = $this->_artpiece_item($artpiece, [
        'distance' => $artpiece->distance
      ]);
    }

    $this->send($artpieces);
  }


  /**
   * Műlap lista bound alapján
   */
  public function get_artpieces_bounds() {
    $data = $this->data;

    // Kötelező értékek
    $nwlat = (float)$data['nwlat'];
    $nwlon = (float)$data['nwlon'];
    $selat = (float)$data['selat'];
    $selon = (float)$data['selon'];

    // Téglalap miatt kiszámolható értékek
    $nelat = isset($data['nelat']) ? (float)$data['nelat'] : $nwlat;
    $nelon = isset($data['nelon']) ? (float)$data['nelon'] : $selon;
    $swlat = isset($data['swlat']) ? (float)$data['swlat'] : $selat;
    $swlon = isset($data['swlon']) ? (float)$data['swlon'] : $nwlon;


    // Ráhagyás, ha jön paraméterben
    if (@$data['padding'] > 0 && $data['padding'] < 1) {
      $p = $data['padding'];
      $lat_adj = ($nelat - $swlat) * $p;
      $lon_adj = ($nelon - $swlon) * $p;
      $nelat += $lat_adj;
      $nelon += $lon_adj;
      $swlat -= $lat_adj;
      $swlon -= $lon_adj;
    }

    // Óriáslekérdezés lehet, szóval a find_array nem OK; az object-et kell használni
    $artpieces_ = $this->Mongo->find('artpieces',
      ['location.coordinates' =>
        ['$geoWithin' =>
          ['$polygon' =>
            [
              [$nwlon, $nwlat],
              [$nelon, $nelat],
              [$selon, $swlat],
              [$swlon, $selat],
            ]
          ]
        ]
      ],
      [
        'limit' => @$data['limit'] > 0 ? (int)$data['limit'] : 1000,
        // népszerűség szerint... ? szinkelni kell
        'sort' => ['artpiece_id' => -1]
      ]
    );

    $artpieces = [];

    foreach ($artpieces_ as $artpiece) {
      $artpieces[(string)$artpiece->_id] = $this->_artpiece_item($artpiece);
    }

    $this->send($artpieces);
  }

  public function search_artpiece() {

    $keyword = $this->data['keyword'];
    if (strlen($keyword) > 1) {
      $text = mb_strtolower($keyword);

      $conditions = [
        "title LIKE '%" . $text . "%'",
        "title_alternatives LIKE '%" . $text . "%'",
      ];

      $artists = $this->DB->find('artists', [
        'type' => 'list',
        'conditions' => [
          'checked' => 1,
          'OR' => [
            'name LIKE' => $text . '%',
            'last_name LIKE' => $text . '%',
            'alternative_names LIKE' => $text . '%',
          ]
        ],
        'order' => 'artpiece_count DESC',
        'fields' => ['id', 'name'],
      ]);
      if (count($artists) > 0) {
        foreach ($artists as $artist) {
          $conditions[] = "JSON_CONTAINS(artists, '{\"id\": " . $artist['id'] . "}')";
        }
      }

      $array = $this->DB->find('artpieces', [
        'conditions' => implode(' OR ', $conditions) . ' AND status_id = 5',
        'order' => 'published DESC'
      ]);

      $artpieces = [];

      if (count($array) > 0) {
        foreach ($array as $item) {

          $first_artist_id = 0;
          $first_artist_name = '';
          $artists = _json_decode($item['artists']);
          if (is_array($artists) && isset($artists[0]['id'])) {
            $first_artist_id = $artists[0]['id'];
            $artist = $this->MC->t('artists', $first_artist_id);
            if ($artist) {
              $first_artist_name = $artist['name'];
            }
          }

          $year = 0;
          $dates = _json_decode($item['dates']);
          $last = count($dates)-1;
          if (is_array($dates) && isset($dates[$last]['y']) && (int)$dates[$last]['y'] > 0) {
            $year = (int)$dates[$last]['y'];
          }

          $place = $this->MC->t('places', $item['place_id']);
          $place_name = $place['name'];

          $artpieces[] = [
            'id' => (string)$item['id'],
            'filename' => '/' . $item['id'],
            'photo_url' => '/' . $item['id']%50 . '/' . $item['photo_slug'],
            'title' => $item['title'],
            'title_alternatives' => $item['title_alternatives'],
            'photo_count' => (string)$item['photo_count'],
            'first_artist_id' => (string)$first_artist_id,
            'first_artist_name' => $first_artist_name,
            'year' => (string)$year,
            'city_name' => $place_name,
            'district_id' => (string)$item['district_id'],
            'address' => $item['address'],
            'place_description' => $item['place_description'],
            'lat' => $item['lat'],
            'lon' => $item['lon'],
            'user_id' => (string)$item['user_id'],
            'touchhug_count' => (string)0,
            'commenthug_count' => (string)0,
            'published' => (string)$item['published'],
          ];
        }
      }

      $this->send($artpieces, false, false);
    }

  }

  public function artpiece_view () {
    echo file_get_contents(CORE['BASE_URL'] . '/' . $this->data['id'] . '?teljeskepernyosmod', false);
    exit;
  }


  // Egyelőre nem készítjük el itt. Az újban folytatjuk.
  public function get_artpiece () {
    $data = $this->data;
  }


  /**
   *
   * Egy műlap elem, ahogy a korábbi API adta vissza
   *
   * @param $artpiece
   * @param array $extra_fields
   * @return array
   */
  private function _artpiece_item($artpiece, $extra_fields = []) {
    $item = [
      '_id' => [
        '$id' => (string)$artpiece->_id
      ],
      'id' => (int)$artpiece->artpiece_id,
      'title' => $artpiece->title,
      'title_en' => $artpiece->title_en,
      'title_alternatives' => $artpiece->title_alternatives,
      'city_name' => $artpiece->place_name,
      'address' => $artpiece->address,
      'first_artist_name' => $artpiece->artist,
      'year' => $artpiece->year,
      'published' => $artpiece->published,
      'modified' => $artpiece->modified,
      'view_total' => $artpiece->view_total,
      'user_id' => $artpiece->user_id,
      'photo_url' => '/' . $artpiece->artpiece_id%50 . '/' . $artpiece->photo_slug,
      'loc' => [
        'lon' => $artpiece->location->coordinates[0],
        'lat' => $artpiece->location->coordinates[1],
      ]
    ];

    if (count($extra_fields) > 0) {
      $item = array_merge($item, $extra_fields);
    }

    return $item;
  }
}