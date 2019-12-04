<?php
if (!$artpiece) {
  return;
}
echo '<div class="mb-3 border-bottom pb-3">';
echo $app->Html->link('', '#', [
  'icon' => 'edit',
  'class' => 'btn btn-outline-secondary btn-sm float-right',
  'ia-edit-text' => '#user-memo-text',
  'ia-edit-text-container' => '.user-memo-textcontainer',
  'ia-edit-text-url' => 'api/artpieces/user_memo',
  'ia-edit-text-id' => $artpiece['id'],
]);
echo '<h5><span class="fal fa-user mr-1"></span>Kutatási naplóm</h5>';

echo '<div class="user-memo-textcontainer">';
if ($artpiece['user_memo'] != '') {
  echo '<div id="user-memo-text" class="p-2 border rounded font-italic">' . $app->Text->format($artpiece['user_memo'], ['format' => false]) . '</div>';
} else {
  echo '<div id="user-memo-text"></div>';
}
echo '</div>';

echo $app->Form->help('Ezt csak te látod és csak te szerkesztheted. Nem mutatjuk sehol.', [
  'icon' => 'info-circle',
  'class' => 'mt-2'
]);
echo '</div>';