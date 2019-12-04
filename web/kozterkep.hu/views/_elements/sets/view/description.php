<?php

if (@$set['place_id'] > 0) {
  $place = $app->MC->t('places', $set['place_id']);
  if ($place) {
    echo '<h4 class="subtitle">Kapcsolódó település</h4>';
    echo '<h5>' . $app->Html->link($place['name'], '', [
      'icon' => 'map-pin',
      'place' => $place,
      'class' => 'font-weight-bold',
    ]) . '</h5>';
  }
}

if ($set['description'] != '') {
  echo '<div class="kt-info-box mb-4">';
  echo $app->Text->read_more($set['description'], 125, true);
  echo '</div>';
}
