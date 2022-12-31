<h5 class="subtitle mb-3 text-dark">További frissen publikált műlapok</h5>
<div class="row">
  <?=$app->element('artpieces/list/list', [
    'artpieces' => $latests,
    'options' => [
      'top_class' => 'col-6 col-md-4 col-lg-3 p-0 d-flex mb-3',
      'top_count' => 0,
      'class' => 'col-4 col-md-2 d-flex mb-3',
    ]
  ])?>
</div>