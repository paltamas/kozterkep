<?php
$i = 0;
if (count($photos) > 0) {

  echo '<div class="text-center">';

  foreach ($photos as $photo) {
    if ($photo['artpiece_id'] > 0) {
      $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
      if ($artpiece['status_id'] != 5) {
        continue;
      }
    }

    $i++;

    $right_margin = $count > 1 && $i < count($photos) ? ' mr-1' : '';

    echo $app->Image->photo($photo, [
      'link' => '#fotolista',
      'link_options' => ['class' => 'tab-button'],
      'size' => $photo_size,
      'class' => 'mb-3 img-thumbnail' . $right_margin,
    ]);

    if ($i >= $count) {
      break;
    }
  }

  echo '<div class="mb-4">';
  echo $app->Html->link('Szignóképek és fotók (' . (count($photos) + count($sign_photos)) . ')', '#fotolista', [
    'icon' => 'images',
    'class' => 'btn btn-outline-primary tab-button',
  ]);
  echo '</div>'; // btn --

  echo '</div>';
}