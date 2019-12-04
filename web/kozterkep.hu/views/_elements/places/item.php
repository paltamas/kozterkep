<?php
$options = (array)@$options + [
  'image_size' => 6,
  'details' => true,
  'simple' => false,
  'container' => 'card mb-4 shadow-sm',
  'name_options' => [],
];

echo '<div class="' . $options['container'] . '">';

echo strpos($options['container'], 'card') !== false ? '<div class="card-body">' : '';

if ($place['checked'] == 0) {
  echo '<span class="fal fa-exclamation-triangle float-right" data-toggle="tooltip" title="Ellenőrizetlen település"></span>';
}

echo '<h5 class="card-title">' . $app->Places->name($place, $options['name_options']) . '</h5>';

echo '<div class="card-subtitle mb-2">';
echo $app->Places->country($place['country_id']);
if ($place['country_id'] == 101 && $place['county_id'] > 0 && $place['county_id'] != 1) {
  echo ', ' . $app->Places->county($place['county_id']);
}
echo '</div>';

echo '<div class="row small text-muted">';

if (!$options['simple'] && $options['details']) {
  echo '<div class="col-3 col-md-3 mb-2">';
  echo '<span class="badge badge-gray-kt">' . _n($place['artpiece_count']) . '</span>';
  echo '</div>';

  echo '<div class="col-9 col-md-9 text-right">';
  echo '<span class="far fa-file-plus mr-1"></span>' . _time($place['created'], ['format' => 'Y.m.d.']);
  echo '</div>';
}

if (!$options['simple']
  && ($place['top_artpiece_id'] > 0 || $place['last_artpiece_id'])) {

  $col_width = $options['image_size'] <= 6 ? 'col-6' : 'col-6 col-sm-3';

  // TOP
  $shown = false;
  if ($place['artpiece_count'] > 2 || $place['top_artpiece_id'] !== $place['last_artpiece_id']) {
    $top_artpiece = $app->MC->t('artpieces', $place['top_artpiece_id']);
    if ($top_artpiece) {
      echo '<div class="' . $col_width . ' mt-1">';
      echo 'Top műlap<br />';
      echo $app->Html->link($app->Image->photo($top_artpiece, [
        'size' => $options['image_size'],
        'class' => 'img-fluid img-thumbnail mt-1',
      ]), '', [
        'artpiece' => $top_artpiece,
        'ia-tooltip' => 'mulap',
        'ia-tooltip-id' => $top_artpiece['id'],
      ]);
      echo '</div>';
      $shown = true;
    }
  }
  echo !$shown ? '<div class="' . $col_width . ' mt-1"></div>' : '';


  // Utolsó
  $last_artpiece = $app->MC->t('artpieces', $place['last_artpiece_id']);
  if ($last_artpiece) {
    echo '<div class="' . $col_width . ' mt-1">';
    echo 'Utolsó műlap<br />';
    echo $app->Html->link($app->Image->photo($last_artpiece, [
      'size' => $options['image_size'],
      'class' => 'img-fluid img-thumbnail mt-1',
    ]), '', [
      'artpiece' => $last_artpiece,
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $last_artpiece['id'],
    ]);
    echo '</div>';
  } else {
    echo '<div class="' . $col_width . ' mt-1"></div>';
  }

} else {
  echo '<div class="col-12"></div>';
}

echo '</div>'; // row --

echo strpos($options['container'], 'card') !== false ? '</div>' : ''; // card-body --

echo '</div>'; // card --