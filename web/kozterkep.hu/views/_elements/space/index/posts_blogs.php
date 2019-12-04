<?php
echo $app->Html->link('Mind', '/blogok/friss', [
  'class' => 'float-right btn btn-outline-primary btn-sm',
  'icon_right' => 'arrow-right',
  'hide_text' => true,
]);
echo '<h5 class="subtitle">Blogbejegyzések</h5>';
echo $app->element('posts/list');
echo '<hr class="my-3" />';
