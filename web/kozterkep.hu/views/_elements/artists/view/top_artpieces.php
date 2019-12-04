<?php
if (count($top_artpieces) > 0) {
  foreach ($top_artpieces as $artpiece) {
    echo '<div class="col-6 p-0 d-flex">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'details' => false,
        'tooltip' => false,
      ],
    ]);
    echo '</div>';
  }
}