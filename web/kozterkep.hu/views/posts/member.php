<?php
echo '<div class="row d-flex">';
echo $app->element('posts/list', [
  'posts' => array_slice($posts, 0, 6),
  'options' => [
    'container' => 'col-sm-6 col-md-4 mb-5',
    'separator' => false,
  ]
]);
echo '</div>';

if (count($posts) > 6) {

  echo $app->element('posts/search_form', [
    'blogger' => $user['link'],
  ]);

  echo '<div class="mt-5 row d-flex justify-content-center">';
  echo '<div class="col-lg-8">';
  echo '<div class="row d-flex">';
  echo $app->element('posts/list', [
    'posts' => array_slice($posts, 6),
    'options' => [
      'separator' => true,
    ]
  ]);

  echo '<div class="col-12 my-5 text-center">';
  echo $app->Html->link('Minden bejegyzés a blogban', '/blogok/kereses?tag=' . $user['id'], [
    'class' => 'btn btn-outline-secondary',
    'icon_right' => 'arrow-right'
  ]);
  echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';

