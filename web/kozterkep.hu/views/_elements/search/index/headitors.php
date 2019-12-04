<?php
if (@$_user['headitor'] == 1 || @$_user['admin'] == 1) {

  if (@$query['statusz'] > 0 || @$query['statusz'] == 'nem-publikusak' || @$query['nyitott_kerdes'] == 1
    || @$query['peldas_szavazas_kezdete'] != '' || @$query['peldas_szavazas_vege'] != '') {
    $collapse = '';
  } else {
    $collapse = 'collapse';
  }


  echo '<div class="border rounded bg-light p-3 ' . $collapse . ' mb-3" id="foszerkesztok">';

  echo '<div class="row">';

  echo '<div class="col-12 mb-3">';
  echo '<h5 class="subtitle"><span class="fal fa-glasses-alt mr-1"></span>Főszerk-szűrések</h5>';
  echo '</div>';

  echo '<div class="col-sm-6 col-md-2">';
  echo $app->Form->input('statusz', [
    'label' => 'Műlap státusz',
    'type' => 'select',
    'options' => [
      '' => '...',
      'nem-publikusak' => 'Nem publikusak',
    ] + $app->Arrays->id_list(sDB['artpiece_statuses'], 0),
  ]);
  echo '</div>';

  echo '<div class="col-sm-6 col-md-2">';
  echo $app->Form->input('peldas_szavazas_kezdete', [
    'label' => 'Példás szavazás ettől',
    'class' => '',
    'type' => 'date',
  ]);
  echo '</div>';

  echo '<div class="col-sm-6 col-md-2">';
  echo $app->Form->input('peldas_szavazas_vege', [
    'label' => 'Példás szavazás eddig',
    'class' => '',
    'type' => 'date',
  ]);
  echo '</div>';

  echo '<div class="col-sm-6 col-md-2 pt-md-4">';
  echo $app->Form->input('nyitott_kerdes', [
    'label' => 'Nyitott kérdés',
    'type' => 'checkbox',
    'value' => 1,
  ]);
  echo '</div>';


  echo '</div>'; // row --
  echo '</div>'; // border --
}
