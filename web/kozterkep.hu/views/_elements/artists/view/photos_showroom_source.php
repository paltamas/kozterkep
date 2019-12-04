<?php
echo '<div class="showroom-info-source d-none" id="file-info-' . $photo['id'] . '">';
echo '<div class="bg-gray-kt p-3 info">';


if ($artpiece) {
  echo '<div class="row my-3">';
  echo '<div class="col-7 col-md-9">';
  echo '<h4 class="subtitle">Fotó műlapja</h4>';
  echo $app->Html->link($artpiece['title'] . ' (' . $app->Places->name($artpiece['place_id'], ['link' => false]) . ')', '', [
    'artpiece' => $artpiece,
    'class' => 'font-weight-bold'
  ]);
  echo '</div>'; // col--
  echo '<div class="col-5 col-md-3">';
  echo $app->Image->photo($artpiece, [
    'size' => 6,
    'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
    'class' => 'img-fluid img-thumbnail mb-4'
  ]);
  echo '</div>'; // col--
  echo '</div>';
} else {
  echo '<h4 class="subtitle">Fotó az alkotóról</h4>';
}


echo @$photo['text'] != '' ? '<div class="mb-4 rounded p-2 bg-yellow-light">' . $app->Text->format($photo['text'], ['intro' => 150]) . '</div>' : '';


echo $app->Html->dl('create');

$contact_link = $app->Users->contact_link($photo['user_id'], [
  'photo_id' => $photo['id'],
  'link_options' => [
    'icon' => 'comment-alt',
    'class' => 'px-1',
  ]
]);

echo $app->Html->dl(['Feltöltő', $app->Users->name($photo['user_id']) . $contact_link]);
echo $app->Html->dl(['Azonosító', $photo['id']]);

if ($photo['source'] != '') {
  echo $app->Html->dl(['Forrás', $photo['source']]);
}

// Csak ha legalább X idővel korábbi
if (@$photo['exif_taken'] > 0 && $photo['exif_taken'] < ($photo['created'] - sDB['limits']['photos']['exif_taken_shown'])) {
  $taken = date('Y.', $photo['exif_taken']) . ' ' . mb_strtolower(sDB['month_names'][date('n', $photo['exif_taken'])]);
  echo $app->Html->dl(['Fotózva', $taken . ' ' . $app->Html->info('A kép EXIF adataiból kiolvasva')]);
}

if (@$photo['year'] > 0) {
  echo $app->Html->dl(['Évszám', $photo['year']]);
}

echo $app->Html->dl(['Feltöltve', _time($photo['created'])]);

echo $app->Html->dl('end');

echo $app->File->exif_info(_json_decode($photo['exif_json']), [
  'class' => 'mb-4'
]);

echo '<hr />';

// Felhasználhatóság
echo '<h6>Felhasználási jogok</h6>';
echo  $app->File->license_info(@$photo['license_type_id'], [
  'class' => 'mb-4 pb-2 border-bottom'
]);


echo '<div class="small">';
echo $app->Html->link('Kép megnyitása új ablakban', C_WS_S3['url'] . 'photos/' . $photo['slug'] . '_1.jpg', [
  'target' => '_blank',
  'icon' => 'search-plus',
]);
echo '</div>';


$link_options = [
  'icon' => 'comment-alt',
  'class' => 'px-1',
];
if ($_user) {
  $link_options['ia-confirm'] = 'Most a beszélgetések oldalra irányítunk, ahol üzenetben kell részletezned a kérésed és egyeztetni a szükséges képméretet, stb.';
}
$contact_link = $app->Users->contact_link($photo['user_id'], [
  'photo_id' => $photo['id'],
  'text' => 'Kérj egyedi engedélyt a feltöltőtől!',
  'link_options' => $link_options,
]);

echo '<p class="text-muted small mt-2">Vízjel nélküli változatra van szükséged? A megadott felhasználhatóságtól eltérően használnád a fájlt? ' . $contact_link . '</p>';

echo '<div class="thumbnails mt-4"></div>';

echo '</div>'; // gray rounded
echo '</div>'; // showroom-info-source