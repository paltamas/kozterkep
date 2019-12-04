<?php
// Töröltek kukázása
$messages = [];
foreach ($conversation['messages'] as $key => $message) {
  // Ha törlése előtti üzenet, akkor nem mutatjuk
  if (!isset($message['deleted'][$_user['id']])) {
    $messages[$key] = $message;
  } else {
    $was_deletion = true;
  }
}
$conversation['messages'] = $messages;

$unread = in_array($_user['id'], $conversation['read']) ? false : true;
$user_link = true;

echo '<div class="font-weight-bold">';
echo $app->element('conversations/user_names', compact('conversation', 'unread', 'user_link'));
echo '</div>';

echo '<div class="row mb-3">';

echo '<div class="col-md-6">';
echo '<span class="text-muted mr-3 thread-info"><span class="count">' . count($conversation['messages']) . '</span> üzenet, utolsó: ' . _time($conversation['updated'], ['ago' => true]) . '</span>';
echo '</div>';
echo '<div class="col-md-6 text-md-right">';
echo '<span class="text-muted">Indult: ' . date('Y.m.d. H:i:s', $conversation['started']) . '</span>';
echo '</div>';
echo '</div>';
echo '<hr />';


echo $app->element('conversations/connected_things', ['options' => [
  'same_info' => false,
  'hidden_inputs' => false,
]]);


echo '<div class="row">';

echo '<div class="col-md-12 col-lg-5 order-lg-1 order-3 text-center text-lg-left">';
echo $app->Html->link('Válasz írása...', '#Form-Conversation-' . $conversation['id'], [
  'icon' => 'comment-alt-plus',
  'data-toggle' => 'collapse',
  'class' => 'font-weight-bold py-2 px-4 btn btn-outline-primary',
  'ia-focus' => '#Message'
]);
echo '</div>';

echo '<div class="col-md-7 col-lg-4 order-lg-2 order-1 pt-md-1 pb-2 text-center">';

$where_to_redirect = 'aktiv';
if (in_array($_user['id'], $conversation['archived'])) {
  $where_to_redirect = 'archivum';
} elseif (in_array($_user['id'], $conversation['trashed'])) {
  $where_to_redirect = 'kuka';
}

// Kedvencelés: kukában nem
if (!in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => in_array($_user['id'], $conversation['favored']) ? 'stop-circle fa-2x' : 'star fa-2x',
    'class' => 'mr-3 favorToggle link-gray',
    'title' => in_array($_user['id'], $conversation['favored']) ? 'Ne legyen kedvenc' : 'Legyen kedvenc',
    'ia-bind' => 'conversations.favor_toggle',
    'ia-pass' => $conversation['id'],
    'ia-target' => '#{id} .far',
    'ia-toggletitle' => 'Legyen kedvenc||Ne legyen kedvenc',
    'ia-toggletitle-target' => '.favorToggle',
    'ia-toggleclass' => 'fa-stop-circle fa-star',
  ]);
}

// Read toggle (csak olvasatlanná tehetjük): inbox
if (!in_array($_user['id'], $conversation['archived']) && !in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => 'circle fa-2x',
    'class' => 'mr-3 link-gray',
    'title' => 'Tegyük olvasatlanná',
    'ia-bind' => 'conversations.read_toggle',
    'ia-pass' => $conversation['id'],
    'ia-redirect' => 'beszelgetesek/' . $where_to_redirect,
  ]);
}

// Archiválás: inboxban
if (!in_array($_user['id'], $conversation['archived']) && !in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => 'archive fa-2x',
    'class' => 'mr-3 link-gray',
    'title' => 'Archiválás',
    'ia-bind' => 'conversations.archive',
    'ia-pass' => $conversation['id'],
    'ia-redirect' => 'beszelgetesek/archivum',
  ]);
}

// Restore: artchived vagy trashed állapotban
if (in_array($_user['id'], $conversation['archived']) || in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => 'inbox fa-2x',
    'class' => 'mr-3 link-gray',
    'title' => 'Aktívak közé',
    'ia-bind' => 'conversations.restore',
    'ia-pass' => $conversation['id'],
    'ia-redirect' => 'beszelgetesek/aktiv',
  ]);
}

// Törlés: kukában nem
if (!in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => 'trash fa-2x',
    'class' => 'link-gray',
    'title' => 'Törlés',
    'ia-bind' => 'conversations.trash',
    'ia-pass' => $conversation['id'],
    'ia-hide' => '.list-conversation-' . $conversation['id'],
    'ia-redirect' => 'beszelgetesek/' . $where_to_redirect,
  ]);
}

// Végleges törlés: kukában csak
if (in_array($_user['id'], $conversation['trashed'])) {
  echo $app->Html->link('', '#', [
    'icon' => 'trash-alt fa-2x',
    'class' => 'link-gray',
    'title' => 'Végleges törlés',
    'ia-bind' => 'conversations.delete',
    'ia-pass' => $conversation['id'],
    'ia-redirect' => 'beszelgetesek/kuka',
  ]);
}

echo '</div>';

echo '<div class="col-md-5 col-lg-3 order-lg-3 order-2 text-right d-none d-md-block">';
echo $app->Form->input('unisearch', [
  'ia-unisearch-container' => '.conversation-thread',
  'ia-unisearch-item-container' => '.row',
  'ia-unisearch-items' => '.row .message-text',
  'placeholder' => 'Keresés...',
  'prepend_icon' => 'search'
]);
echo '</div>';

echo '</div>';

echo $app->Form->create(null, [
  'class' => 'ajaxForm autoSave mt-4 mb-5 collapse messageForm',
  'ia-form-action' => 'api/conversations/append/' . $conversation['id'],
  'ia-form-method' => 'put',
  'id' => 'Form-Conversation-' . $conversation['id'],
  'ia-form-trigger' => 'conversations.refresh_thread, layout.collapse_toggle:.messageForm'
]);

echo $app->Form->input('message', [
  'placeholder' => 'Üzeneted...',
  'type' => 'textarea',
  'rows' => 4,
  'class' => 'controlEnter',
  //'help' => texts('gyorskuldes')
]);

echo $app->Form->input('file', [
  'type' => 'file',
  'divs' => 'form-group fileInput collapse',
  'multiple' => true,
  'ia-previewfile' => true,
  'ia-fileupload' => true
]);

echo $app->Form->submit('Üzenet küldése');

echo $app->Html->link('Fájl csatolás', '', [
  'icon' => 'paperclip',
  'data-toggle' => 'collapse',
  'data-target' => '.fileInput',
  'class' => 'float-right pt-2'
]);

echo $app->Form->end();


$conversation['messages'] = array_reverse($conversation['messages']);

echo '<div class="row">';
echo '<div class="col-md-12 col-lg-10">';

echo '<div class="conversation-thread">';

foreach ($conversation['messages'] as $message) {

  echo '<div id="message-' . $message['mid']
    . '" class="row border rounded my-4 bg-light shadow-sm py-3 ' , $message['user_id'] != $_user['id'] ? 'bg-gray' : '' , '">';

  echo '<div class="col-12">';

  echo '<div class="mb-2">';

  echo '<span class="font-weight-bold text-muted mr-2">';
  echo $app->Users->profile_image($message['user_id']);
  echo $app->Users->name($message['user_id'], ['link' => false]);
  echo '</span>';
  echo '<span>' . _time($message['created'], ['ago' => true]) . '</span>';

  echo '<hr class="my-2" />';

  echo '</div>';

  echo '<div class="message-text">';
  echo $app->Text->format($message['body'], ['format' => false]);
  echo '</div>';

  if (@count(@$conversation['files'][$message['mid']]) > 0) {
    echo $message['body'] != '' ? '<hr />' : '';

    foreach ($conversation['files'][$message['mid']] as $file) {
      echo $app->Html->link($file[1], '/mappak/fajl_mutato/' . $file[0], [
        'icon' => 'paperclip',
        'class' => 'mr-3 file-attachment',
        'target' => '_blank',
        'ia-file' => $file[0],
      ]);
    }
  }

  echo '</div>';

  echo '</div>';

}

if (@$was_deletion === true) {
  echo '<p class="text-muted">Ezt a beszélgetést korábban véglegesen törölted. Csak a törlés óta beérkezett üzenetek érhetőek el.</p>';
}

echo '</div>'; // thread --
echo '</div>'; // col --
echo '</div>'; // row --


//debug($conversation);
