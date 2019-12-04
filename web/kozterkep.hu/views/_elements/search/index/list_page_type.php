<?php
echo '<div class="mb-3 kt-info-box text-center">';
if (isset($_params->query['visszalepes'])) {
  echo $app->Html->link('Visszalépés', $_params->query['visszalepes'], [
    'class' => 'btn btn-outline-secondary mx-2',
    'hide_text' => true,
    'icon' => 'arrow-left'
  ]);
}

if ($total_count <= APP['map']['max_id']) {
  echo $app->Html->link('Találatok térképre', '/terkep?' . http_build_query($_params->query), [
    'class' => 'btn btn-outline-primary mx-2',
    'hide_text' => true,
    'icon' => 'map-marked'
  ]);
}

echo $app->Html->link('Más keresés', '/kereses', [
  'class' => 'btn btn-outline-primary mx-2',
  'hide_text' => true,
  'icon' => 'search'
]);

echo '</div>';
