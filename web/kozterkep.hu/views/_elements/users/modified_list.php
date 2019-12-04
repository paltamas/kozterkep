<?php
$limit = @$limit > 0 ? $limit : 25;

$results = $app->Artpieces->get_modified_list($_user['id'], $limit);

if (count($results) > 0) {
  echo '<h6 class="dropdown-header">Utoljára módosított ' . $limit . ' publikus műlapod</h6>';

  $i = 0;
  foreach ($results as $item) {
    $i++;
    $border = $i < count($results) ? 'border-bottom' : '';
    echo $app->Html->link($item['title'], '', [
      'class' => 'dropdown-item  ' . $border,
      'artpiece' => $item,
    ]);
  }
}