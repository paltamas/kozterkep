<?php

echo '<div class="clearfix">';
echo $app->Html->link('Bezárás', '#parameterek', [
  'data-toggle' => 'collapse',
  'icon' => 'times',
  'class' => 'float-right d-none d-md-block mb-3 mb-sm-0 btn btn-outline-secondary',
]);

echo $app->Html->link('Minden jelölés levétele', '#', [
  'ia-uncheck-all' => '.search-parameters',
  'icon' => 'minus-square',
  'class' => 'float-md-right mt-2 mb-3 mb-sm-0 mr-4',
]);
echo '</div>';

echo '<div class="row search-parameters text-left">';

echo '<div class="col-12 mb-4">';
echo $app->element('search/index/parameters_general');
echo '</div>'; // col --

echo '<div class="col-12 text-left mb-2">';
echo $app->Form->input('parameter_kapcsolas', [
  'label' => 'Alábbi paraméterek figyelembevétele',
  'type' => 'select_button',
  'options' => [
    '' => 'Mind szerepeljen (ÉS kapcsolat)',
    'vagy' => 'Legalább egy szerepeljen (VAGY kapcsolat)',
  ],
]);
echo '</div>'; // col --

echo '<div class="col-sm-6 col-md-3">';
echo $app->element('search/index/parameters_list', ['group_id' => 1]);
echo '</div>'; // col --

echo '<div class="col-sm-6 col-md-3">';
echo $app->element('search/index/parameters_list', ['group_id' => 2]);
echo $app->element('search/index/parameters_list', ['group_id' => 3]);
echo '</div>'; // col --

echo '<div class="col-sm-6 col-md-3">';
echo $app->element('search/index/parameters_list', ['group_id' => 4]);
echo '</div>'; // col --

echo '<div class="col-sm-6 col-md-3">';
echo $app->element('search/index/parameters_list', ['group_id' => 5]);
echo $app->element('search/index/parameters_list', ['group_id' => 6]);
echo '</div>'; // col --

echo '</div>'; // row --