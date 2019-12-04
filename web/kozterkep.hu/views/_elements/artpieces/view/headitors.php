<?php
if (@$_user['headitor'] != 1 && @$_user['admin'] != 1) {
  return;
}

echo '<div class="mt-3 border radius rounded p-2">';
echo '<h6 class="font-weight-bold"><span class="fal fa-glasses-alt mr-1"></span>Főszerk-dolgok</h6>';



if ($artpiece['status_id'] == 2) {
  if ($artpiece['publish_pause'] == 0) {
    echo $app->Html->link('Publikálás szüneteltetése', '#', [
      'icon' => 'pause-circle fas',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'publish_pause',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-confirm' => 'Ezzel leállítod a publikálási szavazást.'
    ]);
  } else {
    echo $app->Html->link('Publikálási szavazás folytatása', '#', [
      'icon' => 'play-circle fas',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'publish_pause',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-vars-cancel' => 1,
    ]);
  }
}

if ($artpiece['open_question'] == 0) {
  echo $app->Html->link('Nyitott kérdés', '#', [
    'icon' => 'question-circle fas',
    'class' => 'd-block mb-2',
    'ia-bind' => 'artpieces.votes',
    'ia-pass' => 'question',
    'ia-vars-artpiece_id' => $artpiece['id'],
    'ia-confirm' => 'A nyitott kérdés jelölés minden látogatónak megjelenik. A jelölést bármelyik főszerkesztő ráteheti a lapra, és vissza is vonhatja.',
  ]);
} else {
  echo $app->Html->link('Nyitott kérdés visszavonása', '#', [
    'icon' => 'times fas',
    'class' => 'd-block mb-2',
    'ia-bind' => 'artpieces.votes',
    'ia-pass' => 'question',
    'ia-vars-artpiece_id' => $artpiece['id'],
    'ia-vars-cancel' => 1,
  ]);
}


if ($artpiece['superb_time'] == 0
  || ($artpiece['superb_time'] > 0 && $artpiece['superb_time'] < strtotime(sDB['limits']['headitors']['superb_revote']))) {
  if (in_array($artpiece['status_id'], [2, 5])) {

    echo $app->Html->link('Példás műlap!', '#', [
      'icon' => 'star-christmas fas',
      'class' => 'mb-2 mr-3 superb-button',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'superb',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-vars-vote' => 1,
    ]);
    echo $app->Html->link('Nem példás', '#', [
      'class' => 'mb-2 mr-2 superb-button',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'superb',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-vars-vote' => 2,
    ]);
  }

  echo '<div class="superb-votes small"></div>';
}

if ($artpiece['superb_time'] > 0) {
  echo '<div class="text-muted mb-1 small">Előző példás szavazás: ' . _time($artpiece['superb_time']) . '</div>';
}


// Admin dolgok
if ($_user['admin'] == 1) {
  echo '<hr />';
  if ($artpiece['harvested'] == 0) {
    echo $app->Html->link('Leszüretelem', '#', [
      'icon' => 'apple-crate fal',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'harvest',
      'ia-vars-artpiece_id' => $artpiece['id'],
    ]);
  } else {
    echo $app->Html->link('Vissza a fára', '#', [
      'icon' => 'undo fas',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'harvest',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-vars-cancel' => 1,
    ]);
  }
}


echo '</div>';