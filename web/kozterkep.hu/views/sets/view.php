<div class="row">

  <div class="col-md-5 col-lg-4 mb-5">

    <?=$app->element('sets/view/cover_artpiece')?>

    <h4 class="subtitle">Legfrissebb besorolások</h4>
    <div class="row">
      <?=$app->element('sets/view/latest_artpieces')?>
    </div>
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

    <div class="row">
      <div class="col-md-7 col-lg-8 mb-3">
        <?=$app->element('sets/view/description')?>
      </div>
      <div class="col-md-5 col-lg-4 mb-3">
        <?=$app->element('sets/view/basic_info')?>
      </div>
    </div>

    <?php
    if (count($artpieces) > 0) {
      $set_type_prefix = $set['set_type_id'] == 1 ? 'kozos_' : '';
      echo $app->element('maps/simple_filtered', ['options' => [
        'count' => count($artpieces),
        'map_artpieces' => $app->Arrays->id_list($artpieces, 'id'),
        'height' => 407,
        'zoom' => 15,
        'center' => [
          'lat' => @$latest_artpieces[0]['lat'],
          'lon' => @$latest_artpieces[0]['lon'],
        ],
        'filter_query' => [
          'r' => 1,
          $set_type_prefix . 'gyujtemeny' => $set['id'],
        ],
        'title' => '<h4 class="subtitle mt-0">Térképen</h4>',
      ]]);
    }
    ?>
  </div>

</div>