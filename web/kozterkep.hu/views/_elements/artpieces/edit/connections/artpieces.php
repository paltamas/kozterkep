<?php
echo $app->Form->input('new_connected_artpiece', [
  'label' => 'Új műlap kapcsolása',
  'help' => 'Kezdd el gépelni az alkotás nevét, vagy írd be az azonosítóját. Kapcsolás hozzáadásakor a kapcsolódó lapon is megjelenik ez a műlap. A törlés is hat a kapcsolt lapra - ott is kikerül ez a lap. Emiatt körültekintően használd ezt a funkciót, és ha nem vagy biztos a dolgodban, kérdezz!',
  'class' => 'not-form-change',
  'ia-auto' => 'artpieces',
  'ia-auto-query' => 'title',
  'ia-auto-key' => 'id',
  'ia-auto-target-run' => 'artpieces.connected_artpiece_add',
  'ia-auto-excluded' => $artpiece['id'],
]);

echo '<div id="connected-artpiece-list">';
if (count($connected_artpieces) > 0) {
  foreach ($connected_artpieces as $connected_artpiece) {

    echo '<div class="row bg-light py-2 mb-2 connected-artpiece-row connected-artpiece-row-' . $connected_artpiece['id'] . '">';

    echo $app->Form->input('connected_artpieces[' . $connected_artpiece['id'] . '][id]', [
      'type' => 'text',
      'class' => 'd-none',
      'value' => $connected_artpiece['id'],
      'divs' => false,
    ]);

    echo '<div class="col-6 col-sm-2 pb-2 pb-md-0">';
    echo $app->Html->link($app->Image->photo($connected_artpiece, [
      'size' => 7,
      'class' => 'img-fluid img-thumbnail'
    ]), '#', [
      'artpiece' => $connected_artpiece,
      'target' => '_blank',
    ]);
    echo '</div>';

    echo '<div class="col-md-4 col-10 pt-0 pt-lg-2">';
    echo $app->Html->link($connected_artpiece['title'], '#', [
      'artpiece' => $connected_artpiece,
      'target' => '_blank',
      'class' => 'font-weight-bold',
    ]);
    echo '</div>';

    echo '<div class="col-md-4 col-10">';
    echo $app->Form->input('connected_artpieces[' . $connected_artpiece['id'] . '][type]', [
      'type' => 'select',
      'options' => sDB['artpiece_connection_types'],
      'value' => $connected_artpiece['type'],
      'divs' => 'mb-0 pb-0 d-inline',
    ]);
    echo '</div>';

    echo '<div class="col-2 pt-1 text-right">';
    echo $app->Html->link('', '#', [
      'icon' => 'trash fa-lg',
      'class' => 'text-muted mr-2 cursor-pointer',
      'ia-confirm' => 'Biztosan törlöd ezt a kapcsolatot? A szerkesztés jóváhagyásakor a kapcsolódó műlapról is lekerül a kapcsolás.',
      'ia-bind' => 'artpieces.connected_artpiece_delete',
      'ia-pass' => $connected_artpiece['id'],
      'title' => 'Törlés',
    ]);
    echo '</div>';

    echo '</div>';
  }
}

echo '</div>'; // connected-artpiece-list