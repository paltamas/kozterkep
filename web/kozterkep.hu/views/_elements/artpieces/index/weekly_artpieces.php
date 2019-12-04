<?=$app->Html->link('Részletes statisztikák', '/mulapok/statisztikak', [
  'icon' => 'chart-line',
  'class' => 'float-right btn btn-outline-primary',
  'hide_text' => true,
])?>
<h4 class="subtitle">Heti népszerű műlapok</h4>
<div class="row">
  <?=$app->element('artpieces/list/viewstats', [
    'artpieces' => $artpieces_weekly,
    'options' => [
      'highlighted_field' => 'view_week',
    ]
  ])?>
</div>