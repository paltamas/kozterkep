<?php
$options = ($options) + [
  'items' => [],
  'total_count' => 0,
  'pagination' => [],
  'orders' => false,
  'order_default' => false,
  'item_counts' => [
    24 => 24,
    36 => 36,
    50 => 50,
    100 => 100,
    200 => 200,
    500 => 500,
  ],
];

if (!$options['order_default'] && $options['orders']) {
  reset($options['orders']);
  $options['order_default'] = key($options['orders']);
}

echo '<div class="row my-md-3">';

echo '<div class="col-12 col-sm-4 my-2 d-lg-none text-center pt-1">';
echo '<strong class="text-muted">' . _n($options['total_count']) . ' találat</strong>';
echo '</div>';

echo '<div class="col-md-8 d-none d-lg-block my-2">';
$options['pagination']['div'] = 'my-0';
$options['pagination']['centered'] = false;
echo $app->Html->pagination(count($options['items']), $options['pagination']);
echo '</div>';

echo '<div class="col-4 col-sm-3 col-md-3 col-lg-1 text-center text-md-right my-2 form-inline">';
echo $app->Form->input('elem', [
  'options' => $options['item_counts'],
  'value' => @$_params->query['elem'] > 0 ? $_params->query['elem'] : 36,
  'class' => 'form-control-sm',
  'ia-urlchange-input' => 'elem',
]);
echo '</div>';


if ($options['orders']) {
  echo '<div class="col-8 col-sm-5 col-md-5 col-lg-3 text-center text-md-right my-2 form-inline">';
  echo $app->Form->input('sorrend', [
    'options' => $options['orders'],
    'value' => @$_params->query['sorrend'] != '' ? $_params->query['sorrend'] : $options['order_default'],
    'class' => 'form-control-sm',
    'ia-urlchange-input' => 'sorrend',
  ]);
  echo '</div>';
}

echo '</div>';