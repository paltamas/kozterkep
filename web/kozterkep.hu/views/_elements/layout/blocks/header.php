<div class="header z-1 <?=@$_mobile_header == false ? 'd-none d-md-block': ''?>">

  <?=$app->element('layout/etc/donation_banner')?>

  <?=$app->element('layout/etc/accept_changes')?>

  <div class="header-top px-md-2 d-none d-md-block">
    <div class="<?=$app->ts('fluid_view') == 1 ? 'container-fluid' : 'container'?>">
      <div class="row">

        <div class="col-md-2 col-sm-12 pt-2 pl-0 pr-0">
          <a href="<?=$app->ts('space_home') != 1 ? '/' : '/kozter'?>" class="site-title">
            <?= $app->Html->image('kozterkep-logo-big.png', [
              'class' => 'aria-hide',
              'style' => 'width: 172px;'
            ]) ?>
            <h1 class="aria-show d-none text-dark-orange nowrap">
              <strong>köztér</strong>kép <span
                class="far fa-map-marker-alt text-gray-dark"></span>
            </h1>
          </a>
        </div>

        <div class="col-md-10 col-sm-12 pt-2 pb-2 px-0 text-right">

          <?= $app->element('layout/navigation/header_search') ?>

          <?php if ($_user) { ?>
            <div class="dropdown d-inline-block ml-3">
              <?= $app->element('layout/partials/conversations') ?>
            </div>

            <div class="dropdown d-inline-block ml-1">
              <?= $app->element('layout/partials/notifications') ?>
            </div>

            <div class="dropdown d-inline-block ml-2 mr-3">
              <?= $app->element('layout/partials/plus') ?>
            </div>

            <div class="dropdown d-inline-block mr-3">
              <?= $app->element('layout/partials/follows') ?>
            </div>
          <?php } ?>

          <div class="d-inline-block text-left">
            <div class="dropdown">
              <?= $app->element('layout/navigation/user_menu') ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="clear header-bottom bg-gray-kt px-md-2">

    <div class="collapse px-3 pt-3 pb-1 text-center" id="instant-search-container">
      <?php
      echo $app->Form->input('mobile_instant_search', [
        'placeholder' => 'Keresés',
        'class' => 'instant-search input-no-clear w-100',
        'divs' => 'pb-0 mb-1'
      ]);
      echo $app->Html->link('Műlapok', '/mulapok/attekintes', [
        'icon' => 'list',
        'class' => 'btn btn-link mr-2'
      ]);
      echo $app->Html->link('Részletesebb...', '/kereses', [
        'icon' => 'search',
        'class' => 'btn btn-link instant-detail-link ml-2'
      ]);
      ?>
    </div>

    <div class="<?=$app->ts('fluid_view') == 1 ? 'container-fluid' : 'container'?> px-0">
      <nav class="navbar navbar-light navbar-expand-md py-2 py-md-1 px-1 px-md-0">

        <?=$app->element('layout/navigation/header_mobile_icons')?>

        <button class="navbar-toggler border-0 pr-0" type="button"
                data-toggle="collapse" data-target="#mainMenu"
                aria-controls="mainMenu" aria-expanded="false"
                aria-label="Toggle navigation">
          <span class="far fa-bars fa-lg toggler-icon"></span>
          <?php if ($_user) { ?>
          <sup class="icon-sup sum-alert-count rounded text-white px-2">&nbsp;</sup>
          <?php } else { // ez kell, hogy nem belépve ne cuppanjon jobbra a toggler ?>
            <sup class="px-2">&nbsp;</sup>
          <?php } ?>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
          <?= $app->element('layout/navigation/header_mobile_menu') ?>
          <?php
          $main_menu = $app->ts('splitted_menu') == 1 ? 'main_menu_splitted' : 'main_menu';
          echo $app->element('layout/navigation/' . $main_menu);
          ?>
        </div>
      </nav>

    </div>

  </div>
</div>