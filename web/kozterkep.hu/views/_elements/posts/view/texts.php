<?php
if ($post['intro'] != '') {
  echo '<div class="lead font-weight-semibold my-4">' . nl2br($post['intro'], false) . '</div>';
} else {
  echo '<div class="my-1 clearfix">&nbsp;</div>';
}

$image = $app->Blog->image($post, [
  'info_container' => true,
  'file_size' => 273,
]);

if ($image != '') {
  echo $image;
}

echo '<div class="embed-container">';
echo $app->Blog->text($post);
echo '</div>';

if ($post['sources'] != '') {
  echo '<hr class="highlighter my-4" />';
  echo '<div class="text-muted">';
  echo '<span class="font-weight-semibold">Források:</span><br /><linkify_custom>' . $app->Text->format_source($post['sources']) . '</linkify_custom>';
  echo '</div>';
}