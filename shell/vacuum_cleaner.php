<?php
/**
 *
 * Korábban hibásra jelentett, beragadt jobok újrapróbálása
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

$jobs = $shell->Mongo->find('jobs', [
  'run_error' => ['$lt' => strtotime('-3 minutes')],
], [
  'sort' => ['created' => 1],
  'limit' => 20, // pl az üzenet kézbesítés combos méretű lehet
]);

if (count($jobs) > 0) {

  foreach ($jobs as $job) {

    $success = $shell->run([
      'class' => $job->class,
      'action' => $job->action,
      'id' => (string)$job->_id
    ]);

    //debug([$success, $job]);

    if ($success) {
      $shell->Mongo->delete('jobs', ['_id' => (string)$job->_id]);
    }
  }
}

$shell->Mongo->delete('jobs', [
  'run_error' => ['$lt' => strtotime('-3 hours')],
]);
