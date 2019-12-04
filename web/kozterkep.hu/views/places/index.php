<div class="row pb-5">
  <div class="col-md-5 col-lg-4 mb-2">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'action' => '/helyek/kereses',
      'placeholder' => 'Település neve',
      'class' => 'mt-md-3 mb-0',
    ]]);
    echo '<div class=""><strong>' . _n($country_count) . '</strong> ország <strong>' . _n($place_count) . '</strong> települése között ' . $app->Html->link('kereshetsz', '/helyek/kereses') . '.</div>';
    ?>
  </div>
  <div class="col-md-7 col-lg-8">
    <div class="kt-info-box">A helyek adattárában a műlapokon keresztül rögzített településeket listázzuk. A helységeket az adattár felelőse, <?=$app->Users->name(CORE['USERS']['places'])?> ellenőrzi.</div>
  </div>
</div>



<div class="row">

  <div class="col-md-6 col-lg-5">

    <h4 class="subtitle">Kiemelt település</h4>
    <?=$app->element('places/item', ['place' => $random_place, 'options' => [
      'image_size' => 5,
      'details' => false
    ]])?>


    <h4 class="subtitle mt-5">Top 10 település</h4>
    <?=$app->element('places/index/top_places')?>


    <h4 class="subtitle mt-5 mb-3">Top 10 ország</h4>
    <?=$app->element('places/index/top_countries')?>

  </div>

  <div class="col-md-6 col-lg-7">

    <h4 class="subtitle">Friss helyek az adattárban</h4>
    <?=$app->element('places/index/latest_places')?>

    <h4 class="subtitle mt-5 mb-3">Blogbejegyzések</h4>
    <?=$app->element('posts/list')?>

  </div>

</div>
