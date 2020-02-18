<?php
echo '<div class="alert alert-warning d-none" id="operations-edit-warning">Az egyes műveletek futtatása előtt mentsd el a nyitott szerkesztésedet.</div>';


echo '<div class="row">';

if ($artpiece['status_id'] != 5) {

  // Publikálás
  echo '<div class="col-12 col-sm-6 mb-5">';
  echo '<h4 class="subtitle">Publikálás</h4>';
  $extra_class = in_array($artpiece['status_id'], [3,5]) || $_user['user_level'] == 0 || @$validations['operations']['publish'] != 1 ? 'disabled' : '';
  echo $app->Html->link('Műlap publikálása', '/mulapok/publikalas/' . $artpiece['id'], [
    'icon' => 'globe',
    'class' => 'my-2 btn btn-success ' . $extra_class,
    'ia-confirm' => 'Biztosan publikálod ezt a műlapot?'
  ]);
  echo $app->Form->help('Minden hazai, vagy magyar vonatkozású alkotás szabadon publikálható a törzstagoknak a minimumelvárások teljesítését követően.');
  echo '</div>';

  // Ellenőrzésre küldés
  echo '<div class="col-12 col-sm-6 mb-5">';
  echo '<h4 class="subtitle">Ellenőrzésre küldés</h4>';
  $extra_class = !in_array($artpiece['status_id'], [1,4]) || @$validations['operations']['submission'] != 1 ? 'disabled' : '';
  echo $app->Html->link('Műlap beküldése', '/mulapok/kozterre_kuldes/' . $artpiece['id'], [
    'icon' => 'glasses-alt',
    'class' => 'my-2 btn btn-success mr-3 ' . $extra_class,
    'ia-confirm' => 'Kutattál, amennyit tudtál és minden adatot megadtál? Amennyiben félkész, vagy erősen hibás lapot küldesz ellenőrzésre a főszerkesztőknek, lehetséges, hogy visszaküldjük szerkesztésre.'
  ]);

  if ($artpiece['status_id'] == 2) {
    echo $app->Html->link('Viszahívás', '/mulapok/visszahivas/' . $artpiece['id'], [
      'icon' => 'arrow-left',
      'class' => 'my-2 btn btn-secondary',
      'ia-confirm' => 'Biztosan visszahívod az ellenőrzésről ezt a műlapot?'
    ]);
  }
  echo $app->Form->help('Minden külföldi nem magyar vonatkozású és a nem törzstagok minden műlapja az ellenőrzésre küldés után, főszerkesztők közreműködésével kerül publikálásra.');
  if (@$validations['operations']['publish'] == 1) {
    echo $app->Form->help('<strong>Ezt a műlapot saját magad publikálhatod</strong>, így nem szükséges beküldened ellenőrzésre.');
  }
  echo '</div>';
}


// Meghívottak kezelése
if ($app->Users->owner_or_head($artpiece, $_user)) {
  echo '<div class="col-12 col-sm-6 mb-5">';
  echo '<h4 class="subtitle">Közreműködők meghívása</h4>';
  $extra_class = in_array($artpiece['status_id'], [2, 5]) ? 'disabled' : '';
  echo $app->Html->link('Meghívottak kezelése', '/mulapok/szerkesztes_meghivas/' . $artpiece['id'], [
    'icon' => 'user-edit',
    'class' => 'my-2 btn btn-secondary ' . $extra_class,
  ]);
  $inviteds = count(_json_decode($artpiece['invited_users']));
  echo $inviteds > 0 ? '<span class="ml-2 text-muted">' . $inviteds . ' meghívott</spanp>' : '';

  /*echo $app->Html->link('Nem tagot képek miatt', '/mulapok/kepfeltoltes_meghivas/' . $artpiece['id'], [
    'icon' => 'images',
    'class' => 'my-2 btn btn-secondary',
  ]);*/
  echo $app->Form->help('Tagok meghívása szerkesztésre és képfeltöltésre beküldés előtt.');
//echo $app->Form->help('Tagok meghívása szerkesztésre Köztérre küldés előtt, és nem regisztráltak meghívása emailben képfeltöltésre, a minimum elvárások teljesítésének érdekében.');
  echo '</div>';
}


// Főszerk funkciók
if ($app->Users->is_head($_user)) {

  // Képek másolása és áthelyezése
  echo '<div class="col-12 col-sm-6 mb-5">';
  echo '<h4 class="subtitle">' . texts('foszerk_ikon') . 'Fotók áttöltése más lapra</h4>';
  echo $app->Html->link('Másol', '#', [
    'icon' => 'copy fa-lg',
    'class' => 'my-2 btn btn-secondary mr-3',
    'ia-bind' => 'artpieces.photo_copy_question',
    'ia-pass' => 'all',
    'ia-vars-delete' => 0,
    'ia-vars-artpiece_id' => $artpiece['id'],
    'title' => 'Fotók másolása',
  ]);
  echo $app->Html->link('Áthelyez', '#', [
    'icon' => 'arrow-alt-from-left fa-lg',
    'class' => 'my-2 btn btn-secondary',
    'ia-bind' => 'artpieces.photo_copy_question',
    'ia-pass' => 'all',
    'ia-vars-delete' => 1,
    'ia-vars-artpiece_id' => $artpiece['id'],
    'title' => 'Fotók áthelyezése',
  ]);
  echo $app->Form->help('A "Fotók" aloldalon egyesével pakolhatsz, itt az összes fotót egyszerre.');
  echo '</div>';


  // Visszaküldés
  if ($app->Users->is_vetohead($_user)) {

    if ($artpiece['status_id'] == 5) {
      echo '<div class="col-12 col-sm-6 mb-5">';
      echo '<h4 class="subtitle">' . texts('foszerk_ikon') . 'Műlap frissítése</h4>';
      echo $app->Html->link('Frissítés', '/mulapok/frissites/' . $artpiece['id'], [
        'class' => 'my-2 btn btn-info ',
      ]);
      echo $app->Form->help('A publikus műlapokat minden módosítás után cache-eljük, hogy ne kelljen minden megtekintéskor összeszedni azt a sok adatot, amiből áll. De vannak esetek, amikor pont rosszul frissül és hibás adatok ragadhatnak be. Ekkor nyomj ide.');
      echo '</div>';
    }


    echo '<div class="col-12 col-sm-6 mb-5">';
    echo '<h4 class="subtitle">' . texts('foszerk_ikon') . 'Műlap visszaküldése</h4>';
    $extra_class = !in_array($artpiece['status_id'], [2, 5]) ? 'disabled' : '';
    echo $app->Html->link('Műlap visszaküldése', '/mulapok/visszakuldes/' . $artpiece['id'], [
      'class' => 'my-2 btn btn-danger ' . $extra_class,
      'ia-confirm' => 'Biztosan visszaküldöd ezt a műlapot? Ha nem elvekbe ütköző, és később újra várjuk, akkor visszaküldés után nyisd vissza, különben nem lesz újra beküldhető.'
    ]);
    echo $app->Form->help('Az ellenőrzésre küldött és a publikus műlapokat visszaküldhetik a főszerkesztők.');
    echo '</div>';
  }

  // Visszaküldött visszanyitása
  if ($app->Users->is_vetohead($_user) && $artpiece['status_id'] == 3) {
    echo '<div class="col-12 col-sm-6 mb-5">';
    echo '<h4 class="subtitle">' . texts('foszerk_ikon') . 'Műlap visszanyitása</h4>';
    echo $app->Html->link('Műlap visszanyitása', '/mulapok/visszanyitas/' . $artpiece['id'], [
      'class' => 'my-2 btn btn-secondary ',
      'ia-confirm' => 'Biztosan visszanyitod a lapot, hogy újra beküldhető legyen?'
    ]);
    echo $app->Form->help('A visszaküldött lapokat visszatehetik szerkesztésbe a főszerkesztők.');
    echo '</div>';
  }
}


// TÖRLÉS
if ($artpiece['status_id'] != 5) {
  echo '<div class="col-12 col-sm-6 mb-5">';
  echo '<h4 class="subtitle">Műlap törlése</h4>';
  $extra_class = in_array($artpiece['status_id'], [2, 5]) ? 'disabled' : '';
  echo $app->Html->link('Műlap törlése', '/mulapok/torles/' . $artpiece['id'], [
    'icon' => 'trash',
    'class' => 'my-2 btn btn-danger ' . $extra_class,
    'ia-confirm' => 'Biztosan törlöd ezt a műlapot? A művelet nem visszavonható, tehát <strong>tényleg minden adatot és képet törlünk</strong>.'
  ]);
  echo $app->Form->help('Minden műlap szabadon törölhető, amíg nem kerül megosztásra vagy publikálásra. A törléssel minden hozzászólás, kép, esemény és adat is végérvényesen törlődik.');
  echo '</div>';
}

/*echo '<div class="col-12 col-sm-6 mb-5">';
echo '<h4 class="subtitle">Műlap duplikálása</h4>';
echo $app->Html->link('Duplikálás', '#', [
  'class' => 'my-2 btn btn-secondary disabled',
  'ia-confirm' => 'Biztosan duplikálod ezt a műlapot?'
]);
echo $app->Form->help('Képek, leírások és adatok duplikálódnak. A kommentek nem, a szerkesztési történetből pedig csak az aktuális állapot.');
echo '</div>';*/


echo '</div>'; // row


echo '<div class="text-muted mt-4"><span class="fal fa-info-circle mr-2"></span>Ez az aloldal csak a műlap létrehozójának, a főszerkesztőknek és az üzemgazdának érhető el.</div>';