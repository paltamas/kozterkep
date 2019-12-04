<?php
if (count($connected_artpieces) > 0 || count($connected_sets) > 0 || count($connected_posts) > 0) {
  echo '<hr class="my-3" />';
  //echo '<h6 class="subtitle">További kapcsolódó</h6>';
}

if (count($connected_artpieces) > 0 ) {
  foreach ($connected_artpieces as $connected_artpiece) {
    if (in_array($connected_artpiece['type'], [2,3])) {
      continue;
    }

    echo '<div class="bg-light p-2 rounded my-2">';

    echo '<div class="row">';
    echo '<div class="col-4 col-md-3 pr-0">';
    echo $app->Html->link($app->Image->photo($connected_artpiece, [
      'link' => false,
      'size' => 6,
      'class' => 'img-thumbnail img-fluid',
    ]), '', [
      'artpiece' => $connected_artpiece,
      'class' => 'font-weight-bold',
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $connected_artpiece['id'],
    ]);
    echo '</div>';
    echo '<div class="col-8 col-md-9 pl-1">';
    echo '<div class="text-muted">';
    echo sDB['artpiece_connection_types'][$connected_artpiece['type']];
    echo '</div>';
    echo $app->Html->link($connected_artpiece['title'], '', [
      'artpiece' => $connected_artpiece,
      'class' => 'font-weight-bold'
    ]);

    echo '</div>';
    echo '</div>'; // row
    echo '</div>'; // rounded
  }
}

if (count($connected_sets) > 0 ) {
  foreach ($connected_sets as $connected_set) {
    echo '<div class="my-2">';
    echo '<div class="small font-weight-bold text-muted">';
    echo '<span class="fal fa-tags mr-1"></span>';
    echo sDB['set_types'][$connected_set['set_type_id']] . ' gyűjteményben';
    echo '</div>';
    echo $app->Html->link($connected_set['name'], '#', [
      'set' => $connected_set,
      'class' => 'font-weight-bold',
    ]);
    echo '</div>';
  }
}

/*
if (count($connected_posts) > 0 ) {
  $connected_post_links = '';
  foreach ($connected_posts as $connected_post) {
    if ($connected_post['artpiece_id'] == $artpiece['id']) {
      // Kiemelt kapcsolás, már szerepel középen
      continue;
    }
    $connected_post_links .= '<div class="mb-2">';
    $connected_post_links .= $app->Html->link($connected_post['title'], '#', [
      'post' => $connected_post,
      'class' => 'font-weight-bold',
    ]);
    $connected_post_links .= '</div>';
  }

  if ($connected_post_links != '') {
    echo '<div class="my-2">';
    echo '<div class="small font-weight-bold text-muted">';
    echo '<span class="fal fa-pen-nib mr-1"></span>';
    echo 'Blogbejegyzésekben';
    echo '</div>';
    echo $connected_post_links;
    echo '</div>';
  }
}*/
