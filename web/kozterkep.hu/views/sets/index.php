<div class="row pb-5">
  <div class="col-md-5 col-lg-4 mb-2">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'action' => '/gyujtemenyek/kereses',
      'placeholder' => 'Gyűjtemény neve',
      'class' => 'mt-md-3',
    ]]);
    echo '<div class=""><strong>' . _n($common_set_count) . ' közös</strong> és <strong>' . _n($user_set_count) . ' tagi</strong> gyűjtemény között ' . $app->Html->link('kereshetsz', '/gyujtemenyek/kereses') . '.</div>';
    ?>
  </div>
  <div class="col-md-7 col-lg-8">
    <div class="kt-info-box">Közös és tagi gyűjteményeink tematikus műlap-halmazok. A <span class="fas fa-user fa-sm ml-1 text-muted"></span> tagi típusba csak a létrehozó hívhatja meg más műlapját, míg a <span class="fas fa-users fa-sm ml-1 text-muted"></span> közös típusba bárki beválogathatja a saját feltöltését. Ha egy tagi gyűjtemény népszerűnek és alapvetőnek bizonyul, közös gyűjteménnyé alakíthatjuk. Ezen az oldalon a népszerű és a mostanában frissült gyűjteményeket listázzuk. A közös gyűjteményeket az adattár felelőse, <?=$app->Users->name(CORE['USERS']['sets'])?> kezeli.</div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <h4 class="subtitle mb-3">Mostanában bővült közös</h4>
    <?php
    foreach ($latest_common_sets as $set) {
      echo $app->element('sets/item', ['set' => $set, 'options' => [
        'count' => 4,
        'image_size' => 6
      ]]);
    }
    ?>

    <hr class="my-3 my-md-5" />

    <h4 class="subtitle mb-3">Mostanában bővült tagi</h4>
    <?php
    foreach ($latest_user_sets as $set) {
      echo $app->element('sets/item', ['set' => $set, 'options' => [
        'count' => 4,
        'image_size' => 6
      ]]);
    }
    ?>
  </div>


  <div class="col-md-6">
    <h4 class="subtitle mb-3">Top közös</h4>
    <?php
    foreach ($top_common_sets as $set) {
      echo $app->element('sets/item', ['set' => $set, 'options' => [
        'count' => 4,
        'image_size' => 6
      ]]);
    }
    ?>
  </div>
</div>
