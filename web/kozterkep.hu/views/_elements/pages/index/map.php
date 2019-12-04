<?php
echo $app->element('maps/simple_filtered', ['options' => [
  'count' => $random_place['artpiece_count'],
  'map_artpieces' => $map_artpieces,
  'height' => 525,
  'zoom' => 16,
  'filter_query' => [
    'hely_az' => $random_place['id'],
    'hely' => $random_place['name'],
  ],
  'title' => '<h5 class="subtitle mb-3 text-dark">Egy hely térképen</h5>',
]]);
?>