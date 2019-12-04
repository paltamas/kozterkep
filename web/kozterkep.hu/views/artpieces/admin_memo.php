<?php
if (!$artpiece) {
  return;
}

echo '<div class="kt-info-box mb-3">';

if ($app->Users->is_head($_user)) {
  echo $app->Html->link('Szintézis', '#', [
    'icon' => 'edit',
    'class' => 'btn btn-outline-secondary btn-sm float-right',
    'ia-edit-text' => '#admin-memo-text',
    'ia-edit-text-container' => '.admin-memo-textcontainer',
    'ia-edit-text-url' => 'api/artpieces/admin_memo',
    'ia-edit-text-id' => $artpiece['id'],
  ]);
}

echo '<h5 class="font-weight-bold"><span class="fas fa-question-circle mr-1"></span>A műlapon nyitott kérdés van</h5>';

echo '<div class="admin-memo-textcontainer mt-2">';
if ($artpiece['admin_memo'] != '') {
  echo '<strong>Főszerk szintézis:</strong> ';
}
echo '<span id="admin-memo-text" class="font-italic">' . $app->Text->format($artpiece['admin_memo'], ['format' => false]) . '</span>';
if ($artpiece['admin_memo_updated'] > 0) {
  echo '<div class="text-muted small mt-1"><span class="fal fa-edit mr-1"></span><span id="admin-memo-text-timestamp">' . _time($artpiece['admin_memo_updated']) . '</span></div>';
}
echo '</div>';

echo '</div>';