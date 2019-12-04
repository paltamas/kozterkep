<table class="table table-sm table-striped">
  <tbody>
  <?php
  $i = 0;
  foreach ($countries as $country) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '.</td>';
    echo '<td class="font-weight-bold">' . $app->Places->country($country['country_id']) . '</td>';
    echo '<td>' . _n($country['artpiece_count']) . '</td>';
    echo '</tr>';
  }
  ?>
  </tbody>
</table>

<?=$app->Html->link('Országok teljes listája', '/helyek/orszagok', [
  'icon_right' => 'arrow-right',
])?>