<?php
$options = (array)@$options + ['truncate' => 100];

echo $app->Image->photo($photo, [
  'link' => 'showroom',
  'size' => 4,
  'class' => 'img-thumbnail img-fluid mb-2',
]);

echo $app->element('artpieces/view/photos_showroom_source', ['photo' => $photo]);

echo '<div class="text-left">';
echo '<span class="mr-3" data-toggle="tooltip" title="Feltöltő">';
echo '<span class="fal fa-user mr-2"></span>';
echo $app->Users->name($photo['user_id']);
echo '</span> ';

echo '<span class="text-nowrap" data-toggle="tooltip" title="Feltöltés ideje">';
echo '<span class="fal fa-upload mr-2"></span>';
echo _time($photo['created'], 'y.m.d. H:i');
echo '</span>';
echo '</div>';

if (@$photo['exif_taken'] > 0 && $photo['exif_taken'] < ($photo['created'] - sDB['limits']['photos']['exif_taken_shown'])) {
  echo '<div class="text-left" data-toggle="tooltip" title="Fotó készítésének ideje EXIF alapján">';
  echo '<span class="fal fa-camera mr-2"></span>';
  echo date('Y.', $photo['exif_taken']) . ' ' . mb_strtolower(sDB['month_names'][date('n', $photo['exif_taken'])]);
  echo '</div>';
} elseif (@$photo['year'] > 0) {
  echo '<div class="text-left" data-toggle="tooltip" title="Fotó készítésének ideje megadott évszám alapján">';
  echo '<span class="fal fa-camera mr-2"></span>';
  echo $photo['year'];
  echo $photo['archive'] == 1 ? ', Archív felvétel' : '';
  echo '</div>';
} else {
  if ($photo['archive'] == 1) {
    echo '<div class="text-left">';
    echo 'Archív felvétel';
    echo '</div>';
  }
}

echo $photo['text'] != '' ? '<div class="bg-light p-2 my-1">' . $app->Text->read_more($photo['text'], $options['truncate'], true) . '</div>' : '';