<?php
echo '<div class="mb-4">';

echo $app->Html->link('Új mappa készítése', '#new-folder', [
  'icon' => 'plus',
  'data-toggle' => 'collapse',
  'class' => 'btn btn-secondary',
  'ia-focus' => '#Name'
]);

echo $app->Form->create(null,
  [
    'method' => 'post',
    'id' => 'new-folder',
    'class' => 'collapse mt-4 border rounded p-4'
  ]
);

echo $app->Form->input('name', [
  'label' => 'Mappa megnevezése',
  'required' => true
]);

echo $app->Form->end('Létrehozás');

echo '</div>';



if (count($folders) > 0) {

  echo '<div class="row">';

  foreach ($folders as $folder) {
    echo '<div class="col-lg-3 col-md-3 d-flex">';
    echo $app->element('folders/item', ['folder' => $folder, 'options' => ['show_state' => true]]);
    echo '</div>'; // col
  }

  echo '</div>'; // row
}
