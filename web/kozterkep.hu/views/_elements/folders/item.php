<?php
$options = (array)@$options + [
  'container' => 'card mb-4 shadow-sm',
  'show_state' => false,
  'details' => true,
];

echo '<div class="' . $options['container'] . '">';

$src = $folder['file_id'] != '' ? '/mappak/fajl_mutato/' . $folder['file_id'] : '/img/placeholder.png';

echo $app->Html->image($src, [
  'link' => [
    '/mappak/megtekintes/' . $folder['id']
  ],
  'class' => 'card-img-top',
  'crop' => 125
]);

//echo '<div class="card-header"><h4 class="my-0 font-weight-normal"></h4></div>';
echo '<div class="card-body">';
echo '<h5 class="card-title">' . $app->Html->link($folder['name'], '/mappak/megtekintes/' . $folder['id']) . '</h5>';


echo '<div class="row small text-muted">';

if ($options['show_state']) {
  echo '<div class="col-12 mb-2">';
  if ($folder['public'] == 1) {
    $state = '<span class="far fa-lock-open-alt mr-2"></span>publikus mappa';
  } else {
    $state = '<span class="far fa-lock-alt mr-2"></span>zárt mappa';
  }
  echo $state;
  echo '</div>';
} else {
  echo '<div class="col-12 mb-2">' . $app->Users->name($folder['user_id']) . '</div>';
}


if ($options['details']) {
  echo '<div class="col-12 mb-2">';
  echo '<span class="far fa-copy mr-1"></span>' . $folder['file_count'];
  echo '<span class="far fa-eye ml-3 mr-1"></span>' . $folder['view_total'];
  echo '</div>';

  echo '<div class="col-12">';
  echo '<span class="far fa-folder-plus mr-1"></span>' . _time($folder['updated'], ['format' => 'Y.m.d.']);
  echo '</div>';
}

echo '</div>';


echo '</div>'; // card-body
echo '</div>'; // card