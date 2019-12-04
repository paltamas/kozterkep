<?php
if ($_user) {

  echo $app->Html->dropdown(
    $app->Users->profile_image($_user) . '<span class="whatsmyname">' . $_user['name'] . '</span>',
    [
      'class' => 'nav-link pr-0',
      'id' => 'headerUserDropdown-' . uniqid()
    ],
    APP_MENUS['usermenu']['logged'],
    [
      'class' => 'dropdown-menu-right mt-3'
    ]
  );



} else {

  // Ha van már hopp benne, azt kukázzuk
  $url = $app->Html->parse_url($_params->here, ['delvars' => ['hopp']]);
  $login_redirect = $url != '' ? '?hopp=' . urlencode($url) : '';

  echo $app->Html->dropdown(
    '<span class="far fa-fw fa-user-circle"></span>Tagság',
    [
      'class' => 'nav-link pr-0',
      'id' => 'headerUserDropdown'
    ],
    APP_MENUS['usermenu']['public'],
    [
      'class' => 'dropdown-menu-right mt-3'
    ]
  );

}

