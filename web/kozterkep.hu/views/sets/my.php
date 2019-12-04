<?php
echo '<div class="mb-4">';

echo $app->Html->link('Új gyűjtemény készítése', '#new-set', [
  'icon' => 'plus',
  'data-toggle' => 'collapse',
  'class' => 'btn btn-secondary',
  'ia-focus' => '#Name'
]);

echo $app->Form->create(null,
  [
    'method' => 'post',
    'id' => 'new-set',
    'class' => 'collapse mt-4 border rounded p-4'
  ]
);

echo $app->Form->input('name', [
  'label' => 'Gyűjtemény megnevezése',
  'required' => true
]);

echo $app->Form->end('Létrehozás');

echo '</div>';



if (count($sets) > 0) {

  echo '<div class="row">';

  foreach ($sets as $set) {
    echo '<div class="col-sm-6 col-md-4 col-lg-3 d-md-flex">';
    echo $app->element('sets/item', ['set' => $set]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs még ilyened.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}

