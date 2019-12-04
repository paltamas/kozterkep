<?php
namespace Kozterkep;

class RenderComponent {

  private $app_config;
  private $Request;
  private $params;

  public function __construct($app_config, $Request) {
    $this->app_config = $app_config;
    $this->Request = $Request;
  }


}