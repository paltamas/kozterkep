<?php
/**
 * Ez ugyanazt csinálja meg, mint a descriptions.js add_row függvénye
 */
$options = (array)@$options + [
  'row_class' => '',
  'buttons' => true,
  'admin_links' => false,
  'intro' => false,
];


echo '<div id="description-row-' . $description['id'] . '" class="row border rounded mb-3 py-2 mx-0 ' . $options['row_class'] . '">';
echo '<div class="col-md-12 description-container">';

// User + szöveg
echo '<div class="description-text-box">';


// Dolgok
if ($options['buttons']) {
  echo '<div class="float-right small text-muted">';
  // Időbélyeg
  echo _time($description['created'], ['ago' => true]);

  // Módosítás, ha volt
  if ($description['modified'] > $description['created']) {
    echo '<span class="fal fa-sm fa-edit ml-2" data-toggle="tooltip" title="Módosítva: ' . _date($description['modified'], 'Y.m.d. H:i:s') . '"></span>';
  }
  echo '</div>';
}

// User
echo '<div class="mb-2"><span class="font-weight-bold mr-2 description-user">' . $app->Users->name($description['user_id']) . '</span></div>';

// Szöveg
echo '<span class="description-text">';
echo $app->Text->format($description['text'], [
  'intro' => $options['intro']
]);
echo '</span>';
echo '</div>'; // description-text-box --

// Kapcsolt dolgok, de előtte kiszedem az alkotót, mert ott vagyunk (így csak a fájl maradhat)
$artist_id = $description['artist_id'];
unset($description['artist_id']);
echo $app->element('layout/partials/connected_things', [
  'item' => $description,
  'options' => [
    'item_type' => 'description',
  ]
]);

// Admin funkciók
if ($options['admin_links']
  && ($app->Users->is_head($_user) || $_user['id'] == CORE['USERS']['artists'])) {
  echo '<div class="mt-3 pt-2 border-top">';
  echo '<span class="text-muted mr-3"><span class="fal fa-glasses-alt mr-1"></span>Admin funkciók:</span>';

  echo $app->Html->link('Legyen hozzászólás', '', [
    'class' => 'btn-link mr-3 d-inline-block text-nowrap',
    'icon' => 'comment',
    'ia-bind' => 'comments.artist_description_convert_back',
    'ia-pass' => $description['id'],
    'ia-vars-artist_id' => $artist_id,
    'ia-confirm' => 'Biztosan visszaminősíted hozzászólássá?',
  ]);

  echo $app->Html->link('Szerkesztés', '/alkotok/adalek_szerkesztes/' . $description['id'], [
    'class' => 'btn-link mr-3 d-inline-block text-nowrap',
    'icon' => 'edit',
    'ia-modal' => 'modal-sm?',
  ]);
  echo '</div>';
}


echo '</div>'; // col --
echo '</div>'; // row --