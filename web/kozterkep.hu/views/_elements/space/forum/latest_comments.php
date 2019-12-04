<?php
$options = (array)@$options + [
  'hide_after' => false,
  'highlight' => false,
];

echo $app->element('comments/add', [
  'options' => [
    'default_text' => @$forum_topic['id'] > 0 ? '' : '<div class="alert alert-info p-2 small">Ha nem hozzászólásra válaszolsz, a "Beszéljük meg" fórumba kerül a kommented.</div>',
    'form_action' => 'comments.prepend_comment:.space-comments',
    'model_name' => 'forum_topic',
    'model_id' => @$forum_topic['id'] > 0 ? $forum_topic['id'] : 4, // beszéljük meg!
    'files' => true,
    'base_model_name' => 'forum_topic',
    'base_model_id' => @$forum_topic['id'] > 0 ? $forum_topic['id'] : 4,
    'link_class' => 'd-block'
  ]
]);

// Csak akkor frissítjük a kommenteket ajaxszal, ha az első oldalon vagyunk,
// vagy nincs léptetés egyáltalán
$refresh_class = (@$_params->query['oldal'] == 1 || !isset($_params->query['oldal']))
  && !isset($_params->query['kifejezes']) && !isset($_params->query['kereses'])
  && !isset($_params->query['tag']) && !isset($_params->query['tipus'])
  ? 'thread-refresh' : '';

echo '<div class="space-comments ' . $refresh_class . '" data-forum-topic-id="' , @$forum_topic['id'] > 0 ? $forum_topic['id'] : '' , '">';
$i = 0;
foreach ($latest_comments as $comment) {
  $i++;
  echo $app->element('comments/item', ['comment' => $comment, 'options' => [
    'row_class' => $options['hide_after'] && $i > $options['hide_after']
      ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
    'highlight' => $options['highlight'],
    'truncate' => 400,
  ]]);
}

echo $app->Html->pagination(count($latest_comments), $pagination);

echo '</div>';