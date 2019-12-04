<a class="dropdown-toggle custom-toggle no-caret badge-dropdown nu link-gray conversation-icon-container" href="#" data-toggle="dropdown" data-target="#conversation-dropdown-menu" id="" role="button" aria-haspopup="true" aria-expanded="false">
  <span class="far fa-comments-alt"></span>
  <sup class="conversation-count rounded text-white font-weight-bold">&nbsp;</sup>
</a>
<div class="conversation-dropdown-menu dropdown-menu dropdown-menu-right maximized-dropdown dropdown-no-focus" aria-labelledby="" role="menu">

  <?php

  echo '<div class="row">';
  echo '<div class="col-5">';
  echo $app->Html->link('Aktívak', '/beszelgetesek/aktiv', [
    'class' => 'dropdown-link',
    'icon' => 'inbox',
  ]);
  echo '</div>';
  echo '<div class="col-7">';
  echo $app->Html->link('Új beszélgetés', '/beszelgetesek/inditas', [
    'class' => 'dropdown-link text-right',
    'icon' => 'comment-alt-plus',
  ]);
  echo '</div>';
  echo '</div>';
  ?>

  <!--
  <?=$app->Html->link('Aktív beszélgetések', '/beszelgetesek/aktiv', [
    'class' => 'dropdown-item',
    'icon' => 'inbox',
  ])?>

  <?=$app->Html->link('Új beszélgetés indítása', '/beszelgetesek/inditas', [
    'class' => 'dropdown-item',
    'icon' => 'comment-alt-plus',
  ])?>
  -->


  <div class="dropdown-divider mb-0 pb-0"></div>
  <form class="conversation-list" role="form"></form>
  <h6 class="dropdown-header conversations-empty">Nincs olvasatlan üzeneted</h6>
</div>