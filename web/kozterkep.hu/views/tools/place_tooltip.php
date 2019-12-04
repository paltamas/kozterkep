<?php
if ($place) {
  echo $app->element('places/item', [
    'options' => ['container' => '']
  ]);
}