<?php
echo '<div class="showroom-info-source d-none" id="file-info-' . $photo['id'] . '">';
echo '<div class="bg-gray-kt p-3 info">';

echo '<h4 class="subtitle">"' . $artpiece['title'] . '" c. alkotás fotói</h4>';

$artists_text = '';
if (@count(@$artists['artists']) > 0 || @count(@$artists['contributors']) > 0) {
  $artist_label = @count(@$artists['artists']) > 1 ? 'Alkotók' : 'Alkotó';

  // Közreműködőket külön kaptuk, ez plusszolja a dimenziót
  foreach ($artists as $type => $artists_by_type) {
    foreach ($artists_by_type as $artist) {
      $artists_text .= $app->Artists->name($artist['id']);

      // Közreműködők infója, vagy szerep nem szobrász
      if ($artist['profession_id'] > 1 || $type == 'contributors') {
        $artists_text .= ' <span class="text-muted">(';
        // szobrász trivia, nem írjuk ki
        $artists_text .= $artist['profession_id'] > 1 ? '<span data-toggle="tooltip" title="Kifejezetten a jelen alkotás létrehozásában betöltött szerep. Tehát nem feltétlenül egyezik meg a személy többnyire űzött hivatásával, ill. szakmájával.">' . mb_strtolower(sDB['artist_professions'][$artist['profession_id']][0]) . '</span>' : '';
        $artists_text .= $artist['profession_id'] > 1 && $type == 'contributors' ? ', ' : '';
        $artists_text .= $type == 'contributors' ? '<span data-toggle="tooltip" title="Nem vett részt az alkotói folyamatban, de fontos közreműködői szerepet vállalt a kivitelezésben.">közreműködő</span>' : '';
        $artists_text .= ')</span>';
      }
    }
  }
}

echo @$photo['text'] != '' ? '<div class="mb-4 rounded p-2 bg-yellow-light">' . $app->Text->format($photo['text'], ['intro' => 150]) . '</div>' : '';

echo $app->Html->dl('create');

if ($artists_text != '') {
  echo $app->Html->dl([$artist_label, $artists_text]);
}

echo $app->Html->dl(['Település', $app->Places->name($artpiece['place_id'], ['link' => false])]);

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
  echo $app->Html->dl(['Forrás',  '<span class="font-weight-normal">' . $photo['source'] . '</span>']);
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

$extra_info = [];
$extra_fields = ['artist', 'sign', 'unveil', 'other_place', 'other', 'joy', 'archive'];
foreach ($extra_fields as $field) {
  if (@$photo[$field] == 1) {
    $extra_info[] = sDB['photo_fields'][$field];
    if ($field == 'artist' && $photo['artist_id'] > 0) {
      $extra_info[] = '(' . $app->Artists->name($photo['artist_id'], ['link' => false]) . ')';
    }
  }
}

if (count($extra_info) > 0) {
  echo $app->Html->dl(['Képinfó', implode('<br />', $extra_info)]);
}


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
  'link_options' => $link_options
]);


echo '<p class="text-muted small mt-2">Vízjel nélküli változatra van szükséged? A megadott felhasználhatóságtól eltérően használnád a fájlt? ' . $contact_link . '</p>';

echo '<div class="thumbnails mt-4"></div>';

echo '</div>'; // gray rounded
echo '</div>'; // showroom-info-source