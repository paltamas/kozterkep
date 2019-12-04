<div class="row pb-5">
  <div class="col-md-5 col-lg-4 mb-2">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'action' => '/alkotok/kereses',
      'placeholder' => 'Alkotó neve',
      'class' => 'mt-md-3 mb-0',
    ]]);
    echo '<div class=""><strong>' . _n($artist_count) . '</strong> alkotó között ' . $app->Html->link('kereshetsz', '/alkotok/kereses') . '.</div>';
    ?>
  </div>
  <div class="col-md-7 col-lg-8">
    <div class="kt-info-box">Az alkotók adattárában a műlapokon keresztül rögzített alkotókat listázzuk. Alkotókról beszélünk, de valójában ez két alapcsoportot jelent: alkotók és közreműködők. Emellett megkülönböztetünk személyeket, alkotócsoportokat és gazdasági társaságokat. Az alkotókat az adattár felelőse, <?=$app->Users->name(CORE['USERS']['artists'])?> kezeli.</div>
  </div>
</div>



<div class="row">

  <div class="col-md-6 col-lg-5">

    <h4 class="subtitle">Kiemelt alkotó</h4>
    <?=$app->element('artists/item', ['artist' => $random_artist, 'options' => [
      'image_size' => 5,
      'details' => false,
    ]])?>

    <h4 class="subtitle mt-5">Évfordulók</h4>
    <?=$app->element('artists/index/anniversaries')?>

    <h4 class="subtitle mt-5">Top 10 alkotó</h4>
    <?=$app->element('artists/index/top_artists')?>

  </div>

  <div class="col-md-6 col-lg-7">

    <h4 class="subtitle">Mostanában feltöltött portrék</h4>
    <?=$app->element('artists/index/photos')?>

    <h4 class="subtitle">Friss személyek az adattárban</h4>
    <?=$app->element('artists/index/latest_artists')?>

    <h4 class="subtitle mt-5 mb-3">Blogbejegyzések</h4>
    <?=$app->element('posts/list')?>

  </div>

</div>
