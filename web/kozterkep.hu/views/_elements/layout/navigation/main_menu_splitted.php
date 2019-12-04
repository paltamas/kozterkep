<?php
echo '<ul class="navbar-nav mr-auto">';
$i = 0;

$main_menu = APP_MENUS['main'];

if ($app->ts('splitted_menu') == 1) {
  unset($main_menu['<span class="far fa-users"></span>']);
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
  $classes['nav-link'] = $i == 1 ? 'pl-md-0 ml-0' : 'pl-md-1 ml-2';

  if (@$link_params[2] == 1) {
    $classes['nav-item'] .= ' d-none d-md-block';
  } elseif (@$link_params[2] == 2) {
    $classes['nav-item'] .= ' d-md-none';
  }


  if (@$_active_menu == $link_name) {
    $classes['nav-item'] .= ' active';
  }

  echo '<li class="pl-3 pl-md-0 nav-item ' . $classes['nav-item'] . '">';

  if (!is_array($link_params[0])) {

    // Sima link
    echo $app->Html->link($link_name, $link_params[0], array(
      'class' => 'nav-link ' . $classes['nav-link']
    ));

  } else {


    // Sima dropdown
    foreach ($link_params[0] as $sublink_name => $sublink_params) {
      $first_link = $sublink_params[0];
      break;
    }
    echo $app->Html->link($link_name, $first_link, array(
      'class' => 'nav-link d-none d-md-block ' . $classes['nav-link'],
      'role' => 'button',
      'aria-haspopup' => 'true',
      'aria-expanded' => 'false'
    ));


    echo '</li>';

    echo '<li class="pl-3 pl-md-0 nav-item ' . $classes['nav-item']  . '">';

    echo $app->Html->link('<span class="d-md-none">' . $link_name . '</span>', '#', array(
      'class' => 'nav-link dropdown-toggle pl-0 pr-2',
      'data-toggle' => 'dropdown',
      'role' => 'button',
      'aria-haspopup' => 'true',
      'aria-expanded' => 'false'
    ));


    echo '<div class="dropdown-menu dropdown-menu-' , $i == 1 ? 'left' : 'right' , '">';

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
          'class' => 'dropdown-item ' . $active
        ]);

      }

    }

    echo '</div>';


  }

  echo '</li>';

}

echo '</ul>';