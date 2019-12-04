<?php
echo '<div class="row" id="lista">';

foreach ($photos as $photo) {

  echo '<div class="col-6 col-sm-4 col-md-6 col-lg-4 mb-3 text-center">';

  $artpiece = $artist = false;

  if ($photo['artpiece_id'] > 0) {
    $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
    $link = '/' . $artpiece['id'] . '#vetito=' . $photo['id'];
  } elseif ($photo['portrait_artist_id'] > 0) {
    $artist = $app->MC->t('artists', $photo['portrait_artist_id']);
    $link = $app->Html->link_url('', ['artist' => $artist]) . '#vetito=' . $photo['id'];
  }

  echo $app->Image->photo($photo, [
    'size' => 3,
    'class' => 'img-fluid img-thumbnail',
    'link' => $link,
  ]);

  $artist_id = $photo['portrait'] == 1 ? $photo['portrait_artist_id'] : $photo['artist_id'];
  $artist = $app->MC->t('artists', $artist_id);
  if ($artist) {
    echo $app->Artists->name($artist, [
      'class' => 'font-weight-bold mr-2',
      'tooltip' => true
    ]);
  }

  echo '</div>';
}

echo '<div class="col-12 mb-4">';
echo $app->Html->link('Minden alkotói portré', '/alkotok/kepkereso?kep_tipus=portrek', [
  'class' => '',
  'icon_right' => 'arrow-right',
]);
echo '</div>';

echo '</div>';

//echo '<hr />';