<?php
if (!$_user || $_user['editor_on'] == 0) {
  return;
}

echo '<div class="mt-4 border radius rounded p-2">';
echo '<h6 class="font-weight-bold"><span class="fal fa-user-edit mr-1"></span>Szerkesztői sarok</h6>';

if (@$_praisable == true) {
  //echo '<hr class="my-2" />';
  echo $app->Html->link('Szép munka!', '#', [
    'icon' => 'award',
    'class' => 'btn btn-outline-primary btn-sm praise-button',
    'ia-bind' => 'artpieces.votes',
    'ia-pass' => 'praise',
    'ia-vars-artpiece_id' => $artpiece['id'],
    'ia-confirm' => 'Úgy érzed, hogy ez a műlap kiemelkedik a többi közül az elvégzett kutatómunkát, illetve a mű bemutatását illetően?',
  ]);
}

if ($artpiece['status_id'] != 2 && @$_praisable != true) {
  echo $app->element('layout/partials/empty', ['class' => 'small']);
}

echo '</div>';