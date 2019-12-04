<?=$app->Html->link('Lista', '/kereses?r=1&peldas=igen#hopp=lista', [
  'icon' => 'star-christmas',
  'class' => 'float-right btn btn-outline-primary btn-sm',
  'hide_text' => true,
])?>
<h5 class="subtitle"><?=$app->Html->icon('star-christmas text-primary fas mr-1')?>Példás műlapok mostanában</h5>
<div class="row">
  <?=$app->element('artpieces/list/list', [
    'artpieces' => $superb,
    'options' => [
      'top_count' => 4,
      'top_class' => 'col-6 col-md-3 px-0'
    ]
  ])?>
</div>