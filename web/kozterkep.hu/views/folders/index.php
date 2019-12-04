<div class="row pb-5">
  <div class="col-md-5 mb-2">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'action' => '/mappak/kereses',
      'placeholder' => 'Mappa neve',
      'class' => 'mt-md-3',
    ]]);
    echo '<div class="">Összesen <strong>' . $folder_count . ' publikus mappa</strong>. ' . $app->Html->link('Keresés', '/mappak/kereses', ['icon_right' => 'arrow-right']) . '</div>';
    ?>
  </div>
  <div class="col-md-7">
    <div class="kt-info-box">Tagi mappáinkban mindenféle olyan állományt tárolunk, amelyek műlapokon, bejegyzésekben vagy más helyeken linkelődnek. Ezen az oldalon a publikusnak jelölt mappákból válogatunk.</div>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-4">
    <h4 class="subtitle mb-3">Mostanában frissült</h4>
    <?php
    foreach ($latest_folders as $folder) {
      echo $app->element('folders/item', ['folder' => $folder]);
    }
    ?>
  </div>

  <div class="col-md-4">
    <h4 class="subtitle mb-3">Heti népszerű</h4>
    <?php
    foreach ($top_folders as $folder) {
      echo $app->element('folders/item', ['folder' => $folder]);
    }
    ?>
  </div>

  <div class="col-md-4">
    <h4 class="subtitle mb-3">Legnagyobb mappák</h4>
    <?php
    foreach ($biggest_folders as $folder) {
      echo $app->element('folders/item', ['folder' => $folder]);
    }
    ?>
  </div>
</div>
