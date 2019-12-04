<?php
echo '<div class="float-right">';

echo $app->Html->link('', '/kozter/forum?tag=ennekem', [
  'title' => 'Engem érintő hozzászólások',
  'icon' => 'user-alt',
  'class' => 'mt-2 mr-2',
  'hide_text' => true,
]);


$topics = [];
$topics[] = [
  'Fórum kezdőlap', '/kozter/forum', []
];
foreach (sDB['forum_topics'] as $topic_id => $topic) {
  if (!$app->Users->is_head($_user) && $topic[1] == 1) {
    continue;
  }
  $topics[] = [
    $topic[0], '/kozter/forum-tema/' . $topic_id, []
  ];
}

echo $app->Html->dropdown(
  $app->Html->icon('comments'),
  [
    'class' => 'd-inline-block pt-2',
    'id' => 'wallForumTopics',
  ],
  $topics,
  [
    'class' => 'dropdown-menu-right'
  ]
);

echo '</div>';

echo $app->element('comments/add', [
  'options' => [
    'default_text' => '<div class="alert alert-info p-2 small">Ha nem hozzászólásra válaszolsz, a "Beszéljük meg" fórumba kerül a kommented.</div>',
    'form_action' => 'comments.prepend_comment:.space-comments',
    'model_name' => 'forum_topic',
    'model_id' => 4, // beszéljük meg!
    'files' => true,
    'base_model_name' => 'forum_topic',
    'base_model_id' => 4,
    'link_class' => 'd-block',
  ]
]);

echo '<div class="space-comments thread-refresh mb-2 pb-3 pb-md-0 border-bottom border-md-0" '
  . ' ia-custom-field="spacewall" ia-custom-value="comments" id="comments">';
$i = 0;
foreach ($comments as $comment) {
  $i++;
  echo $app->element('comments/item', ['comment' => $comment, 'options' => [
    'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
  ]]);
}

echo '<div class="text-center">';
echo $app->Html->link('Ugrás a fórumba', '/kozter/forum', [
  'icon' => 'comments',
  'class' => 'btn btn-outline-primary',
]);
echo '</div>';

echo '</div>';