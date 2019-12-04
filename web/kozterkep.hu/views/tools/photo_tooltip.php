<?php
if ($photo) {
  echo $app->Image->photo($photo, [
    'class' => 'img-fluid img-thumbnail',
    'info' => true,
    'size' => 3,
  ]);
}