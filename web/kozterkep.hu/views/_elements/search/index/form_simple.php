<?php
$options = (array)@$options + [
  'form' => []
];

$active_class = @$_params->query['r'] == 1 ? '' : ' active show ';
echo '<div class="tab-pane' . $active_class . '" id="egyszeru" role="tabpanel" aria-labelledby="egyszeru-tab">';

echo $app->Form->create(@$query, $options['form'] + [
  'class' => 'unsetEmptyFields form-row',
]);

echo '<div class="col-8 col-sm-6 col-md-4">';
echo $app->Form->input('kulcsszo', [
  'placeholder' => 'Alkotás cím...',
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-3 d-none d-sm-block">';
echo $app->Form->input('hely_az', [
  'type' => 'hidden',
  'placeholder' => 'hely az',
  'id' => 'place_id',
]);
echo $app->Form->input('hely', [
  'class' => 'noEnterInput',
  'placeholder' => 'Település...',
  'ia-auto' => 'places',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#place_id',
]);
echo '</div>';

echo '<div class="col-sm-6 col-md-3 d-none d-sm-block">';
echo $app->Form->input('alkoto_az', [
  'type' => 'hidden',
  'placeholder' => 'alkotó az',
  'id' => 'artist_id',
]);
echo $app->Form->input('alkoto', [
  'class' => 'noEnterInput',
  'placeholder' => 'Alkotó...',
  'ia-auto' => 'artists',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#artist_id',
]);
echo '</div>';

echo '<div class="col-3 col-sm-6 col-md-2 mb-2 mb-md-0 text-center text-sm-left">';
echo $app->Form->submit('Mehet', [
  'name' => 'kereses',
]);
echo '</div>';

echo $app->Form->end();

echo '</div>';
