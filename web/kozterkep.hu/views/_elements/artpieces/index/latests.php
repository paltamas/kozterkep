<?=$app->Html->link('Lista', '/kereses#hopp=lista', [
  'icon' => 'list',
  'class' => 'float-right btn btn-outline-primary btn-sm',
  'hide_text' => true,
])?>
<h5 class="subtitle">Friss műlapok</h5>
<div class="row">
<?=$app->element('artpieces/list/list', [
'artpieces' => $latests,
'options' => [
  'top_class' => 'col-3 p-0 d-flex mb-3',
  'top_count' => 24,
  'top_details' => true,
  'class' => 'col-3 my-2 px-1',
]
])?>
</div>

<div class="text-center mt-3">
  <?=$app->Html->link('Műlapok listája', '/kereses#hopp=lista', [
    'icon' => 'list',
    'class' => 'btn btn-outline-primary',
  ])?>
</div>
