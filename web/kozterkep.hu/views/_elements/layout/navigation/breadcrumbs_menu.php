<?php
$_title = isset($_title) && $_title != '' ? $_title : @$page_title;

$uri_level_1 = $app->Request->uri_level(1);
$uri_level_2 = $app->Request->action();
$sitemap = APP_MENUS['sitemap'];

// Köztes szintek
$mid_levels = [];

if (is_array(@$_breadcrumbs_menu)) {
  $mid_levels = $_breadcrumbs_menu;
} elseif ($uri_level_1 !== '' && @count(@$sitemap[$uri_level_1]['menu']) > 0) {
  // Ha megtaláljuk őt egy struktúra menüjében
  $found = false;
  foreach ($sitemap[$uri_level_1]['menu'] as $title => $attributes) {
    $link = $attributes[0];
    if (strpos($_params->here, $link) !== false) {
      $mid_levels = [$sitemap[$uri_level_1]['title'] => $sitemap[$uri_level_1]['startpage']];
      break;
    }
  }
}
?>

<div class="row d-none d-md-flex">
  <div class="col-md-6 col-lg-8 pl-0">
    <div class="mb-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <?php
          // Kezdőlap
          echo '<li class="breadcrumb-item">';
          echo $app->Html->link('', '/', ['icon' => 'home']);
          echo '</li>';

          // Köztes szintek
          if (count($mid_levels) > 0) {
            foreach ($mid_levels as $title => $link) {
              echo '<li class="breadcrumb-item">';
              echo $app->Html->link($title, $link);
              echo '</li>';
            }
          }

          // Aktuális oldal
          if (@$_title != '') {
            $_title = strip_tags($_title);
            $title = mb_strlen($_title) > 30 ? $app->Text->truncate($_title, 50) : $_title;
            echo '<li class="breadcrumb-item active">';
            echo $app->Html->link($title, $_params->here, [
              'title' => mb_strlen($_title) > 30 ? $_title : ''
            ]);
            echo '</li>';
          }
          ?>
        </ol>
      </nav>
    </div>
  </div>

  <div class="col-md-6 col-lg-4 pr-0 text-right d-none d-md-block">
    <div class="mb-3">
      <?=$app->element('layout/navigation/page_menu', ['_title' => @$_title])?>
    </div>
  </div>
</div>