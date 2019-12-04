<?php
$image = $app->Blog->image($post, [
  'info_container' => false,
  'file_size' => 137,
  'image_size' => 5,
]);
if ($image != '') {
  echo '<div class="kt-info-box mb-4 text-center">';
  echo '<div class="mb-2">A bejegyzés főképe:</div>';
  echo $image;
  echo '</div>';
}