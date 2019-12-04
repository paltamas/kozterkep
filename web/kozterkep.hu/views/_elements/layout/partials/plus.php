<a class="dropdown-toggle no-caret badge-dropdown nu link-gray" href="#" id="" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  <span class="fas fa-plus-circle text-dark"></span>
</a>
<div class="plus-artpieces-dropdown-menu dropdown-menu dropdown-menu-right maximized-dropdown dropdown-no-focus" aria-labelledby="">

  <?php

  echo '<div class="row">';
  echo '<div class="col-4">';
  echo $app->Html->link('Új műlap', '/mulapok/letrehozas', [
    'class' => 'dropdown-link text-primary',
    'icon' => 'map-marker-plus fas'
  ]);
  echo '</div>';
  echo '<div class="col-8 text-right">';
  echo $app->Html->link('Saját műlapok', '/kozter/mulapjaim', [
    'class' => 'dropdown-link',
    'icon' => 'map-marker-smile'
  ]);
  echo '</div>';
  echo '</div>';

  // Komment, Mappa, ...?

  /*echo $app->Html->link('Blogbejegyzés létrehozása', '/blogok/letrehozas', [
    'class' => 'dropdown-item',
    'icon' => 'list-alt'
  ]);*/
  ?>

  <div class="dropdown-divider mb-0 pb-0"></div>
  <?=$app->element('users/modified_list', ['limit' => 5])?>
  <?=$app->element('users/edit_list', ['limit' => 10])?>
</div>