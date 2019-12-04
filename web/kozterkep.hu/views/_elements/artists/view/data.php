<?php

echo '<h4 class="subtitle">Alapvető adatok</h4>';

echo $app->Html->dl('create', ['class' => 'row mb-0']);

echo $app->Html->dl(['Név', $app->Artists->name($artist, ['link' => false])]);

if ($artist['profession_id'] > 0) {
  echo $app->Html->dl(['Foglalkozás', sDB['artist_professions'][$artist['profession_id']][0]]);
}

if ($artist['corporation'] == 1) {
  echo $app->Html->dl(['Működési forma', '<span class="fal fa-industry mr-2"></span>Gazdasági társaság']);
}
if ($artist['artistgroup'] == 1) {
  echo $app->Html->dl(['Működési forma', '<span class="fal fa-users mr-2"></span>Alkotócsoport']);
}

if (trim($artist['artist_name']) != '') {
  echo $app->Html->dl(['Művésznév', $artist['artist_name']]);
}

if (trim($artist['alternative_names']) != '') {
  echo $app->Html->dl(['Alternatív névalak', $artist['alternative_names']]);
}

if (trim($artist['born_date']) != '') {
  $place = $artist['born_place_name'] != '' ? ', ' . $artist['born_place_name'] : '';
  echo $app->Html->dl(['Született', _lazydate($artist['born_date'], 'Y.m.d.') . $place]);
}

if (trim($artist['death_date']) != '') {
  $place = $artist['death_place_name'] != '' ? ', ' . $artist['death_place_name'] : '';
  echo $app->Html->dl(['Elhunyt', _lazydate($artist['death_date'], 'Y.m.d.') . $place]);
}


if (trim($artist['website_url']) != '') {
  echo $app->Html->dl(['Weboldal', nl2br($artist['website_url'], false)]);
}

if ($artist['artpeople_id'] > 0) {
  echo $app->Html->dl([
    '<span data-toggle="tooltip" title="Kortárs Magyar Művészeti Lexikon">KMML szócikk</span>',
    $app->Html->link('Szócikk megnyitása', '/adattar/lexikon_szocikk/' . $artist['artpeople_id'])
  ]);
}
echo $app->Html->dl('end');

if (trim($artist['admin_memo']) != '') {
  echo '<div class="kt-info-box mt-2 mb-3 mb-lg-0">';
  echo '<span class="text-muted">Megjegyzés:</span> ';
  echo $app->Text->format($artist['admin_memo']);
  echo '</div>';
}