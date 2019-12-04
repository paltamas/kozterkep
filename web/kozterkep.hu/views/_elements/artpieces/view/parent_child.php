<?php
if (count($connected_artpieces) > 0 ) {
  foreach ($connected_artpieces as $connected_artpiece) {
    if (!in_array($connected_artpiece['type'], [2,3])) {
      continue;
    }
    echo '<div class="bg-light p-2 rounded my-2">';

    echo '<div class="row">';
    echo '<div class="col-2 col-md-3 col-lg-2 pr-0 pr-lg-3">';
    echo $app->Html->link($app->Image->photo($connected_artpiece, [
      'link' => false,
      'size' => 6,
      'class' => 'img-thumbnail img-fluid',
    ]), '', [
      'artpiece' => $connected_artpiece,
      'class' => 'font-weight-bold',
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $connected_artpiece['id'],
    ]);
    echo '</div>';
    echo '<div class="col-8 col-md-9 pl-1 pt-md-1 pt-lg-2">';
    echo '<div class="text-muted">';
    echo sDB['artpiece_connection_types'][$connected_artpiece['type']];
    echo '</div>';
    echo $app->Html->link($connected_artpiece['title'], '', [
      'artpiece' => $connected_artpiece,
      'class' => 'font-weight-bold'
    ]);

    echo '</div>';
    echo '</div>'; // row
    echo '</div>'; // rounded
  }
}