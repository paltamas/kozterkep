<?php
//echo '<h6 class="subtitle">Időpontok</h6>';

if (count($dates) == 0) {
  echo '<div class="text-muted small">Az avatási időpontot nem ismerjük. ' . $app->Html->link('Többet tudsz?', $_editable) . '</div>';
} else {

  echo '<table width="100%">';

  foreach ($dates as $date) {

    list($date_type, $date_string) = $app->Artpieces->parse_date_row($date);

    echo '<tr>';
    echo '<td class="text-nowrap text-muted">' . $date_type . '</td>';
    echo '<td class="font-weight-bold">' . $date_string . '</td>';
    echo '</tr>';

  }
  echo '</table>';
}