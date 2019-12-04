<?php
// App konfig fájlok behúzása
foreach (glob(dirname(__FILE__) . DS . '*.php') as $filename) {
  if (strpos($filename, __FILE__) !== false) {
    continue;
  }
  include $filename;
}

/*
 * App Controller Class behúzások
 */
spl_autoload_register(function ($class_name) {
  $path = CORE['PATHS']['WEB'] . DS . APP['path'] . DS . 'controllers' . DS . $class_name . '.php';
  if (is_file($path)) {
    require_once($path);
  }
});