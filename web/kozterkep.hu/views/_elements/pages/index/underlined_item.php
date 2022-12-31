<?php
$compact = $compact ?? false;

echo '<div class="row">';
echo '<div class="col-12 mb-2">';

// Cím

echo $compact ? '<div class="h4">' : '<div class="h2">';
echo $app->Html->link($artpiece['title'], '', [
  'artpiece' => $artpiece,
  'class' => 'd-block text-dark my-0',
]);
echo '</div>';

// Helység és Év
echo '<div class="font-weight-bold">';
echo $app->Places->name($artpiece['place_id'], ['link' => false]);
$year = $app->Artpieces->get_artpiece_year($artpiece['dates']);
echo $year != '' ? ', ' . $year : '';

// Alkotó
$artist = $app->Artpieces->get_artpiece_artist($artpiece['artists']);
if ($artist) {
  echo ', ' . $app->Artists->name($artist['id'], ['link' => false]);
}
echo '</div>';

echo '</div>'; // col

echo '<div class="col-md-3 col-lg-4 mb-3 mb-md-0">';
echo $app->Html->link($app->Image->photo($artpiece, [
  'size' => $compact ? 4 : 2,
  'class' => 'img-thumbnail',
]), '#', [
  'artpiece' => $artpiece,
]);
echo '</div>';
echo '<div class="col-md-9 col-lg-8">';


$description = preg_replace('/\[[0-9]*\]/', "", $artpiece['descriptions'][0]['text']);
echo '<div class="mb-2 ' , $compact ? '' : 'lead' , '">';
echo $app->Text->truncate($description, $compact ? 250 : 350);
echo '</div>';
echo '<strong>' . $app->Users->name($artpiece['user_id'], [
    'tooltip' => false,
    'link' => false,
    'image' => 4,
  ]) . '</strong>';
echo ' <span class="text-muted mr-2 small">' . _time($artpiece['published'], ['ago' => true]) . '</span>';

echo '</div>';
echo '</div>';