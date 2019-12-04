<?php
echo '<div class="row">';

echo '<div class="col-md-6 mb-4">';

echo $app->Form->create(null, [
  'method' => 'post'
]);

echo $app->Form->input('pass', [
  'type' => 'password',
  'label' => 'Jelszó',
  'autocomplete' => 'ne-toltsd-ki',
  'required' => true,
  'class' => 'focus'
]);
echo $app->Form->input('pass_confirm', [
  'type' => 'password',
  'label' => 'Jelszó újra',
  'autocomplete' => 'ne-toltsd-ki',
  'required' => true
]);

echo $app->Form->end('Mentés', ['class' => 'btn-primary']);

echo '</div>'; // col
echo '<div class="col-md-6 mb-4">';
echo '<h4 class="title">Információk</h4>';

echo '<p>A jelszó legyen minimum 5 karakter hosszú.<br />Adhatod ugyanazt, mint eddig, ezt nem ellenőrizzük.</p>';
echo '<p>Sikeres beállítás után beléptetünk.</p>';

echo '</div>'; // col

echo '</div>'; // row