<?php
if (!$_user || $_user['editor_on'] == 0) {
  return;
}

echo '<div class="mt-4 border radius rounded p-2">';
echo '<h6 class="font-weight-bold"><span class="fal fa-user-edit mr-1"></span>Szerkesztői sarok</h6>';

if ($artpiece['status_id'] == 2) {

  $validation = $this->Artpieces->check($artpiece, false, false);

  if (@$validation['operations']['publishable'] != 1) {
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

  if ($artpiece['publish_pause'] == 0) {
    if ($_user['score'] > 0) {
      echo $app->Html->link('Publikáljuk!', '#', [
        'icon' => 'globe fas',
        'class' => 'btn btn-outline-primary btn-sm mr-2 publish-button',
        'ia-bind' => 'artpieces.votes',
        'ia-pass' => 'publish',
        'ia-vars-artpiece_id' => $artpiece['id'],
        'ia-confirm' => 'Ellenőrizted a műlapot, és szerinted is megfelel elveinknek és publikálható?',
      ]);
    } else {
      echo '<div class="text-muted small">Jelenlegi aktivitásod még nem elegendő arra, hogy szavazz.</div>';
    }
  } else {
    echo '<div class="alert alert-warning p-2 small">A publikálás megszavazása jelenleg szünetel. Ellenőrizd a szerkesztési történetet.</div>';
  }
}

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

if ($artpiece['status_id'] == 2) {
  echo '<div class="publication-votes small"></div>';
}

if ($artpiece['status_id'] != 2 && @$_praisable != true) {
  echo $app->element('layout/partials/empty', ['class' => 'small']);
}

echo '</div>';