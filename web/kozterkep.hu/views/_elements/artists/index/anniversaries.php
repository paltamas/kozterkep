<?php
if (count($births) > 0) {
  echo '<div>';
  echo '<strong>Születési évfordulók:</strong> ';
  $i = 0;
  foreach ($births as $artist) {
    $i++;
    echo '<span class="mr-2">';
    echo $app->Artists->name($artist, [
      'class' => 'font-weight-bold',
      'tooltip' => true,
      'profession' => true,
    ]);
    echo $i < count($births) ? ',</span>' : '</span>';
  }
  echo '</div>';
}

if (count($deaths) > 0) {
  echo '<div class="mt-3">';
  echo '<strong>Halálozási évfordulók:</strong> ';
  $i = 0;
  foreach ($deaths as $artist) {
    $i++;
    echo '<span class="mr-2">';
    echo $app->Artists->name($artist, [
      'class' => 'font-weight-bold',
      'tooltip' => true,
      'profession' => true,
    ]);
    echo $i < count($deaths) ? ',</span>' : '</span>';
  }
  echo '</div>';
}

echo $app->Html->link('Minden évforduló', '/alkotok/evfordulok', [
  'icon_right' => 'arrow-right',
  'class' => 'd-block mt-3',
]);