<?php
echo '<div class="row" id="elhelyezkedes">';

echo '<div class="col-md-6">';

echo $app->Form->input('place_id', [
  'type' => 'text',
  'class' => 'd-none',
  'divs' => false,
]);

// place_ kell, hogy a Chrome ne egészítse ki, mert szarik az autocomplete=off-ra
echo $app->Form->input('place_', [
  'label' => 'Település',
  'value' => $artpiece['places']['name'],
  'ia-auto' => 'places',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#Place-id',
  'data-selected' => $artpiece['places']['name'],
  'required' => true,
  'help' => 'Módosítsd a települést, ha a bemérésből kapott helység nem elég pontos. Válassz a felajánlottak közül. Akkor hozz létre új települést, ha a keresett nincs az adatbázisban semmilyen alakban.',
]);

if ($artpiece['place_id'] == 110) {
  echo $app->Form->input('district_id', [
    'options' => $app->Arrays->id_list(sDB['districts'], 0),
    'label' => 'Budapesti kerület',
    'help' => 'Ezt is beméréskor olvastuk ki. Ha nem helyes, korrigáld!',
  ]);
}

echo $app->Form->input('address', [
  'label' => 'Utca, házszám',
  'help' => 'Beméréskor kiolvassuk a címet, de érdemes ellenőrizni, és ha tudod, pontosítani. Későbbi térképes módosításkor már nem frissítjük, így manuálisan kell. Magyar házszám után pontot teszünk. Külföldi cím jöjjön az ottani helyesírással.',
]);

echo '</div>'; // col-6

echo '<div class="col-md-6">';

echo $app->Form->input('artpiece_location_id', [
  'type' => 'select_button',
  'options' => [
    1 => 'Köztéren',
    2 => 'Közösségi térben',
  ],
  'label' => 'Hol található az alkotás?',
]);

echo $app->Form->input('not_public_type_id', [
  'type' => 'select',
  'options' => [0 => 'Válassz...'] + $app->Arrays->id_list(sDB['not_public_types']),
  'label' => 'Közösségi tér típusa',
  'divs' => [
    'class' => 'd-none mb-3',
    'ia-toggleelement-parent' => '#Artpiece-location-id',
    'ia-toggleelement-value' => 2,
  ]
]);

echo $app->Form->input('place_description', [
  'type' => 'textarea_short',
  'label' => 'Hogy találjuk meg itt?',
  'help' => 'Főként nem köztéri alkotások, vagy épületdíszek esetén hasznos. Azt ne írd le, ami a térképes bemérés vagy az utca/hsz. alapján egyértelmű.',
  'maxlength' => mb_strlen($artpiece['place_description']) > 150 ? mb_strlen($artpiece['place_description']) : 150, // régieket engedjük
]);

echo '</div>'; // col-6

echo '</div>'; // row