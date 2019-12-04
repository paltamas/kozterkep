<?php
$options = (array)$options + [
  'action' => $_params->here,
  'placeholder' => 'Szabad szavas keresés',
  'start_filter' => false,
  'user_filter' => false,
  'custom_inputs' => false,
  'class' => 'mb-3',
];

// Volt-e URL szűrés. A submit miatt kell 2.
$has_filtered = count($_params->query) > 1 ? true : false;

$search_col = 8;

if ($options['start_filter']) {
  $search_col -= 2;
}
if ($options['user_filter']) {
  $search_col -= 2;
}
if ($options['custom_inputs']) {
  $visible_inputs = 0;
  foreach ($options['custom_inputs'] as $ci) {
    if (@$ci[1]['type'] != 'hidden') {
      $visible_inputs++;
    }
  }
  $search_col -= 2 * $visible_inputs;
}

$search_col = max(2, $search_col);

echo $app->Form->create($_params->query, [
  'action' => $options['action'],
  'method' => 'get',
  'class' => 'row unsetEmptyFields ' . $options['class'],
]);

echo '<div class="col-9 col-md-' . $search_col . '">';
echo $app->Form->input('kulcsszo', [
  'placeholder' => $options['placeholder'],
  'autocomplete' => 'off',
  'prepend_icon' => 'search',
  'help' => $has_filtered
    ? $app->Html->link('Szűrés törlése', $_params->path, ['icon' => 'times', 'class' => ''])
      : ''
]);
echo '</div>';

if ($options['start_filter']) {
  echo '<div class="col-12 col-md-2 pt-md-1">';
  echo $app->Form->input('eleje', [
    'type' => 'checkbox',
    'label' => 'Kezdeti egyezés',
    'value' => 1,
  ]);
  echo '</div>';
}

if ($options['custom_inputs'] && @count($options['custom_inputs'] > 0)) {
  foreach ($options['custom_inputs'] as $custom_input) {
    if (@$custom_input[1]['type'] != 'hidden') {
      echo '<div class="col-12 col-md-2 mb-2 mb-md-0">';
    }
    echo $app->Form->input($custom_input[0], $custom_input[1]);
    if (@$custom_input[1]['type'] != 'hidden') {
      echo '</div>';
    }
  }
}

if ($options['user_filter']) {
  $list_type = strlen($options['user_filter']) > 1 ? $options['user_filter'] : 'actives';
  echo '<div class="col-12 col-md-2 mb-2 mb-md-0">';
  echo $app->Form->input('tag', [
    'options' => ['' => 'Minden tag'] + $app->Users->list($list_type),
    'class' => false,
  ]);
  echo '</div>';
}

echo '<div class="col-3">';
echo $app->Form->submit('Mehet', [
  'name' => 'mehete',
  'class' => 'btn-secondary',
]);
echo '</div>';

echo $app->Form->end();