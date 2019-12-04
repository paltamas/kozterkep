<?php
$options = (array)@$options + [
  'class' => 'bg-light rounded px-3 py-4 mt-3',
  'artpiece_details' => false,
  'intro' => false,
  'min_time' => false,
];

echo '<div class="' . $options['class'] . ' description-row description-row-' . $description['id'] . '">';

if ($options['artpiece_details']) {
  $artpiece = $app->MC->t('artpieces', $description['artpieces'][0]);
  echo $app->Html->link($app->Image->photo($artpiece, [
    'size' => 7,
    'class' => 'img-thumbnail',
  ]), '', [
    'ia-tooltip' => 'mulap',
    'ia-tooltip-id' => $artpiece['id'],
    'artpiece' => $artpiece,
    'class' => 'float-left d-inline-block mr-1 mt-1',
  ]);
  echo '<div class="font-weight-bold">';
  echo $app->Html->link($artpiece['title'], '', [
    'artpiece' => $artpiece,
    'ia-tooltip' => 'mulap',
    'ia-tooltip-id' => $artpiece['id'],
  ]);
  echo '</div>';
}

if (@$description['lang'] == 'ENG') {
  echo '<h6 class="font-weight-bold"><span class="far fa-globe mr-1"></span>Angol leírás, sztori</h6>';
}

// Név
echo '<div class="mb-1">';
echo '<strong>' . $app->Users->name($description['user_id']) . '</strong>';
echo '<div class="small float-md-right">';

// Időbélyeg(ek); ha min. dátumot kaptunk,
// akkor csak akkor írjuk ezt, ha ez nagyobb a min. dátumnál
if ($options['min_time'] && $options['min_time']> 0) {
  $description['created'] = $description['created'] < $options['min_time']
    ? $options['min_time'] : $description['created'];
  $description['modified'] = $description['modified'] < $options['min_time']
    ? $options['min_time'] : $description['modified'];
}

echo ' <span class="text-muted">';
echo _date($description['created'], 'Y.m.d. H:i');
echo '</span>';
if ($description['modified'] > $description['created']) {
  echo ' <span class="text-muted" data-toggle="tooltip" title="Leírás utolsó módosítása: ' . _date($description['modified'], 'Y.m.d. H:i') . '"><span class="fal fa-pencil ml-2 mr-1"></span>';
  echo '</span>';
}


echo '</div>';
echo '</div>';

echo $app->Artpieces->story($description, [
  'intro' => $options['intro']
]);

echo '</div>';