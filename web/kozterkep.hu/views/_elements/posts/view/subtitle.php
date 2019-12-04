<?php
echo '<span class="mr-2 font-weight-bold">' . $app->Blog->blog_name($post['user_id'], ['image' => false]) . '</span>';
echo $app->Users->name($post['user_id'], [
  'image' => true,
  'tooltip' => true,
  'class' => ''
]);
echo ' @ ' . _time($post['published']);

$postcategory = sDB['post_categories'][$post['postcategory_id']];
echo $app->Html->link($postcategory[0], '/blogok/tema/' . $postcategory[2], [
  'class' => 'ml-2 text-muted font-weight-bold',
]);