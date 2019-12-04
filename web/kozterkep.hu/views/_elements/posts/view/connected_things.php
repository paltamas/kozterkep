<?php

if ($artist) {
  echo '<div class="mb-5">';
  echo '<h4 class="subtitle mb-3">Kapcsolt alkotó</h4>';
  echo $app->element('artists/item', [
    'artist' => $artist,
    'options' => [
      'details' => false,
      'simple' => true,
      'name_options' => [
        'tooltip' => true
      ]
    ],
  ]);
  echo '</div>';
}

if ($place) {
  echo '<div class="mb-5">';
  echo '<h4 class="subtitle mb-3">Kapcsolt hely</h4>';
  echo $app->element('places/item', [
    'place' => $place,
    'options' => [
      'details' => false,
      'simple' => true,
      'name_options' => [
        'tooltip' => true
      ]
    ],
  ]);
  echo '</div>';
}

if ($folder) {
  echo '<div class="mb-5">';
  echo '<h4 class="subtitle mb-3">Kapcsolt mappa</h4>';
  echo $app->element('folders/item', [
    'folder' => $folder,
    'options' => [
      'details' => false,
    ]
  ]);
  echo '</div>';
}

if ($set) {
  echo '<div class="mb-5">';
  echo '<h4 class="subtitle mb-3">Kapcsolt gyűjtemény</h4>';
  echo $app->element('sets/item', [
    'set' => $set,
    'options' => [
      'artpieces' => false,
    ]
  ]);
  echo '</div>';
}


if ($post['artpiece_id'] > 0) {
  echo '<h4 class="subtitle">Kiemelt alkotás</h4>';

  if ($post['artpiece_id'] > 0) {
    echo $app->element('artpieces/list/item', [
      'artpiece' => $highlighted_artpiece,
      'options' => [
        'details' => false,
        'tooltip' => false,
      ],
    ]);
  }

  echo '<hr />';
}