<?php
echo '<div class="text-center">';

echo $app->Form->submit('Módosítások mentése', [
  'name' => 'save_post',
  'class' => 'mb-3',
]);

if ($post['status_id'] == 1) {
  echo $app->Html->link('Bejegyzés publikálása', $_params->here . '?publikalas', [
    'class' => 'ml-3 mb-3 btn btn-success',
    'ia-confirm' => 'Elmentetted a változtatásokat és átnézted ezeket: bevezető ügyes, helyesírás jó, tagolás szuper, téma helyesen kiválasztva, kapcsolások okosak. <strong>Tehát minden oké, publikálhatunk?</strong>',
  ]);
} else {
  echo $app->Html->link('Visszavétel szerkesztésre', $_params->here . '?visszavetel', [
    'class' => 'ml-3 mb-3 btn btn-secondary',
    'ia-confirm' => '<strong>Biztosan visszaveszed a bejegyzést, hogy ne érje el senki?</strong> Akkor javasoljuk a bejegyzés visszavételét, ha tartósan nem szeretnéd megjelentetni. Ha csak módosítanál benne, megtehed úgy is, hogy publikus.',
  ]);
}
echo '</div>';