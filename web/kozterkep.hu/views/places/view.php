<div class="tab-content">

  <div class="tab-pane show active" id="adatlap" role="tabpanel" aria-labelledby="adatlap-tab">

    <div class="row">



      <div class="col-md-5 col-lg-4 mb-5">

        <h4 class="subtitle">Legfrissebb műlapok</h4>
        <div class="row mb-4">
          <?=$app->element('places/view/latest_artpieces', ['options' => [
            'query' => [
              'hely_az' => $place['id'],
              'hely' => $place['name'],
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


        <div class="row kt-info-box p-2 pt-3 pt-md-2 mb-4">
          <?php if ($place['artpiece_count'] >= 30) { ?>
          <div class="col-md-6">
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
                ['hely_az', [
                  'value' => $place['id'],
                  'type' => 'hidden',
                ]],
                ['hely', [
                  'value' => $place['name'],
                  'type' => 'hidden',
                ]],
              ]
            ]]);
            ?>
          </div>
          <?php } ?>
          <div class="col-md-6 py-2">
            A település <strong><?=_n($place['artpiece_count'])?> alkotása</strong> található meg nálunk.
          </div>
        </div>


        <?php
        if (count($artpieces_by_time) > 2) {
          echo '<h4 class="subtitle">Idővonal</h4>';
          echo $app->element('artpieces/list/timeline', ['artpieces' => $artpieces_by_time, 'options' => [
            'class' => 'mt-2 mb-4',
            'count' => 5
          ]]);
        }
        ?>

        <div class="row">
          <div class="col-lg-8 mb-3">
            <?php
            if ($place['artpiece_count'] > 0) {
              echo $app->element('maps/simple_filtered', ['options' => [
                'count' => $place['artpiece_count'],
                'map_artpieces' => $map_artpieces,
                'height' => 350,
                'zoom' => 15,
                'center' => [
                  'lat' => @$top_artpieces[0]['lat'],
                  'lon' => @$top_artpieces[0]['lon'],
                ],
                'filter_query' => [
                  'hely_az' => $place['id'],
                  'hely' => $place['name'],
                ],
                'title' => '<h4 class="subtitle mt-0">Térképen</h4>',
              ]]);
            }
            ?>
          </div>
          <div class="col-lg-4 mb-3">
            <?=$app->element('places/view/description')?>
            <?=$app->element('places/view/basic_info')?>
          </div>
        </div>


        <?php
        if (count($posts) > 0) {
          echo '<h4 class="subtitle mt-4">Kapcsolódó bejegyzések</h4>';
          echo $app->element('posts/list');
        } else {
          echo '<div class="text-muted mt-3">Még nem születetett bemutató bejegyzés a helyről. Ha van egy jó kis szobros történeted, vagy bemutatnád a települést köztéri műalkotások szemszögéből, írj kapcsolódó blogbejegyzést!</div>';
        }
        ?>



      </div>

    </div>

  </div>

  <div class="tab-pane" id="szerkkomm" role="tabpanel" aria-labelledby="szerkkomm-tab">
    <div class="ajaxdiv-photos" ia-ajaxdiv="/helyek/szerkkomm/<?=$place['id']?>"></div>
  </div>

</div>
