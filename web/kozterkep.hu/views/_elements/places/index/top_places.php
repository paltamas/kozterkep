<?php
$options = (array)@$options + [
  'query' => [],
];
?>

<table class="table table-sm table-striped">
  <tbody>
  <?php
  $i = 0;
  foreach ($top_places as $place) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '.</td>';
    echo '<td class="font-weight-bold">' . $app->Places->name($place) . '</td>';
    echo '<td>' . _n($place['artpiece_count']) . '</td>';


    echo '<td>';
    $top_artpiece = $app->MC->t('artpieces', $place['top_artpiece_id']);
    if ($top_artpiece) {
      echo $app->Html->link($app->Image->photo($top_artpiece, [
        'size' => 8,
        'class' => 'img-fluid',
      ]), '', [
        'artpiece' => $top_artpiece,
        'ia-tooltip' => 'mulap',
        'ia-tooltip-id' => $top_artpiece['id'],
      ]);
    }
    echo '</td>';

    echo '<td>';
    $last_artpiece = $app->MC->t('artpieces', $place['last_artpiece_id']);
    if ($last_artpiece) {
      echo $app->Html->link($app->Image->photo($last_artpiece, [
        'size' => 8,
        'class' => 'img-fluid',
      ]), '', [
        'artpiece' => $last_artpiece,
        'ia-tooltip' => 'mulap',
        'ia-tooltip-id' => $last_artpiece['id'],
      ]);
    }
    echo '</td>';


    echo '</tr>';
  }
  ?>
  </tbody>
</table>

<?php
$query = count($options['query']) > 0 ? '?' . http_build_query($options['query']) : '';
echo $app->Html->link('Települések teljes listája', '/helyek/kereses' . $query, [
  'icon_right' => 'arrow-right',
]);
?>