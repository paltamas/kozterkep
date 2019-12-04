<?php
echo '<div class="row">';

echo '<div class="col-md-4">';
echo $app->Form->input('temporary', [
  'type' => 'checkbox',
  'label' => 'Tervezetten átmeneti felállítás',
  'help' => 'Jelöld, ha ezen a helyen tervezetten csak egy időszakra állították fel a művet. Ha tudod, add meg az elbontás idejét.',
  'value' => 1,
]);
echo '</div>'; // col --

echo '<div class="col-md-4">';
echo $app->Form->input('copy', [
  'type' => 'checkbox',
  'label' => 'Másolat',
  'help' => 'Akkor jelöld, ha az alkotásból más példány korábban már köztérre került (akár ugyanezen a helyen). Ha fent van a másik példány is, kapcsold!',
  'value' => 1,
  'ia-conn-unset' => '#Reconstruction',
]);
echo '</div>'; // col --

echo '<div class="col-md-4">';
echo $app->Form->input('reconstruction', [
  'type' => 'checkbox',
  'label' => 'Rekonstrukció',
  'help' => 'Akkor számít rekonstrukciónak egy példány, ha nincs meg az eredeti, és nem tökéletes másolat születik, de mégis az eredeti koncepciót követik az újraalkotás során. Ha fent van az eredeti mű is, kapcsold!',
  'value' => 1,
  'ia-conn-unset' => '#Copy',
]);
echo '</div>'; // col --

echo '</div>'; // row --




echo '<div class="row">';

echo '<div class="col-md-4">';
echo $app->Form->input('artpiece_condition_id', [
  'type' => 'select',
  'options' => $app->Arrays->id_list(sDB['artpiece_conditions']),
  'label' => 'Állapot',
  'help' => 'Fontos információ arról, mi az alkotás jelenlegi állapota. A "Veszélyben" minden műre igaz, ami (tervezett) építkezés területén áll. "Tudsz róla?" minden alkotás, ha nincs a helyén, de ennek okát nem tudjuk.',
]);
echo '</div>'; // col --

echo '<div class="col-md-4">';
echo $app->Form->input('not_artistic', [
  'type' => 'checkbox',
  'label' => 'Művészi elem nélküli emlékőrző',
  'help' => 'Weblapunk művészi alkotások bemutatását tűzte ki célul, de mellette a jelentős emlékőrző alkotásokat is megjelenítjük. Ha egy mű nem tartalmaz egyedi művészeti elemet, akkor jelöld ezt.',
  'value' => 1,
]);
echo '</div>'; // col --


if ($artpiece['country_id'] != 101) {
  echo '<div class="col-md-4">';
  echo $app->Form->input('hun_related', [
    'type' => 'checkbox',
    'label' => 'Magyar vonatkozású',
    'help' => 'Magyar vonatkozást külföldi alkotásoknál jelölhetjük. Magyar vonatkozású minden, felállításakor Magyarország akkori határain belül álló, vagy magyar személynek/történelmi eseménynek emléket állító, vagy magyar származású alkotó által készített mű.',
    'value' => 1,
  ]);
  echo '</div>'; // col --
} else {
  echo $app->Form->input('hun_related', [
    'type' => 'hidden',
    'value' => 1,
  ]);
}

if ($artpiece['country_id'] == 101) {
  echo '<div class="col-md-4">';
  echo $app->Form->input('national_heritage', [
    'type' => 'checkbox',
    'label' => 'Nyilvántartott műemlék',
    'help' => 'A hazai alkotásoknál jelezd, ha információd van arról, hogy az alkotás nyilvántartott műemlék. Ha pipálsz, kérjük jelezd a forrásoknál, mire alapozod a jelölést. A muemlekem.hu oldal segíthet ebben.',
    'value' => 1,
  ]);
  echo '</div>'; // col --
}

echo '</div>'; // row --




echo '<div class="row">';



echo '</div>'; // row --