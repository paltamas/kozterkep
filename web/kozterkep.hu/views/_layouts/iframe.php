<?= $app->element('layout/blocks/head', compact('_title')) ?>
<div id="site">
<?= $_view_content ?>
</div>
<?= $app->element('layout/partials/unimodal') ?>
<?= $app->element('layout/blocks/js_loader') ?>