<?php
if ($user['header_photo_filename'] == '') {
  return;
}
$image = $app->Html->tag('div', '', [
  //'src' => '/tagok/' . $user['header_photo_filename'],
  'class' => 'rounded',
  'style' => 'background: url(/tagok/' . $user['header_photo_filename'] . ') no-repeat center center; background-size: cover; height: 240px; max-height: 30vh;',
]);
echo $app->Html->link($image, '/tagok/' . $user['header_photo_filename'], ['target' => '_blank']);

return;
?>





<div class="d-none d-md-block">
  <?php
  $top_position = $user['header_photo_height'] < 500 ? '0' : '-100%';
  echo '<div class="rounded" style="height: ' . min(240, $user['header_photo_height']) . 'px; overflow: hidden; position: relative;">';
  $image = $app->Html->tag('img', '', [
    'src' => '/tagok/' . $user['header_photo_filename'],
    'style' => 'position: absolute; width: 100%; top: ' . $top_position . '',
  ]);
  echo $app->Html->link($image, '/tagok/' . $user['header_photo_filename'], ['target' => '_blank']);
  echo '</div>';
  ?>
</div>

<div class="d-md-none">
  <?php
  $image = $app->Html->tag('div', '', [
    'src' => '/tagok/' . $user['header_photo_filename'],
    'class' => 'img-fluid rounded',
  ]);
  echo $app->Html->link($image, '/tagok/' . $user['header_photo_filename'], ['target' => '_blank']);
  ?>
</div>