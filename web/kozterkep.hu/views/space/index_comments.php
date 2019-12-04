<?php
// nem ezt használjuk most, hanem az space/index/comments elementet
echo '<div class="float-right">';

echo $app->Html->link('', '/kozter/forum?tag=ennekem', [
  'title' => 'Engem érintő hozzászólások',
  'icon' => 'user-alt',
  'class' => 'float-right mt-2 mr-2',
  'hide_text' => true,
]);

echo $app->Html->link('', '/kozter/forum', [
  'title' => 'Ugrás a fórumba',
  'icon' => 'comments',
  'class' => 'float-right mt-2',
  'hide_text' => true,
]);

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


echo '<div class="space-comments thread-refresh mb-2 pb-3 pb-md-0 border-bottom border-md-0">';
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