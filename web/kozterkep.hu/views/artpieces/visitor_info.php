<?php
if (!$_user) {
  if ($comment_count > 0 || $edit_count > 0) {
    echo '<div class="kt-info-box mt-4">';

    echo '<div class="mb-1">Erre a műlapra eddig ';
    echo $comment_count > 0 ? $app->Html->icon('comment mx-1') . $comment_count . ' hozzászólás ' : '';
    echo $comment_count > 0 && $edit_count > 0 ? ' és ' : '';
    echo $edit_count > 0 ? $app->Html->icon('edit mx-1') . $edit_count . ' szerkesztés ' : '';
    echo 'érkezett tagjainktól.</div>';

    echo $app->element('users/only_users', [
      'options' => ['alert' => false]
    ]);

    echo '</div>';
  }
}