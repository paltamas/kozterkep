<?php
use Kozterkep\AppBase as AppBase;

class XdirsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Adattár',

    ]);
  }


  public function index() {
    $this->set([
      '_title' => 'Adattár',
      '_active_submenu' => 'Áttekintés',
      '_sidemenu' => true,
    ]);
  }

  public function research_partners() {
    $this->set([
      '_title' => 'Kutatási partnerek',
      '_active_submenu' => 'Kutatási partnerek',
      '_sidemenu' => true,
    ]);
  }


  public function books() {
    $results = [];
    $keyword = false;

    if (@$this->params->query['kulcsszo'] != '') {
      $keyword = $this->params->query['kulcsszo'];
      $results = $this->DB->find('books', [
        'conditions' => [
          'OR' => [
            'title LIKE' => '%' . $keyword . '%',
            'writers LIKE' => '%' . $keyword . '%',
            'artists LIKE' => '%' . $keyword . '%',
          ]
        ],
        'order' => 'owner_count DESC, published DESC',
        'limit' => 100,
      ]);
    }

    $top_books = $this->DB->find('books', [
      'order' => 'owner_count DESC, published DESC',
      'limit' => 10,
    ]);


    $this->set([
      '_title' => 'Könyvtér',
      '_active_submenu' => 'Könyvtér',
      '_sidemenu' => true,

      'top_books' => $top_books,
      'results' => $results,
      'keyword' => $keyword,
      'book_count' => $this->DB->count('books'),
    ]);
  }

  public function book_view() {
    $book = $this->DB->first('books', [
      'id' => (int)$this->params->id
    ]);

    if (!$book) {
      $this->redirect('/adattar/konyvter', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      '_title' => $book['title'],
      '_active_submenu' => 'Könyvtér',
      '_active_sidemenu' => '/adattar/konyvter',
      '_sidemenu' => true,
      '_breadcrumbs_menu' => [
        'Adattár' => '/adattar',
        'Könyvtér' => '/adattar/konyvter',
      ],

      'book' => $book,
      'owners' => _json_decode($book['owners']),
    ]);
  }


  public function lexicon() {
    $results = [];
    $keyword = '';

    if (@$this->params->query['kulcsszo'] != '') {
      $keyword = $this->params->query['kulcsszo'];
      $results = $this->Mongo->find('artpeople', [
        'name' => ['$regex' => $keyword, '$options' => 'i']
      ]);
      $results = $results + $this->Mongo->find('artpeople', [
        '$or' => [
          ['subtitle' => ['$regex' => $keyword, '$options' => 'i']],
          ['text' => ['$regex' => $keyword, '$options' => 'i']]
        ],
      ], ['limit' => 200]);
    }


    $this->set([
      '_title' => 'Kortárs Magyar Művészeti Lexikon kivonata',
      '_active_submenu' => 'Lexikon',
      '_sidemenu' => true,

      'results' => $results,
      'keyword' => $keyword,
      'person_count' => $this->Mongo->count('artpeople'),
    ]);
  }

  public function lexicon_view() {
    $person = $this->Mongo->first('artpeople', [
      'person_id' => (int)$this->params->id
    ]);

    if (!$person) {
      $this->redirect('/adattar/lexikon', [texts('hibas_url'), 'warning']);
    }

    $artist = $this->DB->first('artists', [
      'artpeople_id' => $person['person_id']
    ]);

    $this->set([
      '_title' => $person['name'],
      '_active_submenu' => 'Lexikon',
      '_active_sidemenu' => '/adattar/lexikon',
      '_sidemenu' => true,
      '_breadcrumbs_menu' => [
        'Adattár' => '/adattar',
        'Lexikon' => '/adattar/lexikon',
      ],

      'person' => $person,
      'artist' => $artist,
    ]);
  }


  public function ww_monuments() {
    $query = $this->params->query;

    // A legfontosabbak, amik a keresésekhez és a listákhoz is kellenek
    $parameters_ = $this->Mongo->find('ww_parameters', [
      'parameter_type_id' => ['$in' => [1,2,3,5,6,7,8]]
    ]);
    // Típus szerint bepakolom egy tömbbe
    $parameters = [];
    foreach ($parameters_ as $parameter) {
      $parameters[$parameter->parameter_type_id][$parameter->parameter_id] = (array)$parameter;
    }

    $conditions = [];

    if (isset($query['hely']) && $query['hely'] != '') {
      $conditions['place_name LIKE'] = $query['hely'] . '%';
    }

    if (isset($query['alkoto']) && $query['alkoto'] != '') {
      $conditions['artists_search LIKE'] = $query['alkoto'] . '%';
    }

    if (isset($query['tema']) && $query['tema'] != 'barmilyen') {
      $conditions['topics LIKE'] = '%"' . $query['tema'] . '"%';
    }

    if (isset($query['tipus']) && $query['tipus'] != 'barmilyen') {
      $conditions['type_id'] = (int)$query['tipus'];
    }

    $total_count = $this->DB->count('ww_monuments', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count,
    ];

    $monuments = $this->DB->find('ww_monuments', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'place_name ASC',
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Hősi Emlék',
      '_active_submenu' => 'Hősi Emlék',
      '_sidemenu' => true,

      'was_search' => count($conditions) > 0 ? true : false,
      'parameters' => $parameters,
      'pagination' => $pagination,
      'monuments' => $monuments,
    ]);
  }

  public function ww_monument_view() {
    $monument = $this->DB->first('ww_monuments', [
      'id' => (int)$this->params->id
    ]);

    if (!$monument) {
      $this->redirect('/adattar/hosi-emlek', [texts('hibas_url'), 'warning']);
    }

    // Összeszedjük az összes szükséges paraméterét, hogy lekérdezzük azokat
    // csak azokat.

    $parray_ = array_merge([
      $monument['place_id'],
      $monument['country_id'],
      $monument['county_id'],
      $monument['district_id'],
      $monument['type_id'],
      ],
      _json_decode($monument['sources']),
      _json_decode($monument['topics']),
      _json_decode($monument['states']),
      _json_decode($monument['artists']),
      _json_decode($monument['second_artists']),
      _json_decode($monument['creator_artists']),
      _json_decode($monument['founders']),
      _json_decode($monument['corps']),
      _json_decode($monument['unveilers']),
      _json_decode($monument['maintainers']),
      _json_decode($monument['connected_buildings']),
      _json_decode($monument['connected_monuments']),
      _json_decode($monument['connected_events']),
      _json_decode($monument['nationalities']),
      _json_decode($monument['symbols'])
    );

    // INT legyen
    $parray = [];
    foreach ($parray_ as $item) {
      $parray[] = (int)$item;
    }

    $parameters_ = $this->Mongo->find('ww_parameters', [
      'parameter_id' => ['$in' => array_values($parray)]
    ]);
    $parameters = [];
    foreach ($parameters_ as $parameter) {
      $parameters[$parameter->parameter_id] = (array)$parameter;
    }

    $photos = $this->Mongo->find_array('ww_photos', [
      'monument_id' => $monument['id']
    ]);

    $comments = $this->Mongo->find_array('ww_comments', [
      'monument_id' => $monument['id']
    ]);


    $this->set([
      '_title' => 'Emlékmű ' . $monument['place_name'] . ' településen',
      '_active_submenu' => 'Hősi Emlék',
      '_active_sidemenu' => '/adattar/hosi-emlek',
      '_sidemenu' => true,
      '_breadcrumbs_menu' => [
        'Adattár' => '/adattar',
        'Hősi Emlék' => '/adattar/hosi-emlek',
      ],

      'monument' => $monument,
      'parameters' => $parameters,
      'photos' => $photos,
      'comments' => $comments,
    ]);
  }
}