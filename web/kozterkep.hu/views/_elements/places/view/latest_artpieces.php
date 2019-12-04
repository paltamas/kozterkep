<?php
$options = (array)@$options + [
  'query' => []
];

$i = 0;
if (count($latest_artpieces) > 0) {

  echo $app->element('artpieces/list/list', [
    'artpieces' => $latest_artpieces,
    'options' => [
      'top_count' => 2,
      'top_class' => 'col-6 p-0 d-flex mb-2',
      'class' => 'col-4 col-sm-3 col-md-2 p-md-1',
    ]
  ]);

  echo '<div class="col-12 mt-3 text-center">';

  $http_qery = [
    'oldalcim' => $_title,
    'visszalepes' => $_params->here
  ] + $options['query'];

  echo $app->Html->link('Minden műlap innen', '/kereses/lista?' . http_build_query($http_qery), [
    'class' => 'btn btn-outline-primary btn-block'
  ]);

  echo '</div>';

} else {
  echo $app->element('layout/partials/empty', ['text' => 'Nincs még publikus műlap innen.']);
}