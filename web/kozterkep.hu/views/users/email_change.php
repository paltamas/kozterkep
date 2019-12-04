<?php
echo '<div class="row">';

echo '<div class="col-md-6 mb-4">';

echo $app->Form->create(null, [
  'method' => 'post'
]);

echo '<p>Új emailcím: <strong>' . $user['email_to_confirm'] . '</strong></p>';

echo $app->Form->input('pass', [
  'type' => 'password',
  'label' => 'Jelszavad',
  'required' => true,
  'class' => 'focus'
]);

echo $app->Form->end('Új emailcím jóváhagyása', ['class' => 'btn-primary']);

echo '</div>'; // col
echo '<div class="col-md-6 mb-4">';
echo '<h4 class="title">Információk</h4>';

echo '<p>Biztonsági okból bekérjük jelszavadat az email módosítás véglegesítéséhez.</p>';

echo '</div>'; // col

echo '</div>'; // row


?>
