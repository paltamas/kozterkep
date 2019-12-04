<?php
$common_sets = $user_sets = [];
foreach ($sets as $set) {
  if ($set['set_type_id'] == 1) {
    $common_sets[$set['id']] = $set['name'];
  }
  if ($set['set_type_id'] == 2 && $set['user_id'] == $_user['id']) {
    $user_sets[$set['id']] = $set['name'];
  }
}

echo '<div class="row">';
echo '<div class="col-md-6">';
echo $app->Form->input('new_connected_common_set', [
  'label' => 'Közös gyűjteménybe pakolás',
  'options' => $common_sets,
  'empty' => 'Válassz a kapcsoláshoz...',
  'help' => 'A kapcsolást a Gyűjtemény felelős átnézi. Ha új közös gyűjteményt javasolnál, jelezd a Köztér fórumban.',
  'ia-bind' => 'artpieces.connected_set_add',
  'ia-pass' => 'this.value',
  'ia-vars-type' => 1,
]);
echo '</div>';
echo '<div class="col-md-6">';
echo $app->Form->input('new_connected_user_set', [
  'label' => $artpiece['user_id'] != $_user['id']
    ? 'Meghívás saját gyűjteménybe' : 'Kapcsolás saját gyűjteménybe',
  'options' => $user_sets,
  'empty' => 'Válassz a kapcsoláshoz...',
  'help' => $app->Html->link('Gyűjteményeid kezelése', '/gyujtemenyek/sajat', ['target' => '_blank']) . ' Ha újat hozol létre, frissítsd az oldalt, hogy megjelenjen ebben a legördülőben.',
  'ia-bind' => 'artpieces.connected_set_add',
  'ia-pass' => 'this.value',
  'ia-vars-type' => 2,
]);
echo '</div>';
echo '</div>';

echo '<div id="connected-set-list">';

foreach ($connected_sets as $connected_set) {

  echo '<div class="row bg-light py-2 mb-2 connected-set-row connected-set-row-' . $connected_set['id'] . '" data-id="' . $connected_set['id'] . '">';

  echo '<div class="col-6 col-md-2 order-2 order-md-1">';
  echo sDB['set_types'][$connected_set['set_type_id']];
  echo '</div>';

  echo '<div class="col-12 col-md-9 order-1 order-md-2">';
  echo $app->Html->link($connected_set['name'], '#', [
    'set' => $connected_set,
    'target' => '_blank',
    'class' => 'font-weight-bold',
  ]);
  echo '</div>';

  echo '<div class="col-6 col-md-1 order-3 text-right">';
  echo $app->Html->link('', '#', [
    'icon' => 'trash fa-lg',
    'class' => 'text-muted cursor-pointer',
    'ia-confirm' => 'Biztosan kiveszed a műlapot ebből a gyűjteményből?',
    'ia-bind' => 'artpieces.connected_set_delete',
    'ia-pass' => $connected_set['id'],
    'title' => 'Törlés',
  ]);
  echo '</div>';

  echo '</div>'; // row
}

if (count($possible_sets) > 0) {
  echo '<p><span class="far fa-lightbulb mr-2"></span>A műlap címét vizsgálva az alábbi közös gyűjteményeket találtuk, amik érdekesek lehetnek. Add hozzá ahhoz, amibe szerinted is tényleg beleillik, ha tényleg beleillik.';
  foreach ($possible_sets as $possible_set) {
    echo '<br />' . $app->Html->link('Kapcsol', '#', [
      'icon' => 'plus',
      'ia-bind' => 'artpieces.connected_set_add',
      'ia-pass' => $possible_set['id'],
      'ia-vars-type' => 1,
      'ia-vars-name' => $possible_set['name'],
      'class' => 'btn btn-link mr-2',
    ]);
    echo $app->Html->link($possible_set['name'], '#', [
      'set' => $possible_set,
      'target' => '_blank',
    ]);
  }
  echo '</p>';
}

echo '</div>';


echo $app->Form->input('connected_sets', [
  'type' => 'text',
  'class' => 'd-none',
  'value' => urlencode($artpiece['connected_sets']),
]);