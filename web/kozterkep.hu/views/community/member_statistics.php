<div class="row pb-4">
  <div class="col-md-7 order-2 order-md-1">
    <?=$app->element('community/profile/statistics', ['options' => [
      'stat_link' => false,
      'container' => 'col-6 col-sm-3 col-md-4 col-lg-3 my-3',
    ]])?>
  </div>
  <div class="col-md-5 text-center order-1 order-md-2">
    <?=$app->element('community/profile/namecard', ['options' => [
      'simple' => true,
      'name_link' => true,
    ]])?>
  </div>
</div>

<hr class="my-4 highlighter text-center">

<?php if ($user['artpiece_count'] > 0) { ?>

<div class="row pb-4">
  <div class="col-12">
    <h5 class="subtitle">Mai nap népszerű műlapok</h5>
  </div>
  <?=$app->element('artpieces/list/viewstats', [
    'artpieces' => $artpieces_daily,
    'options' => [
      'highlighted_field' => 'view_day',
    ]
  ])?>
</div>

<hr class="my-4 highlighter text-center">

<div class="row pb-4">
  <div class="col-12">
    <h5 class="subtitle">Heti népszerű műlapok</h5>
  </div>
  <?=$app->element('artpieces/list/viewstats', [
    'artpieces' => $artpieces_weekly,
    'options' => [
      'highlighted_field' => 'view_week',
    ]
  ])?>
</div>

<hr class="my-4 highlighter text-center">

<div class="row pb-4">
  <div class="col-12">
    <h5 class="subtitle">Összesítetten legnépszerűbb műlapok</h5>
  </div>
  <?=$app->element('artpieces/list/viewstats', [
    'artpieces' => $artpieces_total,
    'options' => [
      'highlighted_field' => 'view_total',
    ]
  ])?>
</div>

<hr class="my-4 highlighter text-center">

<div class="row pt-3">
  <div class="col-md-12">
    <h4 class="subtitle">Műlapok publikálásának alakulása havi bontásban</h4>

    <canvas class="chart"
            ia-chart-type="line"
            ia-chart-labels="<?=htmlentities(json_encode(array_keys($artpieces_data)))?>"
            ia-chart-data="<?=htmlentities(json_encode(array_values($artpieces_data)))?>"
    ></canvas>
  </div>
</div>


<hr class="my-4 highlighter text-center">

<?php } ?>

<?php if ($user['comment_count'] > 0) { ?>
<div class="row pt-3">
  <div class="col-md-12">
    <h4 class="subtitle">Hozzászólások számának alakulása havi bontásban</h4>

    <canvas class="chart"
            ia-chart-type="line"
            ia-chart-labels="<?=htmlentities(json_encode(array_keys($comments_data)))?>"
            ia-chart-data="<?=htmlentities(json_encode(array_values($comments_data)))?>"
    ></canvas>
  </div>
</div>
<?php } ?>