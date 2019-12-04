<?php
if (@$artpiece) {
  echo '<div class="mt-1 text-center">';
  echo $app->Html->link($artpiece['title'] . ' (' . $app->Places->name($artpiece['place_id'], ['link' => false]) . ')', '', [
    'artpiece' => $artpiece,
    'class' => 'font-weight-bold small'
  ]);
  echo '</div>';
}

if (@$artist) {
  echo '<div class="mt-1 text-center">';
  echo $app->Artists->name($artist, [
    'link' => true,
    'class' => 'font-weight-bold small',
  ]);
  echo '</div>';
}

echo '<div class="small text-center mt-1">';
echo '<span class="mr-3" data-toggle="tooltip" title="Feltöltő">';
echo '<span class="fal fa-user mr-2"></span>';
echo $app->Users->name($photo['user_id']);
echo '</span> ';

echo '<span class="text-nowrap" data-toggle="tooltip" title="Feltöltés ideje">';
echo '<span class="fal fa-upload mr-2"></span>';
echo _time($photo['created'], 'y.m.d. H:i');
echo '</span>';
echo '</div>';