<?php
if (count($photos) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $photos,
      'pagination' => $pagination,
      'orders' => [],
      'order_default' => 'publikalas-csokkeno'
    ]
  ]);

  echo '<div class="row" id="lista">';

  foreach ($photos as $photo) {

    echo '<div class="col-6 col-md-4 col-lg-3 mb-4">';

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
      'link_options' => ['target' => '_blank'],
    ]);

    echo $app->element('photos/info', compact('artpiece', 'artist', 'photo'));

    echo '</div>';

  }

  echo '</div>';

  echo $app->Html->pagination(count($photos), $pagination);
} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}