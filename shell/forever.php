<?php
/**
 *
 * Ö-R-Ö-K-j-o-b
 * ezt beröffentem 1-szer és pöcögteti a taskokat
 *
 * Így röffentsd be:
 * cd /var/www/kozterkep/shell/
 * nohup php forever.php >/dev/null 2>&1 &
 *
 * Így indítsd újra, ha már fut, de komponens kód változott:
 * nohup php forever.php clean >/dev/null 2>&1 &
 *
 *
 * Így gyilkold meg csak
 * php forever.php kill
 *
 * HOPP
 * Nem alkalmas percekig futó jobok pöcögtetésére, nem elég stabil azokkal.
 * Ilyet inkább direkt cronjob futtatással indítsunk. User tevékenységhez amúgy
 * sem szabad egy több percig futó jobot kötni!
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

/*error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', CORE['PATHS']['DATA'] . '/logs/php.log');*/

// Környezetfüggő prefix, hogy ne lőjék ki egymást
$prefix = strpos(__FILE__, '/var/www/kozterkep_dev/') === 0
  ? 'dev_' : '';


// App start
$shell = new Kozterkep\JobBase();

// Újra kell-e indítani
$have_to_restart = false;



// Ellenőrző logika
// ezt futtatja a job futását ellenőrző job :D
if (@$argv[1] == $prefix . 'check') {
  exec("ps aux | grep -i '[p]hp forever.php " . $prefix . "clean'", $pids);
  if (empty($pids)) {
    // Újra kell indítanunk, mert nem fut
    $have_to_restart = true;
    echo 'restartoltunk' . PHP_EOL;
  }
}


// Restart, meggyilkolja az eddigi futást
if ($have_to_restart
  || @$argv[1] == $prefix . 'clean' || @$argv[1] == $prefix . 'kill') {
  $lock_file = __FILE__.".lock";
  if (is_file($lock_file))
  $pid = file_get_contents($lock_file);
  if (@$pid > 0) {
    $k = posix_kill($pid, SIGKILL);
    echo $pid . ' killed' . PHP_EOL;
  }
  $hLock = fopen($lock_file, "w+");
  flock($hLock, LOCK_UN);
  fclose($hLock);
  unlink($lock_file);

  if (@$argv[1] == $prefix . 'kill') {
    echo 'exit' . PHP_EOL;
    exit;
  }
}


// Foreverség
ignore_user_abort(true);
set_time_limit(0);


// Sima futás
$hLock = fopen(__FILE__.".lock", "w+");
if (!flock($hLock, LOCK_EX | LOCK_NB)) {
  die("Már futok; lőj ki, ha újra beröffentenél!" . PHP_EOL);
}

// Beírom a pidet, hogy ha restart lesz, tudjuk, kit kell megölni
fwrite($hLock, getmypid());

// Ez fut killig
while (true) {

  /**
   * Aktuális feladatok
   * amik nem hibásak, vagy legalább X perce voltak hibásak
   */
  $jobs = $shell->Mongo->find('jobs', [
    'run_error' => ['$exists' => false],
    'started' => ['$exists' => false],
  ], [
    'sort' => ['created' => 1],
    'limit' => 10, // pl az üzenet kézbesítés combos méretű lehet
  ]);

  foreach ($jobs as $job) {
    $shell->Mongo->update('jobs', [
      'started' => time()
    ], ['_id' => (string)$job->_id]);

    // Futtatom a feladatot, átadva mindent
    // az ID alapján a run kiolvassa és kezeli
    $success = $shell->run([
      'class' => $job->class,
      'action' => $job->action,
      'id' => (string)$job->_id
    ]);

    //var_dump($success);

    if ($success) {
      // Sikeres futás után töröljük a feladatot
      $shell->Mongo->delete('jobs', ['_id' => (string)$job->_id]);
    } else {
      $shell->Mongo->update('jobs', [
        'run_error' => time()
      ], ['_id' => (string)$job->_id]);
    }

    // Töröljük a legalább 10 perce kezdett jobokat, mert tuti beragadtak
    $shell->Mongo->delete('jobs', [
      'started' => ['$lt' => strtotime('-10 minutes')],
      'run_error' => ['$exists' => false],
    ]);
  }

  $jobs = null;

  sleep(2);
}

flock($hLock, LOCK_UN);
fclose($hLock);
unlink(__FILE__.".lock");