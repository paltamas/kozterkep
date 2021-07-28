<?php
/**
 * Super/shell
 *
 */

// Alapok
define('C_PATH', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

// Keretrendszer init
require_once C_PATH . DS . 'bootstrap' . DS . 'bootstrap.php';

// App init
require_once dirname(__FILE__) . DS . 'config' . DS . 'shell_init.php';

// App start
$shell = new Kozterkep\JobBase();

/**
 *
 *
 * EGYEDI TASK FUTTATÁSA
 * jellemzően cronjobból hívva
 *
 * Futtatás pl:
 *
 * php /var/www/kozterkep/shell/run.php photos handle -id=440783
 *
 * teljes migráció
 * php shell/run.php Migrations sync_all
 *
 * paraméterek:
 *
 * 1 = class
 * 2 = action
 * 3 = id
 *
 * A joboknak csak egy tömb lehet az opciójuk, amit
 * a run az "id" paraméter alapján olvas ki Mongo-ból,
 * majd sikeres futtatás után törli.
 *
 * Crontab-ban beállított jobok esetében ez az opció tömb
 * logika nem használható.
 * Sebaj. Hisz igazából akkor kell átadnunk opciókat, ha
 * valahonnan - jellemzően app controllerekből, vagy más
 * jobokból - hívjuk.
 *
 * A szép tömbös-opciós logikákhoz a forever.php-t használjuk,
 * ami a Mongo.jobs collectiont hajtja végre gyönyörűen.
 *
 */

$shell->run([
  'class' => @$argv[1],
  'action' => @$argv[2],
  'id' => @$argv[3] ? $argv[3] : false
]);