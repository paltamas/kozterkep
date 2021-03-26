<div class="row">
  <div class="col-sm-6 col-lg-5 text-center text-sm-left mb-4">
    <?php
    echo $app->Form->input('user_instant_search', [
      'placeholder' => 'Saját műlap gyorskereső',
      'class' => 'd-inline-block instant-search input-no-clear',
      'data-target' => '#my-artpieces',
      'data-type' => 'simple',
      'data-mine' => 1,
      'divs' => 'mb-1',
    ]);

    echo $app->Html->link('Részletes keresés saját műlapok közt', '/kereses?r=1&sajat=1', [
      'icon' => 'far fa-search',
    ]);
    ?>
  </div>

  <div class="col-lg-1 d-none d-lg-flex"></div>

  <div class="col-sm-6 text-center text-md-right mb-4">
    <?php
    echo $app->Html->link('Új műlap létrehozása', '/mulapok/letrehozas', [
      'class' => 'btn btn-primary btn-lg',
      'icon' => 'map-marker-plus fas'
    ]);
    ?>
  </div>
</div>


<div id="my-artpieces"></div>

<?php
if (count($latest_artpieces) > 0) {
  echo '<div class="row py-3 my-3 border-top border-bottom">';
  echo '<div class="col-12">';
  echo '<h5 class="subtitle">Legfrissebb műlapok</h5>';
  echo '</div>';
  echo $app->element('artpieces/list/list', [
    'artpieces' => $latest_artpieces,
    'options' => [
      'top_count' => 6,
    ]
  ]);
  echo '</div>';
}
?>


<div class="row mt-4">

  <?php
  if (count($invitations) > 0) {
    echo '<div class="col-12 mb-5">';
    echo '<h5 class="subtitle">Szerkesztési meghívások</h5>';

    echo '<div class="row">';

    foreach ($invitations as $artpiece) {
      echo '<div class="col-4 col-sm-3 col-lg-2 mb-2 text-center small">';
      echo $app->element('artpieces/list/item', [
        'artpiece' => $artpiece,
        'options' => [
          'simple' => true,
          'tooltip' => true,
          'extra_class' => '',
        ],
      ]);
      echo '</div>';
    }

    echo '</div>';

    echo '</div>';
  }
  ?>

  <div class="col-md-7 col-12 mb-5">
    <h5 class="subtitle">Heti népszerűk</h5>
    <?php
    if (count($top_artpieces) > 0) {
      echo $app->element('artpieces/list/toplist', ['artpieces' => $top_artpieces, 'options' => [
        'field' => 'view_week'
      ]]);
    } else {
      echo $app->element('layout/partials/empty', ['class' => 'py-3']);
    }

    echo $app->Html->link('Statisztikáim', '/kozosseg/tag_statisztikak/' . $_user['link'], [
      'icon' => 'chart-line'
    ]);

    echo $app->Html->link('Műlapjaim össz. nézettség szerint', '/kereses?r=1&sajat=1&sorrend=nezettseg-csokkeno', [
      'icon_right' => 'arrow-right',
      'class' => 'ml-4'
    ]);
    ?>
  </div>

  <div class="col-md-5 col-12 mb-5">

    <h5 class="subtitle">Mostanában módosított publikus</h5>
    <?php
    if (count($modified_artpieces) > 0) {
      echo '<div class="row">';
      foreach ($modified_artpieces as $artpiece) {
        echo '<div class="col-6 col-sm-4 col-md-3 p-md-1">';
        echo $app->element('artpieces/list/item', [
          'artpiece' => $artpiece,
          'options' => [
            'simple' => true,
            'tooltip' => true,
            'extra_class' => 'mb-2',
          ],
        ]);
        echo '</div>';
      }
      echo '</div>'; // row --
    } else {
      echo $app->element('layout/partials/empty', ['class' => 'py-3']);
    }
    ?>

  </div>


  <div class="col-12 mb-4">
    <?php
    echo '<h5 class="subtitle">Nem publikus műlapjaim</h5>';
    if (count($work_artpieces) > 0) {
      echo '<hr />';
      echo '<div class="row">';
      foreach ($work_artpieces as $artpiece) {
        echo '<div class="col-6 col-md-4 col-lg-2 p-md-1">';
        echo $app->element('artpieces/list/item', [
          'artpiece' => $artpiece,
          'options' => [
            'details' => false,
            //'tooltip' => true,
            //'extra_class' => 'mb-2',
            'status' => true,
          ],
        ]);
        echo '</div>';
      }
      echo '</div>'; // row --
    } else {
      echo $app->element('layout/partials/empty', ['class' => 'py-3']);
    }
    ?>

  </div>

</div>