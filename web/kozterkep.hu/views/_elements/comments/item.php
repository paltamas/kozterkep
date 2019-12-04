<?php
/**
 * Ez ugyanazt csinálja meg, mint a comments.js add_row függvénye
 */
$options = (array)@$options + [
  'row_class' => '',
  'highlight' => false,
  'reply' => true,
  'buttons' => true,
  'thread_links' => true,
  'truncate' => 150,
  'connected_things' => true,
];

if (@$_params->query['komment'] == $comment['id']) {
  $options['row_class'] .= ' bg-gray-kt ';
}

echo '<div id="comment-row-' . $comment['id'] . '" class="row comment-row border rounded mb-3 py-2 mx-0 ' . $options['row_class'] . '">';
echo '<div class="col-md-12 comment-container px-2">';

// User + szöveg
echo '<div class="comment-text-box">';

// Jobb felső dolgok
echo '<div class="float-right">';
if ($options['buttons'] && $options['reply']) {
  // Válasz ikon
  echo $app->Html->link('Válasz', '#', [
    'icon' => 'reply',
    'hide_text' => true,
    'class' => 'replyComment small text-muted',
    'title' => 'Válasz írása',
    'data-id' => $comment['id'],
  ]);
}
echo '</div>';

// User
echo '<div class="mb-1">';
echo $app->Users->profile_image($comment['user_id']);
echo '<span class="font-weight-bold mr-2 comment-user">' . $app->Users->name($comment['user_id']) . '</span>';
echo '</div>';

// Kapcsolódók
if ($options['connected_things']) {
  $connected_things = $app->element('layout/partials/connected_things', [
    'item' => $comment,
    'options' => [
      'item_type' => 'comment',
      'file_previews' => false,
      'things_class' => 'small text-muted ml-1',
    ]
  ]);

  echo $connected_things == '' ? '' : $connected_things . '<hr class="my-1" />';
}

echo '<nolink class="comment-text-for-edit d-none">';
echo $app->Text->format($comment['text']);
echo '</nolink>';


// Szöveg
echo '<div class="comment-text my-2">';
if ($options['highlight']) {
  echo $app->Text->format($comment['text'], ['highlight' => $options['highlight']]);
} else {
  echo $app->Text->read_more($comment['text'], $options['truncate'], true);
}
echo '</div>';
echo '</div>'; // comment-text-box --


// Jobb alsó dolgok
if ($options['buttons']) {
  echo '<div class="float-right pt-1">';
  echo $app->Html->link('', '/kozter/komment/' . $comment['id'], [
    'icon' => 'ellipsis-h fas',
    'class' => 'small text-muted ml-3',
    'title' => 'További lehetőségek',
    'data-id' => $comment['id'],
    'ia-modal' => 'modal-md',
  ]);
  echo '</div>';
}

// Előzménye van...
if (@$comment['answered_id'] != '' && $options['thread_links']) {
  echo '<div class="small mt-2 text-muted float-right">';
  /*echo $app->Html->link('Előzmény...', '#comment-row-' . $comment['answered_id'], [
    'icon' => 'arrow-down',
  ]);*/
  // Párbeszéd link
  if (@$comment['parent_answered_id'] != '') {
    echo $app->Html->link('Párbeszéd', '/kozter/parbeszed/' . $comment['parent_answered_id'], [
      'icon' => 'stream',
      'class' => 'ml-3'
    ]);
  }
  echo '</div>';
}


// Bal alsó dolgok
// Időbélyegek
echo '<small class="text-muted">' . _time($comment['created'], ['ago' => true]) . '</small>';
if ($comment['modified'] > $comment['created']) {
  echo '<span class="fal fa-sm fa-edit ml-2" data-toggle="tooltip" title="Módosítva: ' . _date($comment['modified'], 'Y.m.d. H:i:s') . '"></span>';
}

// Kiemelés
if (@$comment['artpiece_id'] > 0 && @$comment['highlighted'] > 0) {
  echo '<span class="fal fa-sm fa-angle-double-up ml-2" data-toggle="tooltip" title="Kiemelt hozzászólás aktualitás miatt eddig: ' . _date($comment['highlighted']+(sDB['limits']['comments']['highlight_months']*30*24*60*60), 'Y.m.d. H:i:s') . '"></span>';
}

if ($options['connected_things']) {
// Kapcsolt fájlok
  echo $app->element('layout/partials/connected_things', [
    'item' => $comment,
    'item_type' => 'comment',
    'options' => [
      'file_previews' => true,
      'only_files' => true,
    ]
  ]);
}


echo '</div>'; // col --
echo '</div>'; // row --