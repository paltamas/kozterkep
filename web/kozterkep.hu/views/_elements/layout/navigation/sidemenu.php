<?php
// Van-e
$uri_level_1 = $app->Request->uri_level(1);
$uri_level_2 = $app->Request->uri_level(2);
$sitemap = APP_MENUS['sitemap'];

/**
 * Ez itt gány-gyanús!
 * @todo
 */

$sitemap = $sitemap + [
  'orszagok' => $sitemap['helyek'],
  'megyek' => $sitemap['helyek'],
  'budapesti-keruletek' => $sitemap['helyek'],
];

if ($uri_level_1 !== '' && @count(@$sitemap[$uri_level_1]['menu']) > 0) {

  echo '<div class="col-lg-2 col-md-3 pl-0 ' , @$_simple_mobile ? 'd-none d-md-block' : '' , '">';
  echo '<h5 class="mt-0 mt-md-3 pt-1 mb-4 ml-2 text-secondary sidemenu-title"><span>' . $sitemap[$uri_level_1]['title'] . '</span></h5>';

  echo '<div class="sidemenu-list">';
  echo '<div class="list-group list-group-flush d-none d-md-block mb-5">';

  foreach($sitemap[$uri_level_1]['menu'] as $title => $attributes) {
    if (@$attributes[1] == 1
      || (@$attributes[1] >= 2 && !$_user) // tagoknak innen csak
      || (@$attributes[1] == 3 && @$_user['headitor'] != 1 && @$_user['admin'] != 1) // csak főszerkesztőknek (és adminoknak)
      || (@$attributes[1] == 4 && @$_user['admin'] != 1) // csak adminoknak
    ) {
      // Rejtett vagy tagi
      continue;
    }

    $link = $attributes[0];
    $active = strpos($_params->here, $link) !== false || @$_active_sidemenu == $link
      ? ' active' : '';
    echo $app->Html->link(
      $title,
      $link,
      [
        'class' => 'p-2 pl-0 list-group-item list-group-item-action' . $active,
        'icon' => @$attributes[2]
      ]
    );
  }
  echo '</div>'; // list-group
  echo '</div>'; // sidemenu-list
  echo '</div>'; // col-lg-2

  echo '<div class="col-lg-10 col-md-9 pr-0 pl-0 pl-md-4">';
}