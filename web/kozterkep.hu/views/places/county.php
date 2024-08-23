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
        ['megye', [
          'value' => $county_id,
          'type' => 'hidden',
        ]],
      ]
    ]]);
    ?>
  </div>
  <div class="col-md-7 col-lg-8 py-3">
    A vármegye <strong><?=_n($place_count)?></strong> településének <strong><?=_n($artpiece_count)?> alkotása</strong> található meg nálunk.
  </div>
</div>


<div class="row">

  <div class="col-md-6 col-lg-5">

    <h4 class="subtitle">Legfrissebb műlapok</h4>
    <div class="row">
      <?=$app->element('places/view/latest_artpieces', ['options' => [
        'query' => [
          'megye' => $county_id,
        ]
      ]])?>
    </div>

    <h4 class="subtitle mt-4">Top 5 település</h4>
    <?=$app->element('places/index/top_places', ['options' => ['query' => ['megye' => $county_id]]])?>

    <h4 class="subtitle mt-4">Legnépszerűbbek alkotások</h4>
    <div class="row">
      <?=$app->element('places/view/top_artpieces')?>
    </div>


  </div>

  <div class="col-md-6 col-lg-7">

    <?php
    if (count($artpieces_by_time) > 2) {
      echo '<h4 class="subtitle">Idővonal</h4>';
      echo $app->element('artpieces/list/timeline', ['artpieces' => $artpieces_by_time, 'options' => [
        'class' => 'mt-2 mb-4',
        'count' => 4
      ]]);
    }
    ?>

    <h4 class="subtitle mt-4">Friss helyek a megyéből</h4>
    <?=$app->element('places/index/latest_places')?>

    <?php
    $title = $artpiece_count > APP['map']['max_id'] ? 'Térképen egy városa' : 'Térképen';
    echo $app->element('maps/simple_filtered', ['options' => [
      'count' => $artpiece_count,
      'map_artpieces' => $map_artpieces,
      'height' => 400,
      'zoom' => 14,
      'center' => [
        'lat' => $top_artpieces[0]['lat'],
        'lon' => $top_artpieces[0]['lon'],
      ],
      'filter_query' => [
        'megye' => $county_id,
      ],
      'title' => '<h4 class="subtitle">' . $title . '</h4>',
      'div_class' => 'mt-4'
    ]]);
    ?>

  </div>

</div>
