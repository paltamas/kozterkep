<?php
$options = (array)@$options + [
  'class' => '',
  'count' => 4,
  'image_size' => 6,
];

if (!$artpieces || !is_array($artpieces) || count($artpieces) == 0) {
  echo $app->element('layout/partials/empty');
  return;
}

$have_to_display = $options['count'];
$total_count = count($artpieces);
$step = ceil($total_count / ($have_to_display-1));

$i = 0;
$displayed = $last_displayed = 0;

echo '<div class="bg-light rounded p-2 ' . $options['class'] . '">';
echo '<div class="row mx-0">';
foreach ($artpieces as $year => $artpiece) {
  $i++;
  if ($i == 1 || $i % $step == 0 || $i == $total_count || $total_count <= $have_to_display) {

    echo '<div class="col px-0 mx-0 text-center">';

    echo '<div class="font-weight-bold mb-2">' . $year . '</div>';

    echo $app->Image->photo($artpiece, [
      'size' => $options['image_size'],
      'class' => 'img-thumbnail',
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $artpiece['id'],
      'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
    ]);

    echo '<div class="small mt-1 d-none d-sm-block">';
    $title = mb_strlen($artpiece['title']) > 20
      ? '<span title="' . $artpiece['title'] . '" data-toggle="tooltip">' . $app->Text->truncate($artpiece['title'], 20) . '</span>'
        : $artpiece['title'];
    echo $app->Html->link($title, '', ['artpiece' => $artpiece]);
    echo '</div>';

    echo '</div>';

    $displayed++;
  }

  if ($last_displayed < $displayed && $displayed < $have_to_display && $displayed < $total_count) {
    echo '<div class="col px-0 mx-0 pt-4 pt-sm-5 text-center">';
    echo '<div class="mx-0 mt-2">';
    echo '<span class="fas text-muted fa-long-arrow-right fa-lg"></span>';
    echo '</div>';
    echo '</div>';
    $last_displayed = $displayed;
  }
}

echo '</div>'; // row --
echo '</div>'; // bg-light --