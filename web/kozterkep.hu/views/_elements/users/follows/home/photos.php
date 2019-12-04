<h4 class="subtitle">Fotók</h4>
<?=$app->Form->help('Követett műlapokra és alkotói adatlapokra töltött fotók.')?>

<?php
if (count($photos) > 0) {
  echo '<div class="row mt-2">';
  foreach ($photos as $photo) {
    echo '<div class="col-6 col-sm-4 col-md-12 col-lg-6 mb-3">';

    $artpiece = $artist = false;

    if ($photo['artpiece_id'] > 0) {
      $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
      $link = '/' . $artpiece['id'] . '#vetito=' . $photo['id'];
    } elseif ($photo['portrait_artist_id'] > 0) {
      $artist = $app->MC->t('artists', $photo['portrait_artist_id']);
      $link = $app->Html->link_url('', ['artist' => $artist]) . '#vetito=' . $photo['id'];
    }

    echo $app->Image->photo($photo, [
      'size' => 4,
      'info' => true,
      'class' => 'img-fluid img-thumbnail',
      'link' => $link,
      'artpiece' => $artpiece,
      'artis' => $artist,
    ]);
    echo '</div>'; // row --
  }
  echo '</div>'; // row --
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>