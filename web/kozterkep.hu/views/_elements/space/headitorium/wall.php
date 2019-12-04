<?php
echo $app->Html->tabs([
  'FőszerkSzoba' => [
    'hash' => 'hozzaszolasok',
    'icon' => 'comments',
  ],
  'Események' => [
    'hash' => 'esemenyek',
    'icon' => 'history',
  ],
], [
  'type' => 'pills',
  'align' => 'center',
  'selected' => 1,
  'class' => 'small mb-2'
]);
?>

<div class="tab-content">
  <div class="tab-pane show active" id="hozzaszolasok" role="tabpanel" aria-labelledby="hozzaszolasok-tab">
    <?php
    echo $app->Html->link('', '/kozter/forum-tema/6', [
      'title' => 'Ugrás a fórumba',
      'icon' => 'comments',
      'class' => 'float-right mt-2',
    ]);

    echo $app->element('comments/thread', [
      'model_name' => 'forum_topic',
      'model_id' => 6,
      'files' => true,
      'limit' => 30,
    ]);
    ?>
  </div>

  <div class="tab-pane" id="esemenyek" role="tabpanel" aria-labelledby="esemenyek-tab">
    <?=$app->element('space/index/events')?>
  </div>
</div>