<?php
echo $app->Html->dl('create', ['class' => 'row mb-0 small text-muted link-not-muted']);
echo $app->Html->dl(['Műlapok', count($artpieces) > 0 ? count($artpieces) : '-']);
echo $app->Html->dl(['Kezeli', $app->Users->name($set['user_id'])]);
echo $app->Html->dl(['Bővült', _time($set['updated'])]);
echo $app->Html->dl(['Létrehozás', _time($set['created'])]);
echo $app->Html->dl('end');