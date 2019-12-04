<?php
if ($user['harakiri'] == 1) {
  return;
}

if (count($places) > 0) {
  echo '<div class="kt-info-box mb-3">';
  echo '<h5 class="subtitle">Települések, ahol aktív</h5>';
  foreach ($places as $place) {
    echo '<span class="mr-3 mb-2 font-weight-normal">';
    echo $app->Places->name($place['place_id'], [
      //'class' => 'text-white',
      'tooltip' => true,
    ]) . ' (' . $place['artpiece_count'] . ')';
    echo '</span>';
  }
  echo '</div>';
}