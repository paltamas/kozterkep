<?php
$text = !isset($text) ? '' : $text;

if (is_array($text) && count($text) > 1) {
  $text = '<div class="mt-2">' . $app->Html->list($text) . '</div>';
} elseif (is_array($text) || $text != '') {
  $text = is_array($text) ? $text[0] : $text;
} else {
  $text = false;
}

echo '<div class="kt-info-box p-4 px-5 mb-4">';

echo '<div>';
echo '<strong class="text-dark"><span class="far fa-tools fa-fw"></span>Fejlesztés alatt.</strong>';
echo $text ? '<span class="ml-3 font-italic">' . $text . '</span>' : '';
echo '</div>';

if ($_user) {
  echo '<div class="text-muted mt-3">Ha bármi extra kérdés, ötlet vagy proléma van, ' .  $app->Html->link('jelezz az üzemgazdának', '/beszelgetesek/inditas?tag=1', [
    'icon' => 'comment-alt'
  ]) . '.</div>';
}

echo '</div>';