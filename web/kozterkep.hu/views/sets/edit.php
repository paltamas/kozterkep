<?php
echo $app->Form->create($set,
  [
    'method' => 'post',
    'id' => 'edit-set',
    'class' => ''
  ]
);

echo $app->Form->input('name', [
  'label' => 'Gyűjtemény megnevezése',
  'required' => true,
]);

echo $app->Form->input('place_id', [
  'type' => 'hidden',
  'id' => 'place_id',
]);
echo $app->Form->input('place_name', [
  'class' => 'noEnterInput',
  'label' => 'Kapcsolódó település',
  'value' => @$set['place_id'] > 0 ? $app->MC->t('places', $set['place_id'])['name'] : '',
  'id' => 'place_name',
  'ia-auto' => 'places',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#place_id',
  'autocomplete' => 'off',
  'help' => 'Akkor add meg, ha a gyűjtemény kifejezetten egy településen belül gyűjt alkotásokat.'
]);

echo $app->Form->input('cover_artpiece_id', [
  'label' => 'Kiemelt műlap AZ',
  'help' => 'Add meg annak a műlapnak az azonosítóját, amit ki szeretnél emelni.',
]);

echo $app->Form->input('description', [
  'type' => 'textarea',
  'label' => 'Gyűjtemény leírása',
]);


echo $app->Form->end('Mentés', ['name' => 'save_settings']);
?>

