<a class="dropdown-toggle custom-toggle no-caret badge-dropdown nu link-gray notification-icon-container" href="#" data-toggle="dropdown" data-target="#notification-dropdown-menu" id="" role="button" aria-haspopup="true" aria-expanded="false">
  <span class="far fa-bell"></span>
  <sup class="notification-count rounded text-white font-weight-bold">&nbsp;</sup>
</a>
<div class="notification-dropdown-menu dropdown-menu dropdown-menu-right maximized-dropdown dropdown-no-focus" aria-labelledby="" role="menu">
  <?=$app->Html->link('Értesítések listája', '/tagsag/ertesitesek', [
    'class' => 'dropdown-item',
    'icon' => 'list-alt',
  ])?>
  <div class="dropdown-divider mb-0 pb-0"></div>
  <form class="notification-list" role="form"></form>
  <h6 class="dropdown-header notifications-empty">Nincs olvasatlan értesítésed</h6>
</div>