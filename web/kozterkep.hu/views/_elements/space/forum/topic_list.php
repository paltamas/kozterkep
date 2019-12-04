<?php
foreach (sDB['forum_topics'] as $topic_id => $topic) {
  if (!$app->Users->is_head($_user) && $topic[1] == 1) {
    continue;
  }

  $topic_name = $topic[0];

  $active = isset($forum_topic) && $forum_topic['id'] == $topic_id ? ' active' : '';

  echo $app->Html->link($topic_name, '/kozter/forum-tema/' . $topic_id, [
    'icon' => $topic[2] . ' fas',
    'class' => 'btn btn-outline-primary btn-sm mr-2 mb-2 py-1 px-2' . $active
  ]);
}