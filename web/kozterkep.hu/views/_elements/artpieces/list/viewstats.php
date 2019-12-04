<?php
if (count($artpieces) == 0) {
  echo $app->element('layout/partials/empty');
  return;
}

$options = (array)@$options + [
  'latests' => 6,
  'latests_class' => 'col-6 col-md-4 col-lg-2 mb-3',
  'class' => 'col-4 col-sm-3 col-md-2 col-lg-1',
  'max_items' => false,
  'highlighted_field' => '',
];

$i = 0;
foreach ($artpieces as $artpiece) {
  $i++;
  if ($options['max_items'] && $i > $options['max_items']) {
    break;
  }

  if ($i <= $options['latests']) {
    echo '<div class="' . $options['latests_class'] . ' text-center">';
  } else {
    echo '<div class="' . $options['class'] . ' text-center">';
  }

  echo $app->Image->photo($artpiece, [
    'artpiece_tooltip' => $artpiece['id'],
    'class' => 'img-fluid img-thumbnail mx-2',
    'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
    'link_options' => ['class' => 'd-block']
  ]);

  echo '<div class="small text-muted">';
  echo '<span class="text-nowrap ' , $options['highlighted_field'] == 'view_day' ? 'font-weight-bold' : '' , ' mr-2">napi ' . _n($artpiece['view_day']) . '</span> ';
  echo '<span class="text-nowrap ' , $options['highlighted_field'] == 'view_week' ? 'font-weight-bold' : '' , ' mr-2">heti ' . _n($artpiece['view_week']) . '</span> ';
  echo '<span class="text-nowrap ' , $options['highlighted_field'] == 'view_total' ? 'font-weight-bold' : '' , '">össz. ' . _n($artpiece['view_total']) . '</span>';
  echo '</div>';

  echo '</div>';
}