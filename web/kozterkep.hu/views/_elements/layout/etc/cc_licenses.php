<?php
$licenses = sDB['license_types'];
$license_transmissions = sDB['license_transmissions'];
$license_infos = sDB['license_infos'];

echo '<div class="table-responsive">';
echo '<table class="table table-striped table-sm">';
echo '<thead>';
echo '<tr>';
echo '<th>AZ</th>';
echo '<th>Megnevezés</th>';
echo '<th>Gyermek AZ*</th>';
echo '</tr>';
echo '</thead>';
foreach ($licenses as $id => $licens) {
  $icons = [];
  foreach ($license_infos[$id][0] as $icon_class) {
    $icons[] = '<span class="' . $icon_class . ' mr-2 fa-lg"></span>';
  }
  echo '<tr>';
  echo '<td>' . $id . '</td>';
  echo '<td>';
  $name = implode('', $icons) . $licens;
  if ($id < 7) {
    echo $app->Html->link($name, $license_infos[$id][1], [
      'target' => '_blank',
      'title' => 'A Creative Commons licensz teljes leírása',
    ]);
  } else {
    echo $name;
  }
  echo '</td>';
  echo '<td>' . implode(', ', $license_transmissions[$id]['children']);
  echo '</tr>';
}
echo '</table>';
echo '</div>';

echo '<p>* Egy licensz a gyermek licenszekbe váltható csak át.</p>';