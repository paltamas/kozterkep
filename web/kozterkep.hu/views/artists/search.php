<?php
echo $app->element('artists/search/form', ['options' => []]);

if (count($artists) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $artists,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'orders' => $order_options
    ]
  ]);

  echo '<div class="row">';

  foreach ($artists as $artist) {
    echo '<div class="col-sm-6 col-md-4 col-lg-3 d-md-flex">';
    echo $app->element('artists/item', [
      'artist' => $artist,
      'options' => [
        'name_options' => [
          'english_comma' => true
        ]
      ]
    ]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

  echo $app->Html->pagination(count($artists), $pagination);

} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}

