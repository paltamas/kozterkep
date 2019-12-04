<div class="row mt-3">
  <div class="col-126 mb-4">
    <h5 class="subtitle">Heti top műlapok</h5>
    <div class="row">
      <?php
      echo $app->element('artpieces/list/viewstats', [
        'artpieces' => $artpieces_weekly,
        'options' => [
          'highlighted_field' => 'view_week',
          'max_items' => 6,
          'latests' => 6,
        ]
      ]);
      ?>
    </div>
  </div>

  <div class="col-md-4 mb-4">
    <h5 class="subtitle">Napi toplista</h5>
    <?php
    echo $app->element('artpieces/list/toplist', [
      'artpieces' => $artpieces_daily,
      'options' => [
        'field' => 'view_day',
        'simple' => true,
      ]
    ]);
    ?>
  </div>

  <div class="col-md-4 mb-4">
    <h5 class="subtitle">Heti toplista</h5>
    <?php
    echo $app->element('artpieces/list/toplist', [
      'artpieces' => $artpieces_weekly,
      'options' => [
        'field' => 'view_week',
        'simple' => true,
      ]
    ]);
    ?>
  </div>

  <div class="col-md-4 mb-4">
    <h5 class="subtitle">Összesített toplista</h5>
    <?php
    echo $app->element('artpieces/list/toplist', [
      'artpieces' => $artpieces_total,
      'options' => [
        'field' => 'view_total',
        'simple' => true,
      ]
    ]);
    ?>
  </div>

</div>