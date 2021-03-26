<?php
$active_class = @$_params->query['r'] == 1 ? ' active show ' : '';
echo '<div class="tab-pane' . $active_class . '" id="reszletes" role="tabpanel" aria-labelledby="reszletes-tab">';

echo $app->Form->create($query, [
  'class' => 'unsetEmptyFields'
]);

echo $app->Form->input('r', [
  'type' => 'hidden',
  'value' => 1,
]);


echo '<div class="row mb-2">';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('kulcsszo', [
  'label' => 'Keresett kifejezés',
  'divs' => 'pb-0 mb-0 mb-sm-3'
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('leirasban', [
  'label' => 'Leírásokban is',
  'type' => 'checkbox',
  'value' => 1,
  'divs' => 'text-search-1',
  'ia-uncheck-all' => '.text-search-2'
]);
echo $app->Form->input('kulcsszo_reszben', [
  'label' => 'Részleges egyezés is',
  'type' => 'checkbox',
  'value' => 1,
  'divs' => 'text-search-1',
  'ia-uncheck-all' => '.text-search-2'
]);
echo $app->Form->input('hasonlo', [
  'label' => 'Hasonlókat is',
  'type' => 'checkbox',
  'value' => 1,
  'divs' => 'mb-3 text-search-2',
  'ia-uncheck-all' => '.text-search-1'
]);
echo '</div>';



echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('evszam_ettol', [
  'class' => 'narrow d-inline-block mr-1 mb-3',
  'label' => 'Évszám ettől&ndash;eddig&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
  'ia-input' => 'number',
  'ia-input-min' => '0',
  'ia-input-max' => date('Y')+10, // későbbi elbontásokra gondolva ;)
  'divs' => false,
]);
echo $app->Html->icon('long-arrow-right d-none d-md-inline-block');
echo $app->Form->input('evszam_eddig', [
  'class' => 'narrow d-inline-block mb-3 ml-1',
  'ia-input' => 'number',
  'ia-input-min' => '0',
  'ia-input-max' => date('Y')+10,
  'divs' => false,
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('evszam_kereses', [
  'label' => 'Évszám-logika',
  'type' => 'select',
  'options' => [
    '' => 'Legtágabb intervallumot veszi',
    'legutolsok' => 'Legutolsó évszámok alapján',
    'legelsok' => 'Legelső évszámok alapján',
  ],
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('kozos_gyujtemeny', [
  'label' => 'Közös gyűjteményben',
  'type' => 'select',
  'options' => [
    '' => '...',
    'egyikben-sem' => '--Egyikben sem--',
  ] + $sets,
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('peldas', [
  'label' => 'Példás műlap',
  'options' => [
    '' => '...',
    'igen' => 'Példás műlapok',
    'nem' => 'Nem példás műlapok',
  ],
]);
echo '</div>';


echo '</div>';


echo '<div class="row mb-2">';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('alkoto_az', [
  'type' => 'hidden',
  'id' => 'artist_id_detailed',
]);
echo $app->Form->input('alkoto', [
  'class' => 'noEnterInput',
  'label' => 'Alkotó neve',
  'id' => 'artist_detailed',
  'ia-auto' => 'artists',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#artist_id_detailed',
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('alkotoi_szerep', [
  'label' => 'Alkotói szerep',
  'options' => [
    '' => 'Minden',
    'alkoto' => 'Alkotó',
    'kozremukodo' => 'Közreműködő',
  ]
]);
echo '</div>';


echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('hely_az', [
  'type' => 'hidden',
  'id' => 'place_id_detailed',
]);
echo $app->Form->input('hely', [
  'class' => 'noEnterInput',
  'label' => 'Település',
  'id' => 'place_detailed',
  'ia-auto' => 'places',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#place_id_detailed',
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('megye', [
  'label' => 'Megye',
  'type' => 'select',
  'empty' => '...',
  'options' => $app->Arrays->id_list(sDB['counties'], 0, ['sort' => 'ASC']),
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('kerulet', [
  'label' => 'Kerület',
  'type' => 'select',
  'empty' => '...',
  'options' => $app->Arrays->id_list(sDB['districts'], 0),
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('orszag', [
  'label' => 'Ország',
  'type' => 'select',
  'empty' => '...',
  'options' => $app->Arrays->id_list(sDB['countries'], 1, ['sort' => 'ASC']),
]);
echo '</div>';


echo '</div>';




echo '<div class="row mb-2">';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('allapot', [
  'label' => 'Állapot',
  'type' => 'select',
  'empty' => '...',
  'options' => [
    'fellelheto' => '-- Fellelhető --',
    'nincsott' => '-- Nincs már a helyén --',
  ] + $app->Arrays->id_list(sDB['artpiece_conditions'], 0),
]);
echo '</div>';


echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('elhelyezkedes', [
  'label' => 'Elhelyezkedés',
  'type' => 'select',
  'empty' => '...',
  'options' => [
    1 => 'Köztéren',
    2 => 'Közösségi térben',
  ],
  'ia-conn-show' => 'location'
]);
echo '</div>';

// EZT AKKOR hozzuk be, ha majd minden végig van pipálva...
$d_none = @$_params->query['elhelyezkedes'] == 2 ? '' : 'd-none fade';
echo '<div class="col-sm-6 col-md-2 ' . $d_none . ' location" id="location-2">';
echo $app->Form->input('kozossegi_ter', [
  'label' => 'Közösségi tér típusa',
  'type' => 'select',
  'empty' => '...',
  'options' => $app->Arrays->id_list(sDB['not_public_types'], 0),
]);
echo '</div>';


echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('magyar_vonatkozas', [
  'label' => 'Magyar vonatkozás',
  'type' => 'select',
  'empty' => '...',
  'options' => [
    'minden_magyar' => 'Minden magyar vonatkozású',
    'hazai' => 'Hazai alkotások',
    'kulfoldi_magyar' => 'Külföldi nagyar vonatkozásúak',
    'kulfoldi_nem_magyar' => 'Külföldi nem magyar von.',
    'kulfoldi' => 'Minden külföldi alkotás',
  ],
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-2">';
echo $app->Form->input('letrehozo', [
  'label' => 'Létrehozó',
  'type' => 'select',
  'empty' => '...',
  'options' => $creator_users,
]);
echo '</div>';

if ($_user) {
  echo '<div class="col-sm-6 col-md-2">';

  $user_filtered = '';
  if (@$_params->query['sajat'] == 1 || @$_params->query['kovetettek'] == 1
    || @$_params->query['sajat_statusz'] > 0 || @$_params->query['sajat_statusz'] == 'nem-publikusak') {
    $user_filtered = ' text-primary ';
  }

  echo '<div><span class="fas ' . $user_filtered . ' fa-user mr-1"></span>Tagi szűrők</div>';

  echo $app->Form->input('sajat', [
    'label' => 'Saját műlapok',
    'type' => 'checkbox',
    'value' => 1,
    'divs' => 'pt-1'
  ]);

  echo $app->Form->input('kovetettek', [
    'label' => 'Követettek',
    'type' => 'checkbox',
    'value' => 1,
    'divs' => 'pt-1'
  ]);

  echo '</div>';

  echo '<div class="col-sm-6 col-md-2">';

  echo $app->Form->input('sajat_statusz', [
    'label' => 'Sajátok státuszonként',
    'type' => 'select',
    'options' => [
      '' => '...',
      'nem-publikusak' => '-- Nem publikusak --',
    ] + $app->Arrays->id_list(sDB['artpiece_statuses'], 0),
  ]);

  echo '</div>';
}

echo '</div>';


echo '<div class="row">';

// Paraméterek
echo '<div class="col-12 col-md-4 mb-4">';
echo $app->Html->icon('check-square mr-1 text-muted');
echo $app->Html->link('Szűrés paraméterek szerint', '#parameterek', [
  'icon_right' => 'caret-down fas',
  'data-toggle' => 'collapse',
  'class' => 'font-weight-bold',
]);
if (count($selected_parameters['generals']) > 0) {
  echo '<div class="mt-1 text-muted">';
  echo '<strong>' . implode(', ', $selected_parameters['generals']) . '</strong>';
  echo '</div>';
}
if (count($selected_parameters['parameters']) > 0) {
  echo '<div class="mt-1 text-muted">';
  echo @$query['parameter_kapcsolas'] == 'vagy' ? 'Legalább egy: ' : 'Ez mind: ';
  echo '<strong>' . implode(', ', $selected_parameters['parameters']) . '</strong>';
  echo '</div>';
}

echo '</div>';

echo '<div class="col-12 col-md-4 text-md-center mb-4">';
echo $app->Html->icon('empty-set mr-1 text-muted');
echo $app->Html->link('Hiánykereső', '#hianykereso', [
  'icon_right' => 'caret-down fas',
  'data-toggle' => 'collapse',
  'class' => 'font-weight-bold',
]);
echo '</div>';

if (@$_user['headitor'] == 1 || @$_user['admin'] == 1) {
  echo '<div class="col-12 col-md-4 text-md-right mb-4">';
  echo $app->Html->icon('glasses-alt mr-1 text-muted');
  echo $app->Html->link('Főszerk-szűrések', '#foszerkesztok', [
    'icon_right' => 'caret-down fas',
    'data-toggle' => 'collapse',
    'class' => 'font-weight-bold',
  ]);
  echo '</div>';
}

echo '</div>'; // row --


echo '<div class="border rounded bg-light p-3 collapse mb-3" id="parameterek">';
echo $app->element('search/index/parameters');
echo '</div>';

echo $app->element('search/index/void');

echo $app->element('search/index/headitors');

echo '<div class="row">';
echo '<div class="col-12 text-center mb-3">';
echo $app->Form->submit('Mehet', [
  'name' => 'kereses',
  'class' => 'btn-lg',
]);
echo '</div>';
echo '</div>';



echo $app->Form->end();

echo '</div>';
