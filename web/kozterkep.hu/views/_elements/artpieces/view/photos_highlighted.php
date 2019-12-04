<?php
if (count($artpiece_photos) > 0) {
  echo '<div class="text-center">';
  echo $app->Html->link('Fotólista megnyitása (' . count($artpiece_photos) . ')', '#fotolista', array(
    'icon' => 'images',
    'class' => 'btn btn-outline-primary mt-3 mb-4 mr-2 tab-button',
  ));
  echo '</div>';

  //echo '<div class="row mb-3 d-none d-md-flex">';
  echo '<div class="scroll-md-row">';
  echo '<div class="row">';
  $i = 0;

  $photo_list = [
    'currents' => '',
    'archives' => '',
  ];

  foreach ($artpiece_photos as $artpiece_photo) {

    if (!isset($photos[$artpiece_photo['id']])) {
      // migrálás alatt
      continue;
    }

    $photo = $photos[$artpiece_photo['id']] + $artpiece_photo;

    if ($photo['joy'] == 1 || $photo['other'] == 1 || $photo['other_place'] == 1) {
      // A kezdőlapon nincs élménykép se adalék se máshelyszín
      continue;
    }

    $i++;
    /*if ($photo['id'] == $artpiece['photo_id']) {
      continue;
    }*/
    if ($photo['top'] == 0) {
      break;
    }

    $type = $photo['archive'] == 1 ? 'archives' : 'currents';
    $photo_list[$type] .= '<div class="col-4 col-md-6 col-lg-4 scroll-col text-center pr-0 pr-md-2">';
    $photo_list[$type] .= $app->Image->photo($photo, [
      'link' => 'showroom',
      'size' => 5,
      'class' => 'img-thumbnail img-fluid mb-2',
    ]);
    $photo_list[$type] .= '</div>';
  }


  echo $photo_list['currents'];

  if ($photo_list['currents'] != '' && $photo_list['archives'] != '') {
    // Ha van mit elválasztani...
    echo '</div><h6 class="subtitle mt-3">Archív képek</h6><div class="row">';
  }

  echo $photo_list['archives'];

  echo '</div>';

  if ($i < count($artpiece_photos)) {
    echo '<div class="text-muted text-center d-none d-md-block mt-2">';
    echo 'További képek a ';
    echo $app->Html->link('Fotólistában', '#fotolista', array(
      'icon' => 'images',
      'class' => 'font-weight-bold tab-button',
    ));
    echo '</div>'; // row --
  }

  echo '</div>'; // scroll-row --
} else {
  echo $app->element('layout/partials/empty');
}

/*echo '<div class="mt-4 text-center text-md-left">';
echo $app->Html->link('Saját fotók feltöltése', $_editable . '#szerk-fotok', [
  'icon' => 'camera',
]);
echo '</div>';*/