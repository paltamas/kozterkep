<?php
// opciók
$options = (array)@$options + [
  'joy_photos' => false,
];


echo '<div class="">';
echo $app->Html->link('Új fotók feltöltése', '#fileUploadForm', [
  'icon' => 'upload',
  'class' => 'btn btn-secondary photos-upload-toggle',
  'data-toggle' => 'collapse',
]);
echo '</div>';

echo $app->Form->create(null, [
  'class' => 'ajaxForm collapse border rounded p-4 my-4',
  'ia-form-method' => 'post',
  'ia-form-action' => 'api/artpieces/photos',
  'ia-form-redirect' => 'mulapok/szerkesztes/' . $artpiece['id'] . '?tab=3&' . uniqid(),
  'id' => 'fileUploadForm'
]);

echo $app->Form->input('artpiece_id', [
  'type' => 'hidden',
  'value' => $artpiece['id']
]);

echo $app->Html->alert('Új fotó feltöltéséhez előbb mentsd a nyitott szerkesztésedet.', 'info', [
  'id' => 'photos-save-edit-info',
  'class' => 'd-none'
]);


if ($options['joy_photos']) {
  $upload_types = [
    1 => 'Normál fotó',
    3 => 'Élménykép',
  ];
} else {
  $upload_types = [
    1 => 'Normál',
    2 => 'Archív',
    3 => 'Élménykép',
  ];
}


echo $app->Form->input('photo_upload_type', [
  'type' => 'select_button',
  'options' => $upload_types,
  'value' => 1,
  'input' => [
    'ia-conn-show' => 'photo-form',
  ]
]);


echo '<div id="photo-form-1" class="photo-form">';

/*echo $app->Form->input('photo_files', [
  'label' => 'Képek kiválasztása',
  'type' => 'file',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-filetype' => 'images',
  'ia-fileupload' => true,
  //'required' => true,
]);*/

echo '<p class="text-muted">Legalább ' . sDB['limits']['photos']['min_size'] . ' pixel hosszabbik oldali felbontással rendelkező képet tölts fel.<br />A képek az alapértelmezett felhasználási jogoddal kerülnek beállításra. Ezt, valamint a képek leírását és más részleteket a feltöltés után kezelheted. A képeket feltöltés után automatikusan ellenőrizzük: ha kell, elforgatjuk, átméretezzük és vízjellel látjuk el.</p>';

echo '</div>';

echo '<div id="photo-form-2" class="photo-form d-none">';

/*echo $app->Form->input('archive_photo_files', [
  'label' => 'Archív nem saját képek kiválasztása',
  'type' => 'file',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-filetype' => 'images',
  'ia-fileupload' => true,
  //'required' => true,
]);*/

echo '<p class="text-muted">Archív idegen képeknél 400 pixel a méret limit. Az így feltöltött képek archív beállítása nem változtatható és speciális, nem módosítható felhasználási joggal kerülnek feltöltésre.</p>';

echo '</div>';


echo '<div id="photo-form-3" class="photo-form d-none">';

/*echo $app->Form->input('joy_photo_files', [
  'label' => 'Élményképek kiválasztása',
  'type' => 'file',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-filetype' => 'images',
  'ia-fileupload' => true,
  //'required' => true,
]);*/

echo '<p class="text-muted">Az alkotással kapcsolatos, mostanában készült, élményszerű saját fotóid, ami nem kifejezetten dokumentarista, de mindenképp hangulatos és közösségi. Az "Élménykép" jelölést később is módosíthatod. Ugyanazok a szabályok érvényesek, mint a normál saját képnél. Élménykép példák: önarckép szoborral, galamb a szobor fején, kisgyerek mászott a szoborra, a biciklid és a szobor közös képe: bármi extra, de nem CSAK egy fotó a szoborról.</p>';

echo '</div>';


echo $app->Form->input('photo_files', [
  'label' => 'Képek kiválasztása',
  'type' => 'file',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-filetype' => 'images',
  'ia-fileupload' => true,
  //'required' => true,
]);

echo $app->Form->end('Feltöltés indítása', ['id' => 'Photo-submit']);