<?php
echo '<div class="row">';
echo $app->element('artpieces/list/list', [
  'artpieces' => $latest_unveils,
  'options' => [
    'top_count' =>  3,
    'top_class' =>  'col-6 col-md-4 p-0 d-flex',
    'top_details' => true,
    'class' => 'col-4 col-sm-3 col-md-2 p-md-1',
  ]
]);
echo '</div>'; // row --
