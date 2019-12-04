<?php
if ($cover) {
  echo '<div class="text-center">';
  echo $app->Image->photo($cover, [
    'link' => 'showroom',
    'size' => 4,
    'class' => 'img-thumbnail mb-2',
  ]);
  echo '</div>';
}