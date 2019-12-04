<h4 class="subtitle">Műlapok</h4>
<?=$app->Form->help('Követett tagokhoz, alkotókhoz és helyekhez kapcsolódó legfrissebb műlapok.')?>
<?php
if (count($artpieces) > 0) {
  echo '<div class="row">';
  foreach ($artpieces as $artpiece) {
    echo '<div class="col-6 col-sm-4 col-md-12 col-lg-6 px-0">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
    ]);
    echo '</div>';
  }
  echo '</div>'; // row --
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>