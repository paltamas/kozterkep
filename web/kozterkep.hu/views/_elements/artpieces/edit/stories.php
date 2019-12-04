<?php
echo $app->Form->create($artpiece, [
  //'method' => 'post',
  'class' => 'w-100 artpiece-edit-form ajaxForm',
]);

$mine = [
  'HUN' => false,
  'ENG' => false,
];

if (count($descriptions) > 0) {
  $key = 1;
  foreach ($descriptions as $description) {
    $key++;
    if ($_user['id'] == $description['user_id']) {
      $mine[@$description['lang']] = [$description['id'], $key];
    }
  }
}

echo '<div class="text-center mb-4">';

if (!$mine['HUN']) {
  echo $app->Html->link('Sztori hozzáadása', '#', [
    'class' => 'btn btn-outline-primary mr-4 mb-2 mb-md-0',
    'ia-bind' => '',
    'ia-removeclass' => 'collapse',
    'ia-target' => '#new-description-hun',
    'ia-focus' => '#Descriptions-0-text',
  ]);
} else {
  echo $app->Html->link('Saját sztori szerkesztése', '#', [
    'icon' => 'edit',
    'ia-bind' => 'artpieces.description_edit',
    'ia-pass' => $mine['HUN'][0],
    'ia-vars-key' => $mine['HUN'][1],
    'class' => 'btn btn-outline-primary mr-4 mb-2 mb-md-0',
  ]);
}

if (!$mine['ENG']) {
  echo $app->Html->link('Angol nyelvű sztori hozzáadása', '#', [
    'class' => 'btn btn-outline-primary mr-4',
    'ia-bind' => '',
    'ia-removeclass' => 'collapse',
    'ia-target' => '#new-description-eng',
    'ia-focus' => '#Descriptions-1-text',
  ]);
} else {
  echo $app->Html->link('Saját angol sztori szerkesztése', '#', [
    'icon' => 'edit',
    'ia-bind' => 'artpieces.description_edit',
    'ia-pass' => $mine['ENG'][0],
    'ia-vars-key' => $mine['ENG'][1],
    'class' => 'btn btn-outline-primary mr-4 mb-2 mb-md-0',
  ]);
}
echo '</div>';

if (count($descriptions) > 0) {

  $key = 1; // innen indulunk (0: új magyar, 1: új angol; aztán a többi)

  foreach ($descriptions as $description) {

    $key++;

    echo '<div class="bg-light rounded px-3 py-2 mb-4 description-row description-row-' . $description['id'] . '">';

    if (@$description['lang'] == 'ENG') {
      echo '<h6 class="font-weight-bold"><span class="far fa-globe mr-1"></span>Angol leírás, sztori</h6>';
    }

    // Név
    echo '<div class="mb-1">';
    echo '<strong>' . $app->Users->name($description['user_id']) . '</strong>';
    echo '<div class="small float-md-right">';
    echo ' <span class="text-muted">';
    echo _date($description['created'], 'Y.m.d. H:i');
    echo '</span>';
    if ($description['modified'] > $description['created']) {
      echo ' <span class="text-muted">&bull; Frissítve: ';
      echo _date($description['modified'], 'y.m.d. H:i');
      echo '</span>';
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="texts">';

    // Nyelv, ami szerkesztéskor kell
    echo '<span class="d-none" id="description-lang-' . $description['id'] . '">' . $description['lang'] . '</span>';

    // Szöveg
    echo '<div>' . $app->Text->format($description['text']) . '</div>';
    echo '<textarea class="d-none" id="description-text-' . $description['id'] . '">' . $description['text'] . '</textarea>';

    // Források, ha van
    if (@$description['source'] != '') {
      echo '<hr class="my-3" />';
      echo '<div class="text-muted">';
      echo '<strong>Források:</strong><br /><linkify_custom>' . $app->Text->format_source($description['source']) . '</linkify_custom>';
      echo '</div>';
    }
    echo '<textarea class="d-none" id="description-source-' . $description['id'] . '">' . $description['source'] . '</textarea>';

    // Szerk-link
    echo '<div class="my-3 text-left">';
    $edit_options = [
      'icon' => 'edit',
      'ia-bind' => 'artpieces.description_edit',
      'ia-pass' => $description['id'],
      'ia-vars-key' => $key,
      'class' => 'font-weight-bold',
    ];

    if ($_user['id'] != $description['user_id']) {
      $edit_options['ia-confirm'] = 'Biztosan ezt szeretnéd? A leírások védelmének elvét követve más tagok leírásaiban csak elütéseket vagy egyértelmű tévedéseket korrigálunk (évszámok, nevek stb.), illetve nem feltüntetett forrásokat adunk hozzá.<br><strong>A leírást ne szerkeszd át.</strong><br>Ha új vagy pontosabb információid vannak, adj hozzá saját leírást.';
    }
    echo $app->Html->link('Sztori szerkesztése', '#', $edit_options);
    echo '</div>';

    echo '</div>'; // texts-div; ezt rejtjük, ha szerkesztésre nyitjuk

    echo '</div>';
  }
}





echo '<div class="bg-light p-4 mb-4 collapse" id="new-description-hun">';
echo $app->Form->input('descriptions[0][id]', [
  'type' => 'text',
  'value' => 'new_hun',
  'class' => 'd-none',
]);

echo $app->Form->input('descriptions[0][text]', [
  'label' => 'Saját történeted, fontos adalékod az alkotásról',
  'type' => 'textarea',
  'help' => 'Ha hivatkoznál a szövegben a számozott forrásokra, akkor használd a sorszámot szögletes zárójelben a kívánt helyen: [1] Szögletes zárójel? Magyar billentyűzeten jobb Alt+F és jobb Alt+G',
]);

echo $app->Form->input('descriptions[0][source]', [
  'label' => 'Leírásodban használt források',
  'placeholder' => 'Forrásaim...',
  'type' => 'textarea',
  'help' => 'Több forrás esetén mindet új sorba írd! Ha hivatkoznál a forrásokra a szövegben, akkor itt sorszámozd őket így: [1] ...szöveg...',
]);

echo $app->Html->link('Szöveg elvetése', '#', [
  'icon' => 'trash',
  'class' => 'btn btn-link',
  'ia-bind' => 'artpieces.description_cancel',
  'ia-pass' => '#Descriptions-0-text,#Descriptions-0-source',
  'ia-addclass' => 'collapse',
  'ia-target' => '#new-description-hun',
  'ia-confirm' => 'A szöveget is töröljük, ha beírtál már valamit.',
]);

echo '</div>';

echo '<div class="bg-light p-4 collapse" id="new-description-eng">';
echo $app->Form->input('descriptions[1][id]', [
  'type' => 'text',
  'value' => 'new_eng',
  'class' => 'd-none',
]);

echo $app->Form->input('descriptions[1][text]', [
  'label' => 'Saját angol sztorid az alkotásról',
  'type' => 'textarea',
  'help' => 'Ha hivatkoznál a szövegben a számozott forrásokra, akkor használd a sorszámot szögletes zárójelben a kívánt helyen: [1] Szögletes zárójel? Magyar billentyűzeten jobb Alt+F és jobb Alt+G',
]);

echo $app->Form->input('descriptions[1][source]', [
  'label' => 'Szövegedben használt források',
  'placeholder' => 'Forrásaim...',
  'type' => 'textarea',
  'help' => 'Több forrás esetén mindet új sorba írd! Ha hivatkoznál a forrásokra a szövegben, akkor itt sorszámozd őket így: [1] ...szöveg...',
]);

echo $app->Html->link('Angol szöveg elvetése', '#', [
  'icon' => 'trash',
  'class' => 'btn btn-link',
  'ia-bind' => 'artpieces.description_cancel',
  'ia-pass' => '#Descriptions-1-text,#Descriptions-1-source',
  'ia-addclass' => 'collapse',
  'ia-target' => '#new-description-eng',
  'ia-confirm' => 'A szöveget is töröljük, ha beírtál már valamit.',
]);
echo '</div>';

echo $app->Form->end();