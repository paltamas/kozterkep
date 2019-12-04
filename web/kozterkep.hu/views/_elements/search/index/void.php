<?php
if (@$query['foto_hiany'] == 1 || @$query['alkoto_hiany'] == 1 || @$query['datum_hiany'] == 1) {
  $collapse = '';
} else {
  $collapse = 'collapse';
}


echo '<div class="border rounded bg-light p-3 ' . $collapse . ' mb-3" id="hianykereso">';
echo '<div class="row">';

echo '<div class="col-12 mb-3">';
echo '<h5 class="subtitle">Hiánykereső</h5>';
echo '</div>';

echo '<div class="col-sm-6 col-md-3">';
echo $app->Form->input('foto_hiany', [
  'label' => 'Kevesebb, mint 3 fotó',
  'type' => 'checkbox',
  'value' => 1,
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-3">';
echo $app->Form->input('alkoto_hiany', [
  'label' => 'Nincs alkotó',
  'type' => 'checkbox',
  'value' => 1,
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-3">';
echo $app->Form->input('datum_hiany', [
  'label' => 'Nincsenek időpontok',
  'type' => 'checkbox',
  'value' => 1,
]);
echo '</div>';


echo '</div>'; // row --

echo '</div>'; // collapse --