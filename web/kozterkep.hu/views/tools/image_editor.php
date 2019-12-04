<?php
echo '<div class="kt-info-box">Ezt a funkciót csak a kép feltöltője, és a főszerkesztők érik el. Más tagok szerkesztés keretében sem tudnak nem saját képeket beforgatni.</div>';
echo '<div class="text-center my-3">';
echo '<div class="row">';
echo '<div class="col-md-6">';
echo $app->Html->link('Forgatás', '#', [
  'class' => 'rotate-image mx-3 btn btn-secondary',
  'title' => 'Forgatás jobbra 90 fokkal',
  'icon' => 'redo',
  'data-angle' => 90,
  'data-target' => '#editimage',
]);
echo '</div>';

echo '<div class="col-md-6 form-inline">';
echo $app->Form->create(null, [
  'method' => 'post',
]);

echo $app->Form->input('angle', [
  'type' => 'hidden',
  'value' => 0,
]);

echo $app->Form->end('Beállítások mentése');
echo $app->Html->link('Mégsem', '/mulapok/szerkesztes/' . $photo['artpiece_id'] . '#szerk-fotok', [
  'class' => 'btn btn-outline-primary ml-5',
]);
echo '</div>';
echo '</div>';

echo '</div>';




echo '<div class="text-center" id="editimage-container">';
echo $app->Image->photo($photo, [
  'size' => 2,
  'class' => 'img-fluid',
  'id' => 'editimage',
]);
echo '</div>';