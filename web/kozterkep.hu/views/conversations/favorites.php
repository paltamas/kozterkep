<?php
echo '<div class="row">';
echo '<div class="col-lg-8"></div>';
echo '<div class="col-lg-4">';
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Keresés...'
]]);
echo '</div>';
echo '</div>';

if ($conversations) {
  foreach ($conversations as $conversation) {

    $unread = in_array($_user['id'], $conversation['read']) ? false : true;

    $bold =  $unread ? ' font-weight-bold ' : '';

    echo '<div class="list-conversation-' . $conversation['id'] . ' row px-1 py-2 rounded ' , $unread ? 'bg-yellow-light' : '' , ' border-bottom fade-icons">';

    // BESZ. PARTNEREK, IDŐBÉLYEG
    echo '<div class="col-lg-3 col-8 cursor-pointer" ia-href="/beszelgetesek/folyam/' . $conversation['id'] . '">';
    echo '<span class="names ' . $bold . '">';
    echo $app->element('conversations/user_names', compact('conversation', 'unread'));
    echo '</span>';
    echo '<br />';
    echo '<span class="text-muted small">' . _time($conversation['updated'], ['ago' => true]) . '</span>';
    echo '</div>';


    // TÁRGY, BEVEZETŐ SZÖVEG
    echo '<div class="col-lg-7 col-12 cursor-pointer texts" ia-href="/beszelgetesek/folyam/' . $conversation['id'] . '">';
    echo @$conversation['subject'] != '' ? '<span class="' . $bold . ' mr-2 subject">' . $conversation['subject'] . '</span>' : '';
    echo $conversation['messages'][count($conversation['messages']) - 1]['body'] != '' ?
      '<span class="text-muted">'
        . $app->Text->truncate($app->Text->format($conversation['messages'][count($conversation['messages']) - 1]['body'], [
            'format' => false,
            'nl2br' => false,
          ]), 120)
        . '</span>'
      : '';
    echo '</div>';


    // GOMBOK
    echo '<div class="col-lg-2 col-12 text-right pt-2 pl-0">';

    echo $app->Html->link('', '#', [
      'icon' => 'file fa-lg',
      'class' => 'mr-4 mr-md-1',
      'title' => 'Ne legyen kedvenc',
      'ia-bind' => 'conversations.favor_toggle',
      'ia-pass' => $conversation['id'],
      'ia-hide' => '.list-conversation-' . $conversation['id'],
    ]);

    echo $app->Html->link('', '#', [
      'icon' => 'trash fa-lg',
      'class' => '',
      'title' => 'Törlés',
      'ia-bind' => 'conversations.trash',
      'ia-pass' => $conversation['id'],
      'ia-hide' => '.list-conversation-' . $conversation['id'],
    ]);
    echo '</div>';


    echo '</div>'; // row
  }

  echo $app->Html->pagination(count($conversations), $pagination);

} else {
  echo '<p class="text-muted">Itt most semmi sincs...</p>';
}