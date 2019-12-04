<?php
echo $app->Html->link('', '#', [
  'class' => 'helpSwitch ml-2 link-gray px-1',
  'icon' => 'info-circle',
  'title' => 'Űrlap segédletek megjelenítése és elrejtése'
]);

echo $app->Html->link('', '#', [
  'class' => 'viewSwitch ml-1 link-gray hide-when-mapping px-1',
  'icon' => $app->ts('fluid_view') == 1 ? 'desktop-alt' : 'tv',
  'title' => 'Teljes szélességű nézet ki- és bekapcsolása',
  'ia-bind' => 'users.tiny_settings',
  'ia-vars-view_toggle' => '1',
]);