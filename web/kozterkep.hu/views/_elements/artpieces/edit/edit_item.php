<?php
$options = (array)@$options + [
  'div_class' => '',
  'artpiece_details' => false,
  'simple' => false,
  'modal' => true,
  'artpiece' => false,
  'photo_class' => '',
];

$edit = $app->Mongo->arraize($edit);

if (!$options['artpiece']) {
  $artpiece = $app->MC->t('artpieces', $edit['artpiece_id']);
}

if ($options['simple']) {

  echo $app->Html->link($app->Image->photo($artpiece, [
    'size' => 6,
    'class' => 'img-fluid img-thumbnail ' . $options['photo_class'],
  ]), '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'], [
    'ia-modal' => $options['modal'] ? 'modal-lg' : '',
    'ia-tooltip' => 'mulap',
    'ia-tooltip-id' => $edit['artpiece_id'],
  ]);

} else {

  echo '<div class="row py-2 mx-1 mb-3 border rounded ' . $options['div_class'] . '" name="szerkesztes-' . $edit['id'] . '">';

  echo '<div class="col-12 mb-2">';

  echo $app->Html->link('Részletek', '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'], [
    'icon_right' => 'window-restore',
    'ia-modal' => $options['modal'] ? 'modal-lg' : '',
    'class' => 'font-weight-bold float-right ml-2 mb-2 small',
    'target' => '_blank'
  ]);

  // Műlap fotó, ha kell
  if ($options['artpiece_details']) {
    echo $app->Html->link($app->Image->photo($artpiece, [
      'size' => 7,
      'class' => 'img-thumbnail',
      'width' => 47,
    ]), '#', [
      'artpiece' => $artpiece,
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $edit['artpiece_id'],
      'class' => 'float-left d-inline-block mr-1 mt-1',
    ]);

    echo '<div class="font-weight-bold pt-3 mb-4">';
    echo $app->Html->link($artpiece['title'], '/mulapok/szerkesztes/' . $edit['artpiece_id'], [
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $edit['artpiece_id'],
    ]);
    echo '</div>';
  }

  // Részletek
  echo '<div class="mb-2">';
  echo implode('<br>', $app->Artpieces->edit_details($edit, [
    'excluded' => sDB['hidden_edit_fields'],
    'change_separator' => ', ',
    'full_values' => false,
    'simple' => true
  ]));
  echo '</div>'; // small --

  echo '</div>'; // col-12 --


  // Egyéb adatok
  echo '<div class="col-6 col-sm-5 small font-weight-bold">';
  echo $app->Users->name(@$edit['user_id'], ['image' => true]);
  echo '</div>';
  echo '<div class="col-6 col-sm-7 small text-right">';
  echo sDB['edit_statuses'][$edit['status_id']][0] . ' &bull; ';
  echo '<span class="text-muted">' . _time(@$edit['approved'] > 0 ? $edit['approved'] : $edit['created'], ['ago' => true]) . '</span>';
  echo '</div>';


  if (@$edit['invisible'] == 1) {
    echo '<div class="col-12 font-weight-bold text-center text-muted small">';
    echo '<hr class="mt-2 mb-1" />';
    echo $app->Html->icon('eye-slash mr-1') . 'Láthatatlan szerkesztés';
    echo '</div>';
  }


  echo '</div>'; // row --
}