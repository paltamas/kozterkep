<?php
echo $app->Image->photo($photo, [
  'link' => 'showroom',
  'size' => 2,
  'class' => 'img-thumbnail img-fluid',
]);

echo $app->element('artists/view/photos_showroom_source', [
  'photo' => $photo,
  'artpiece' => $artpiece,
]);

echo $app->element('photos/info');