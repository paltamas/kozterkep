<?php
echo '<div class="showroom-info-source d-none" id="file-info-' . $file['id'] . '">';
echo '<div class="bg-gray-kt p-3 info">';

echo '<h4 class="subtitle">' . $file['original_name'] . '</h4>';

echo $file['text'] != '' ? '<div class="mb-4 rounded p-2 bg-yellow-light">' . $app->Text->format($file['text'], ['intro' => 150]) . '</div>' : '';

echo $app->Html->dl('create');

$contact_link = $app->Users->contact_link($file['user_id'], [
  'file_id' => $file['id'],
  'link_options' => [
    'icon' => 'comment-alt',
    'class' => 'px-1',
  ]
]);

echo $app->Html->dl(['Feltöltő', $app->Users->name($file['user_id']) . $contact_link]);

echo $app->Html->dl(['Mappa', $folder['name']]);

echo $app->Html->dl(['Azonosító', $file['id']]);

echo $app->Html->dl(['Forrás', $file['source'] != '' ? $file['source'] : 'Saját']);

if ($file['exif_taken'] > 0) {
  echo $app->Html->dl(['Fotózva', _time($file['exif_taken']) . ' ' . $app->Html->info('A kép EXIF adataiból kiolvasva')]);
}

echo $app->Html->dl(['Feltöltve', _time($file['created'])]);

/*if ($file['size'] > 0) {
  echo $app->Html->dl(['Eredeti méret', _q($file['size'], 'mb')]);
}*/

echo $app->Html->dl('end');

$exif_array = _json_decode($file['exif_json']);
if (isset($exif_array['exif'])) {
  echo $app->File->exif_info($exif_array['exif'], [
    'class' => 'mb-4'
  ]);
}

echo '<hr />';

// Felhasználhatóság
echo '<h6>Felhasználási jogok</h6>';
echo  $app->File->license_info($file['license_type_id'], [
  'class' => 'mb-4 pb-2 border-bottom'
]);

// Letöltés (képeknél csak belépve, CC jog esetén
if ((_contains($file['type'], 'image') && $_user && $file['license_type_id'] > 0 && $file['license_type_id'] != 7)
  || !_contains($file['type'], 'image')) {
  echo $app->Html->link('Fájl letöltése',
    '/mappak/fajl_mutato/' . $file['id'],
    [
      'icon' => 'download',
      'target' => '_blank',
      'class' => 'btn btn-primary',
    ]
  );
}

$if_image = _contains($file['type'], 'image') ? 'Vízjel nélküli változatra van szükséged? ' : '';


if (_contains($file['type'], 'image')) {
  echo '<div class="small">';
  echo $app->Html->link('Kép megnyitása új ablakban', C_WS_S3['url'] . 'files/' . $file['onesize'] . '.jpg', [
    'target' => '_blank',
    'icon' => 'search-plus',
  ]);
  echo '</div>';
}


$link_options = [
  'icon' => 'comment-alt',
  'class' => 'px-1',
];
if ($_user) {
  $link_options['ia-confirm'] = 'Most a beszélgetések oldalra irányítunk, ahol üzenetben kell részletezned a kérésed és egyeztetni a szükséges képméretet, stb.';
}
$contact_link = $app->Users->contact_link($file['user_id'], [
  'file_id' => $file['id'],
  'text' => 'Kérj egyedi engedélyt a feltöltőtől!',
  'link_options' => $link_options,
]);

echo '<p class="text-muted small mt-2"> ' . $if_image . 'A megadott felhasználhatóságtól eltérően használnád a fájlt? '. $contact_link . '</p>';


echo '<div class="thumbnails mt-4"></div>';

echo '</div>'; // gray rounded
echo '</div>'; // showroom-info-source