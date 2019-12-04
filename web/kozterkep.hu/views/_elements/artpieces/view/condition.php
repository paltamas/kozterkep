<?php
if ($artpiece['artpiece_condition_id'] > 1) {
  $status = sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']];

  echo '<div>';
  echo '<span class="text-muted">Állapot:</span> ';
  echo '<span class="font-weight-bold badge badge-' . $status[4] . ' badge-lg mr-2"><span class="fas fa-' . $status[5] . ' mr-2"></span>' . $status[0] . '</span>';
  //echo $status[3] == 0 ? '<span class="text-muted text-nowrap"><span class="fal fa-times-circle mr-1"></span>itt már található meg</span>' : '';
  echo '</div>';
  echo '<hr class="my-3" />';
}