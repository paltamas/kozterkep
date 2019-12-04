<?php
echo $app->Form->create(null, [
  'class' => 'bg-gray-kt py-2 px-3 rounded mb-4'
]);

echo '<h5 class="mt-2 mb-3"><span class="fal fa-cog mr-2"></span>Oldal beállításai</h5>';
echo $app->Form->input('napszam', [
  'label' => 'Hány napra menjünk vissza?',
  'type' => 'select',
  'options' => [
    2 => '2 nap',
    7 => '7 nap',
    15 => '15 nap',
    30 => '30 nap',
  ],
  'class' => 'narrow',
  'value' => $day_count,
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->input('elemszam', [
  'label' => 'Max elemszám dobozonként?',
  'type' => 'select',
  'options' => [
    10 => 10,
    20 => 20,
    50 => 50,
    100 => 100,
    300 => 300,
  ],
  'class' => 'narrow',
  'value' => $item_limit,
  'divs' => 'd-inline-block mb-3 mr-3'
]);

echo $app->Form->end('Beállítás', ['class' => 'btn-secondary']);