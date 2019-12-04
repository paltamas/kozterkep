<?php
$options = (array)@$options + [
  'top_count' => 6,
  'top_class' => 'col-6 col-md-4 col-lg-2 p-0 d-flex mb-3',
  'top_background' => 'rounded bg-light',
  'top_details' => false,
  'class' => 'col-4 col-sm-3 col-md-2 col-lg-1 p-md-1',
  'max_items' => false,
  'separator_element' => '',
];

$i = 0;
$separator_element = false;
foreach ($artpieces as $artpiece) {
  if (is_numeric($artpiece)) {
    $artpiece = $app->MC->t('artpieces', $artpiece);
  }

  $i++;
  if ($options['max_items'] && $i > $options['max_items']) {
    break;
  }
  if ($i <= $options['top_count']) {
    echo '<div class="' . $options['top_class'] . '">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'background' => $options['top_background'],
        'details' => $options['top_details'],
        'tooltip' => true,
      ],
    ]);
    echo '</div>';
  } else {

    if ($options['separator_element'] && !$separator_element) {
      echo '<div class="col-12 mt-4 px-0">';
      echo $app->element($options['separator_element']);
      echo '</div>';
      $separator_element = true;
    }

    echo '<div class="' . $options['class'] . '">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
        'tooltip' => true,
        'extra_class' => 'mb-2',
      ],
    ]);
    echo '</div>';
  }
}