<?php
if ($place['admin_memo'] != '' || $place['alternative_names'] != '') {
  echo '<div class="kt-info-box mb-3">';
  echo $place['alternative_names'] != '' ? '<h5><span class="text-muted">Alternatív és rész-megnevezések</span>: ' . $place['alternative_names'] . '</h5>' : '';
  if ($place['admin_memo'] != '') {
    echo '<div class="mt-2">';
    echo '<span class="text-muted">Megjegyzés:</span> ';
    echo $app->Text->format($place['admin_memo']);
    echo '</div>';
  }
  echo '</div>';
}