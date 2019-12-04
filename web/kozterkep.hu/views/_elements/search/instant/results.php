<?php
$col_class = isset($col_class) ? $col_class : 'col-6 col-sm-6 col-md-4 col-lg-3 mb-3';
$for_map = isset($for_map) ? $for_map : false;

if (count($artpieces) > 0) {

  echo '<div class="row">';

  foreach ($artpieces as $artpiece) {
    echo '<div class="' . $col_class . ' px-0 px-sm-2">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'for_map' => $for_map,
        'condition' => true,
        'status' => true,
      ],
    ]);
    echo '</div>';
  }

  echo '</div>';

  if (count($artpieces) == $limit) {
    echo '<div class="text-center text-muted my-3">Ebben a keresőben maximum ' . $limit . ' elemet mutatunk. Ha ennél teljesebb keresést végeznél, használd a ' . $app->Html->link('Keresés', '/kereses?kulcsszo=' . $query['kulcsszo'], ['class' => 'font-weight-bold']) . ' aloldalt.</div>';
  }

} else {
  echo '<div class="text-center text-muted my-5">';
  echo strlen(@$query['kulcsszo']) < 3 ? 'Legalább 3 karaktert adj meg.' : 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}