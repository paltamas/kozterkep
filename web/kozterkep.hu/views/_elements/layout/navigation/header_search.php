<?php
// Instant keresőmező
echo $app->Form->input('header_instant_search', [
  'placeholder' => 'Keresés',
  'class' => 'd-inline-block instant-search input-no-clear',
  'divs' => 'd-inline-block ml-3',
  'style' => 'width: 20vw;'
]);

// Kereső ikon
echo $app->Html->link('', '/kereses', [
  'icon' => 'far fa-search',
  'class' => 'text-muted',
  'divs' => 'd-inline-block ml-1',
  'title' => 'Ugrás a keresőoldalra!'
]);