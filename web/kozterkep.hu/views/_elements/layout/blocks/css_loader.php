<?php
$file_paths = [
  'bootstrap.css',
  'vendor/all.min.css',
  'vendor/leaflet.css',
  'vendor/summernote.css',
];

if (APP['minify']) {
  if (isset($_params->query['minify'])) {
    $app->Html->minify_css($file_paths);
  }
  $dev_id = CORE['ENV'] == 'dev' ? uniqid() : '';
  echo '<link rel="stylesheet" href="/css/app/build.min.css?' . CORE['VER'] .  $dev_id . '">' . PHP_EOL;
} else {
  $i = 0;
  foreach ($file_paths as $file_path) {
    $i++;
    // gyönyörű kódot, na
    echo $i > 1 ? '    ' : '';
    echo '<link rel="stylesheet" href="/css/' . $file_path . '?' . uniqid() . '">' . PHP_EOL;
  }
}