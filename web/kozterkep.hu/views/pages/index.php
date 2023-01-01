<div class="row my-3 my-md-4 d-none d-md-flex">
  <div class="col-md-6 p-3 bg-gray-kt rounded">
    <?php
    echo '<div class="float-right pr-3">';
    echo $app->Html->link('Részletes keresés', '/kereses?r=1', [
      'icon' => 'far fa-search',
      'class' => 'font-weight-bold'
    ]);
    echo '</div>';

    echo '<h5 class="mt-0 mb-2">' . $app->Html->icon('search mr-1') . 'Gyorskeresés</h5>';

    echo $app->Form->input('instant_search', [
      'placeholder' => 'Műlap keresés...',
      'class' => 'd-inline-block form-control-lg instant-search input-no-clear',
      'data-target' => '#instant-search',
      'divs' => 'mb-1',
    ]);
    ?>
  </div>

  <div class="col-md-6">
    <?=$app->element('pages/index/short_intro')?>
  </div>

  <div class="col-12">
    <div id="instant-search" class="my-4"></div>
  </div>
</div>

<hr class="highlighter text-center my-3 d-none d-md-block">

<div class="row my-3 my-md-4">
  <div class="col-12 mb-4 mb-md-0">
    <?=$app->element('pages/index/harvest')?>
  </div>
</div>

<hr class="highlighter text-center my-5">

<div class="row my-3 my-md-4">
  <div class="col-md-6 mb-4 mb-md-0">
    <h4 class="subtitle">Alkotói évfordulók</h4>
    <?=$app->element('artists/index/anniversaries', ['births' => $artist_births, 'deaths' => $artist_deaths])?>
    <h4 class="subtitle mt-4">Mostanában feltöltött alkotói portrék</h4>
    <?=$app->element('artists/index/photos', ['photos' => $artist_photos])?>
    <h4 class="subtitle mt-4">Friss személyek az Alkotótárban</h4>
    <?=$app->element('artists/index/latest_artists')?>
  </div>
  <div class="col-md-6 mb-4 mb-md-0">
    <h4 class="subtitle">Legutolsó avatások</h4>
    <?=$app->element('pages/index/unveils')?>
    <h4 class="subtitle mt-4">Friss helyek a településtárban</h4>
    <?=$app->element('places/index/latest_places')?>
  </div>
</div>

<hr class="highlighter text-center my-5">

<div class="row">
  <div class="col-12">
    <?=$app->element('pages/index/map')?>
  </div>
</div>

<hr class="highlighter text-center my-5">

<div class="row">

  <div class="col-md-8 pr-md-5 mb-3">
    <?=$app->element('pages/index/latest_artpieces')?>
  </div>

  <div class="col-md-4 mb-3">
    <?=$app->element('community/index/highlighted_user')?>
    <?=$app->element('pages/index/top_users')?>
    <?=$app->element('pages/index/blog_friends')?>
  </div>
</div>


