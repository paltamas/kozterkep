<?php
if ($artpiece['place_id'] > 0) {
  echo '<div class="d-sm-none mb-3 text-muted">';
  echo $app->Html->icon('map-marker mr-1');
  echo '<strong>' . $app->Places->name($artpiece['place_id'], ['link' => false]) . '</strong>';
  if ($artpiece['address'] != '' || $artpiece['district_id'] > 0) {
    echo ', ';
    echo $artpiece['district_id'] > 0 ? $app->Places->district($artpiece['district_id'], ['link' => false]) : '';
    echo $artpiece['address'] != '' && $artpiece['district_id'] > 0 ? ', ' : '';
    echo $artpiece['address'] != '' ? $artpiece['address'] : '';
  }
  echo '</div>';
}