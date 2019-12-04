<?php
$tabs = [
  /*'Megtekintés' => [
    'link' => '/' . $artpiece['id'] . '/' . $app->Text->slug($artpiece['title']),
    'icon' => 'file',
    //'target' => '_blank'
  ],*/
  'SzerkKomm' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm',
    'icon' => 'comment-edit',
  ],
  'Bemérés' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-terkep',
    'icon' => 'map-marker',
  ],
  'Fotók' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-fotok',
    'icon' => 'images',
  ],
  'Adatok' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-adatok',
    'icon' => 'list-alt',
  ],
  'Sztorik' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-sztorik',
    'icon' => 'paragraph',
  ],
  'Kapcsok' => [
    'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-kapcsok',
    'icon' => 'paperclip',
  ],
];

if ($app->Users->owner_or_head($artpiece, $_user)) {
  $tabs = $tabs + ['Műveletek' => [
      'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-muveletek',
      'icon' => 'database',
    ]];
}

echo $app->Html->tabs($tabs, [
  'type' => 'pills',
  'align' => 'center',
  'selected' => 1,
  'class' => 'remove-in-modal',
  'preload' => true,
]);
?>

<hr class="remove-in-modal"/>


<div class="row">
  <div class="col-md-8 mb-4">
    <?php
    $not_all_prev = false;

    if (@$edit['invisible'] == 1) {
      echo '<div class="kt-info-box mb-4">';
      echo $app->Html->icon('eye-slash mr-1') . '<strong>Láthatatlan szerkesztés:</strong> Csak a szerkesztés beküldője és a műlap gazdája látja. Elfogadás vagy visszavonás esetén töröljük a naplókból és nem jelenik meg a laptörténetben, valamint nem számít bele a szerkesztési statisztikákba sem. Röviden: beemelés után olyan, mintha soha nem lett volna. Hozzászólás láthatatlan szerkesztéshez nem rögzíthető.';
      echo '</div>';
    }

    // Alapadatok
    echo '<div class="row mb-3">';
    echo '<div class="col-lg-6">';
    echo $app->Html->dl('create');
    echo $app->Html->dl(['Műlap', $app->Html->link($artpiece['title'], '/mulapok/szerkesztes/' . $artpiece['id'], [
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $artpiece['id'],
    ])]);
    echo $app->Html->dl(['Státusz', sDB['edit_statuses'][$edit['status_id']][0]]);
    echo $app->Html->dl(['Azonosító', '<small>' . $edit['id'] . '</small>']);
    echo $app->Html->dl('end');
    echo '</div>';
    echo '<div class="col-lg-6 mb-4">';
    echo $app->Html->dl('create');
    echo $app->Html->dl(['Létrehozás', _time($edit['created'])]);
    echo $app->Html->dl(['Modósítás', _time($edit['modified'])]);
    echo $app->Html->dl(['Szerkesztő', $app->Users->name($edit['user_id'])]);
    echo $app->Html->dl('end');
    echo '</div>';
    echo '</div>';

    //debug($edit);

    // Mezők
    foreach ($edit_details as $key => $change) {

      //debug($change);

      // Előkészítések
      // Régi adat
      $prev_data = $arrow = '';
      if (isset($edit['prev_data'][$key])) {
        $prev_data = $app->Artpieces->edit_field($key, $edit['prev_data'][$key], ['full_values' => true])[1];
        $arrow = '<span class="far fa-arrow-circle-right text-success mx-1 align-middle d-inline-block"></span>';
      } else {
        $not_all_prev = true;
      }

      // Új adat
      $new_data = array_values($change)[0] != '' ? array_values($change)[0] : '-';


      // Ezeket nem mutatjuk, csak begyűjtjük
      if ($key == 'lat') {
        $lat = array_values($change)[0];
        continue;
      }
      if ($key == 'lon') {
        $lon = array_values($change)[0];
        continue;
      }
      if ($key == 'photolist' || $key == 'photos') {
        $prev_photos = $prev_data;
        $new_photos = $new_data;
        continue;
      }
      if ($key == 'descriptions') {
        $prev_descriptions = $prev_data;
        $new_descriptions = $new_data;
        continue;
      }
      if ($key == 'photo_id') {
        // Borító állítás, emellett jön a slug, azt elcsípjük
        continue;
      }

      // Sor indítása
      echo '<div class="row rounded bg-light mb-4 p-2">';
      echo '<div class="col-12 font-weight-bold text-center mb-2">' . key($change) . '</div>';

      // Mezőnként eltérű logika
      switch ($key) {
        case 'place_description':
          echo '<div class="col-12 diff">' . $app->Text->html_diff($prev_data, $new_data) . '</div>';
          break;

        case 'photo_slug':
          echo '<div class="col-md-5 text-center">' . $app->Image->photo($prev_data, [
              'class' => 'img-thumbnail img-fluid',
              'size' => 5,
            ]) . '</div>';
          echo '<div class="col-md-1 text-right mx-0 px-0">' . $arrow . '</div>';
          echo '<div class="col-md-6 text-center">' . $app->Image->photo($new_data, [
              'class' => 'img-thumbnail img-fluid',
              'size' => 5,
            ]) . '</div>';
          break;

        default:
          echo '<div class="col-md-5">' . $prev_data . '</div>';
          echo '<div class="col-md-1 text-right mx-0 px-0">' . $arrow . '</div>';
          echo '<div class="col-md-6">' . $new_data . '</div>';
          break;
      }

      // Sor zárása
      echo '</div>';

    }


    //////// TELJESEN EGYEDI MEGJELENÍTÉSEK


    // Fotók
    if (@$prev_photos && @$new_photos) {
      echo '<div class="row rounded bg-light mb-4 p-2">';
      echo '<div class="col-12 font-weight-bold text-center mb-2">Fotók változása</div>';
      $new_photos = _json_decode($new_photos);
      foreach ($new_photos as $new_photo) {
        echo '<div class="col-3 col-md-2">';
        echo $app->Image->photo($new_photo, [
          'size' => 6,
          'class' => 'img-thumbnail img-fluid'
        ]);
        echo '<div class="mb-2">';
        foreach ($new_photo as $field => $value) {
          if (in_array($field, ['id', 'slug'])) {
            continue;
          }
          list($f, $v) = $app->Artpieces->photo_field($field, $value);
          echo '<div class="mt-2 small">';
          echo $f . ': ';
          echo strlen($v) > 20 ? '<br />' : '';
          echo '<strong>' . $v . '</strong>';
          echo '</div>';
        }
        echo '</div>';
        echo '</div>';
      }

      echo '</div>';
    }


    // Leírások
    if (@$new_descriptions) {
      $prev_descriptions = _json_decode($prev_descriptions);
      $new_descriptions = _json_decode($new_descriptions);

      foreach ($new_descriptions as $key => $new_description) {

        // Hogy tutira az előzmény legyen
        $real_prev = false;
        if (@$prev_descriptions) {
          foreach ($prev_descriptions as $prev_description) {
            if ($prev_description['id'] == $new_description['id']) {
              $real_prev = $prev_description;
            }
          }
        }

        echo '<div class="rounded bg-light mb-4 p-2">';

        if (strpos($new_description['id'], 'new') !== false) {
          $lang = strpos($new_description['id'], 'eng') !== false ? 'angol' : '';
          $from_comment = @$new_description['comment_time'] > 0 ? ' korábbi hozzászólás átminősítésével (' . _time($new_description['comment_time']) . ')' : '';
          echo '<div class="font-weight-bold text-center mb-2">Új ' . $lang . ' leírás létrehozása' . $from_comment . '</div>';
          echo $app->Artpieces->story($new_description);

        } else {
          echo '<div class="font-weight-bold text-center mb-2">Meglévő leírás módosítása</div>';

          if (isset($new_description['text'])) {
            echo '<div class="diff">';
            echo $app->Text->html_diff(@$real_prev['text'], $new_description['text']);
            echo '</div>';
          }
          if (isset($new_description['source'])) {
            echo '<div class="diff">';
            echo '<strong>Források:</strong><br /><linkify_custom>' . $app->Text->html_diff(@$real_prev['source'], $new_description['source']) . '</linkify_custom>';
            echo '</div>';
          }

          if (isset($new_description['text'])) {
            echo '<div class="font-weight-bold mt-4">Új szöveg, formázva</div>';
            echo '<div class="text-muted mb-2">A különbségeket mutató szövegben egyelőre nem érhető el a formázás, ezért külön itt mutatjuk a formázott ÚJ változatot.</div>';
            echo $app->Artpieces->story($new_description, ['intro' => 125]);
          }
        }
        echo '</div>';
      }
    }


    // Térkép
    if (@$lat && @$lon) {

      $map_options = [
        'lat' => $edit['lat'],
        'lon' => $edit['lon'],
        'height' => 400,
        'google_link' => true,
        'iframe' => true,
      ];

      echo '<p class="font-weight-bold mb-0">Pozíció módosulása</p>';
      if (@$edit['prev_data']['lat'] && @$edit['prev_data']['lon']) {
        $map_options['lat0'] = $edit['prev_data']['lat'];
        $map_options['lon0'] = $edit['prev_data']['lon'];
        echo '<p class="my-0">Narancs jelölővel mutatjuk az új helyszínt, szürkével a korábbit.</p>';
      } else {
        $not_all_prev = true;
        echo '<p class="my-0">Narancs jelölővel mutatjuk az új helyszínt.</p>';
      }
      echo $app->element('maps/simple', ['options' => $map_options]);
    }


    if ($not_all_prev) {
      echo '<p class="text-muted">A szerkesztésnek nem volt előzménye, vagy akkor még nem mentettük.</p>';
    }
    ?>

  </div>

  <div class="col-md-4 mb-4">

    <div class="d-none only-in-modal mb-3">
      <?= $app->Html->link('Új lapon nyit', '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'], [
        'target' => '_blank',
        'icon' => 'external-link',
        'class' => 'font-weight-bold',
      ]) ?>
    </div>

    <?= $app->element('artpieces/edit/manage_edit') ?>

    <hr/>
    <h5 class="subtitle">Hozzászólások</h5>
    <?php
    if (@$edit['invisible'] != 1) {
      echo $app->element('comments/thread', [
        'model_name' => 'artpiece',
        'model_id' => $artpiece['id'],
        'custom_field' => 'artpiece_edits_id',
        'custom_value' => $edit['id'],
        'files' => true,
        'search' => false,
        'link_class' => 'd-block',
      ]);
    } else {
      echo '<p class="text-muted">Láthatatlan szerkesztésekhez nem rögzíthető hozzászólás.</p>';
    }
    ?>
  </div>
</div>