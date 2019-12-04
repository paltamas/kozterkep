<?php
$last_subgroup_id = 0;
echo '<div class="row">';

foreach ($artpiece_parameters as $parameter) {
  if ($parameter['parameter_group_id'] != 3) {
    continue;
  }

  if ($last_subgroup_id != $parameter['parameter_subgroup_id']) {
    if (sDB['artpiece_form_groups'][$parameter['parameter_subgroup_id']] != '') {
      echo '<div class="col-md-12 mt-4">';
      echo '<h6 class="font-weight-bold">' . sDB['artpiece_form_groups'][$parameter['parameter_subgroup_id']] . '</h6>';
      echo '</div>';
    }
    $last_subgroup_id = $parameter['parameter_subgroup_id'];
  }

  echo '<div class="col-6 col-lg-3">';

  echo $app->Form->input('parameters[' . $parameter['id'] . '][id]', [
    'value' => $parameter['id'],
    'type' => 'text',
    'class' => 'd-none',
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