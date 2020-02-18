<?php

echo $app->element('comments/add', [
  'options' => [
    'default_text' => '<div class="alert alert-info p-2 small">Amit itt írsz, az a "FőszerkSzoba" fórumba kerül.</div>',
    'form_action' => 'comments.prepend_comment:.space-headitor-comments',
    'model_name' => 'forum_topic',
    'model_id' => 6, // főszerkszoba
    'files' => true,
    'base_model_name' => 'forum_topic',
    'base_model_id' => 6,
    'link_class' => 'd-block',
  ]
]);

echo '<div class="space-headitor-comments thread-refresh mb-2 pb-3 pb-md-0 border-bottom border-md-0" '
  . ' ia-custom-field="spacewall" ia-custom-value="headitorcomments" id="headitorcomments" data-forum-topic-id="6">';
$i = 0;
foreach ($headitorcomments as $comment) {
  $i++;
  echo $app->element('comments/item', ['comment' => $comment, 'options' => [
    'reply' => true,
    'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
  ]]);
}

echo '</div>';