<?php

class RobotJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   * Majd adni kell ennek egy normális nevet
   * Ezt hívjuk most 10 percenként cron jobban
   */
  public function things() {
    // Köztérgép soha nem pihen!
    $this->DB->update('users', ['last_here' => time()], 2);

    return true;
  }
}