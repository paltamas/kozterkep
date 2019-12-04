<div class="d-block d-md-none my-2 z-1">

  <div class="row mx-0">
    <div class="col-12">

      <hr />

      <div class="navbar-nav mr-auto">
        <div class="dropdown">
          <?= $app->element('layout/navigation/user_menu') ?>
        </div>

        <?php
        if ($_user) {
          echo $app->Html->link(
            'Beszélgetések<span class="navlink-conversation-count"></span>',
            '#',
            [
              'class' => 'nav-link in-modal conversation-icon-container',
              'data-source' => '.conversation-dropdown-menu',
              'data-title' => 'Olvasatlan üzeneteid',
              'icon' => 'comments-alt'
            ]
          );

          echo $app->Html->link(
            'Értesítések<span class="navlink-notification-count"></span>',
            '#',
            [
              'class' => 'nav-link in-modal notification-icon-container',
              'data-source' => '.notification-dropdown-menu',
              'data-title' => 'Olvasatlan értesítéseid',
              'icon' => 'bell'
            ]
          );

          echo $app->Html->link(
            'Műlapjaid',
            '#',
            [
              'class' => 'nav-link in-modal',
              'data-source' => '.plus-artpieces-dropdown-menu',
              'data-title' => 'Saját műlapok',
              'icon' => 'plus-circle'
            ]
          );

          echo $app->Html->link(
            'Követéseim',
            '/tagsag/koveteseim',
            [
              'class' => 'nav-link',
              'icon' => 'star'
            ]
          );
        }
        ?>
      </div>

      <hr class="text-gray-dark" />

    </div>
  </div>
</div>