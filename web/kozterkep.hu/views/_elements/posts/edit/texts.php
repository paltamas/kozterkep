<?php
echo $app->Form->input('title', [
  'label' => 'Bejegyzés címe',
]);

// Ha admin témába került és nem admin az író, akkor nem tudja módosítani
if ($_user['admin'] == 0 && in_array($post['postcategory_id'], $admin_categories)) {
  echo '<div class="mb-3">';
  echo 'Téma: <strong>' . $postcategories[$post['postcategory_id']] . '</strong>';
  echo $app->Form->help('Az üzemgazda zárolt témába sorolta a bejegyzésedet, így a téma nem módosítható. Jelezz neki, ha ezt kéred.');
  echo '</div>';
} else {
  echo $app->Form->input('postcategory_id', [
    'label' => 'Téma',
    'options' => $postcategories,
    'class' => '',
    'help' => 'Válaszd ki a bejegyzésedhez leginkább illő témát.',
  ]);
}

echo '<div class="form-inline">';
echo $app->Form->input('photo_id', [
  'label' => 'Kiemelt műlap fotó',
  'value' => $post['photo_id'] > 0 ? $post['photo_id'] : '',
  'placeholder' => 'Műlap fotó AZ',
]);
echo $app->Form->input('file_id', [
  'label' => ['Kiemelt mappa fájl', [
    'class' => 'mr-2 ml-4'
  ]],
  'value' => $post['file_id'] > 0 ? $post['file_id'] : '',
  'placeholder' => 'Mappa fájl AZ',
]);
echo '</div>';
echo '<div class="mb-3">';
echo $app->Form->help('A bejegyzésed kiemelt képe a listákban és a megtekintésnél a szöveg elején. Keresd kia  képet, nyisd meg nagyban és keresd az információs dobozban az "Azonosító" számot. Egyszerre csak 1 mezőt tölts ki. Mappa fájl esetén képet válassz.');
echo '</div>';

echo $app->Form->input('intro', [
  'type' => 'textarea',
  'maxlength' => sDB['limits']['posts']['intro_max_length'],
  'label' => 'Bevezető',
  'class' => '',
  'help' => 'Max. ' . sDB['limits']['posts']['intro_max_length'] . ' karakterben foglald össze, miről szól a bejegyzésed. Ez jelenik meg a listákban.',
]);

echo $app->Form->input('text', [
  'type' => 'textarea',
  'label' => 'Bejegyzés szövege',
  'class' => $post['html_formatted'] == 1 ? 'html-editor' : '',
  'help' => 'A formázás egyelőre korlátozottan érhető el: ' . htmlentities('<strong>vastagítás</strong>') . ', műlap fotót így: ' . htmlentities('<aimage>fotóAZ</aimage>') . ', mappa fájlt pedog így szúrhatsz be: ' . htmlentities('<ffile>fájlAZ</ffile>'),
]);

echo $app->Form->input('sources', [
  'label' => 'Bejegyzésedben használt források',
  'placeholder' => 'Forrásaim...',
  'type' => 'textarea',
  'help' => 'Több forrás esetén mindet új sorba írd! Ha hivatkoznál a forrásokra a szövegben, akkor itt sorszámozd őket így: [1] ...szöveg...',
]);
?>