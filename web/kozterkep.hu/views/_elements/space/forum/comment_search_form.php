<?php
$options = (array)@$options + [
  'show_types' => false,
];

echo $app->Form->create($_params->query, [
  'class' => 'unsetEmptyFields form-inline'
]);
echo $app->Form->input('kifejezes', [
  'placeholder' => 'Keresett kifejezés',
  'class' => 'mx-1'
]);

if ($options['show_types']) {
  echo $app->Form->input('tipus', [
    'options' => [
        '' => 'Bárhol',
        'mulap' => 'Műlapokon',
      ] + $forum_topic_list + [
        'alkoto' => 'Alkotóknál',
        'hely' => 'Helyeknél',
        'blog' => 'Blogoknál',
        'mappa' => 'Mappáknál',
        'konyv' => 'Könyvnél',
      ],
    'class' => 'mx-1 narrow'
  ]);
}

if (@$forum_topic['id'] == 6) {
  $user_list = $app->Users->list('headitors_ever');
} else {
  $user_list = $app->Users->list('commenters');
}

echo $app->Form->input('tag', [
  'options' => [
    '' => 'Bárki által',
    'ennekem' => '-- Általam vagy nekem --',
    'nekem' => '-- Nekem --',
  ] + $user_list,
  'class' => 'mx-1 narrow'
]);

echo $app->Form->submit('Mehet', [
  'name' => 'kereses',
  'class' => 'btn btn-secondary',
  'divs' => 'form-group',
]);

echo $app->Form->end();

if (@$_params->query['kifejezes'] != ''
  || @$_params->query['tipus'] != ''
  || @$_params->query['tag'] != '') {
  echo '<div class="small mt-2">'
    . $app->Html->link('Szűrés törlése', $_params->path, ['icon' => 'times', 'class' => ''])
    . '</div>';
}

?>