<?php
$options = (array)@$options + [
  'profile_photo' => false,
  'count_field' => 'artpiece_count',
  'count_label' => 'Műlap',
  'sort' => -1,
  'limit' => 10,
];

if (!isset($users) || @count(@$users) == 0) {
  echo '-';
  return;
}
$users = $app->Arrays->sort_by_key($users, $options['count_field'], $options['sort']);
?><table class="table table-sm table-striped">
  <thead>
  <tr>
    <th class="border-top-0">#</th>
    <th class="border-top-0">Tag</th>
    <th class="border-top-0 text-right pr-3"><?=$options['count_label']?></th>
  </tr>
  </thead>
  <tbody>
  <?php
  $i = 0;
  foreach ($users as $top_user) {
    $i++;
    if ($i > $options['limit']) {
      break;
    }
    echo '<tr>';
    echo '<td>' . $i . '.</td>';
    echo '<td class="font-weight-bold">';
    echo $app->Users->name($top_user, [
      'image' => $options['profile_photo'],
      'tooltip' => true,
    ]);
    echo '</td>';
    echo '<td class="text-right pr-3">' . _n($top_user[$options['count_field']]) . '</td>';
    echo '</tr>';
  }
  ?>
  </tbody>
</table>