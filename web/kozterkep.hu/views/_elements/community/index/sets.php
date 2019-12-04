<h5 class="subtitle mt-4">Frissen bővült gyűjtemények</h5>
<?php
foreach ($sets as $set) {
  echo '<span class="mr-3">';
  echo $app->Html->link($set['name'] . ' (' . count($set['artpieces']) . ')', '', [
    'icon' => 'tag',
    'set' => $set,
    'ia-tooltip' => 'gyujtemeny',
    'ia-tooltip-id' => $set['id'],
  ]);
  echo '</span>';
}
