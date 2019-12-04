<?= $app->element('layout/blocks/head', compact('_title')) ?>
<div class="full-overlay position-fixed z-10000 w-100 h-100 bg-overlay d-none align-content-center flex-wrap">
  <div class="text-center w-100 mt-3">
    <span class="far fa-compass fa-spin fa-4x text-green-kt"></span>
  </div>
</div>

<div id="site">
  <?= @$_header ? $app->element('layout/blocks/header') : '' ?>

  <div class="page-overlay position-fixed z-10000 w-100 h-100 bg-overlay d-none"></div>

  <div class="<?=@$_map_layout ? 'p-0' : 'px-1 px-md-2'?>">

    <main role="main" class="<?=$app->ts('fluid_view') == 1 || @$_map_layout || @$_fluid_layout ? 'container-fluid' : 'container'?> content <?=@$_map_layout ? 'p-0' : 'py-3'?> mt-0">

      <?php
      if (@$_breadcrumbs_menu) {
        echo $app->element('layout/navigation/breadcrumbs_menu', ['_title' => @$_title]);
      }

      if (@$_title_row == false) {
        echo $app->element('layout/navigation/mobile_page_tabs_icons', [
          'container' => true
        ]);
      }

      if (@$_breadcrumbs_menu && !@$_title_row) {
        echo $app->element('layout/partials/separator', ['padding' => 3]);
      }

      if (@$_sidemenu) {
        echo '<div class="row px-0 mx-0 mb-5">';
        echo $app->element('layout/navigation/sidemenu');
      }

      if (@$_title_row) {
        echo $app->element('layout/blocks/title_row', ['_title' => @$_title]);
      }

      // Na végre, már vártuk.
      echo $_view_content;

      if (@$_sidemenu) {
        echo '</div>';
        echo '</div>';
      }
      ?>
    </main>
  </div>
</div>
<?= @$_footer ? $app->element('layout/blocks/footer') : '' ?>
<?= $app->element('layout/partials/unimodal') ?>
<?= !$_user ? $app->element('layout/blocks/cookie_consent') : '' ?>
<?= $app->element('layout/blocks/js_loader') ?>