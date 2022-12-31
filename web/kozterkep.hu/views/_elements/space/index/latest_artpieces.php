<?php
echo $app->Html->link('', '#', [
  'data-target' => '#latest-artpieces',
  'data-toggle' => 'collapse',
  'class' => 'float-right latest-toggle fa-lg d-sm-none',
  'icon' => $app->ts('space_hidden_latests') == 1 ? 'plus-square fas' : 'minus-square fas',
  'ia-bind' => 'users.tiny_settings',
  'ia-vars-space_hidden_latests' => $app->ts('space_hidden_latests') == 1 ? 0 : 1,
  'ia-toggleclass' => 'fa-plus-square fa-minus-square',
  'ia-target' => '.latest-toggle .fas',
]);

echo '<h5 class="subtitle">Friss műlapok</h5>';

echo '<div class="collapse show-sm ' , $app->ts('space_hidden_latests') == 1 ? '' : 'show' , '" id="latest-artpieces">';
echo '<div class="row">';
echo $app->element('artpieces/list/list', [
  'artpieces' => $latests,
  'options' => [
    'top_count' => 18,
    'top_class' => 'col-4 mx-0 px-0',
    'top_details' => true,
    //'separator_element' => 'space/index/important_message',
    'class' => 'col-3 my-2 px-1',
  ]
]);
echo '</div>'; // row --

echo '<div class="text-center mt-3">';
echo $app->Html->link('Műlapok listája', '/kereses#hopp=lista', [
  'icon' => 'list',
  'class' => 'btn btn-outline-primary',
]);
echo '</div>';

echo '<hr class="my-3 my-md-5" />';

echo '<h5 class="subtitle">Mostanában bővült korábbi műlapok</h5>';
echo '<div class="row">';
echo $app->element('artpieces/list/list', [
  'artpieces' => $updated_artpieces,
  'options' => [
    'top_count' => 4,
    'top_class' => 'col-6 px-0',
    'class' => 'col-3 col-md-2 my-2 px-1',
  ]
]);
echo '</div>';

echo '</div>'; // collapse --