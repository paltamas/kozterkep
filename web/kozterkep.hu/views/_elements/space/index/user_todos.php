<?php
if (count($invitations) > 0) {
  echo '<h5 class="subtitle">Szerkesztési meghívások</h5>';

  echo '<div class="row">';

  foreach ($invitations as $artpiece) {
    echo '<div class="col-6 mb-2 text-center small">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
        'tooltip' => true,
        'extra_class' => '',
      ],
    ]);
    echo '</div>';
  }

  echo '</div>';

  echo $app->Form->help('Meghívást kaptál az itt látható még nem megosztott, készülő műlap(ok)ra, mint szerkesztő.', ['class' => 'mt-0 mb-2', 'icon' => 'info-circle mr-1']);
  
  echo '<hr class="mt-1 mb-3" />';
}


if (count($edits_for_me) > 0) {
  echo '<h5 class="subtitle">Szerkesztések nálam</h5>';

  echo '<div class="row">';

  foreach ($edits_for_me as $edit) {
    $artpiece = $app->MC->t('artpieces', $edit->artpiece_id);
    echo '<div class="col-6 mb-2">';
    echo $app->element('artpieces/edit/edit_item', [
      'edit' => $edit,
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
      ],
    ]);
    echo '</div>';
  }

  echo '</div>';

  echo $app->Form->help('Ezek a szerkesztések várakoznak műlapjaidon. Kérjük, kezeld őket.', ['class' => 'mt-0 mb-2', 'icon' => 'info-circle mr-1']);

  echo '<hr class="mt-1 mb-3" />';
}