<?php
echo $app->Form->help('<strong>Friss fotók, közösségi ellenőrzésre.</strong> Nem törzstagok által mostanában feltöltött fotók olyan műlapokra, amelyek gazdája nem szeretné kezelni a műlapjait, vagy min. ' . sDB['limits']['edits']['inactive_after_months'] . ' hónapja nem járt erre.', ['class' => 'mt-0 mb-2', 'icon' => 'info-circle mr-1']);

$last_artpiece_id = 0;

echo '<div class="row mx-0">';

foreach ($photos_to_check as $photo) {

  if ($last_artpiece_id != $photo['artpiece_id']) {
    echo '</div>';
    echo '<div class="row py-2 my-2 mx-0 border rounded">';

    echo '<div class="col-6 col-sm-4 mb-4">';
    $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'photo_size' => 6,
        'extra_class' => '',
        'background' => '',
        'details_simple' => true,
      ]
    ]);
    echo '</div>';

    $last_artpiece_id = $photo['artpiece_id'];
  }

  echo '<div class="col-6 mb-2">';
  echo $app->Image->photo($photo, [
    'size' => 3,
    'class' => 'img-fluid img-thumbnail',
    'link' => '/' . $photo['artpiece_id'] . '#vetito=' . $photo['id'],
    'link_options' => ['target' => '_blank'],
    'uploader' => true,
  ]);
  echo '</div>';

}
echo '</div>'; // row --