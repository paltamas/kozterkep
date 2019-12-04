<?php
if (@$set['cover_artpiece_id'] > 0 && in_array($set['cover_artpiece_id'], $app->Arrays->id_list($artpieces, 'id'))) {
  $artpiece = $app->MC->t('artpieces', $set['cover_artpiece_id']);
  if ($artpiece['status_id'] == 5) {
    echo '<div class="mb-4">';
    echo '<h4 class="subtitle">Kiemelt műlap</h4>';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'details' => false,
        'tooltip' => false,
      ],
    ]);
    echo '</div>';
  }
}