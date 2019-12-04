<?php
echo $app->Form->create($_params->query, [
  'class' => 'form-inline p-3 bg-gray-kt rounded justify-content-center'
]);

echo '<div class="row px-3">';

echo $app->Form->input('kulcsszo', [
  'placeholder' => 'Név...',
  'class' => 'mr-4 my-2 col',
]);

echo $app->Form->input('eleje', [
  'type' => 'checkbox',
  'label' => 'Kezdeti egyezés',
  'value' => 1,
  'divs' => 'mt-md-2 mr-4 col text-nowrap',
]);

echo $app->Form->input('monogram', [
  'type' => 'checkbox',
  'label' => 'Monogram',
  'value' => 1,
  'divs' => 'mt-md-2 mr-4 col text-nowrap',
]);

echo $app->Form->input('muveszeti_ag', [
  'type' => 'select',
  'empty' => 'Minden ág',
  'options' => $app->Artists->professions(['only_professions' => 1]),
  'class' => 'mr-4 my-2 col',
]);



echo '</div>';
echo '<div class="row px-3">';

echo $app->Form->input('nemzetiseg', [
  'type' => 'select',
  'empty' => 'Minden nemzetiség',
  'options' => [
    'magyar' => 'Magyar (származású)',
    'nem_magyar' => 'Nem magyar',
  ],
  'class' => 'mr-4 my-2 col',
]);

echo $app->Form->label('Évek', [
  'class' => 'ml-3 ml-md-0 mr-2 mt-2 mt-sm-1'
]);

echo $app->Form->input('ev_ettol', [
  'placeholder' => 'év...',
  'class' => 'mr-2 my-2 narrow col',
]);

echo $app->Html->icon('arrow-right mr-2 mt-3');

echo $app->Form->input('ev_eddig', [
  'placeholder' => 'év...',
  'class' => 'mr-4 my-2 narrow col',
]);

echo $app->Form->input('alkoto_besorolas', [
  'type' => 'select',
  'empty' => 'Minden típus',
  'options' => [
    'szemelyek' => 'Személyek',
    'alkotocsoportok' => 'Alkotócsoportok',
    'tarsasagok' => 'Gazdasági társaságok',
  ],
  'class' => 'mr-4 my-2 col',
]);


echo '</div>';

echo '<div class="row px-3">';

echo $app->Form->input('ellenorizetlen', [
  'type' => 'select',
  'value' => @$_params->query['ellenorizetlen'] > -1
    ? $_params->query['ellenorizetlen'] : 'mindegy',
  'options' => [
    0 => 'Ellenőrzött',
    1 => 'Ellenőrizetlen',
    'mindegy' => 'Minden állapot',
  ],
  'class' => 'mr-4 my-2 col',
]);

if ($_user) {
  echo $app->Form->input('kovetettek', [
    'type' => 'checkbox',
    'label' => 'Követettek',
    'value' => 1,
    'divs' => 'mt-md-2 mr-4 col',
  ]);
}


echo $app->Form->submit('Keresés', [
  'class' => 'btn-secondary my-1',
  'divs' => 'col',
]);
echo '</div>';

echo $app->Form->end();