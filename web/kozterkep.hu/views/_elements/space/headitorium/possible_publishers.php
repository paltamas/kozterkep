<?php
if (count($possible_publishers) > 0) {
  echo '<h5 class="subtitle">Tagok szabad publikálási jog nélkül</h5>';
  echo '<p class="text-muted">Legalább 10 műlap, nem blokkolt, és három hónapon belül belépett.</p>';
  echo $app->Html->table('create', [
    'Szerkesztő',
    'Műlapszám',
    'Reg. ideje',
  ]);
  foreach ($possible_publishers as $possible_publisher) {
    echo '<tr>';
    echo '<td>' . $app->Users->name($possible_publisher, [
      'image' => true,
      'link' => true,
    ]) . '</td>';
    echo '<td>' . _n($possible_publisher['artpiece_count']) . '</td>';
    echo '<td>' . _time($possible_publisher['activated']) . '</td>';
    echo '</tr>';
  }
  echo $app->Html->table('end');
}