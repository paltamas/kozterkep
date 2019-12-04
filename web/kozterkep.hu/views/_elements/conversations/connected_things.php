<?php
$options = (array)@$options + [
  'same_info' => true,
  'hidden_inputs' => true,
];

if ($photo && ($artpiece || $artist)) {
  // Kapcsolódó fotó
  // ezt a műlap előtt, mert itt is lehet műlap kapcsolás
  // és jön $artpiece

  if ($artpiece) {
    $link = $app->Html->link_url('', [
      'artpiece' => $artpiece,
      'url_end' => '#vetito=' . $photo['id'],
    ]);
  } elseif ($artist) {
    $link = $app->Html->link_url('', [
      'artist' => $artist,
      'url_end' => '#vetito=' . $photo['id'],
    ]);
  }

  echo '<div class="row kt-info-box mb-4 d-flex align-items-center">';
  echo '<div class="col-4 col-sm-2 col-lg-1 px-0">';
  echo $app->Image->photo($photo, [
    'size' => 5,
    'class' => 'img-thumbnail img-fluid',
    'link' => $link,
    'link_options' => [
      'target' => '_blank',
    ]
  ]);
  echo '</div>'; // col1


  if ($artpiece) {
    echo '<div class="col">';
    echo '<h6 class="mb-0">Fotó kapcsán erről a műlapról:</h6>';
    echo $app->Html->link($artpiece['title'], '', [
      'artpiece' => $artpiece,
      'target' => '_blank',
      'class' => 'font-weight-bold',
    ]);
    echo '</div>'; // col2
  } elseif ($artist) {
    echo '<div class="col">';
    echo '<h6 class="mb-0">Fotó kapcsán ennél az alkotónál:</h6>';
    echo $app->Html->link($artist['name'], '', [
      'artist' => $artist,
      'target' => '_blank',
      'class' => 'font-weight-bold',
    ]);
    echo '</div>'; // col2
  }


  if ($options['same_info'] && @$same == true) {
    echo '<div class="col-12 mt-3 px-0 same-info"><span class="fal fa-exclamation-circle mr-1"></span>Van már beszélgetésetek erről a fotóról: ';
    echo $app->Html->link('ugorjunk oda bele!', '/beszelgetesek/folyam/' . $same['id']);
    echo '</div>';
  }


  echo '</div>'; // row

  if ($options['hidden_inputs']) {
    if ($artpiece) {
      echo $app->Form->input('artpiece_id', [
        'type' => 'hidden',
        'value' => $artpiece['id'],
      ]);
    } elseif ($artist) {
      echo $app->Form->input('artist_id', [
        'type' => 'hidden',
        'value' => $artist['id'],
      ]);
    }

    echo $app->Form->input('photo_id', [
      'type' => 'hidden',
      'value' => $photo['id'],
    ]);
  }


} elseif ($file) {
  // Kapcsolódó fájl

  echo '<div class="row kt-info-box mb-4 d-flex align-items-center">';
  echo '<div class="col-4 col-sm-2 col-lg-1 px-0">';
  echo $app->Html->image($file['id'], [
    'class' => 'img-thumbnail img-fluid',
    'link' => [
      $app->Html->link_url('', [
        'folder' => $folder,
        'url_end' => '#vetito=' . $file['id']
      ]),
      ['target' => '_blank']
    ],
  ]);
  echo '</div>'; // col1

  echo '<div class="col">';
  echo '<h6 class="mb-0">Fájl kapcsán ebben a mappában:</h6>';
  echo $app->Html->link($folder['name'], '', [
    'folder' => $folder,
    'target' => '_blank',
    'class' => 'font-weight-bold',
  ]);
  echo '</div>'; // col2


  if ($options['same_info'] && @$same == true) {
    echo '<div class="col-12 mt-3 px-0 same-info"><span class="fal fa-exclamation-circle mr-1"></span>Van már beszélgetésetek erről a fájlról: ';
    echo $app->Html->link('ugorjunk oda bele!', '/beszelgetesek/folyam/' . $same['id']);
    echo '</div>';
  }

  echo '</div>'; // row

  if ($options['hidden_inputs']) {
    echo $app->Form->input('file_id', [
      'type' => 'hidden',
      'value' => $file['id'],
    ]);
  }

} elseif ($artpiece) {
  // Kapcsolódó műlap
  echo '<div class="row kt-info-box mb-4 d-flex align-items-center">';
  echo '<div class="col-4 col-sm-2 col-lg-1 px-0">';
  echo $app->Image->photo($artpiece, [
    'size' => 5,
    'class' => 'img-thumbnail img-fluid',
    'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
    'link_options' => ['target' => '_blank']
  ]);
  echo '</div>'; // col1

  echo '<div class="col">';
  echo '<h6 class="mb-0">Kapcsolódó műlap:</h6>';
  echo $app->Html->link($artpiece['title'], '', [
    'artpiece' => $artpiece,
    'target' => '_blank',
    'class' => 'font-weight-bold',
  ]);
  echo '</div>'; // col2


  if ($options['same_info'] && @$same == true) {
    echo '<div class="col-12 mt-3 px-0 same-info"><span class="fal fa-exclamation-circle mr-1"></span>Van már beszélgetésetek erről a műlapról: ';
    echo $app->Html->link('ugorjunk oda bele!', '/beszelgetesek/folyam/' . $same['id']);
    echo '</div>';
  }

  echo '</div>'; // row

  if ($options['hidden_inputs']) {
    echo $app->Form->input('artpiece_id', [
      'type' => 'hidden',
      'value' => $artpiece['id'],
    ]);
  }
}