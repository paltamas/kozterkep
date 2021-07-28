<?php

if (!$list_page_type) {
  $tabs = [
    'Egyszerű keresés' => [
      'hash' => 'egyszeru',
      'icon' => 'search',
    ],
    'Részletes keresés' => [
      'hash' => 'reszletes',
      'icon' => 'th-list',
    ],
  ];

  echo '<div class="px-3 pt-3 pb-2 my-0 my-md-3 rounded bg-gray-kt">';

  if ($total_count <= APP['map']['max_id']) {
    $query = _unset($_params->query, ['oldal', 'elem', 'sorrend', 'kereses', 'minify', 'regen', 'recalc']);
    if (count($query) > 0 && count($artpieces) > 0) {
      echo $app->Html->link('Találatok térképre', '/terkep?' . http_build_query($_params->query), [
        'class' => 'btn btn-outline-primary float-right',
        'hide_text' => true,
        'icon' => 'map-marked'
      ]);
    }
  }

  echo $app->Html->tabs($tabs, [
    'type' => 'pills',
    'align' => 'center',
    'selected' => @$_params->query['r'] == 1 ? 2 : 1,
    'class' => 'mb-md-3',
  ]);



  echo '<div class="tab-content">';
  echo $app->element('search/index/form_simple');
  echo $app->element('search/index/form_detailed');
  echo '</div>';

  echo $app->element('search/index/form_links');
  echo '</div>';
} else {

  echo $app->element('search/index/list_page_type');

}

echo $app->element('search/index/list');