<?php
$custom_inputs = [
  ['mulaposok', [
    'type' => 'checkbox',
    'label' => 'Műlaposok',
    'value' => 1,
    'divs' => 'mt-1',
  ]],
  ['fotosok', [
    'type' => 'checkbox',
    'label' => 'Fotófeltöltők',
    'value' => 1,
    'divs' => 'mt-1',
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
  'placeholder' => 'Tag neve',
  'start_filter' => true,
  'custom_inputs' => $custom_inputs
]]);

if (count($users) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $users,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'orders' => $order_options,
      'order_default' => 'aktivitas-csokkeno',
    ]
  ]);

  echo '<div class="row">';

  foreach ($users as $user) {
    echo '<div class="col-sm-6 col-md-4 d-md-flex mb-3">';
    echo $app->element('community/user', ['user' => $user, 'options' => [

    ]]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

  echo $app->Html->pagination(count($users), $pagination);

} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}