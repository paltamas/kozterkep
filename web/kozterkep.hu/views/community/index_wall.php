<h5 class="subtitle">Legfrissebb élményképek</h5>
<?php
if (count($photos) > 0) {
  $i = 0;
  echo '<div class="row">';
  foreach ($photos as $photo) {
    $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
    if ($artpiece['status_id'] != 5) {
      continue;
    }
    echo '<div class="col-4 col-sm-3 col-md-4">';
    echo $app->Image->photo($photo, [
      'size' => 4,
      'class' => 'img-thumbnail img-fluid mr-3 mb-2',
      'photo_tooltip' => $photo['id'],
      'link' => '/' . $photo['artpiece_id'] . '#vetito=' . $photo['id'],
    ]);
    echo '</div>';
  }
  echo '</div>';
} else {
  echo $app->element('layout/partials/empty');
}
?>


<h5 class="subtitle mt-4">Friss laptörténet</h5>
<?php
$i = 0;
foreach ($events as $event) {
  $i++;
  echo $app->element('events/item', ['event' => $event, 'options' => [
    'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 esemény után mobilon nem mutatjuk
  ]]);
}
?>