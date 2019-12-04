<?php
$limit = @$limit > 0 ? $limit : 25;

$results = $app->Artpieces->get_edit_list($_user['id'], $limit);

if (count($results) > 0) {

  $title = count($results) > $limit ? 'Utoljára módosított ' . $limit . ' szerkesztés alati műlapod' : 'Nem publikus műlapjaid';

  echo '<h6 class="dropdown-header">' . $title . '</h6>';

  $i = 0;
  foreach ($results as $item) {
    $i++;
    $border = $i < count($results) ? 'border-bottom' : '';
    $suffix = $item['status_id'] > 1
      ? ' <span class="text-muted small">(' . sDB['artpiece_statuses'][$item['status_id']][0] . ')</span>'
        : '';
    echo $app->Html->link($item['title'] . $suffix, '', [
      'class' => 'dropdown-item  ' . $border,
      'artpiece' => $item,
    ]);
  }

} else {
  echo '<h6 class="dropdown-header">Nincs szerkesztés alatti műlapod</h6>';
}