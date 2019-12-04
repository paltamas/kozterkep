<?php
if (count($comments) > 0) {

  echo '<div class="mt-4 mb-4">';
  echo '<h6 class="subtitle">Kiemelt aktualitások</h6>';

  foreach ($comments as $comment) {
    echo $app->element('comments/item', ['comment' => $comment, 'options' => [
      'buttons' => false,
      'reply' => false,
      'connected_things' => false,
      'thread_links' => false,
    ]]);
  }
  echo '</div>';
}