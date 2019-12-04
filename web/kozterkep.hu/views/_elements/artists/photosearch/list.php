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
        //'link_options' => ['target' => '_blank'],
      ]);



      echo '<div class="text-center my-2">';

      if ($photo['sign'] == 1) {
        if ($photo['sign_artist_id'] > 0) {
          $artist = $app->MC->t('artists', $photo['sign_artist_id']);
          if ($artist) {
            echo $app->Artists->name($artist, [
              'class' => 'font-weight-bold mr-2',
              'tooltip' => true
            ]);
          }
        }
        echo '<span class="badge badge-gray-kt badge-lg"><span class="fas fa-signature mr-1"></span>szignó</span>';
      }

      if ($photo['artist'] == 1 || $photo['portrait'] == 1) {
        if ($photo['artist_id'] > 0 || $photo['portrait_artist_id'] > 0) {
          $artist_id = $photo['portrait'] == 1 ? $photo['portrait_artist_id'] : $photo['artist_id'];
          $artist = $app->MC->t('artists', $artist_id);
          if ($artist) {
            echo $app->Artists->name($artist, [
              'class' => 'font-weight-bold mr-2',
              'tooltip' => true
            ]);
          }
        }
        echo '<span class="badge badge-gray-kt badge-lg"><span class="far fa-user mr-1"></span>alkotó</span>';
      }

      echo '</div>';

      echo $app->element('photos/info', compact('artpiece', 'photo'));

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