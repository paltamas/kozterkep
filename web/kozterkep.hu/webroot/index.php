<?php
/**
 * Jeah.
 * Start: 2018.03.10.
 * 18:05, egy jó futás után.
 * 9 fok, esti madárcsicsergés.
 * Tavasz, Csobánka.
 *
 * Kezdjünk bele harmadszor tavaly ősz óta az egész Köztérkép
 * architektúra teljes újragondolásába.
 *
 * Aktuális fókusz: még rövidebb kódok, csak ami kell.
 *
 * ===  z e n  ===
 *
 */

// Alapok
define('C_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('DS', DIRECTORY_SEPARATOR);

// Keretrendszer init
require_once C_PATH . DS . 'bootstrap' . DS . 'bootstrap.php';

// App init
require_once dirname(dirname(__FILE__)) . DS . 'config' . DS . 'app_init.php';

// App start
$app = new AppController();