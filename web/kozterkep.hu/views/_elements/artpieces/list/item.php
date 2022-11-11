<?php
$options = (array)@$options + [
  'photo_size' => 4,
  'photo_class' => '',
  'background' => 'rounded bg-light',
  'links' => true,
  'simple' => false,
  'tooltip' => false,
  'for_map' => false,
  'details' => true,
  'details_simple' => false,
  'extra_class' => 'mx-1 my-2 py-1 px-2',
  'link_options' => [],
  'status' => false,
  'condition' => false,
  'editor_info' => false,
];

if ($options['for_map']) {
  $options['extra_class'] = 'mb-2 small';
  $options['links'] = false;
}

echo '<div class="' , strpos($options['extra_class'], '-left') === false ? 'text-center' : '' , '">';

echo '<div class="' . $options['background'] . ' ' . $options['extra_class'] . '">';

if ($options['for_map']) {
  $link_options = [
    'class' => 'showOnMap',
    'data-lat' => $artpiece['lat'],
    'data-lon' => $artpiece['lon'],
    'data-id' => $artpiece['id'],
    'data-animation' => 0,
  ];
} else {
  $link_options = [
    'artpiece' => $artpiece
  ];
}

if ($options['tooltip']) {
  $link_options = $link_options + [
    'ia-tooltip' => 'mulap',
    'ia-tooltip-id' => $artpiece['id'],
  ];
}

if (count($options['link_options']) > 0) {
  $link_options += $options['link_options'];
}

echo $app->Html->link($app->Image->photo($artpiece, [
  'size' => $options['photo_size'],
  'class' => 'img-thumbnail ' . $options['photo_class'],
]), '#', $link_options);


if ($options['editor_info']) {
  $s = '';
  if ($artpiece['status_id'] == 2 && $artpiece['publish_pause'] == 1) {
    $s .= '<span class="fas fa-pause-circle mx-1 text-secondary" data-toggle="tooltip" title="Szünetel a publikálási szavazás"></span>';
  }
  if ($artpiece['open_question'] == 1) {
    $s .= '<span class="fas fa-question-circle mx-1 text-secondary" data-toggle="tooltip" title="Nyitott kérdés van a műlapon"></span>';
  }
  echo $s != '' ? '<div class="mb-2 editor-info" data-id="' . $artpiece['id'] . '">' . $s . '</div>' : '';
}



if (!$options['simple']) {

  echo '<div class="' , strpos($options['extra_class'], '-left') === false ? 'text-center' : '' , '">';


  if ($options['status']) {
    if ($artpiece['status_id'] != 5) {
      $status = sDB['artpiece_statuses'][$artpiece['status_id']];
      echo '<div class="small mb-1"><span class="badge badge-' . $status[1] . '">' . $status[0] . '</span></div>';
    }
  }

  echo '<div class="font-weight-bold">';
  if ($options['links'] || $options['for_map']) {
    echo $app->Html->link($artpiece['title'], '', [
      'artpiece' => $artpiece,
      'icon_right' => $options['for_map'] ? 'arrow-right fal fa-sm' : '',
    ]);
  } else {
    echo $artpiece['title'];
  }
  echo '</div>';

  if ($options['condition']) {
    if ($artpiece['artpiece_condition_id'] != 1) {
      $condition = sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']];
      echo '<span class="font-weight-normal badge badge-' . $condition[4] . ' small mr-2"><span class="fas fa-' . $condition[5] . ' mr-2"></span>' . $condition[0] . '</span>';
    }
  }

  echo $app->Places->name($artpiece['place_id'], ['link' => $options['links']]);
  $year = $app->Artpieces->get_artpiece_year($artpiece['dates']);
  echo $year != '' ? ', ' . $year : '';

  $artist = $app->Artpieces->get_artpiece_artist($artpiece['artists']);
  if ($artist) {
    echo '<br />' . $app->Artists->name($artist['id'], ['link' => $options['links']]);
  }

  // Ezt most kikapcsoltam
  if (1 == 2 && $options['for_map']) {
    echo $app->Html->link('Menj oda', '#', [
      'class' => 'd-block my-1 showOnMap',
      'icon' => 'bullseye-pointer',
      'data-lat' => $artpiece['lat'],
      'data-lon' => $artpiece['lon'],
      'data-id' => $artpiece['id'],
      'data-animation' => 0,
    ]);
  }

  // Tag, idő, stb
  if (!$options['for_map'] && $options['details_simple']) {
    echo '<div class="small text-muted mt-2 pt-2 border-top mx-0">';
    echo '<div class="mb-2">' . $app->Users->name($artpiece['user_id'], [
      'tooltip' => true,
      'image' => 5,
    ]) . '</div>';
    if ($artpiece['published'] > 0) {
      echo '<span class="fal fa-share-square mr-1"></span>' . _time($artpiece['published'], ['format' => 'y.m.d.']);
    } elseif ($artpiece['submitted'] > 0) {
      echo '<span class="fal fa-users mr-1"></span>' . _time($artpiece['submitted'], ['format' => 'y.m.d.']);
    } else {
      echo '<span class="fal fa-edit mr-1"></span>' . _time($artpiece['updated'], ['format' => 'y.m.d.']);
    }
    echo '</div>';
  } elseif (!$options['for_map'] && $options['details']) {
    echo '<div class="row small text-muted mt-2 pt-2 border-top mx-0 d-none d-sm-flex">';
    echo '<div class="col-3 mb-1 mx-0 px-0">';
    echo '<span class="fal fa-eye mr-1"></span>' . $artpiece['view_total'];
    echo '</div>';
    echo '<div class="col-9 mb-1 mx-0 pl-0 pr-2 text-right">';
    echo $app->Users->name($artpiece['user_id'], [
      'tooltip' => true,
      'image' => 5,
    ]);
    echo '</div>';
    echo '<div class="col-3 mb-1 mx-0 px-0">';
    echo '#' . $artpiece['id'];
    echo '</div>';
    echo '<div class="col-9 mb-1 mx-0 pl-0 pr-2 text-right">';
    if ($artpiece['published'] > 0) {
      echo '<span class="fal fa-share-square mr-1"></span>' . _time($artpiece['published'], ['format' => 'y.m.d.']);
    } elseif ($artpiece['submitted'] > 0) {
      echo '<span class="fal fa-users mr-1"></span>' . _time($artpiece['submitted'], ['format' => 'y.m.d.']);
    } else {
      echo '<span class="fal fa-edit mr-1"></span>' . _time($artpiece['updated'], ['format' => 'y.m.d.']);
    }
    echo '</div>';
    echo '</div>';
  }



  // tag, idő --


  echo '</div>';
}

echo '</div>';
echo '</div>';