<div class="footer mt-5 bg-gray-kt">
  <div class="<?=$app->ts('fluid_view') == 1 ? 'container-fluid' : 'container'?> pt-4 pb-5">

    <div class="row py-4 mt-4">
      <div class="col-lg-12">
        <?= $app->element('layout/partials/top_members', [
          'count' => 50,
          'members' => $app->Users->top_members(50)
        ])?>
      </div>
    </div>

    <hr />
    
    <div class="row py-4 sitemap-menu">
      <?= $app->element('layout/navigation/footer_sitemap_menu')?>
      <?php // echo $app->element('layout/navigation/footer_app_links')?>
    </div>

    <hr />
    
    <div class="row text-muted mt-5 pb-5 mb-4">

      <div class="col-lg-5 col-12 text-center text-lg-right mb-2 my-1">
        2006&ndash;<?= date('Y') ?> &copy; <?=$app->Html->link('Köztérkép Mozgalom', '/oldalak/kozterkep-mozgalom')?>
      </div>

      <div class="col-lg-7 col-12 text-center text-lg-left pl-lg-5">
        <?=$app->Html->link('Jogi nyilatkozat', '/oldalak/jogi-nyilatkozat', array('class' => 'd-inline-block mr-3 my-1'))?>
        <?=$app->Html->link('Adatkezelési szabályzat', '/oldalak/adatkezelesi-szabalyzat', array('class' => 'd-inline-block mr-3 my-1'))?>
        <?=$app->Html->link('Impresszum', '/oldalak/impresszum', array('class' => 'd-inline-block mr-3 my-1'))?>
        <?=$app->Html->link('Kapcsolat', '/oldalak/kapcsolat', array('class' => 'd-inline-block my-1'))?>
      </div>

    </div>
  </div>
</div>

<div class="fixed-bottom float-left mb-5 mb-md-3 mx-3 bubbleContainer z-10000"></div>

<?php //echo $app->element('layout/navigation/footer_mobile_menu');?>

<?php // echo $app->element('layout/partials/bot');?>

<!-- scroll top -->
<a href="#" class="scroll-top d-none z-1000"><span class="far fa-arrow-up"></span></a>
<!-- scroll top -->