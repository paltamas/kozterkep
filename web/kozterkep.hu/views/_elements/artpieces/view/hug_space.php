<?php
if ($artpiece['status_id'] == 5
  && sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']][6] == 1) {

  echo '<div id="hug_space" class="border bg-light rounded mb-3 pt-1 d-none text-center" data-lat="' . $artpiece['lat'] . '" data-lon="' . $artpiece['lon'] . '">';


  echo '<div id="distance-container" class="mb-2"></div>';

  /*if ($artpiece['has_spacecapsule'] == 1) {
    echo $app->Html->link('Térkapszula!', '/mulapok/terkapszula/' . $artpiece['id'], [
      'icon' => 'gift',
      'class' => 'btn btn-success mr-3 mb-2 spacecapsule-button',
      'ia-modal' => 'modal-lg',
    ]);
  }

  echo $app->Html->link('Térkapszula', '/mulapok/terkapszula/' . $artpiece['id'], [
    'icon' => 'gift',
    'class' => 'btn btn-primary shadow-pulse-primary mb-3 mx-3 d-none spacecapsule-button',
    'ia-modal' => 'modal-lg',
  ]);*/

  echo $app->Html->link('Érintés', '/mulapok/erintes/' . $artpiece['id'], [
    'icon' => 'hand-point-up',
    'class' => 'btn btn-outline-primary mb-3 mx-3 d-none hug-button',
    'ia-modal' => 'modal-lg',
  ]);

  echo '</div>';
}