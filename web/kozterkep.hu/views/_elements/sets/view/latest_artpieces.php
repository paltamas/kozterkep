<?php
$i = 0;
if (count($artpieces) > 0) {

  echo $app->element('artpieces/list/list', [
    'artpieces' => $artpieces,
    'options' => [
      'top_count' => 4,
      'top_class' => 'col-6 p-0 d-flex mb-2',
      'class' => 'col-4 col-sm-3 col-md-2 p-md-1',
      'max_items' => 16,
    ]
  ]);

  echo '<div class="col-12 mt-3 text-center">';

  /**
   * Ezzel linkeljük a listát, hogy körbejárhassunk
   * térkép, lista, gyűjtemény... mindig a megfelelő szűréssel és címmel
   */
  $set_type_prefix = $set['set_type_id'] == 1 ? 'kozos_' : '';
  $http_qery = [
    'r' => 1,
    $set_type_prefix . 'gyujtemeny' => $set['id'],
    'oldalcim' => $_title,
    'visszalepes' => $_params->here
  ];

  echo $app->Html->link('Minden besorolt műlap', '/kereses/lista?' . http_build_query($http_qery), [
    'class' => 'btn btn-outline-primary btn-block'
  ]);

  echo '</div>';

} else {
  echo $app->element('layout/partials/empty', ['text' => 'Nincs még publikus műlap a gyűjteményben.']);
}