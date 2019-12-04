<?php
echo $app->element('posts/search_form');

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