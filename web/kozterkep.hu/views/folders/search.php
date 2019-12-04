<?php
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Mappa neve',
  'user_filter' => 'folderers',
]]);

if (count($folders) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $folders,
      'total_count' => $total_count,
      'pagination' => $pagination,
    ]
  ]);


  echo '<div class="row">';

  foreach ($folders as $folder) {
    echo '<div class="col-md-3 d-flex">';
    echo $app->element('folders/item', ['folder' => $folder]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

  echo $app->Html->pagination(count($folders), $pagination);
} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}

