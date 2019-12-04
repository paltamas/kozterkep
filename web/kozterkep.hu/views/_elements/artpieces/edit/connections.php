<?php
echo $app->Form->create($artpiece, [
  //'method' => 'post',
  'class' => 'w-100 artpiece-edit-form ajaxForm',
]);

$blocks = [
  'religions' => 'Vallási kapcsolódás',
  'history' => 'Történelmi esemény',
  'sets' => 'Gyűjtemények',
  'artpieces' => 'Műlapok',
  'others' => 'Egyéb',
];

$i = 0;

foreach ($blocks as $block => $title) {
  $i++;
  echo '<h4 class="subtitle">' . $title . '</h4>';
  echo '<div class="my-3">';
  echo $app->element('artpieces/edit/connections/' . $block);
  echo '</div>';
  echo $i < count($blocks) ? '<hr />' : '';
}

echo $app->Form->end();