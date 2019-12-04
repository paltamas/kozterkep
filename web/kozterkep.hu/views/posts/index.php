<?php
if (count($highlighted_posts) > 0) {

  echo '<div class="row d-flex mt-md-3">';
  foreach ($highlighted_posts as $post) {
    echo '<div class="col-md-6 mb-4 mb-md-0">';
    echo $app->Blog->intro($post, [
      'title_size' => 2,
      'image_size' => 175,
    ]);
    echo '</div>';
  }
  echo '</div>';
}

echo '<div class="my-5">';
echo $app->element('posts/search_form');
echo '</div>';

echo '<div class="row d-flex">';
echo $app->element('posts/list', [
  'posts' => $posts,
  'options' => [
    'container' => 'col-sm-6 col-md-4 mb-5',
    'separator' => false,
    'intro' => [
      //'image_position' => '',
    ]
  ]
]);
echo '</div>';