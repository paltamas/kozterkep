<?php
echo $folder['public'] == 0 ? '<div class="alert alert-secondary mb-2"><strong><span class="far fa-lock mr-2"></span>A mappa zárt,</strong> csak te éred el. ' . $app->Html->link('Beállítás', '/mappak/szerkesztes/' . $folder['id'] . '#beallitasok') . '</div>' : '';

echo $folder['description'] != '' ? '<div class="mb-3 pb-2 border-bottom">' . $app->Text->format($folder['description'], ['intro' => 150]) . '</div>' : '';


echo '<div class="mt-2 mb-1 pb-2 border-bottom small">';
echo '<strong>' . $app->Users->name($folder['user_id'], ['image' => 4]) . '</strong> mappája';
echo '</div>';

echo $app->Html->dl('create', ['class' => 'row small text-muted link-not-muted mt-3']);
echo $app->Html->dl(['Frissítve', _time($folder['updated'])]);
echo $app->Html->dl(['Fájlok', _n($folder['file_count']) . ' db']);

if ($folder['public'] == 1) {
  echo $app->Html->dl(['Látogatás', '<span class="model-view-stats">' . _loading() . '</span>']);
}

echo $app->Html->dl('end');