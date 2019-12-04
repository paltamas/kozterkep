<div class="row kt-info-box p-2 pt-3 pt-md-2 mb-4">
  <div class="col-md-5 col-lg-4">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'action' => '/kereses',
      'placeholder' => 'Keress alkotásra...',
      'class' => 'mt-md-2 mb-0',
      'custom_inputs' => [
        ['r', [
          'value' => 1,
          'type' => 'hidden',
        ]],
        ['kerulet', [
          'value' => $district_id,
          'type' => 'hidden',
        ]],
      ]
    ]]);
    ?>
  </div>
  <div class="col-md-7 col-lg-8 py-2">
    A <?=$district_id == 24 ? 'Margitsziget városrész' : 'kerület'?> <strong><?=_n($artpiece_count)?> alkotása</strong> található meg nálunk. Ha teljes Budapestre vagy kíváncsi, <?=$app->Html->link('kattints ide', '/helyek/megtekintes/110/budapest')?>.
  </div>
</div>


<div class="row">

  <div class="col-md-5 col-lg-4 mb-5">

    <h4 class="subtitle">Legfrissebb műlapok</h4>
    <div class="row mb-4">
      <?=$app->element('places/view/latest_artpieces', ['options' => [
        'query' => [
          'kerulet' => $district_id,
        ]
      ]])?>
    </div>

    <?php if (count($latest_artpieces) > 5) { ?>
      <h4 class="subtitle">Legnépszerűbbek</h4>
      <div class="row">
        <?=$app->element('places/view/top_artpieces')?>
      </div>
    <?php } ?>

  </div>


  <div class="col-md-7 col-lg-8">

    <?php
    if (count($artpieces_by_time) > 2) {
      echo '<h4 class="subtitle">Idővonal</h4>';
      echo $app->element('artpieces/list/timeline', ['artpieces' => $artpieces_by_time, 'options' => [
        'class' => 'mt-2 mb-4',
        'count' => 5
      ]]);
    }
    ?>

    <?php
    if ($artpiece_count > 0) {
      echo $app->element('maps/simple_filtered', ['options' => [
        'count' => $artpiece_count,
        'map_artpieces' => $map_artpieces,
        'height' => 400,
        'zoom' => 15,
        'center' => [
          'lat' => @$top_artpieces[0]['lat'],
          'lon' => @$top_artpieces[0]['lon'],
        ],
        'filter_query' => [
          'kerulet' => $district_id,
        ],
        'title' => '<h4 class="subtitle mt-0">Térképen</h4>',
      ]]);
    }
    ?>
  </div>

</div>