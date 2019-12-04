<?php
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Név...',
  'start_filter' => true,
  'custom_inputs' => [
    ['kep_tipus', [
      'empty' => 'Portrék és szignók...',
      'options' => [
        'portrek' => 'Csak portrék',
        'szignok' => 'Csak szignók',
      ],
    ]],
  ]
]]);


if (@$_params->query['kulcsszo'] != '') {
  echo '<div class="kt-info-box">';
  if (count($artists) > 0) {
    echo count($artists) . ' alkotót találtunk: ';
    foreach ($artists as $artist) {
      echo $app->Artists->name($artist, [
        'class' => 'mx-2 font-weight-bold',
        'tooltip' => true
      ]);
    }
  } else {
    echo 'Nem találtunk alkotót a megadott kifejezés alapján.';
  }
  echo '</div>';
}
