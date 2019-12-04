<?php
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Gyűjtemény neve',
  'user_filter' => 'setters',
  'custom_inputs' => [
    ['tipus', [
      'options' => [
        '' => 'Minden típus',
        'kozos' => 'Közös',
        'tagi' => 'Tagi',
      ]
    ]]
  ]
]]);

if (count($sets) > 0) {

  echo $app->Html->pagination(count($sets), $pagination + ['div' => 'mt-1 mb-3']);

  echo '<div class="row">';

  foreach ($sets as $set) {
    echo '<div class="col-sm-6 col-md-4 col-lg-3 d-md-flex">';
    echo $app->element('sets/item', ['set' => $set]);
    echo '</div>'; // col
  }

  echo '</div>'; // row

  echo $app->Html->pagination(count($sets), $pagination);

} else {
  echo '<div class="text-center text-muted my-5">';
  echo 'Nincs találat a megadott feltételek mellett.';
  echo $app->element('layout/partials/empty');
  echo '</div>';
}

