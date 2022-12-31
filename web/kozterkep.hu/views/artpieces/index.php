<div class="row mt-3">

  <div class="col-12 mb-4">
    <?=$app->element('artpieces/index/search_form')?>
  </div>

  <div class="col-md-6 col-lg-8 mb-4">
    <?=$app->element('artpieces/index/latests')?>
  </div>

  <div class="col-md-6 col-lg-4 mb-4">

    <div class="mb-4">
      <?=$app->element('artpieces/index/updated_artpieces')?>
    </div>

    <div class="mb-4">
      <?php
      echo '<h5 class="subtitle mb-3">Mai népszerű műlapok</h5>';
      echo $app->element('artpieces/list/toplist', [
        'artpieces' => $artpieces_daily,
        'options' => [
          'field' => 'view_day',
        ]
      ]);
      ?>
    </div>
    <div class="mb-4">
      <?=$app->element('artpieces/index/random_artpiece')?>
    </div>
    <div class="mb-4">
      <?php
      echo '<h5 class="subtitle mb-3">Heti népszerű műlapok</h5>';
      echo $app->element('artpieces/list/toplist', [
        'artpieces' => $artpieces_weekly,
        'options' => [
          'field' => 'view_week',
        ]
      ]);

      echo '<div class="text-center mt-3">';
      echo $app->Html->link('Részletes statisztikák', '/mulapok/statisztikak', [
        'icon' => 'chart-line',
        'class' => 'btn btn-outline-primary',
      ]);
      echo '</div>';

      ?>
    </div>
  </div>

</div>