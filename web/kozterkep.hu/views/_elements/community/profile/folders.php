<?php
if (count($folders) > 0) {
  echo '<hr />';
  echo '<h5 class="subtitle mb-3">Mappák</h5>';
  foreach ($folders as $folder) {
    echo '<span class="mr-4">';
    echo $app->Html->link($folder['name'] . ' (' . $folder['file_count'] . ')', '', [
      'icon' => 'folder',
      'folder' => $folder,
    ]);
    echo '</span>';
  }
}