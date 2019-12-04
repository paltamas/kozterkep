<?php
echo '<h5 class="subtitle">Műlapok kapcsolása</h5>';
echo '<p class="text-muted">Több műlapot kapcsolhatsz a bejegyzésedhez. Ezek a bejegyzés alatt láthatóak majd. A műlapokon a bejegyzés a jobb hasábban látszik.</p>';

echo $app->Form->input('new_connected_artpiece', [
  'label' => 'Új műlap kapcsolása',
  'placeholder' => 'Műlap cím, AZ',
  'class' => 'noEnterInput',
  'ia-auto' => 'artpieces',
  'ia-auto-query' => 'title',
  'ia-auto-key' => 'id',
  'ia-auto-target-run' => 'autocomplete.connected_artpiece_add',
]);

echo '<div id="connected-artpiece-list">';



if (count($connected_artpieces) > 0) {
  foreach ($connected_artpieces as $connected_artpiece) {

    echo '<div class="row bg-light py-2 mb-2 connected-artpiece-row connected-artpiece-row-' . $connected_artpiece['id'] . '" data-id="' . $connected_artpiece['id'] . '">';


      echo '<div class="col-6 col-md-2 pb-2 pb-md-0">';
    echo $app->Html->link($app->Image->photo($connected_artpiece, [
      'size' => 7,
      'class' => 'img-fluid img-thumbnail'
    ]), '#', [
      'artpiece' => $connected_artpiece,
      'target' => '_blank',
    ]);
    echo '</div>';

    echo '<div class="col-md-8 col-10 pt-0 pt-lg-2">';
    echo $app->Html->link($connected_artpiece['title'], '#', [
      'artpiece' => $connected_artpiece,
      'target' => '_blank',
      'class' => 'font-weight-bold',
    ]);
    echo '</div>';

    echo '<div class="col-2 pt-2 text-right">';
    echo $app->Html->link('', '#', [
      'icon' => 'trash fa-lg',
      'class' => 'text-muted mr-2 cursor-pointer',
      'ia-confirm' => 'Biztosan törlöd ezt a kapcsolatot? Mentened kell a bejegyzést, hogy a törlés véglegessé váljon.',
      'ia-bind' => 'autocomplete.connected_artpiece_delete',
      'ia-pass' => $connected_artpiece['id'],
      'title' => 'Törlés',
    ]);
    echo '</div>';

    echo '</div>';
  }
}
echo '</div>'; // connected-artpiece-list

echo $app->Form->input('connected_artpieces', [
  'type' => 'text',
  'class' => 'd-none',
  'value' => urlencode($post['connected_artpieces']),
  'divs' => false,
]);
