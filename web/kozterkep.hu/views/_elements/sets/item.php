<?php
$options = (array)@$options + [
  'artpieces' => true,
  'image_size' => 7,
  'count' => 3,
  'container' => 'card mb-4 shadow-sm',
];

$set = $app->Mongo->arraize($set);

echo '<div class="' . $options['container'] . '">';

echo '<div class="card-body">';
echo '<h5 class="card-title">' . $app->Html->link($set['name'], '', ['set' => $set]) . '</h5>';

echo '<div class="card-subtitle mb-2">';
if ($set['set_type_id'] == 1) {
  echo '<span class="fa fa-users mr-2"></span>közös';
} else {
  echo '<span class="fa fa-user mr-2"></span>tagi';
  echo ' (' . $app->Users->name($set['user_id']) . ')';
}
echo '</div>';

echo '<div class="row small text-muted">';

echo '<div class="col-12 col-md-3 mb-2">';
echo '<span class="badge badge-gray-kt">' . count($set['artpieces']) . '</span>';
echo '</div>';
echo '<div class="col-12 col-md-9">';
echo '<span class="far fa-file-plus mr-1"></span>' . _time($set['updated'], ['format' => 'Y.m.d.']);
echo '</div>';
echo '</div>';

if ($options['artpieces']) {
  if (count($set['artpieces']) > 0) {

    $set['artpieces'] = _sort($set['artpieces'], 'created', 'desc');

    $i = 0;
    foreach ($set['artpieces'] as $a) {
      if ($i == $options['count']) {
        break;
      }
      $artpiece = $app->MC->t('artpieces', $a['artpiece_id']);
      if (@$artpiece['status_id'] == 5) {
        $i++;

        if ($i == 1) {
          echo '<hr class="mt-1 mb-3" />';
        }

        echo $app->Html->link($app->Image->photo($artpiece, [
          'size' => $options['image_size'],
          'class' => 'img-fluid img-thumbnail mr-2 mb-2',
        ]), '', [
          'artpiece' => $artpiece,
          'ia-tooltip' => 'mulap',
          'ia-tooltip-id' => $artpiece['id'],
        ]);
      }
    }
  }
}

echo '</div>'; // card-body
echo '</div>'; // card