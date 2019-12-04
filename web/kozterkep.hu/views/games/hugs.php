<div class="row">
  <div class="col-md-6">

    <div class="kt-info-box mb-4">
      <div class="text-success font-weight-bold mb-2">
        <div class="d-inline-block">Találj rá<?=$app->Html->icon('angle-right ml-2 mr-1')?></div>
        <div class="d-inline-block">Csodáld meg<?=$app->Html->icon('angle-right ml-2 mr-1')?></div>
        <div class="d-inline-block">Érintsd!</div>
      </div>
      <p>Köztéri alkotásra lelni nagy öröm, oszd meg a közösséggel! Sétálj oda a kiszemelt műhöz és okostelefonod segítségével "Érintsd" meg az alkotást a műlap tetején megjelenő gombra kattintva.</p>
    </div>

    <?php
    $i = 0;
    foreach ($events as $event) {
      $i++;
      echo $app->element('events/item', ['event' => $event, 'options' => [
        'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
      ]]);
    }
    ?>

  </div>
  <div class="col-md-6">

    <h5 class="subtitle">Legfrissebb élményképek</h5>
    <?php
    if (count($photos) > 0) {
      $i = 0;
      echo '<div class="row mb-2">';
      foreach ($photos as $photo) {
        $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);
        if ($artpiece['status_id'] != 5) {
          continue;
        }
        echo '<div class="col-4 col-sm-3 col-md-4">';
        echo $app->Image->photo($photo, [
          'size' => 4,
          'class' => 'img-thumbnail img-fluid mr-3 mb-2',
          'photo_tooltip' => $photo['id'],
          'link' => '/' . $photo['artpiece_id'] . '#vetito=' . $photo['id'],
        ]);
        echo '</div>';
      }
      echo '</div>';
    } else {
      echo $app->element('layout/partials/empty');
    }
    ?>

    <h4 class="subtitle">Az elmúlt 30 nap érintői</h4>
    <?php
    echo $app->element('games/hug_toplist_table', [
      'users' => $hugs_30
    ]);
    ?>

    <h4 class="subtitle mt-4">Az elmúlt év érintői</h4>
    <?php
    echo $app->element('games/hug_toplist_table', [
      'users' => $hugs_360
    ]);
    ?>

    <h4 class="subtitle mt-4">Összesítetten legtöbbet érintők</h4>
    <?php
    echo $app->element('games/hug_toplist_table', [
      'users' => $top_huggers
    ]);
    ?>
  </div>
</div>
