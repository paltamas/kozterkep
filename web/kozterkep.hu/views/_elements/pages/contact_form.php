<?php
echo $app->Form->create($data, [
  'method' => 'post',
]);

if (!$_user) {
  echo $app->Form->input('name', [
    'type' => 'text',
    'label' => 'Neved',
    'required' => true,
  ]);

  echo $app->Form->input('email', [
    'type' => 'email',
    'label' => 'Email címed',
    'required' => true,
  ]);
} else {
  echo '<div class="mb-3">';
  echo 'Feladó: <strong>' . $_user['name'] . '</strong>, <nolink class="">' . $_user['email'] . '</nolink>';
  echo '</div>';
}

if (@$_params->query['webcim'] != '') {
  echo $app->Form->input('url', [
    'type' => 'text',
    'label' => 'Érintett webcím',
    'value' => urldecode($_params->query['webcim']),
  ]);
}

echo $app->Form->input('subject', [
  'type' => 'text',
  'label' => 'Téma röviden',
  'required' => true,
  'maxlength' => 60,
]);

echo $app->Form->input('message', [
  'type' => 'textarea',
  'label' => 'Üzeneted',
  'required' => true,
  'help' => @$_params->query['webcim'] != '' ? '' : 'Ha egy konkrét műlappal, vagy más aloldallal kapcsolatban írsz, másold ide a webcímet, hogy könnyebben segíthessünk!',
]);

if (!$_user) {
  echo $app->Form->captcha();

  echo $app->Form->input('disclaimer', [
    'type' => 'checkbox',
    'label' => 'Hozzájárulok a megadott adataim kezeléséhez az Adatkezelési Szabályzat szerint',
    'value' => 1,
  ]);
}

echo $app->Form->end('Üzenet küldése');