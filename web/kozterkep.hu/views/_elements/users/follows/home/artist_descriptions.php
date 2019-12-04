<h4 class="subtitle">Alkotó adalékok</h4>
<?=$app->Form->help('Követett alkotók adatlapjára érkező szöveges adalékok.', ['class' => 'mb-3'])?>

<?php
if (count($artist_descriptions) > 0) {
  foreach ($artist_descriptions as $artist_description) {
    echo $app->element('artists/view/description_item', [
      'description' => $artist_description,
      'options' => [
        'intro' => 300,
      ]
    ]);
  }
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>