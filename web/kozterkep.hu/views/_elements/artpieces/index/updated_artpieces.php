<h5 class="subtitle">Mostanában bővült korábbi műlapok</h5>
<div class="row">
  <?=$app->element('artpieces/list/list', [
    'artpieces' => $updated_artpieces,
    'options' => [
      'top_count' => 4,
      'top_class' => 'col-6 col-md-3 px-0',
      'class' => 'col-3 col-md-2 my-2 px-1',
    ]
  ])?>
</div>