<?php
// Publikus és ellenőrzésre küldött lapoknál lépkedhetünk
if (in_array($artpiece['status_id'], [2,5])) {
  echo '<div class="text-center badge-gray-kt px-1 pt-2 mb-2 rounded">';
  if (!isset($_params->query['elso'])) {
    /*$prev .= $app->Html->link('', '/mulapok/leptetes?elso', [
      'icon' => 'arrow-to-left',
      'class' => 'mr-1',
    ]);*/
    echo $app->Html->link('Előző', '/mulapok/leptetes/' . $artpiece['id'] . '?elozo', [
      'icon' => 'arrow-left',
      'class' => 'btn btn-secondary btn-sm mr-2 mb-2'
    ]);
  }

  // Meglepetés csak publikus lapoknál van
  if ($artpiece['status_id'] == 5) {
    echo $app->Html->link('', '/mulapok/leptetes?veletlen', [
      'icon' => 'redo fas',
      'title' => 'Véletlen műlapot kérek!',
      'class' => 'btn btn-secondary btn-sm mr-2 mb-2'
    ]);
  }

  if (!isset($_params->query['utolso'])) {

    echo $app->Html->link('Köv.', '/mulapok/leptetes/' . $artpiece['id'] . '?kovetkezo', [
      'icon_right' => 'arrow-right',
      'class' => 'btn btn-secondary btn-sm mr-2 mb-2'
    ]);

    // Utolsóra csak publikusoknál ugrunk
    if ($artpiece['status_id'] == 5) {
      echo $app->Html->link('', '/mulapok/leptetes?utolso', [
        'icon' => 'arrow-to-right',
        'title' => 'Ugrás a legfrissebb műlaphoz',
        'class' => 'btn btn-secondary btn-sm mb-2'
      ]);
    }

  }
  echo '</div>';
}