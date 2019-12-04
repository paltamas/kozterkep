<?php
$connected_artpieces = _json_decode($post['connected_artpieces']);
if (count($connected_artpieces) > 0) {
  echo '<hr class="my-5" />';
  echo '<h4 class="subtitle">Kapcsolt műlapok</h4>';
  if (count($connected_artpieces) > 0) {
    echo '<div class="row">';
    echo $app->element('artpieces/list/list', [
      'artpieces' => $connected_artpieces,
      'options' => [
        'top_count' => 100,
        'top_class' => 'col-6 col-sm-4 col-md-4 col-lg-3 p-0 d-flex mb-3',
      ]
    ]);
    echo '</div>';
  }
}