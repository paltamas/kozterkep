<?php
if (count($artpieces) > 0) {

  echo $app->element('layout/partials/search_list_settings', [
    'options' => [
      'items' => $artpieces,
      'total_count' => $total_count,
      'pagination' => $pagination,
      'orders' => $order_options,
      'order_default' => 'publikalas-csokkeno'
    ]
  ]);

  echo '<div class="row" id="lista">';

  foreach ($artpieces as $artpiece) {

    echo '<div class="col-6 col-md-4 col-lg-3 d-sm-flex px-0 px-sm-2 mb-3">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'condition' => true,
        // Csak akkor írjuk ki a státuszt, ha státusz szűrés van főszerkik részéről
        'status' => ($app->Users->is_head($_user) && isset($_params->query['statusz']))
          || isset($_params->query['sajat_statusz']) ? true : false
      ],
    ]);
    echo '</div>';

  }

  echo '</div>';

  echo $app->Html->pagination(count($artpieces), $pagination);
} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}