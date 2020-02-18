<?php
$was_sg = false;

// Nem publikus szerkesztés
if ($edit['status_id'] <= 3) {


  $story_to_comment_button = '';
  if (is_array(@$edit['prev_data']) && count($edit['prev_data']) == 0
    && @count(@$edit['descriptions']) == 1) {
    $story_to_comment_button .= '<div class="my-2">';
    $story_to_comment_button .= $app->Html->link('Legyen hozzászólás', '/mulapok/szerkesztes_hozzaszolas/' . $artpiece['id'] . '/' . $edit['id'], [
      'class' => 'btn btn-outline-secondary mb-2',
      'ia-confirm' => 'Biztosan átváltoztatod ezt a sztorit hozzászólássá?',
      'icon' => 'comment',
    ]);
    $story_to_comment_button .= '</div>';
  }


  // Főszerk vagy admin vagy tulaj módosíthatja a szerkesztést
  if ($app->Users->owner_or_head($edit, $_user)) {
    echo $app->Html->link('Szerkesztés módosítása', '/mulapok/szerkesztes_szerkesztese/' . $artpiece['id'] . '/' . $edit['id'], [
      'class' => 'btn btn-info mb-2 mr-2',
    ]);
  }

  // Szerkesztés tulajdonosa visszavonhat és módosíthat
  if ($edit['user_id'] == $_user['id']) {

    echo $app->Html->link('Szerkesztésem visszavonása', '/mulapok/szerkesztes_torlese/' . $artpiece['id'] . '/' . $edit['id'], [
      'class' => 'btn btn-danger mb-2',
      'ia-confirm' => 'Biztosan visszavonod az elfogadás alatt álló szerkesztésedet?'
    ]);

    echo $story_to_comment_button;
    $story_to_comment_button = '';

    $was_sg = true;
  }


  // Műlap tulajdonosa és headitor elutasíthat és elfogadhat
  if ($app->Users->owner_or_head($artpiece, $_user)) {

    echo '<div>';

    echo $app->Html->link('Jóváhagyás', '/mulapok/szerkesztes_jovahagyasa/' . $artpiece['id'] . '/' . $edit['id'], [
      'class' => 'btn btn-success mr-4 mb-2',
      'ia-confirm' => 'Biztosan jóváhagyod ezt a szerkesztést?'
    ]);

    if ($edit['user_id'] != $_user['id']) {
      echo $app->Html->link('Elutasítás', '/mulapok/szerkesztes_torlese/' . $artpiece['id'] . '/' . $edit['id'], [
        'class' => 'btn btn-danger mb-2',
        'ia-confirm' => 'Biztosan elutasítod az elfogadás alatt álló szerkesztést?'
      ]);
    }
    echo '</div>';

    echo $story_to_comment_button;
    $story_to_comment_button = '';

    echo '<p class="text-muted">Ezek a funkciók csak műlap létrehozójának, valamint a főszerkesztőknek és az üzemgazdának érhető el.</p>';

    $was_sg = true;
  }

}

// Jóváhagyott szerkesztés visszanyitása és műlap visszaállítása
if ($edit['status_id'] == 5) {

  // @todo
  // ezt még nem...
  if (1 == 2 && @count(@$edit['prev_data']) > 0 && ($_user['headitor'] == 1 || $_user['admin'] == 1)) {
    echo $app->Html->link('Műlap visszaállítása & szerkesztés újranyitása', '/mulapok/szerkesztes_visszaallitasa/' . $artpiece['id'] . '/' . $edit['id'], [
      'icon' => 'undo',
      'ia-confirm' => 'Biztosan visszanyitod a szerkesztést és visszaállítod a műlap előző állapotát? Csak akkor végezd el ezt a műveletet, ha a szerkesztés eredeti állapota tényleg az utolsó helyes állapot! Ha azóta ezekre az adatokra új szerkesztés érkezett, ne állítsd vissza.'
    ]);

    echo '<p class="text-muted">Ez a funkció csak főszerkesztőknek és az üzemgazdának érhető el.</p>';

    $was_sg = true;
  }

}


// Visszavont szerkesztés visszanyitása a beküldőnek, vagy a főszerkesztőknek vagy az adminnak
if ($edit['status_id'] == 4 && ($edit['user_id'] == $_user['id']
    || $_user['headitor'] == 1 || $_user['admin'] == 1)) {

  echo $app->Html->link('Szerkesztés újranyitása', '/mulapok/szerkesztes_ujranyitasa/' . $artpiece['id'] . '/' .
  $edit['id'], [
    'icon' => 'backward',
    'ia-confirm' => 'Biztosan újranyitod a szerkesztést?'
  ]);

  $was_sg = true;
}


// Elutasított szerkesztés visszanyitása a főszerkesztőknek vagy az adminnak
if ($edit['status_id'] == 6) {

  if ($_user['headitor'] == 1 || $_user['admin'] == 1) {
    echo $app->Html->link('Szerkesztés újranyitása', '/mulapok/szerkesztes_ujranyitasa/' . $artpiece['id'] . '/' . $edit['id'], [
      'icon' => 'backward',
      'ia-confirm' => 'Biztosan újranyitod a szerkesztést?'
    ]);

    echo '<p class="text-muted">Ez a funkció csak főszerkesztőknek és üzemgazdának érhető el.</p>';

    $was_sg = true;
  }

}


// Elfogadás, ha lehet
// Szerk megszavazás kiiktatva
/*if ($artpiece['status_id'] > 1 && in_array($edit['status_id'], [2,3])
  && ($app->Users->not_managing($artpiece_user) || $edit['created'] < strtotime('-' . sDB['limits']['edits']['wait_days'] . ' days'))) {
  echo '<hr />';
  echo '<h5 class="subtitle">Szavazás az elfogadásra</h5>';

  if ($_user['score'] > 0) {
    echo $app->Html->link('Elfogadom!', '#', [
      'icon' => 'check fas',
      'class' => 'btn btn-outline-primary btn-sm mr-2 accept-button',
      'ia-bind' => 'artpieces.edit_votes',
      'ia-pass' => 'accept',
      'ia-vars-edit_id' => $edit['id'],
      'ia-confirm' => 'Ellenőrizted a szerkesztést, és szerinted is megfelelően forrásolt, és helyes adatokat tartalmaz, tehát módosítsuk a műlapot?',
    ]);

    echo '<div class="edit-votes small mb-3" data-artpiece_id="' . $edit['artpiece_id'] . '" data-edit_id="' . $edit['id'] . '"></div>';
  } else {
    echo '<div class="text-muted small">Amennyiben aktívan részt veszel a közösségi munkában, te is szavazhatsz majd a szerkesztések elfogadására.</div>';
  }

  $was_sg = true;
}*/

if ($edit['status_id'] == 5 && $edit['user_id'] != $artpiece['user_id'] && @$edit['approved'] > 0) {
  echo '<h5 class="subtitle">Jóváhagyás részletei</h5>';
  echo $app->Html->dl('create');
  echo $app->Html->dl(['Időpont', _time($edit['approved'])]);
  if (@$edit['manage_user_id'] > 0) {
    echo $app->Html->dl(['Jóváhagyó', $app->Users->name($edit['manage_user_id'])]);
  }
  echo $app->Html->dl('end');

  if (isset($edit['manage_user_id']) && $edit['manage_user_id'] == CORE['ROBOT']) {
    echo '<div class="mt-3">Szavazók:</div>';
    echo '<div class="edit-votes small mb-3" data-artpiece_id="' . $edit['artpiece_id'] . '" data-edit_id="' . $edit['id'] . '"></div>';
  }
}

if (!$was_sg) {
  //echo '<p class="text-muted">A szerkesztés állapota nem módosítható.</p>';
}


