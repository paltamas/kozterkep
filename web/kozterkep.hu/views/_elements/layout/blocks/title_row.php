<?php
if (@$_title != '') { ?>
  <div class="row <?=@$_simple_mobile ? 'd-none d-md-flex' : ''?>">

    <div class="col-md-12 bg-gray border-bottom px-3 pt-3 pb-2 mb-4">
      <?php
      // Ha nincs breadcrumbs, akkor itt hívjuk be a page-menut
      if (!$_breadcrumbs_menu) {
        echo '<div class="d-none d-md-block float-right pt-3">';
        echo $app->element('layout/navigation/page_menu', ['_title' => $_title]);
        echo '</div>';
      }

      if (@$_viewable != '') {
        echo $app->Html->link('', $_viewable, [
          'icon' => 'eye fa-lg',
          'class' => 'btn btn-outline-primary float-right d-md-none',
          'hide_text' => true,
        ]);
      }

      ?>

      <h1 class="float-md-left m-0 display-4 font-weight-semibold"><?= @$_title_prefix . $_title ?></h1>

      <?=$app->element('layout/navigation/mobile_page_tabs_icons')?>

      <?php if (@$subtitle != '') { ?>
        <strong><?= $subtitle ?></strong>
      <?php } ?>
    </div>
  </div>
<?php } ?>