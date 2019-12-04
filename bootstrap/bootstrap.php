<?php
/**
 * Konfig fájlok beolvasása, kivéve eztet.
 */
foreach (glob(dirname(__FILE__) . DS . '*.php') as $filename) {
  if (strpos($filename, __FILE__) !== false) {
    continue;
  }
  include $filename;
}


/**
 * Hibák kijelzése
 */
if (CORE['ERRORS']) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('error_log', CORE['PATHS']['DATA'] . '/logs/php.log');
} else {
  error_reporting(0);
  ini_set('display_errors', 0);
}


/*
 * Klasszik vendor könyvtár behúzás
 */
require_once(CORE['PATHS']['LIBS'] . DS . 'vendor' . DS . 'autoload.php');


/*
 * Saját library set behúzás
 */
spl_autoload_register(function ($class_name) {
  // Controllerekre ne ugorjunk
  if (strpos($class_name, 'Controller') !== false) {
    return;
  }

  // A könyvtárak, amikben túrunk:
  $base_folder = CORE['PATHS']['LIBS'] . DS . CORE['LIB_DIR'] . DS;
  $lib_dirs = array_slice(scandir($base_folder), 2);
  // Kiszedjük a hívásból a namespace-t, hogy a fájlt megkapjuk
  $class_file = str_replace(CORE['LIB_NAMESPACE'] . '\\', '', $class_name);

  // Adott könyvtárak végigpörgetése
  foreach ($lib_dirs as $lib_dir) {

    // Class elvárt útvonala
    $path = $base_folder . $lib_dir . DS . $class_file . '.php';

    // Ha van ilyen fájl, behúzzuk és leállunk
    if (is_file($path)) {
      require_once($path);
      break;
    }
  }
});