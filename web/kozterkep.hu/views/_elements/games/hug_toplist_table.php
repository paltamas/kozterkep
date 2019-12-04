<?php
$options = (array)@$options + [
  'profile_photo' => true,
  'limit' => 20,
];

if (!isset($users) || @count(@$users) == 0) {
  echo '-';
  return;
}
?><table class="table table-sm table-striped">
  <thead>
  <tr>
    <th class="border-top-0">#</th>
    <th class="border-top-0">Tag</th>
    <th class="border-top-0 text-right pr-3">Érintés</th>
  </tr>
  </thead>
  <tbody>
  <?php
  $i = 0;
  foreach ($users as $top_user) {
    if (isset($top_user->_id)) {
      $top_user_ = $app->MC->t('users', $top_user->_id);
      $top_user_['hug_count'] = $top_user->count;
      $top_user = $top_user_;
    }

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
    echo '<td class="text-right pr-3">' . _n($top_user['hug_count']) . '</td>';
    echo '</tr>';
  }
  ?>
  </tbody>
</table>