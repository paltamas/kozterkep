<?php
echo '<div class="row">';

echo '<div class="col-md-6">';

echo $app->Form->input('title', [
  'label' => 'Alkotás címe',
  'required' => true,
  'ia-empty-if-focus' => 'Egy új műlap...',
  'help' => 'A mű magyar címe. Külföldi alkotásoknál törekedj helyes fordításra, amennyire lehetséges.',
]);

echo $app->Form->input('title_alternatives', [
  'label' => 'Helyi és/vagy alternatív elnevezések',
  'help' => 'Helyi, hivatalos vagy saját alternatív nevek, amelyek pontosítják az összképet. Ha több van, vesszővel válaszd el őket. Ide írd a külföldi alkotás angoltól eltérő helyi megnevezését.',
]);

echo '</div>'; // col-6

echo '<div class="col-md-6">';

echo $app->Form->input('title_en', [
  'label' => 'Angol cím',
  'help' => 'Külföldi alkotások esetén kötelező megadni.',
]);

echo '</div>'; // col-6

echo '</div>'; // row