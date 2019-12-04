<?php
echo '<h4 class="subtitle mt-0">Adatlap infók</h4>';
echo $app->Html->dl('create', ['class' => 'row mb-0 small text-muted link-not-muted']);
echo $app->Html->dl(['Műlapok', $artist['artpiece_count'] > 0 ? $artist['artpiece_count'] : '-']);
echo $app->Html->dl(['Látogatás', '<span class="model-view-stats">' . _loading() . '</span>']);
echo $app->Html->dl(['Kezeli', $app->Users->name($artist['user_id'])]);
echo $app->Html->dl(['Ellenőrizve', $artist['checked'] == 1 ? _time($artist['checked_time']) : '-']);
echo $app->Html->dl(['Frissítve', _time($artist['modified'])]);
echo $app->Html->dl(['Létrehozás', _time($artist['created'])]);
echo $app->Html->dl('end');