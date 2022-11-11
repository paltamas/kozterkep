<?= $app->element('layout/blocks/head', compact('_title')) ?>
<div id="site">
  <div class="px-1 px-md-2">

    <main role="main" class="<?=$app->ts('fluid_view') == 1 || @$_map_layout ? 'container-fluid' : 'container'?> content <?=@$_map_layout ? 'p-0' : 'pt-md-3 pb-3'?> mt-0">

      <?php
      // Na végre, már vártuk.
      echo $_view_content;
      ?>
    </main>
  </div>
</div>
<?= $app->element('layout/partials/unimodal') ?>
<?= !$_user ? $app->element('layout/blocks/cookie_consent') : '' ?>
<?= $app->element('layout/blocks/js_loader') ?>