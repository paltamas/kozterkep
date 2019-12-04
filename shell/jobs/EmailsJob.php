<?php
class EmailsJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
    $this->apikey = C_WS_SENDGRID['apikey'];
  }


  /**
   *
   * Egyszerű email küldő job.
   * Nagyon egyszerű.
   * Tényleg.
   * .
   *
   * @param array $options
   * @return bool
   *
   */
  public function send () {
    return $this->Email->send(self::$_options);
  }
}