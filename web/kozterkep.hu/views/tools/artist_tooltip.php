<?php
if ($artist) {
  echo $app->element('artists/item', ['options' => [
    'container' => ''
  ]]);
}