<?php
if (count($artist_descriptions) > 0) {
  foreach ($artist_descriptions as $artist_description) {
    echo $app->element('artists/view/description_item', [
      'description' => $artist_description,
      'options' => [
        'admin_links' => true
      ]
    ]);
  }
} else {
  echo $app->element('layout/partials/empty');
}