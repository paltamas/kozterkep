<h4 class="subtitle">Mappa fájlok</h4>
<?=$app->Form->help('Követett mappákba töltött fájlok.')?>
<?php
if (count($files) > 0) {
  echo '<div class="row mt-2">';
  foreach ($files as $file) {
    $folder = $app->MC->t('folders', $file['folder_id']);

    if (!$folder || $folder['public'] == 0) {
      // hopp!
      continue;
    }

    echo '<div class="col-6 col-sm-4 col-md-12 col-lg-6 mb-3">';
    echo $app->element('files/preview', [
      'file' => $file,
      'link' => '/mappak/megtekintes/' . $file['folder_id'] . '#vetito=' . $file['id'],
      'options' => [
        'class' => 'img-fluid img-thumbnail',
        'folder_details' => true,
        'folder' => $folder,
      ]
    ]);
    echo '</div>';
  }
  echo '</div>'; // row
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>