<?=$app->Html->link('', '#', [
  'class' => 'float-right btn btn-outline-secondary btn-sm',
  'icon' => 'redo',
  'ia-ajaxdiv-load-simple' => '/mulapok/veletlen?meret=4',
  'ia-ajaxdiv-target' => '.ajaxdiv-random',
  'title' => 'Másik műlapot kérek véletlenül!',
])?>
<div class="kt-info-box">
  <h5 class="mb-3">Egy véletlen műlap...</h5>
  <div class="ajaxdiv-random" ia-ajaxdiv="/mulapok/veletlen?meret=4"></div>
</div>