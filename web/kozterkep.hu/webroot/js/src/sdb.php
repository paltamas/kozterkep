<?php
// static_database => JS JSON, hogy bárhol használhassuk
define('C_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
define('DS', DIRECTORY_SEPARATOR);
$texts = ['texts' => require_once C_PATH . DS . 'data' . DS . 'texts' . DS . 'texts.php'];
$vars = require_once C_PATH . DS . 'data' . DS . 'constants' . DS . 'static_database.php';

// Nem szükségesek a JS-nek
foreach (['bots', 'video_guides', 'countries', 'blog_friends'] as $field) {
  unset($vars[$field]);
}

echo '$sDB = ' . json_encode(array_merge($texts, $vars)) . ';';