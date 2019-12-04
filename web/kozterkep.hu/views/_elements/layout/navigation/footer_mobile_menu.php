<div class="footer-menu bg-gray-kt d-md-none px-xl-2 fixed-bottom-md">
  <div class="container px-0">
    <nav class="navbar-light navbar-expand-lg py-2 row">

      <div class="col px-1">
        <?php
        echo $app->Html->link(
          '',
          '/terkep',
          [
            'class' => 'mx-3 ml-4 nav-icon',
            'icon' => 'map'
          ]
        );
        ?>
      </div>

      <div class="col px-1">
        <?php
        echo $app->Html->link(
          '',
          '/terkep',
          [
            'class' => 'mx-3 nav-icon',
            'icon' => 'compass'
          ]
        );
        ?>
      </div>          

      <div class="col px-1">
        <?php
        echo $app->Html->link(
          '',
          '/kereses',
          [
            'class' => 'mx-3 nav-icon',
            'icon' => 'search'
          ]
        );
        ?>
      </div>

      <?php if ($_user) { ?>
        <div class="col px-1">
          <?php
          echo $app->Html->link(
            '',
            '/kozter',
            [
              'class' => 'mx-3 nav-icon',
              'icon' => 'users'
            ]
          );
          ?>
        </div>

        <div class="col px-1 dropup">
          <?php
          $image = $_user['profile_photo_filename'] != ''
            ? $app->Users->profile_image($_user) : '<span class="far fa-fw fa-user-circle"></span>';

          echo $app->Html->dropdown(
            $image . '<sup class="icon-sup sum-alert-count rounded text-white px-2">&nbsp;</sup>',
            [
              'no_caret' => true,
              'class' => 'nav-icon'
            ],
            array_merge(
              array_reverse(APP_MENUS['usermenu']['logged']),
              [
                '',
                [
                  'Raklapok',
                  '#',
                  ['icon' => 'cubes']
                ],
                [
                  'Értesítések<span class="navlink-notification-count"></span>',
                  '#',
                  [
                    'icon' => 'bell',
                    'class' => 'in-modal',
                    'data-source' => '.notification-dropdown-menu',
                    'data-title' => 'Olvasatlan értesítéseid'
                  ]
                ],
                [
                  'Beszélgetések<span class="navlink-conversation-count"></span>',
                  '#',
                  [
                    'icon' => 'envelope',
                    'class' => 'in-modal',
                    'data-source' => '.conversation-dropdown-menu',
                    'data-title' => 'Olvasatlan üzeneteid'
                  ]
                ]
              ]
            )
          );
          ?>
        </div>


      <?php } else { ?>

        <div class="col px-1">
          <?php
          echo $app->Html->link(
            '',
            '/jatek/info',
            [
              'class' => 'mx-3 nav-icon',
              'icon' => 'trophy'
            ]
          );
          ?>
        </div>

        <div class="col px-1 dropup">
          <?php
          echo $app->Html->dropdown(
            '',
            [
              'no_caret' => true,
              'icon' => 'user-circle',
              'class' => 'nav-icon'
            ],
            array_reverse(APP_MENUS['usermenu']['public'])
          );
          ?>
        </div>

      <?php } ?>

    </nav>
  </div>
</div>
