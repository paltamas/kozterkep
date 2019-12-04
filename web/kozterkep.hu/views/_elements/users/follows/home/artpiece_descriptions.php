<h4 class="subtitle">Műlap sztorik</h4>
<?=$app->Form->help('Követett műlapokon elfogadott sztorik.', ['class' => 'mb-3'])?>

<?php
if (count($artpiece_descriptions) > 0) {
  foreach ($artpiece_descriptions as $artpiece_description) {
    echo $app->element('artpieces/view/story_item', [
      'description' => $artpiece_description,
      'options' => [
        'class' => 'p-2 mb-3 border rounded',
        'artpiece_details' => true,
        'intro' => 300
      ]
    ]);
  }
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>