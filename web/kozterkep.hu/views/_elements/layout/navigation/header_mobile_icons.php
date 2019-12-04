<?php
echo $app->Html->link(
  $app->Html->image('kozterkep-app-icon.png', [
    'style' => 'width: 30px;',
    'class' => 'aria-hide',
  ]),
  '/',
  [
    'class' => 'd-block d-md-none site-title nav-icon',
  ]
);

echo $app->Html->link(
  '',
  '/terkep',
  [
    'class' => 'd-block d-md-none ml-2 nav-icon'
    , _contains($_params->path, ['/terkep']) ? ' active' : '',
    'icon' => 'map-marked'
  ]
);

$active = _contains($_params->path, '/mulapok/kozelben') ? ' active' : '';
echo $app->Html->link(
  '',
  '/mulapok/kozelben',
  [
    'class' => 'd-block d-md-none ml-2 nav-icon' . $active,
    'icon' => 'compass'
  ]
);

$active = _contains($_params->path, '/kereses') ? ' active' : '';
echo $app->Html->link(
  '',
  '#instant-search-container',
  [
    'class' => 'd-block d-md-none ml-2 nav-icon header-search-toggle' . $active,
    'icon' => 'search',
    'data-toggle' => 'collapse',
    'ia-focus' => '#instant-search-container .instant-search',
  ]
);

$active = _contains($_params->path, '/jatekok/erinto') ? ' active' : '';
echo $app->Html->link(
  '',
  '/jatekok/erinto',
  [
    'class' => 'd-block d-md-none ml-2 nav-icon' . $active,
    'icon' => 'trophy',
  ]
);

$active = _contains($_params->path, '/kozter') ? ' active' : '';
if ($_user && $_user['editor_on'] == 1) {
  echo $app->Html->link(
    '',
    '/kozter',
    [
      'class' => 'd-block d-md-none ml-2 nav-icon' . $active,
      'icon' => 'users'
    ]
  );
}