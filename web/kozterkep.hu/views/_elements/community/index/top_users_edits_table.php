<?php
$options = (array)@$options + ['latest' => false];

$latest_suffix = $options['latest'] ? '_latest' : '';

?><table class="table table-sm table-striped">
  <thead>
  <tr>
    <th class="border-top-0">#</th>
    <th class="border-top-0">Tag</th>
    <th class="border-top-0">Szerk.</th>
    <th class="border-top-0">Sztori</th>
  </tr>
  </thead>
  <tbody>
  <?php
  $i = 0;
  foreach ($top_users as $top_user) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '.</td>';
    echo '<td class="font-weight-bold">';
    echo $app->Users->name($top_user, [

    ]);
    echo '</td>';
    echo '<td class="text-center">' . _n($top_user['edit_other_count' . $latest_suffix]) . '</td>';
    echo '<td class="text-center">' . _n($top_user['description_other_count' . $latest_suffix]) . '</td>';
    echo '</tr>';
  }
  ?>
  </tbody>
</table>