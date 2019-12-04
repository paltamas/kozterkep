<div class="row">
  <div class="col-md-6 pr-md-5">
    <h4 class="subtitle my-3">Top műlaposok az elmúlt 12 hónapban</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'profile_photo' => true,
        'count_field' => 'artpiece_count_latest',
        'count_label' => $app->Html->icon('map-marker mr-1') . 'Műlap',
        'limit' => 30,
      ]
    ])?>
  </div>
  <div class="col-md-6 pl-md-5">
    <h4 class="subtitle my-3">Top műlaposok összesítve</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'profile_photo' => true,
        'count_field' => 'artpiece_count',
        'count_label' => $app->Html->icon('map-marker mr-1') . 'Műlap',
        'limit' => 30,
      ]
    ])?>
  </div>
</div>

<div class="row">
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

<div class="row">
  <div class="col-md-6 pr-md-5">
    <h4 class="subtitle my-3">Top fotósok (12 hó)</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'photo_count_latest',
        'count_label' => $app->Html->icon('images mr-1') . 'Fotó',
        'limit' => 10,
      ]
    ])?>
  </div>
  <div class="col-md-6 pl-md-5">
    <h4 class="subtitle my-3">Top fotósok össz.</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'photo_count',
        'count_label' => $app->Html->icon('images mr-1') . 'Fotó',
        'limit' => 10,
      ]
    ])?>
  </div>
</div>

<div class="row">
  <div class="col-md-6 pr-md-5">
    <h4 class="subtitle my-3">Top közösségi szerkesztők (12 hó)</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'edit_other_count_latest',
        'count_label' => $app->Html->icon('edit mr-1') . 'Szerk. máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'description_other_count_latest',
        'count_label' => $app->Html->icon('paragraph mr-1') . 'Sztori máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'photo_other_count_latest',
        'count_label' => $app->Html->icon('images mr-1') . 'Fotó máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'comment_count_latest',
        'count_label' => $app->Html->icon('comments mr-1') . 'Hozzászólás',
        'limit' => 10,
      ]
    ])?>
  </div>
  <div class="col-md-6 pl-md-5">
    <h4 class="subtitle my-3">Top közösségi szerkesztők össz.</h4>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'edit_other_count',
        'count_label' => $app->Html->icon('edit mr-1') . 'Szerk. máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'description_other_count',
        'count_label' => $app->Html->icon('paragraph mr-1') . 'Sztori máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'photo_other_count',
        'count_label' => $app->Html->icon('images mr-1') . 'Fotó máshoz',
        'limit' => 10,
      ]
    ])?>
    <?=$app->element('community/statistics/toplist_table', [
      'users' => $users,
      'options' => [
        'count_field' => 'comment_count',
        'count_label' => $app->Html->icon('comments mr-1') . 'Hozzászólás',
        'limit' => 10,
      ]
    ])?>
  </div>
</div>

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

<hr class="my-4 highlighter text-center">

<div class="row">
  <div class="col-md-12">
    <h4 class="subtitle">Köztérkép tagok regisztrációjának alakulása havi bontásban</h4>

    <canvas class="chart"
            ia-chart-type="line"
            ia-chart-labels="<?=htmlentities(json_encode(array_keys($registers_data)))?>"
            ia-chart-data="<?=htmlentities(json_encode(array_values($registers_data)))?>"
    ></canvas>
  </div>
</div>