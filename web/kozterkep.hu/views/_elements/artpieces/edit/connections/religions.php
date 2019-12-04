<?php
echo '<div class="row">';

foreach ($artpiece_parameters as $parameter) {
  if ($parameter['parameter_group_id'] != 5 || $parameter['hidden'] == 1) {
    continue;
  }

  echo '<div class="col-6 col-lg-3">';

  echo $app->Form->input('parameters[' . $parameter['id'] . '][id]', [
    'value' => $parameter['id'],
    'type' => 'hidden',
  ]);

  echo $app->Form->input('parameters[' . $parameter['id'] . '][value]', [
    'type' => 'checkbox',
    'label' => $parameter['name'],
    'value' => 1,
    'divs' => 'py-0',
    'checked' => @in_array($parameter['id'], $parameters) ? true : false,
  ]);

  echo '</div>';
}

echo '</div>'; // row --