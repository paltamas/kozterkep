<?php
$custom_inputs = [
  ['megye', [
    'empty' => 'Minden megye...',
    'options' => $app->Arrays->id_list(sDB['counties'], 0, ['sort' => 'ASC']),
  ]],
  ['orszag', [
    'empty' => 'Minden ország...',
    'options' => $app->Arrays->id_list(sDB['countries'], 1, ['sort' => 'ASC']),
  ]],
  ['ellenorizetlen', [
    'value' => @$_params->query['ellenorizetlen'] > -1
      ? $_params->query['ellenorizetlen'] : 'mindegy',
    'options' => [
      0 => 'Ellenőrzött',
      1 => 'Ellenőrizetlen',
      'mindegy' => 'Minden állapot',
    ],
  ]],
];


if ($_user) {
  $custom_inputs[] = ['kovetettek', [
    'type' => 'checkbox',
    'label' => 'Követettek',
    'value' => 1,
    'divs' => 'mt-1',
  ]];
}

echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Település neve',
  'start_filter' => true,
  'custom_inputs' => $custom_inputs
]]);

if (count($places) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $places,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'orders' => $order_options
    ]
  ]);

  echo '<div class="row">';

  foreach ($places as $place) {
    echo '<div class="col-sm-6 col-md-4 col-lg-3 d-md-flex">';
    echo $app->element('places/item', ['place' => $place]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

  echo $app->Html->pagination(count($places), $pagination);

} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}

