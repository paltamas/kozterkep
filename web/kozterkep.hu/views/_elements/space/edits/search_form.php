<?php
echo $app->Form->create($_params->query, [
  'class' => 'unsetEmptyFields form-inline'
]);

echo $app->Form->input('statusz', [
  'options' => [0 => 'Minden státusz...']
    + $app->Arrays->id_list(sDB['edit_statuses'], 0, ['excluded_keys' => [1,3,7]]),
  'class' => 'm-1 narrow'
]);

echo $app->Form->input('tag', [
  'options' => [
      '' => 'Bárki által',
      'altalam' => '-- Általam --',
      'nekem' => '-- Nekem --',
    ] + $app->Users->list('made_edits'),
  'class' => 'm-1 narrow'
]);

echo $app->Form->input('rendezes', [
  'options' => [
      'csokkeno' => 'Újak elől',
      'novekvo' => 'Régiek elől',
      'jovahagyas' => 'Jóváhagyás szerint csökkenő',
  ],
  'class' => 'm-1 narrow'
]);

echo $app->Form->submit('Mehet', [
  'name' => 'kereses',
  'class' => 'btn btn-secondary',
  'divs' => 'form-group',
]);

echo $app->Form->end();

if (@$_params->query['statusz'] != ''
  || @$_params->query['tag'] != ''
  || @$_params->query['rendezes'] != '') {
  echo '<div class="small mt-2">'
    . $app->Html->link('Szűrés törlése', $_params->path, ['icon' => 'times', 'class' => ''])
    . '</div>';
}