<?php
if (@$_user['headitor'] != 1 && @$_user['admin'] != 1) {
  return;
}

echo '<div class="mt-3 border radius rounded p-2">';
echo '<h6 class="font-weight-bold"><span class="fal fa-glasses-alt mr-1"></span>Főszerk-dolgok</h6>';



$validation = $this->Artpieces->check($artpiece, false, false);

if (in_array($artpiece['status_id'], [2]) && @$validation['operations']['publishable'] != 1) {
  echo '<div class="alert alert-warning p-2 small mb-1">';
  echo '<strong><span class="fas fa-exclamation-circle mr-1"></span>Hiányos lap</strong> Nézd át, mielőtt szavazol. ' . $app->Html->link('Szerkesztésre megnyitom', '/mulapok/szerkesztes/' . $artpiece['id'], [
      'icon_right' => 'arrow-right',
      'class' => 'font-weight-bold'
    ]);
  echo '</div>';
}

if ($artpiece['open_question'] == 1) {
  echo '<div class="alert alert-warning p-2 small">';
  echo '<strong><span class="fas fa-question-circle mr-1"></span>Nyitott kérdés van a lapon</strong> Nézd át a hozzászólásokat, mielőtt szavazol. ' . $app->Html->link('Szerkesztésre megnyitom', '/mulapok/szerkesztes/' . $artpiece['id'], [
      'icon_right' => 'arrow-right',
      'class' => 'font-weight-bold',
    ]);
  echo '</div>';
}

echo '<div class="comment-' . $artpiece['id'] . '-count-text d-none p-1 px-2 bg-light border rounded my-1 small"><span class="fal fa-comments mr-1"></span></div>';

if ($artpiece['status_id'] == 2) {

  echo $app->Html->link('Átnéztem!', '#', [
    'icon' => 'glasses-alt fas',
    'class' => 'btn btn-outline-primary btn-sm mr-2 checked-button',
    'ia-bind' => 'artpieces.votes',
    'ia-pass' => 'checked',
    'ia-vars-artpiece_id' => $artpiece['id'],
    'ia-confirm' => 'Ellenőrizted a műlapot?',
  ]);

  echo '<div class="checked-votes small mb-4"></div>';

  if ($artpiece['publish_pause'] == 0) {
    echo $app->Html->link('Publikáljuk!', '#', [
      'icon' => 'globe fas',
      'class' => 'btn btn-outline-primary btn-sm mr-2 publish-button',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'publish',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-confirm' => 'Ellenőrizted a műlapot, és szerinted is megfelel elveinknek és publikálható?',
    ]);
  } else {
    echo '<div class="alert alert-warning p-2 small">A publikálás megszavazása jelenleg szünetel. Ellenőrizd a szerkesztési történetet.</div>';
  }

  echo '<div class="publication-votes small mb-4"></div>';
}



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


// Admin dolgok
if (in_array($_user['id'], CORE['USERS']['harvesters'])) {
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

  echo '<hr />';
  if ($artpiece['underlined'] == 0) {
    echo $app->Html->link('Aláhúzom!', '#', [
      'icon' => 'underline',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'underline',
      'ia-vars-artpiece_id' => $artpiece['id'],
    ]);
  } else {
    echo $app->Html->link('Mégsem húzom', '#', [
      'icon' => 'undo fas',
      'class' => 'd-block mb-2',
      'ia-bind' => 'artpieces.votes',
      'ia-pass' => 'underline',
      'ia-vars-artpiece_id' => $artpiece['id'],
      'ia-vars-cancel' => 1,
    ]);
  }
}


echo '</div>';