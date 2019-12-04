<?php
if ($_user['admin'] == 1) {
  echo '<div class="kt-info-box mb-4">';
  $postcategory = sDB['post_categories'][$post['postcategory_id']];
  if ($postcategory[1] == 1) {
    echo $app->Form->input('html_formatted', [
      'label' => 'HTML formázás bekapcsolása',
      'type' => 'checkbox',
      'value' => 1,
    ]);
  }
  echo $app->Form->input('comments_blocked', [
    'label' => 'Hozzászólások blokkolása',
    'type' => 'checkbox',
    'value' => 1,
  ]);
  echo $app->Form->input('highlighted', [
    'label' => 'Bejegyzés kiemelése',
    'type' => 'checkbox',
    'value' => 1,
    'help' => 'Ezek emelődnek ki az aloldalakon és bekerülnek a heti Minervába is.',
  ]);
  echo $app->Form->input('super_high', [
    'label' => 'Tegyük kezdőlapra',
    'type' => 'checkbox',
    'value' => 1,
    'help' => 'Csak ezek a bejegyzések kerülnek a weblap kezdőoldalára.',
    'divs' => false,
  ]);
  echo '</div>';
}