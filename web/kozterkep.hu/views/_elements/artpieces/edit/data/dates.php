<?php
echo $app->Form->help('Add meg az alkotás legfontosabb dátumait. Mindegy, milyen sorrendben rögzíted, mentés után időrendbe tesszük.', ['class' => 'mb-2']);
echo '<div id="date-list">';

$dates = _json_decode($artpiece['dates']);
$dates = $app->Arrays->sort_by_key($dates, 'date', -1);

if (count($dates) == 0) {
  $dates = [
    [
      'id' => 1,
      'date' => '0000-00-00',
      'y' => '0000',
      'm' => '00',
      'd' => '00',
      'century' => 0,
      'type' => 'erection',
      'cca' => 0,
      'bc' => 0,
    ]
  ];
}

foreach ($dates as $date) {
  echo '<div class="row bg-light py-2 mb-2 date-row date-row-' . $date['id'] . '">';

  echo $app->Form->input('dates[' . $date['id'] . '][id]', [
    'type' => 'text',
    'class' => 'd-none date-id-field', // ezt figyeli a JS, amikor hozzáadunk
    'value' => $date['id'],
    'divs' => false,
    'divs' => false,
  ]);

  echo '<div class="col-lg-5 col-12 mb-2 mb-lg-0 form-inline">';

  if ($date['century'] == 0) {

    // Dátumos típus

    echo $app->Form->input('dates[' . $date['id'] . '][y]', [
      'maxlength' => 4,
      'placeholder' => 'Évszám',
      'value' => $date['y'] > 0 ? $date['y'] : '',
      'class' => 'narrow mr-2',
      'divs' => 'm-0 p-0 d-inline',
    ]);
    echo $app->Form->input('dates[' . $date['id'] . '][m]', [
      'type' => 'select',
      'options' => sDB['month_names'],
      'empty' => [0 => 'Hónap...'],
      'value' => $date['m'],
      'class' => 'mr-2',
      'divs' => 'm-0 p-0 d-inline',
    ]);
    echo $app->Form->input('dates[' . $date['id'] . '][d]', [
      'maxlength' => 2,
      'placeholder' => 'Nap...',
      'value' => $date['d'] > 0 ? ltrim($date['d'], '0') : '',
      'class' => 'narrow mr-2',
      'divs' => 'm-0 p-0 pl-2 d-inline',
    ]);

  } else {

    // Százados típus
    echo $app->Form->input('dates[' . $date['id'] . '][century]', [
      'label' => 'Század',
      'maxlength' => 2,
      'placeholder' => '...',
      'value' => $date['century'] > 0 ? $date['century'] : '',
      'class' => 'narrow ml-2',
      'divs' => false,
    ]);

  }

  echo '</div>';


  echo '<div class="col-lg-5 col-12 mb-2 mb-lg-0 form-inline">';

  echo $app->Form->input('dates[' . $date['id'] . '][type]', [
    'type' => 'select_button',
    'options' => sDB['date_types'],
    'value' => $date['type'],
    'divs' => 'mb-0 pb-0 d-inline',
  ]);

  echo '</div>';

  echo '<div class="col-lg-1 col-6 pt-1">';

  if ($date['century'] == 0) {
    echo $app->Form->input('dates[' . $date['id'] . '][cca]', [
      'type' => 'checkbox',
      'label' => '<span class="far text-muted fa-question fa-lg" data-toggle="tooltip" title="Az esemény időpontja bizonytalan vagy hozzávetőleges"></span>',
      'value' => 1,
      'checked' => $date['cca'] == 1 ? true : false,
      'class' => 'd-inline',
      'title' => 'Az esemény időpontja bizonytalan vagy hozzávetőleges',
      'divs' => false,
    ]);
  }

  echo '</div>';

  echo '<div class="col-lg-1 col-6 pt-1 text-right">';

  echo $app->Html->link('', '#', [
    'icon' => 'trash fa-lg',
    'class' => 'text-muted mr-2 cursor-pointer',
    'ia-confirm' => 'Biztosan törlöd ezt az eseményt? Módosíthatod is, ha pontosabb esemény időpontot tudsz.',
    'ia-bind' => 'artpieces.date_delete',
    'ia-pass' => $date['id'],
    'title' => 'Törlés',
  ]);
  echo '</div>';

  echo '</div>';
}

echo '</div>';

echo '<div class="row">';
echo '<div class="col-lg-6 mb-2 mb-lg-0">';
echo $app->Html->link('További esemény évszámmal', '#', [
  'icon' => 'plus',
  'ia-bind' => 'artpieces.date_add',
  'ia-pass' => 'date'
]);
echo '</div>';
echo '<div class="col-lg-6 text-lg-right">';
echo $app->Html->link('További esemény századdal', '#', [
  'icon' => 'plus',
  'ia-bind' => 'artpieces.date_add',
  'ia-pass' => 'century'
]);
echo '</div>';
echo '</div>';