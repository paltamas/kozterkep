<?php
if (@$_params->query['tag'] > 0) {
  $user_id = $_params->query['tag'];
}

echo $app->Form->create(null, [
  'class' => 'ajaxForm' , @$_params->query['user_id'] > 0 ? '' : ' autoSave',
  'ia-form-action' => 'api/conversations',
  'ia-form-redirect' => 'beszelgetesek/folyam/{response}',
]);

// ha van ID, rejtjük, hogy ne is lássuk az ID-vel kitöltött mezőt
$extra_class = @$_params->query['user_id'] > 0 ? ' d-none' : '';
echo $app->Form->input('user_id', [
  'placeholder' => 'Címzett neve',
  'autocomplete' => 'off',
  'class' => 'focus userSelect' . $extra_class,
  //'help' => 'Kezdd el gépelni a tag nevét és válassz a felajánlott lehetőségek közül.',
  'ia-autouser' => 'users',
  'ia-autouser-min' => 3,
  'ia-autouser-query' => 'name',
  'ia-autouser-key' => 'id',
  'ia-autouser-image' => 'profile_photo_filename',
  'ia-autouser-next-focus' => '#Subject',
  'value' => @$user_id,
  'required' => true
]);


echo $app->element('conversations/connected_things', ['options' => [
  'same_info' => true,
  'hidden_inputs' => true,
]]);

echo $app->Form->input('subject', [
  'placeholder' => 'Beszélgetés témája',
  'autocomplete' => 'off',
  'class' => '',
  'value' => $subject ? $subject : '',
]);

echo $app->Form->input('message', [
  'placeholder' => 'Üzeneted...',
  'type' => 'textarea',
  'rows' => 4,
  'class' => 'controlEnter',
  'help' => texts('gyorskuldes')
]);

echo $app->Form->input('file', [
  'label' => 'Fájl csatolása',
  'type' => 'file',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-fileupload' => true
]);

echo $app->Form->submit('Üzenet küldése', [
  'class' => 'mr-2'
]);

echo $app->Form->submit('Adatok törlése', [
  'class' => 'btn-outline-secondary float-right delForm'
]);

/*echo $app->Form->submit('Mentés piszkozatként', [
  'class' => 'btn-outline-secondary float-right'
]);*/

echo $app->Form->end();