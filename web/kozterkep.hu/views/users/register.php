<?php
echo '<div class="row">';

echo '<div class="col-md-6 mb-4">';

echo $app->Form->create($_params->data, [
  'method' => 'post'
]);

if (@$_params->query['idejojjunkmajd'] != '') {
  echo $app->Form->input('redirect_after', [
    'value' => $_params->query['idejojjunkmajd'],
    'type' => 'hidden',
  ]);
}

echo $app->Form->input('name', [
  'label' => 'Megjelenített név',
  'autocomplete' => 'on',
  'required' => true,
  'class' => 'focus'
]);

echo $app->Form->input('email', [
  'type' => 'email',
  'autocomplete' => 'on',
  'label' => 'Email cím',
  'required' => true
]);

echo $app->Form->input('password', [
  'type' => 'password',
  'label' => 'Jelszó',
  'autocomplete' => 'ne-toltsd-ki',
  'required' => true
]);

echo $app->Form->captcha();

echo '<p>Regisztrációd előtt olvasd el az alábbiakat:<br />';
echo $app->Html->link('Működési elvek', '/oldalak/mukodesi-szabalyzat', [
  'target' => '_blank',
  'icon' => 'book',
  'class' => 'd-block'
]);
echo $app->Html->link('Adatkezelési szabályzat', '/oldalak/adatkezelesi-szabalyzat', [
  'target' => '_blank',
  'icon' => 'book',
  'class' => 'd-block'
]);
echo $app->Html->link('Jogi nyilatkozat', '/oldalak/jogi-nyilatkozat', [
  'target' => '_blank',
  'icon' => 'book',
  'class' => 'd-block'
]);
echo '</p>';

echo $app->Form->input('disclaimer', [
  'type' => 'checkbox',
  'label' => 'Megismertem és elfogadom a Köztérkép Jogi nyilatkozatát, Adatkezelési szabályzatát és Működési elveit',
  'value' => 1,
  'checked' => false
]);

echo $app->Form->end('Regisztráció', ['class' => 'btn-primary']);

echo '</div>'; // col
echo '<div class="col-md-6 mb-4">';
echo '<h4 class="title">Hasznos linkek</h4>';

echo '<p>Van már Köztérkép hozzáférésed?<br />';
echo $app->Html->link('Lépj be itt!', '/tagsag/belepes', [
  'class' => 'font-weight-bold'
]);
echo '</p>';

echo '<p>Elfelejtetted a jelszavad vagy más problémád van?<br />';
echo $app->Html->link('Készíts új jelszót!', '/tagsag/bejelentkezesi-segitseg', [
  'class' => 'font-weight-bold'
]);
echo '</p>';

echo '</div>'; // col

echo '</div>'; // row


?>
