<?php
echo '<div class="mb-4">';
echo '<h5 class="subtitle">' . sDB['parameter_groups'][$group_id][0] . '</h5>';
foreach ($artpiece_parameters as $parameter) {
  if ($parameter['parameter_group_id'] != $group_id || $parameter['hidden'] == 1) {
    continue;
  }
  echo $app->Form->input('p_' . $parameter['id'], [
    'type' => 'checkbox',
    'label' => $parameter['name'],
    'value' => 1,
    'divs' => 'py-0',
  ]);
}
echo '</div>';