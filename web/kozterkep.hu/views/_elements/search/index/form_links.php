<?php
echo '<div class="row">';

echo '<div class="col-12 col-md-2 text-center text-md-left">';
if (count($query) > 0) {
  echo $app->Html->link('Szűrés törlése', '/kereses', [
    'icon' => 'times',
    'class' => 'btn btn-outline-primary btn-sm mb-2'
  ]);
}
echo '</div>';

echo '<div class="col-12 col-md-10 text-center text-md-right">';
echo $app->element('search/index/history');
echo '</div>';
echo '</div>';
