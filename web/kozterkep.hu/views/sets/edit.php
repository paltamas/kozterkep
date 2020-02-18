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

if ($_user['id'] == CORE['USERS']['sets']
  || $_user['admin'] == 1 || $_user['headitor'] == 1) {
  echo '<div class="border rounded p-2 mb-4 bg-light">';
  echo '<h5><span class="fa fa-glasses-alt mr-2"></span>Gyűjtemény felelősi és Főszerk funkciók</h5>';
  echo $app->Form->input('set_type_id', [
    'type' => 'select',
    'options' => sDB['set_types_public'],
    'label' => 'Gyűjtemény típusa',
    'help' => 'Ha egy tagi gyűjteményt közös gyűjteménnyé változtatsz, az átkerül a gyűjteményfelelős kezelésébe. Visszaváltáskor nem kerül vissza a tagra, mert akkor már nem tudjuk, ki a tag. Vagyis kérlek, akkor váltsd, ha leegyeztetésre került a taggal, hogy ezt a gyűjteményt elveszed tőle és közös gyűjteményként te kezeled tovább.',
  ]);
  echo '</div>';
}


echo $app->Form->end('Mentés', ['name' => 'save_settings']);
?>

