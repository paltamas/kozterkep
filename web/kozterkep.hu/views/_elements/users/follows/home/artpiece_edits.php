<h4 class="subtitle">Műlap szerkesztések</h4>
<?=$app->Form->help('Követett műlapokon elfogadott szerkesztések.', ['class' => 'mb-3'])?>

<?php
if (count($artpiece_edits) > 0) {
  foreach ($artpiece_edits as $artpiece_edit) {
    echo $app->element('artpieces/edit/edit_item', [
      'edit' => $artpiece_edit,
      'options' => [
        'simple' => true,
        'artpiece_details' => true,
      ]
    ]);
  }
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>