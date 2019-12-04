<hr class="mt-5" />

<p class="text-muted">Az oldal utolsó módosítása: <?=_time($page['modified'])?></p>

<?php
if ($_user) {
  echo $app->Html->tabs([
    'Megtekintés' => [
      'link' => $page['path'],
      'icon' => 'file-alt',
    ],
    'Történet' => [
      'link' => '/oldalak/szerkesztesi-tortenet/' . $page['id'],
      'icon' => 'history',
    ],
    'Szerkesztés' => [
      'link' => '/oldalak/szerkesztes/' . $page['id'],
      'icon' => 'edit',
    ],
  ], [
    'type' => 'pills',
    'align' => 'left',
    'selected' => @$selected > 0 ? $selected : 1,
    'class' => 'mt-4'
  ]);
}