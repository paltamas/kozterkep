<?php
/*
 * Shell Job Class behúzások
 */
spl_autoload_register(function ($class_name) {
  $path = CORE['PATHS']['SHELL'] . DS . 'jobs' . DS . $class_name . '.php';
  if (is_file($path)) {
    require_once($path);
  }
});