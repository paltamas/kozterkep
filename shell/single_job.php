<?php
/**
 *
 * Egy konkrét job lefuttatása, ha elkezdett, ha nem.
 *
 */
// Alapok
define('C_PATH', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

ini_set('max_execution_time', 3000000000);
ini_set('memory_limit', '5000M');

// Keretrendszer init
require_once C_PATH . DS . 'bootstrap' . DS . 'bootstrap.php';

// App init
require_once dirname(__FILE__) . DS . 'config' . DS . 'shell_init.php';

// App start
$shell = new Kozterkep\JobBase();

if (@$argv[1] != '') {
  $job = $shell->Mongo->first('jobs', [
    '_id' => (string)$argv[1]
  ]);

  if ($job) {
    $success = $shell->run([
      'class' => $job['class'],
      'action' => $job['action'],
      'id' => (string)$job['id']
    ]);

    echo 'lefutott' . PHP_EOL;
    var_dump($success);

    // Töröljük
    if ($success) {
      $shell->Mongo->delete('jobs', ['_id' => (string)$job['id']]);
    }

    echo 'toroltuk a jobot' . PHP_EOL;

  } else {
    echo 'nincs ilyen job' . PHP_EOL;
  }
}