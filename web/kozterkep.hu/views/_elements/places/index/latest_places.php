<?php
$i = 0;
foreach ($latest_places as $place) {
  $i++;
  echo '<span class="mr-2">';

  echo $app->Html->link($place['name'], '', [
    'place' => $place,
    'class' => 'font-weight-bold'
  ]);

  echo ' <span class="text-muted">(';
  echo $app->Places->country($place['country_id']);
  if ($place['country_id'] == 101 && $place['county_id'] > 0 && $place['county_id'] != 1) {
    echo ', ' . $app->Places->county($place['county_id']);
  }
  echo ')</span>';

  echo $i < count($latest_places) ? ',</span>' : '</span>';

}