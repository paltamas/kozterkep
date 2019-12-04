<?php
use Kozterkep\AppBase as AppBase;

class ErrorsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
  }

  public function black_hole() {

    $this->set([
      '_sidemenu' => false,
      '_title' => 'Biztonsági hiba',
    ]);

  }

  public function error_4xx() {

    $this->set([
      '_sidemenu' => false,
      '_title' => 'Az oldal nem található',
    ]);

  }

  public function error_5xx() {

    $this->set([
      '_sidemenu' => false,
      '_title' => 'Szerverhiba',
    ]);

  }

}