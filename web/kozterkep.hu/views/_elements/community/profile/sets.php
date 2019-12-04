<?php
if (count($sets) > 0) {
  echo '<hr />';
  echo '<h5 class="subtitle mb-3">Gyűjtemények</h5>';
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
}