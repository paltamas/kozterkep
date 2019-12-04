<?php
echo $app->Html->link('', '#', [
  'data-target' => '#headitorium-questions',
  'data-toggle' => 'collapse',
  'class' => 'float-right latest-toggle fa-lg pt-1',
  'icon' => $app->ts('space_hidden_headitorium_questions') == 1 ? 'plus-square fas' : 'minus-square fas',
  'ia-bind' => 'users.tiny_settings',
  'ia-vars-space_hidden_headitorium_questions' => $app->ts('space_hidden_headitorium_questions') == 1 ? 0 : 1,
  'ia-toggleclass' => 'fa-plus-square fa-minus-square',
  'ia-target' => '.latest-toggle .fas',
]);

echo '<h5 class="subtitle mb-3"><span class="fas fa-question-circle mr-2 text-muted"></span>Nyitott kérdéses műlapok</h5>';

echo '<div class="collapse ' , $app->ts('space_hidden_headitorium_questions') == 1 ? '' : 'show' , '" id="headitorium-questions">';
echo $app->element('space/headitorium/artpieces_list', [
  'artpieces' => $open_questions
]);
echo '</div>';