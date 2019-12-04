<?php
namespace Kozterkep;

/**
 *
 * Az osztály célja: tetszőleges helyről meghívni shell parancsokat úgy, hogy
 * azok aszinkron fussanak, és ne terheljék az adott thread-et.
 *
 * !!! Csak shell parancsok meghívására alkalmas.
 *
 * Class ShellComponent
 * @package Kozterkep
 */

class ShellComponent {

  public function __construct() {

  }

  /**
   *
   * Job futtatás "csendben"
   * !! az innen hívott jobokon belül nem megy a log írás
   * és gondolom más probléma is lehet.
   *
   * @param $class: Job class, "job" nem kell
   * @param $action: class action
   * @param array $options: job action option tömb
   */
  public function execute($class, $action, $options = []) {
    if ($class == '' || $action == '') {
      return false;
    }

    // Van-e opció, és ha igen, akkor írjuk meg
    $option_argument = '';
    if (count($options) > 0) {
      $option_id = 'jo_' . uniqid() . '_' . time();
      $option_argument = ' ' . $option_id;
      apcu_store($option_id, $options); // ez nem megy CLI-ben!
    }
    usleep(10000);

    $run_file = CORE['PATHS']['SHELL'] . DS . 'run.php';
    $arguments = $class . ' ' . $action . $option_argument;
    $noout = ' > /dev/null 2>&1 &';

    $cmd = 'nohup php ' . $run_file . ' ' . $arguments . ' ' . $noout;

    shell_exec($cmd);
    return true;
  }

}
