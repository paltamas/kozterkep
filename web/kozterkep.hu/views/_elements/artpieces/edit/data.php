<?php
echo $app->Form->create($artpiece, [
  //'method' => 'post',
  'class' => 'w-100 artpiece-edit-form ajaxForm',
]);

$blocks = [
  'titles' => 'Alkotás címe, megnevezése',
  'placement' => 'Elhelyezkedés',
  'artists' => 'Alkotók és közreműködők',
  'dates' => 'Legfontosabb események',
  'parameters' => 'Kiemelt paraméterek',
  'types' => 'Típus meghatározása',
  'styles' => 'Stílusok',
  'forms' => 'Ábrázolt formák',
  'materials' => 'Felhasznált anyagok',
];

$i = 0;

foreach ($blocks as $block => $title) {
  $i++;
  echo '<h4 class="subtitle">' . $title . '</h4>';
  echo '<div class="my-3">';
  echo $app->element('artpieces/edit/data/' . $block);
  echo '</div>';
  echo $i < count($blocks) ? '<hr />' : '';
}

echo $app->Form->end();