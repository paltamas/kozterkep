<?php
echo $app->Form->create($artpiece, [
  //'method' => 'post',
  'class' => 'w-100 artpiece-edit-form ajaxForm photos-sort-form d-none',
]);

echo $app->Form->input('photos', [
  'class' => 'd-none',
  'value' => urlencode($artpiece['photos']),
  'id' => 'Photos-sorter'
]);

echo $app->Form->input('top_photo_count', [
  'class' => 'd-none',
]);

echo '<div class="float-right">' . $app->Html->link('Vissza a listához', '#', [
    'icon' => 'arrow-left',
    'class' => 'btn btn-secondary photos-list-button',
  ]) . '</div>';

echo '<h4 class="subtitle">Fotók rendezése</h4>';


if (count($artpiece_photos) > 0) {

  echo '<p>Kattints az áthelyezni kívánt képre, és az egér gombját lenyomva tartva húzd a megfelelő helyre majd mentsd a változásokat. A szintén mozgatható sötét határoló elem előtti fotók jelennek meg a műlap első lapján, a többi kép a képek aloldalon látható csak. Érintőkijelzős eszközön ez az oldal nem érhető el.<br />Az élményképek, adalékok és más helyszínt mutató képek nem jelennek meg a műlap kezdőlapján.<br />A műlap kezdőlapjára válogatott archív képek mindig a lista végén jelennek meg.</p>';

  echo '<div class="row photo-sort" id="drag-and-drop" ia-dragorder=".rank-input" ia-draghandler="draghandler" ia-dragcallback="artpieces.photos_sorted">';

  $top_0 = false;

  $i = 0;
  
  
  $separator_div = '<div class="col-6 py-lg-2 top-separator item">';
  $separator_div .= '<div class="row bg-dark p-2 py-lg-4 rounded my-4 text-white text-center">';
  $separator_div .= '<div class="col-6 draghandler"><span class="far fa-arrow-left mr-2"></span>Kiemelt képek</div>';
  $separator_div .= '<div class="col-6 draghandler">További képek<span class="far fa-arrow-right ml-2"></span></div>';
  $separator_div .= '<div class="col-12 small draghandler">Mozgasd ezt a határoló elemet a kívánt helyre.</div>';
  $separator_div .= '</div>';
  $separator_div .= '</div>';


  foreach ($artpiece_photos as $artpiece_photo) {

    $photo = $photos[$artpiece_photo['id']];

    if ($photo['joy'] == 1 || $photo['other'] == 1 || $photo['other_place'] == 1) {
      continue;
    }

    $i++;

    if ($artpiece_photo['top'] == 0 && !$top_0) {
      echo $separator_div;
      $top_0 = true;
    }

    echo '<div class="col-6 col-md-4 col-lg-2 photo-sort-card photo-sort-card-' . $photo['id'] . '">';

    echo $app->Form->input('photoranks[' . $i . '][rank]', [
      'type' => 'text',
      'value' => $artpiece_photo['rank'],
      'class' => 'd-none rank-input not-form-change item', // trükközés miatt kell kiiktatni az auto form change érzékelést
      'data-photo-id' => $photo['id'],
      'data-photo-slug' => $photo['slug'],
      'data-photo-top' => $artpiece_photo['top'],
    ]);

    echo $app->Image->photo($photo, [
      'size' => 5,
      'class' => 'img-thumbnail draghandler',
    ]);

    echo '</div>';

  }

  if (!$top_0) {
    echo $separator_div;
  }


  // Élményképek és adalékok
  foreach ($artpiece_photos as $artpiece_photo) {

    $photo = $photos[$artpiece_photo['id']];

    if ($photo['joy'] != 1 && $photo['other'] != 1 && $photo['other_place'] != 1) {
      continue;
    }

    $i++;

    echo '<div class="col-6 col-md-4 col-lg-2 photo-sort-card photo-sort-card-' . $photo['id'] . '">';

    echo $app->Form->input('photoranks[' . $i . '][rank]', [
      'type' => 'text',
      'value' => $artpiece_photo['rank'],
      'class' => 'd-none rank-input not-form-change item', // trükközés miatt kell kiiktatni az auto form change érzékelést
      'data-photo-id' => $photo['id'],
      'data-photo-slug' => $photo['slug'],
      'data-photo-top' => $artpiece_photo['top'],
    ]);

    echo $app->Image->photo($photo, [
      'size' => 5,
      'class' => 'img-thumbnail',
    ]);

    echo '</div>';

  }

  echo '</div>'; // drag&drop container

}

echo $app->Form->end();