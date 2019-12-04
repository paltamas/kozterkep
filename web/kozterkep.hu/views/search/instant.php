<?php
echo '<div id="instant_results">';

if (!isset($query['egyszeru'])) {
  echo '<div class="row border-md-bottom pb-md-2">';
  echo '<div class="col-md-6 order-2 order-md-1 text-center text-md-left d-none d-md-block">';
  echo '<h1 class="my-0">Gyorskeresés</h1>';
  echo '</div>';


  echo '<div class="col-md-6 order-1 order-md-2 text-right pt-md-2">';

  echo $app->Html->link('Részletesebb keresés', '/kereses?kulcsszo=' . @$query['kulcsszo'], [
    'icon' => 'search',
    'class' => 'btn btn-link mr-4 d-none d-md-inline block'
  ]);
  echo $app->Html->link('Elrejt', '#', [
    'icon_right' => 'times',
    'class' => 'btn btn-link close-instant-search'
  ]);

  echo '</div>';
  echo '</div>';

  echo '<div class="text-muted my-4 d-none d-md-block">Ebben a listában maximum 200 találatot jelenítünk meg. Ha pontosabb és teljesebb találati listát szeretnél, lépj a ' . $app->Html->link('kereső oldalra', '/kereses?kulcsszo=' . @$query['kulcsszo']) . ', ahol számos szűrőfeltétel segít neked.</div>';
}


// Ha van hasonló alkotó vagy település, akkor két hasábban nyomjuk, egyébként teljes szélesség
if (!isset($query['egyszeru']) && (count($places) > 0 || count($artists) > 0 || count($sets) > 0 || count($users) > 0)) {
  echo '<div class="row">';
  echo '<div class="col-md-9 order-2 order-md-1">';
  echo $app->element('search/instant/results');
  echo '</div>';
  echo '<div class="col-md-3 pt-2 order-1 order-md-2">';
  echo $app->element('search/instant/hints');
  echo '</div>';
  echo '</div>';
} else {
  echo $app->element('search/instant/results', [
    'col_class' => isset($query['egyszeru']) ? 'col-6 col-sm-4 mb-3' : 'col-6 col-md-4 col-lg-3 mb-3',
    'for_map' => isset($query['terkepes']) ? true : false,
  ]);
}

echo '</div>';