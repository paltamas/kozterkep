<?php
echo $app->Form->create($_params->query, [
  'class' => 'bg-gray-kt py-2 px-3 rounded mb-4 unsetEmptyFields '
]);

echo $app->Form->input('tag', [
  'label' => 'Fotó feltöltője',
  'options' => $app->Users->list('photoers'),
  'divs' => 'd-inline-block mb-3 mr-3',
  'empty' => '...',
]);

echo $app->Form->input('kihez', [
  'label' => 'Kihez töltötte?',
  'options' => [
    'mashoz' => 'Máshoz',
    'magahoz' => 'Saját műlapjára',
  ],
  'empty' => '...',
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->input('archiv', [
  'type' => 'checkbox',
  'value' => 1,
  'label' => 'Archív',
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->input('adalek', [
  'type' => 'checkbox',
  'value' => 1,
  'label' => 'Adalék',
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->input('elmenyfoto', [
  'type' => 'checkbox',
  'value' => 1,
  'label' => 'Élménykép',
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->input('mas_helyrol', [
  'type' => 'checkbox',
  'value' => 1,
  'label' => 'Máshonnan',
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->end('Szűrés', ['class' => 'btn-secondary']);

if ($filtered) {
  echo '<div class="small text-center">' . $app->Html->link('szűrések törlése', '/fotok/kereses', [
    'icon' => 'times'
  ]) . '</div>';
}