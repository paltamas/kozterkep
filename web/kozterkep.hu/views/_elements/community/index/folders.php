<h5 class="subtitle mt-4">Mostanában bővült mappák</h5>
<?php
foreach ($folders as $folder) {
  echo '<span class="mr-4">';
  echo $app->Html->link($folder['name'] . ' (' . $folder['file_count'] . ')', '', [
    'icon' => 'folder',
    'folder' => $folder,
  ]);
  echo '</span>';
}
