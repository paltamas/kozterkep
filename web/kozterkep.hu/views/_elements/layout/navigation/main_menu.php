<?php
echo '<ul class="navbar-nav mr-auto">';
$i = 0;

$main_menu = APP_MENUS['main'];


// Kezdőlap, ha Köztér a home
if ($app->ts('space_home') == 1) {
  $i++;

  echo '<li class="pl-3 pl-md-0 nav-item pr-md-1">';

  // Desktopon
  echo $app->Html->link('', '/oldalak/kezdolap', array(
    'icon' => 'home',
    'class' => 'nav-link pl-0 ml-0 d-none d-md-inline-block',
  ));

  // Mobilon
  echo $app->Html->link('Kezdőlap', '/oldalak/kezdolap', array(
    'class' => 'nav-link pl-0 ml-0 d-flex d-md-none',
  ));

  echo '</li>';

  // Kiszedjük a users ikont a köztérre, hogy ne legyen ikontolulás
  array_shift($main_menu);
}



foreach ($main_menu as $link_name => $link_params) {

  //_debug($link_params);

  // User level alapján tovább megyünk, ha...
  if (
    $_user && @$link_params[1] == 1 || // auth=false esetén látható csak
    !$_user && @$link_params[1] == 2 // auth=true esetén látható csak
  ) {
    continue;
  }

  $i++;

  // Ha nem szerkesztő, nincs köztér link kitéve
  if (@$_user['editor_on'] === 0 && $i < 3) {
    continue;
  }

  $classes['nav-item'] = is_array($link_params[0]) ? ' dropdown' : '';
  $classes['nav-link'] = $i == 1 ? ' pl-0 ml-0' : '';

  if (@$link_params[2] == 1) {
    $classes['nav-item'] .= ' d-none d-md-block';
  } elseif (@$link_params[2] == 2) {
    $classes['nav-item'] .= ' d-md-none';
  }

  // Ha csak egy ikonunk van, akkor nincs jobb helyköz
  if (strip_tags($link_name) == '') {
    $classes['nav-item'] .= ' pr-0 mr-0';
    $classes['nav-link'] .= ' pr-0 mr-0';
  }

  if (@$_active_menu == $link_name) {
    $classes['nav-item'] .= ' active';
  }

  echo '<li class="pl-3 pl-md-0 nav-item' . $classes['nav-item'] . '">';

  if (!is_array($link_params[0])) {

    // Sima link
    echo $app->Html->link($link_name, $link_params[0], array(
      'class' => 'nav-link' . $classes['nav-link']
    ));

  } else {

    if (@$link_params[3] != '') {

      // Link + Dropdown caret külön
      echo $app->Html->link($link_name, $link_params[3], array(
        'class' => 'nav-link mr-0 pr-0 d-none d-md-inline-block' . $classes['nav-link']
      ));
      echo '</li>';

      echo '<li class="pl-0 pl-md-0 nav-item' . $classes['nav-item'] . '">';
      echo $app->Html->link($link_name, '#', array(
        'hide_text' => 'd-md-none d-lg-none',
        'class' => 'nav-link dropdown-toggle' . $classes['nav-link'],
        'data-toggle' => 'dropdown',
        'role' => 'button',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false'
      ));

    } else {

      // Sima dropdown
      echo $app->Html->link($link_name, '#', array(
        'class' => 'nav-link dropdown-toggle' . $classes['nav-link'],
        'data-toggle' => 'dropdown',
        'role' => 'button',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false'
      ));

    }

    echo '<div class="dropdown-menu">';

    foreach ($link_params[0] as $sublink_name => $sublink_params) {

      if ((@$sublink_params[1] >= 1 && !$_user)
        || (@$sublink_params[1] == 2 && @$_user['headitor'] != 1 && @$_user['admin'] != 1)
        || (@$sublink_params[1] == 3 && @$_user['admin'] != 1)
      ) {
        continue;
      }

      if ($sublink_params == '') {

        // Divider
        echo '<div class="dropdown-divider"></div>';

      } else {

        if (@$_active_menu == $link_name && @$_active_submenu == $sublink_name) {
          $active = ' active';
        } else {
          $active = '';
        }

        // Link
        echo $app->Html->link($sublink_name, $sublink_params[0], [
          'class' => 'dropdown-item' . $active
        ]);

      }

    }

    echo '</div>';

  }

  echo '</li>';

}

echo '</ul>';