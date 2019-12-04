<?php
echo '<div class="row">';

echo '<div class="col-md-6 mb-4">';

echo $app->Form->create($_params->data, [
  'method' => 'post'
]);

echo $app->Form->input('email', [
  'type' => 'email',
  'autocomplete' => 'on',
  'label' => 'Add meg regisztrált email címed',
  'required' => true,
  'class' => 'focus'
]);

echo '<p class="mt-1 float-right">';
echo $app->Html->link('Vissza a belépéshez', '/tagsag/belepes');
echo '</p>';

echo $app->Form->end('Segítség kérése', ['class' => 'btn-primary']);

echo '</div>'; // col
echo '<div class="col-md-6 mb-4">';
echo '<h4 class="title">Miben segítünk?</h4>';

echo $app->Html->list([
  'Ha elfelejtetted jelszavad;',
  'ha nem találod a regisztrációd aktivációs emailjét;',
  'ha régi hozzáférésedet szeretnéd újraaktiválni.'
], ['class' => 'my-2']);

echo '<p>Ha rendszerünkben létezik a megadott email cím, akkor kiküldünk rá egy 24 óráig használható jelszó generáló linket.</p>';

echo '</div>'; // col

echo '</div>'; // row