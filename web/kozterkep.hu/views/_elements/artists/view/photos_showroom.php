<?php
if (count($sign_photos) > 0 || count($photos) > 0) {

  $i = $k = 0;

  echo '<div class="row">';

  echo '<div class="col-12 mb-3 text-center">';
  echo $app->Html->link('Adatlap', '#adatlap', [
    'icon' => 'arrow-left',
    'class' => 'btn btn-outline-primary mx-2 tab-button',
  ]);

  echo $app->Html->link('Nagy képek', '', [
    'ia-showroom' => 'photo',
    'ia-showroom-hash' => 'fotolista',
    'ia-showroom-container' => '#fotolista',
    'icon' => 'expand-alt',
    'class' => 'btn btn-outline-primary mx-2'
  ]);
  echo '</div>';
  echo '</div>'; // row


  echo '<div class="row">';


  echo '<div class="col-md-6 col-lg-6 mb-4 bg-light rounded pt-3">';

  if (count($photos) > 0) {
    echo '<h4 class="subtitle mb-3">Fotók az alkotóról</h4>';

    echo '<div class="row">';
    foreach ($photos as $photo) {
      $artpiece = false;

      if ($photo['artpiece_id'] > 0) {
        $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
        if ($artpiece['status_id'] != 5) {
          continue;
        }
      }

      $k++;

      echo '<div class="col-6 text-center mb-3">';
      echo $app->element('artists/view/photos_showroom_item', [
        'photo' => $photo,
        'artpiece' => $artpiece,
      ]);
      echo '</div>';
    }
    echo '</div>'; // row --
  }


  echo '</div>'; // col --


  echo '<div class="col-md-' , count($photos) > 0 ? '6 pt-3' : '12' , ' mb-4">';

  if (count($sign_photos) > 0) {
    echo '<h4 class="subtitle mb-3">Szignóképek</h4>';
  }

  echo '<div class="row">';
  foreach ($sign_photos as $photo) {

    $artpiece = false;

    if ($photo['artpiece_id'] > 0) {
      $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
      if ($artpiece['status_id'] != 5) {
        continue;
      }
    }

    $i++;

    echo '<div class="col-6 col-md-4 text-center mb-3">';

    echo $app->element('artists/view/photos_showroom_item', [
      'photo' => $photo,
      'artpiece' => $artpiece,
    ]);

    echo '</div>';
  }
  echo '</div>'; // row --

  echo '</div>'; // col --







  echo '</div>'; // row --

  if ($i > 0 || $k > 0) {
    echo '<div class="text-muted">';
    echo '<strong>Összesen ' . $i . ' szignókép és ' . $k . ' fotó az alkotóról.</strong>';
    echo '</div>';
  }

} else {

  echo $app->element('layout/partials/empty', ['text' => 'Még nem került fel olyan fénykép, amin az alkotó szignója látszik, vagy magáról az alkotóról készült.']);

}