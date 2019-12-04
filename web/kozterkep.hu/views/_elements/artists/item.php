<?php
$options = (array)@$options + [
  'image_size' => 6,
  'details' => true,
  'simple' => false,
  'container' => 'card mb-4 shadow-sm',
  'name_options' => [],
];

if (is_numeric($artist)) {
  $artist = $app->MC->t('artists', $artist);
}

echo '<div class="' . $options['container'] . '">';

echo strpos($options['container'], 'card') !== false ? '<div class="card-body">' : '';

if ($artist['checked'] == 0) {
  echo '<span class="fal fa-exclamation-triangle float-right" data-toggle="tooltip" title="Ellenőrizetlen alkotó"></span>';
}

echo '<h5 class="card-title">' . $app->Artists->name($artist, $options['name_options']) . '</h5>';

if ($artist['profession_id'] > 0) {
  echo '<div class="card-subtitle mb-2">';
  if ($artist['corporation'] == 1) {
    echo '<span class="fal fa-industry mr-2" data-toggle="tooltip" title="Gazdasági társaság"></span>';
  }
  if ($artist['artistgroup'] == 1) {
    echo '<span class="fal fa-users mr-2" data-toggle="tooltip" title="Alkotócsoport"></span>';
  }
  echo sDB['artist_professions'][$artist['profession_id']][0];
  echo '</div>';
}


echo '<div class="row small text-muted">';

if (!$options['simple'] && $options['details']) {
  echo '<div class="col-3 col-md-3 mb-2">';
  echo '<span class="badge badge-gray-kt">' . _n($artist['artpiece_count']) . '</span>';
  echo '</div>';

  echo '<div class="col-9 col-md-9 text-right">';
  if ($artist['born_date'] != '') {
    echo '<span class="far fa-calendar mr-1"></span>' . _lazydate($artist['born_date']);
    if ($artist['death_date'] != '') {
      echo '<span class="fal fa-cross ml-2" title="' . _lazydate($artist['death_date']) . '" data-toggle="tooltip"></span>';
    }
  }
  echo '</div>';
}

if (!$options['simple']
  && ($artist['top_artpiece_id'] > 0 || $artist['last_artpiece_id'])) {

  $col_width = $options['image_size'] <= 6 ? 'col-6' : 'col-6 col-sm-3';

  // TOP
  $shown = false;
  if ($artist['artpiece_count'] > 2 || $artist['top_artpiece_id'] !== $artist['last_artpiece_id']) {
    $top_artpiece = $app->MC->t('artpieces', $artist['top_artpiece_id']);
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
  $last_artpiece = $app->MC->t('artpieces', $artist['last_artpiece_id']);
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