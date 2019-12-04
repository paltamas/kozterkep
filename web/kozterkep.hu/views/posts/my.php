<?php
echo '<div class="row">';
echo '<div class="col-md-9 order-2 order-md-1 mb-4 mb-md-0">';
echo $app->element('posts/search_form', [
  'status_filter' => true,
  'large' => false,
  'action' => '/blogok/sajat'
]);
echo '</div>';
echo '<div class="col-md-3 order-1 order-md-2 pt-md-4 text-center mb-4 mb-md-0">';
echo $app->Html->link('Új bejegyzés', $_params->here . '?letrehozas', [
  'class' => 'btn btn-primary btn-lg',
  'icon' => 'plus-circle fas',
  'ia-confirm' => 'Biztosan létrehozzuk az új bejegyzést?',
]);
echo '</div>';
echo '</div>';

echo '<div class="row d-flex justify-content-center">';
echo '<div class="col-lg-8">';

if (@$_params->query['kulcsszo'] != '') {
  echo '<div class="my-2 text-center">';
  echo $app->Html->link('Szűrés törlése', '/blogok/kereses', [
    'icon' => 'times',
  ]);
  echo '</div>';
}

echo $app->Html->pagination(count($posts), $pagination);

if (count($posts) > 0) {
  echo $app->element('posts/list', [
    'options' => [
      'intro' => [
        'category' => true,
      ]
    ]
  ]);
} else {
  echo $app->element('layout/partials/empty', [
    'class' => 'text-center my-5'
  ]);
}

if (count($posts) > 10) {
  echo $app->Html->pagination(count($posts), $pagination);
}

echo '</div>';
echo '</div>';