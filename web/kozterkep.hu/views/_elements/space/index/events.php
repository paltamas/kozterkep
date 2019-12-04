<?php

/*echo '<div class="text-right">';
echo $app->Html->link('', '/kozter/laptortenet', [
  'title' => 'Ugrás a laptörténhethez',
  'icon' => 'history',
  'class' => 'mt-2',
]);
echo '</div>';*/

echo '<div class="mb-2 pb-3 pb-md-0 border-bottom border-md-0">';
$i = 0;
foreach ($events as $event) {
  $i++;
  echo $app->element('events/item', ['event' => $event, 'options' => [
    'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
  ]]);
}

echo '<div class="text-center">';
echo $app->Html->link('Ugrás a laptörténhethez', '/kozter/laptortenet', [
  'icon' => 'history',
  'class' => 'btn btn-outline-primary',
]);
echo '</div>';

echo '</div>';