<div class="text-muted float-right font-italic pt-1">szubjektív válogatás <span class="d-none d-md-inline-block">friss műlapjainkból</span></div>
<h5 class="subtitle text-dark mb-1">Szüret</h5>
<div class="row">
  <?php
  $options = (array)@$options + [
    'top_count' => 8,
    'top_class' => 'col-6 col-md-3 p-0 d-flex',
    'max_items' => false,
  ];
  echo $app->element('artpieces/list/list', [
    'artpieces' => $highlighteds,
    'options' => [
      'top_count' =>  $options['top_count'],
      'top_class' =>  $options['top_class'],
      'top_details' => false,
      'top_background' => '',
      'class' => 'col-4 col-sm-3 col-md-2 col-lg-1 p-md-1',
      'max_items' => $options['max_items'],
    ]
  ]);
  ?>
</div>