<?php
if (count($artpieces) == 0) {
  echo $app->element('layout/partials/empty');
  return;
}

$options = (array)@$options + [
  'simple' => false,
  'field' => 'view_day',
];

switch ($options['field']) {
  case 'view_day':
    $column = 'Napi';
    break;
  case 'view_week':
    $column = 'Heti';
    break;
  case 'view_total':
    $column = 'Össz.';
    break;
}

?><div class="table-responsive">
  <table class="table table-sm table-striped">
    <thead>
    <tr>
      <th>#</th>
      <th>Műlap</th>
      <th><?=$column?></th>
      <?php if (!$options['simple']) { ?>
      <th>Össz.</th>
      <?php } ?>
      <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    foreach ($artpieces as $artpiece) {
      $i++;
      echo '<tr>';
      echo '<td>' . $i . '.</td>';
      echo '<td class="font-weight-bold">';
      echo $app->Image->photo($artpiece, [
        'size' => 8,
        'class' => 'img-fluid mr-2 d-none d-sm-inline-block'
      ]);
      echo $app->Html->link($artpiece['title'], '', [
        'artpiece' => $artpiece,
        'ia-tooltip' => 'mulap',
        'ia-tooltip-id' => $artpiece['id'],
      ]);
      echo '</td>';
      echo '<td class="text-center">' . _n($artpiece[$options['field']]) . '</td>';

      if (!$options['simple']) {
        echo '<td>' . _n($artpiece['view_total']) . '</td>';
      }

      echo '<td>' . $app->Html->link('', '/webstat/oldalak?vp=artpieces&vi=' . $artpiece['id'], [
          'icon' => 'chart-line',
          'title' => 'Részletes webstat',
        ]) . '</td>';
      echo '</tr>';
    }
    ?>
    </tbody>
  </table>
</div>