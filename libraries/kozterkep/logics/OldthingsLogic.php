<?php

namespace Kozterkep;

class OldthingsLogic {

  private $app_config;

  public function __construct($app_config) {
    $this->app_config = $app_config;
  }


  /**
   * Régi szoborlapos és műlapos URL-ek továbbdobása, ha tudományosan kell értelmezni
   * (nginx nem tudja)
   */
  public function redirects() {
    $uri = $_SERVER['REQUEST_URI'];
    $uri_parts = explode('/', $uri);

    $redirect_url = false;


    /**
     * Régi szoborlap műlap URL-ek
     *
     * Így néznek ki: 17_56_os_emlekmu_Dunaujvaros_XY_1998
     * Szétszedem _ mentén, és ha szám, és /szam_ formábal indul az URI.
     *
     */
    $first_parts = explode('_', $uri_parts[1]);
    $id = $first_parts[0];
    if (is_numeric($id) && strpos($uri_parts[1], $id . '_') === 0) {
      $redirect_url = '/' . $id;
    }


    /**
     * Régi szoborlapos URL-ek továbbdobása
     * ha ID-t kell kibányászni az alulvonásból
     */
    switch ($uri_parts[1]) {
      case 'alkoto':
        $id_parts = explode('_', $uri_parts[2]);
        $id = $id_parts[0];
        $redirect_url = '/alkotok/megtekintes/' . $id;
        break;

      case 'telepules':
        $id_parts = explode('_', $uri_parts[2]);
        $id = $id_parts[0];
        $redirect_url = '/helyek/megtekintes/' . $id;
        break;
    }

    // Régi mappa...
    if (@$uri_parts[2] == 'profil' && @$uri_parts[4] == 'folders' && @$uri_parts[5] > 0) {
      $redirect_url = '/mappak/megtekintes/' . $uri_parts[5];
    }


    if ($redirect_url) {
      header("Location: " . $redirect_url, false, 302);
      exit;
    }
  }
}

