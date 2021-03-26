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

    // Alapadatok
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo $app->Html->dl('create');
    echo $app->Html->dl(['Műlap', $app->Html->link($artpiece['title'], '/mulapok/szerkesztes/' . $artpiece['id'], [
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $artpiece['id'],
    ])]);
    echo $app->Html->dl(['Státusz', sDB['edit_statuses'][$edit['status_id']][0]]);
    echo $app->Html->dl(['Azonosító', '<small>' . $edit['id'] . '</small>']);
    echo $app->Html->dl('end');
    echo '</div>';
    echo '<div class="col-md-6 mb-4">';
    echo $app->Html->dl('create');
    echo $app->Html->dl(['Létrehozás', _time($edit['created'])]);
    echo $app->Html->dl(['Módosítás', _time($edit['modified'])]);
    echo $app->Html->dl(['Szerkesztő', $app->Users->name($edit['user_id'])]);
    echo $app->Html->dl('end');
    echo '</div>';
    echo '</div>';

    echo '<hr />';

    $mod = 0;

    echo $app->Form->create(null, ['method' => 'post']);

    foreach ($edit as $field => $value) {
      switch ($field) {

        case 'title':
        case 'title_alternatives':
        case 'title_en':
        case 'address':
          echo $app->Form->input($field, [
            'value' => $value,
            'label' => @sDB['artpiece_fields'][$field],
          ]);
          $mod++;
          break;

        case 'place_description':
        case 'links':
          echo $app->Form->input($field, [
            'type' => 'textarea',
            'value' => $value,
            'label' => @sDB['artpiece_fields'][$field],
          ]);
          $mod++;
          break;

        case 'descriptions':
          //debug($value);
          foreach ($value as $key => $description) {
            echo '<div class="border rounded bg-light p-3 my-3">';
            if ($description['id'] == 'new_hun') {
              echo '<h5>Új sztori</h5>';
            } elseif ($description['id'] == 'new_eng') {
              echo '<h5>Új angol sztori</h5>';
            } elseif ($description['lang'] == 'HUN') {
              echo '<h5>Meglévő sztori</h5>';
            } elseif ($description['lang'] == 'ENG') {
              echo '<h5>Meglévő angol sztori</h5>';
            }
            if (isset($description['text'])) {
              echo $app->Form->input('descriptions[' . $description['id'] . '][text]', [
                'type' => 'textarea',
                'value' => $description['text'],
                'label' => 'Sztori szövege',
              ]);
            }
            if (isset($description['source'])) {
              echo $app->Form->input('descriptions[' . $description['id'] . '][source]', [
                'type' => 'textarea',
                'value' => $description['source'],
                'label' => 'Felhasznált források',
              ]);
            }
            echo '</div>';
          }
          $mod++;
          break;
      }
    }

    echo '<div class="kt-info-box mt-2"><strong>Csak az egyszerű szöveges mezők módosíthatók.</strong> Amennyiben olyan mezőt szeretnél módosítani, amit nem látsz itt, vissza kell vonnod a szerkesztésedet és új, helyes formában beküldened.</div>';


    ?>

  </div>

  <div class="col-md-4 mb-4">

    <?php
    if ($mod > 0) {
      echo $app->Form->submit('Módosítások mentése', [
        'class' => 'btn btn-primary mb-2 mr-3',
      ]);
    }

    echo $app->Form->end();

    echo $app->Html->link('Szerkesztés visszavonása', '/mulapok/szerkesztes_torlese/' . $artpiece['id'] . '/' . $edit['id'], [
      'class' => 'btn btn-outline-danger mb-2',
      'ia-confirm' => 'Biztosan visszavonod az elfogadás alatt álló szerkesztést?'
    ]);

    echo '<p>';
    echo $app->Html->link('Visszalépés a szerkesztésekhez', '/mulapok/szerkesztes/' . $artpiece['id'] . '', [

    ]);
    echo '</p>';
    ?>

    <hr/>
    <h5 class="subtitle">Hozzászólások</h5>
    <?php
    echo $app->element('comments/thread', [
      'model_name' => 'artpiece',
      'model_id' => $artpiece['id'],
      'custom_field' => 'artpiece_edits_id',
      'custom_value' => $edit['id'],
      'files' => true,
      'search' => false,
    ]);
    ?>
  </div>
</div>