<?php
$options = (array)@$options + [
  'title' => '',
  'icon' => '',
  'items' => [],
];

$items = is_array($options['items']) ? $options['items'] : APP_MENUS['main'][$options['items']][0];

echo '<div class="mb-1"><strong><span class="far fa-fw mr-1 fa-' . $options['icon'] . '"></span>' . $options['title'] . '</strong></div>';
echo '<ul class="nav flex-column">';
foreach ($items as $key => $value) {
  // Ha nem láthatja a menüpontot
  if (isset($value[1])
    && ((!$_user && $value[1] == 1) || ($_user['headitor'] == 0 && $value[1] == 2))) {
    continue;
  }
  $link_name = $key;
  $link_path = is_array($value) ? $value[0] : $value;
  echo $app->Html->link($link_name, $link_path, [
    'class' => 'nav-item'
  ]);
}
echo '</ul>';