<?php
if ($artpiece['artpiece_location_id'] == 2) {
  echo '<div class="mb-2">';
  if ($artpiece['not_public_type_id'] > 0) {
    // Ki van már töltve
    $type = sDB['not_public_types'][$artpiece['not_public_type_id']];
    echo '<span class="text-muted mr-2">Nem köztéri:</span>';
    echo '<span class="fal fa-' . $type[1] . ' mr-1"></span>';
    echo '<span>' . $type[0] . '</span>';
  } else {
    // Nincs kitöltve
    echo '<span class="fal fa-building mr-1"></span>';
    echo '<span>Nem köztéri</span>';
  }
  echo '</div>';
}

if (count($parameters) == 0) {
  return;
}

$parameters = $app->Artpieces->sort_displayed_parameters($parameters, $artpiece_parameters);
foreach ($parameters as $parameter) {
  if (@$parameter['name'] != '') {
    $parameter_group = sDB['parameter_groups'][$parameter['parameter_group_id']];
    echo '<div class="d-inline-block mr-2 my-1 text-nowrap" data-toggle="tooltip" title="' . $parameter_group[0] . '">';
    echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
    echo '<span class="fal fa-' . $parameter_group[1] . ' mr-1"></span>';
    echo $app->Html->link($parameter['name'], '/kereses?r=1&p_' . $parameter['id'] . '=1#hopp=lista', ['class' => '']);
    echo '</span>';
    echo '</div>';
  }
}

if ($artpiece['temporary'] == 1 || $artpiece['copy'] == 1
  || $artpiece['reconstruction'] == 1 || $artpiece['not_artistic'] == 1
  || ($artpiece['hun_related'] == 1 && $artpiece['country_id'] != 101)) {
  echo '<div class="mt-2"></div>';
}

if ($artpiece['temporary'] == 1) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-hourglass-half mr-1"></span>';
  echo $app->Html->link('Tervezetten átmeneti felállítás', '/kereses?r=1&atmeneti_felallitas=1#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}

if ($artpiece['copy'] == 1) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-clone mr-1"></span>';
  echo $app->Html->link('Köztéri mű másolata', '/kereses?r=1&masolat=1#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}

if ($artpiece['reconstruction'] == 1) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-recycle mr-1"></span>';
  echo $app->Html->link('Köztéri mű rekonstrukciója', '/kereses?r=1&rekonstrukcio=1#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}

if ($artpiece['national_heritage'] == 1) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-landmark mr-1"></span>';
  echo $app->Html->link('Nyilvántartott műemlék', '/kereses?r=1&muemlek=1#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}

if ($artpiece['not_artistic'] == 1) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap" title="Művészi elem nélküli emlékőrző" data-toggle="tooltip">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-monument mr-1"></span>';
  echo $app->Html->link('Emlékőrző', '/kereses?r=1&nem_muveszi_emlekorzo=1#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}

if ($artpiece['hun_related'] == 1 && $artpiece['country_id'] != 101) {
  echo '<div class="d-inline-block mr-2 my-1 text-nowrap">';
  echo '<span class="badge badge-gray-kt badge-lg font-weight-normal">';
  echo '<span class="fal fa-flag-alt mr-1"></span>';
  echo $app->Html->link('Magyar vonatkozású külföldi', '/kereses?r=1&magyar_vonatkozas=kulfoldi_magyar#hopp=lista', ['class' => '']);
  echo '</span>';
  echo '</div>';
}