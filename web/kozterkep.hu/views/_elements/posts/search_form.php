<?php
if (isset($category)) {
  $_params->query['tema'] = $category;
}
if (isset($blogger)) {
  $_params->query['tag'] = $blogger;
}

if (isset($large) && !$large) {
  $class_input = '';
  $class_btn = '';
} else {
  $class_input = 'form-control-lg ';
  $class_btn = 'btn-lg ';
}

echo $app->Form->create($_params->query, [
  'action' => isset($action) ? $action : '/blogok/kereses',
  'class' => 'form-inline p-4 bg-gray-kt d-flex rounded justify-content-center'
]);

echo $app->Form->label($app->Html->icon('search mr-2') . 'Keresés', [
  'class' => 'mr-4 my-1 text-muted'
]);

echo $app->Form->input('kulcsszo', [
  'placeholder' => 'Keresett kifejezés',
  'class' => $class_input . 'mr-4 my-1'
]);

echo $app->Form->input('tema', [
  'type' => 'select',
  'options' => ['barmilyen' => 'Bármilyen téma'] + $app->Blog->category_list(),
  'class' => $class_input . 'mr-4 my-1',
]);

if (isset($_params->query['tag'])) {
  echo $app->Form->input('tag', [
    'type' => 'select',
    'options' => ['barki' => 'Bárki'] + $app->Users->list('bloggers'),
    'class' => $class_input . 'mr-4 my-1 limited',
  ]);
}

if (isset($status_filter)) {
  echo $app->Form->input('statusz', [
    'type' => 'select',
    'options' => ['minden' => 'Minden státusz'] + [
      1 => 'Szerkesztés alatt',
      5 => 'Publikus',
    ],
    'class' => $class_input . 'mr-4 my-1 limited',
  ]);
}

echo $app->Form->submit('Keres', [
  'class' => $class_btn . 'btn-secondary my-1',
]);

echo $app->Form->end();