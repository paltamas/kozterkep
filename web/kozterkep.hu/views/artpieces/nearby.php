<?php
echo '<span id="get-location"></span>';
echo $app->Form->input('radius', [
  'type' => 'select_button',
  'options' => [
    100 => '100m',
    500 => '500m',
    1000 => '1km',
    2000 => '2km',
    5000 => '5km',
    25000 => '25km',
  ],
  'value' => 100,
  'class' => 'py-1 px-1 radius-select',
  'divs' => [
    'class' => 'text-center mb-4'
  ]
]);
?>

<div class="nearby-page-list text-center text-left" ia-alist-limit="200" ia-alist-showdir="true"></div>