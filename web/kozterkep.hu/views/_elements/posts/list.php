<?php
if (count($posts) == 0) {
  echo $app->element('layout/partials/empty', [
    'class' => 'text-center col-12 my-5'
  ]);
} else {

  $options = (array)@$options + [
    'separator' => true,
    'container' => 'my-2',
    'intro' => [
      'image_position' => 'left',
    ]
  ];
  $i = 0;
  foreach ($posts as $post) {
    $i++;

    $bg_class = $post['status_id'] != 5 ? 'bg-yellow-light p-3' : '';

    echo '<div class="' . $options['container'] . ' clearfix ' . $bg_class . '">';
    echo $app->Blog->intro($post, $options['intro']);
    echo '</div>';

    echo $options['separator'] && $i < count($posts) ? '<hr />' : '';
  }
}
?>