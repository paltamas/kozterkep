<?php
use Kozterkep\AppBase as AppBase;

class MapublicController extends AppController {

  private $allowed_domains = [
    'http://localhost',
    'http://localhost:3000',
    'https://app.kozterkep.hu',
  ],
    $allowed_domain = false,
    $allowed_request = false;

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
    $this->data = $this->Request->data();
    $this->MapublicLogic = new \Kozterkep\MapublicLogic();

    if ($this->MapublicLogic->auth($this->allowed_domains)) {
      $this->serveRoute();
    } else {
      $this->response([], 401);
    }
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