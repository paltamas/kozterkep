<?php
if ($edit_edit) {
  echo '<div class="alert alert-info p-2">Jelenleg egy szerkesztés módosítását végzed.</div>';
  echo $app->Form->input('edit_id', [
    'type' => 'hidden',
    'value' => $edit_edit['id'],
  ]);
}

if ($artpiece['status_id'] == 3) {
  echo '<div class="alert alert-warning p-2">A műlap <strong>visszaküldött státuszban</strong> van, így nem tudod újra publikálni. Ha új alkotást töltenél fel, készíts új műlapot. Ha ezt az alkotást szeretnéd újra publikálni, jelezz a főszerkesztőknek és visszanyitják.</div>';
}

if (count($edits) > 0) {
  $i = 0;
  foreach ($edits as $item) {
    if ($item['status_id'] == 2 && @$item['invisible'] != 1) {
      $i++;
    }
  }

  if ($i > 0) {
    echo '<div class="alert alert-warning p-2" data-toggle="tooltip" title="A SzerkKomm fülön láthatod a várakozó szerkesztéseket.">';
    echo $app->Html->icon('edit mr-1') . $i . ' várakozó szerkesztés';
    echo '</div>';
  }
}

echo '<div id="save-box" class="d-none border-bottom mb-4 pb-4">';

if ($artpiece['status_id'] == 1 || $_user['id'] == $artpiece['user_id']) {
  $edit_accept_info = 'A mentést követően ezek azonnal megjelennek a műlapon.';
} else {
  $edit_accept_info = 'A mentést követően a szerkesztést a műlap létrehozója, vagy a szerkesztők hagyják jóvá.';
}

echo '<div class="text-muted mb-2 small">' . $edit_accept_info . '</div>';


$save_class = $_user['id'] == $artpiece['user_id'] ? 'save-edits-final' : 'not-mine';

echo $app->Html->link('Szerkesztés mentése', '#', [
  'class' => 'btn btn-primary ' . $save_class,
  'id' => 'save-edits',
]);

echo '<div class="mt-2">' . $app->Html->link('módosítások visszavonása', 'referer', [
  'icon' => 'undo',
  'class' => 'small',
]) . '</div>';
echo '</div>';

echo '<div class="">';
echo $app->Html->dl('create', ['class' => 'row small']);
echo $app->Html->dl(['Azonosító', $artpiece['id']]);
echo $app->Html->dl(['Státusz', sDB['artpiece_statuses'][$artpiece['status_id']][0]]);
echo $app->Html->dl(['Frissítve', _time($artpiece['updated'])]);
if ($artpiece['status_id'] == 2) {
  echo $app->Html->dl(['Beküldve', _time($artpiece['submitted'])]);
}
echo $app->Html->dl(['Publikálva', _time($artpiece['published'])]);
echo $app->Html->dl(['Létrehozó', $app->Users->name($artpiece['creator_user_id'])]);
if ($artpiece['creator_user_id'] != $artpiece['user_id']) {
  echo $app->Html->dl(['Kezeli', $app->Users->name($artpiece['user_id'])]);
}

if (count(_json_decode($artpiece['invited_users'])) > 0) {
  echo $app->Html->dl(['Meghívottak', $app->Users->namelist($artpiece['invited_users'], ', ')]);
}

if ($artpiece['status_id'] < 5) {
  echo '<div class="col-12"><hr class="my-4" />';
  if ($_user['admin'] == 1) {
    echo $app->Html->link('infó', '/mulapok/szerkinfo/' . $artpiece['id'], [
      'icon' => 'info-circle',
      'class' => 'float-right',
      'ia-modal' => 'modal-md',
    ]);
  }
  echo '<h6>Beküldési feltételek</h6></div>';
  echo $app->Html->dl([$app->Html->link('Bemérés', '#szerk-terkep', [
    'class' => 'tab-button',
    'icon' => 'map-marker'
  ]), '<span class="conditions-map">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Fotók', '#szerk-fotok', [
    'class' => 'tab-button',
    'icon' => 'images'
  ]), '<span class="conditions-photos">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Címek', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-titles">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Hely', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-place">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Alkotó', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-artist">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Dátum', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-date">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Típus', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-type">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Stílus', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-style">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Forma', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-form">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Anyag', '#szerk-adatok', [
    'class' => 'tab-button',
    'icon' => 'list-alt'
  ]), '<span class="conditions-material">' . _loading() . '</span>']);
  echo $app->Html->dl([$app->Html->link('Sztori', '#szerk-sztorik', [
    'class' => 'tab-button',
    'icon' => 'paragraph'
  ]), '<span class="conditions-stories">' . _loading() . '</span>']);
  echo '<div class="col-12"><hr class="my-2" /></div>';
  echo $app->Html->dl(['<strong>Köztérre</strong>', '<span class="operations-submission">' . _loading() . '</span>']);
  echo $app->Html->dl(['<strong>Publikálható</strong>', '<span class="operations-publish">' . _loading() . '</span>']);

  if ($app->Users->owner_or_head($artpiece, $_user)) {
    echo '<div class="col-12 mt-2">' . $app->Html->link('Műveletek', '#szerk-muveletek', [
      'class' => 'font-weight-bold tab-button',
      'icon' => 'database'
    ]) . '</div>';
  }
}

echo $app->Html->dl('end');

if ($artpiece['status_id'] < 5) {
  echo '<div class="d-none mt-2 text-muted" id="submit-memo"></div>';
}

echo '</div>';



?>