<?php
use Kozterkep\AppBase as AppBase;

class NewsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Hírek',
    ]);
  }

  public function index() {

  }

  public function calendar() {

    $this->set([
      '_active_submenu' => 'Eseménynaptár',
      '_title' => 'Eseménynaptár',
    ]);

  }

}