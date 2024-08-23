<?php
echo '<h4 class="subtitle mt-0">Adatlap infók</h4>';

echo $app->Html->dl('create', ['class' => 'row mb-0 small text-muted link-not-muted']);

if ($place['country_id'] > 0) {
  echo $app->Html->dl([
    'Ország',
    $app->Html->link(sDB['countries'][$place['country_id']][1], '', [
      'country' => sDB['countries'][$place['country_id']] + ['id' => $place['country_id']]
    ])
  ]);
}

if ($place['county_id'] > 0 && $place['id'] != 110) {
  echo $app->Html->dl([
    'Vármegye',
    $app->Html->link(sDB['counties'][$place['county_id']][0], '', [
      'county' => sDB['counties'][$place['county_id']] + ['id' => $place['county_id']]
    ])
  ]);
}


echo $app->Html->dl(['Műlapok', $place['artpiece_count'] > 0 ? $place['artpiece_count'] : '-']);

echo $app->Html->dl(['Látogatás', '<span class="model-view-stats">' . _loading() . '</span>']);

echo $app->Html->dl(['Kezeli', $app->Users->name($place['user_id'])]);
echo $app->Html->dl(['Ellenőrizve', $place['checked'] == 1 ? _time($place['checked_time']) : '-']);
echo $app->Html->dl(['Frissítve', _time($place['modified'])]);
echo $app->Html->dl(['Létrehozás', _time($place['created'])]);
echo $app->Html->dl('end');


if ($place['id'] == 110) {
  echo '<div class="mt-3">';
  echo $app->Html->link('Budapesti kerületek oldala', '/helyek/budapesti-keruletek', [
    'class' => 'font-weight-bold',
    'icon_right' => 'arrow-right',
  ]);
  echo '</div>';
}