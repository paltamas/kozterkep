<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Műlapok',
    'icon' => 'map-marker-alt',
    'items' => 'Műlapok',
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Alkotók',
    'icon' => 'user',
    'items' => 'Alkotók',
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Helyek',
    'icon' => 'globe',
    'items' => 'Helyek',
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Egyéb adattárak',
    'icon' => 'database',
    'items' => 'Adattár',
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Hírek',
    'icon' => 'newspaper',
    'items' => 'Hírek',
  ]]);
  ?>
</div>


  <?php
  //<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  /*echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Játék',
    'icon' => 'trophy',
    'items' => 'Játék',
  ]]);*/
  //</div>
  ?>


<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Közösség',
    'icon' => 'users',
    'items' => 'Közösség',
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Rólunk',
    'icon' => 'question-circle',
    'items' => [
      'Röviden rólunk' => '/oldalak/roviden-rolunk',
      'Támogatás minket!' => '/oldalak/tamogass-minket',
      //'Történetünk' => '/oldalak/tortenetunk',
      'Köztérkép Mozgalom' => '/oldalak/kozterkep-mozgalom',
    ],
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Hasznos infók',
    'icon' => 'book-open',
    'items' => [
      'Segédlet' => '/oldalak/segedlet',
      //'Fejlesztőknek' => '/oldalak/fejlesztoknek',
      'Webstat' => '/webstat/attekintes',
    ],
  ]]);
  ?>
</div>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
  <?php
  echo $app->element('layout/navigation/footer_sitemap_menulist', ['options' => [
    'title' => 'Szabályzatok',
    'icon' => 'pen-alt',
    'items' => [
      'Működési elvek' => '/oldalak/mukodesi-elvek',
      'Jogi nyilatkozat' => '/oldalak/jogi-nyilatkozat',
      'Adatkezelési szabályzat' => '/oldalak/adatkezelesi-szabalyzat',
    ],
  ]]);
  ?>
</div>

<div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-4">
  <div class="mb-1"><strong><span class="far fa-fw mr-1 fa-mobile-alt"></span>Menet közben szobroznál?</strong></div>
  <ul class="nav flex-column">
    <li class="nav-item">
      <?php
      echo $app->Html->link('Szoborkereső iOS-re', 'https://itunes.apple.com/hu/app/szoborkereso/id1150066881', [
        'target' => '_blank',
        'icon' => 'app-store-ios fab',
      ]);
      ?>
    </li>
    <li class="nav-item">
      <?php
      echo $app->Html->link('Köztérkép Androidra', 'https://play.google.com/store/apps/details?id=hu.idealap.kt2', [
        'target' => '_blank',
        'icon' => 'google-play fab',
      ]);
      ?>
    </li>
  </ul>
</div>

<div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-4">
  <div class="mb-1"><strong><span class="far fa-fw mr-1 fa-thumbs-up"></span>Közösségi médium vagy-e?</strong></div>
  <ul class="nav flex-column">
    <li class="nav-item">
      <?php
      echo $app->Html->link('Facebook oldalunk', 'https://www.facebook.com/Kozterkep/', [
        'target' => '_blank',
        'icon' => 'facebook fab',
      ]);
      ?>
    </li>
    <li class="nav-item">
      <?php
      echo $app->Html->link('Instagram profilunk', 'https://www.instagram.com/kozterkep/', [
        'target' => '_blank',
        'icon' => 'instagram fab',
      ]);
      ?>
    </li>
  </ul>
</div>