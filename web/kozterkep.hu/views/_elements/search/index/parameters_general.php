<?php
echo '<div class="mb-4">';
echo '<h5 class="subtitle">Általános paraméterek</h5>';

$array = [
  'masolat' => 'Köztéri mű másolata',
  'rekonstrukcio' => 'Köztéri mű rekonstrukciója',
  'nem_muveszi_emlekorzo' => 'Művészi elem nélküli emlékőrző',
  'atmeneti_felallitas' => 'Tervezetten átmeneti felállítás',
  'muemlek' => 'Nyilvántartott műemlék',
];

foreach ($array as $field => $label) {
  echo $app->Form->input($field, [
    'type' => 'checkbox',
    'label' => $label,
    'value' => 1,
    'divs' => false,
  ]);
}

echo '</div>';