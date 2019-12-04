<?php
echo $_user['out_of_work'] == 1 ? '<div class="alert alert-info"><strong>Jelenleg vakáció-választ kap minden neked író tag.</strong> Ezt a ' . $app->Html->link('Beállítások / Értesítések', '/tagsag/beallitasok#ertesitesek') . ' alatt kezelheted.</div>' : '';

echo '<div class="row">';
echo '<div class="col-lg-8 mb-3 mb-lg-0 text-center text-md-left">';
echo '<div class="btn-group" role="group" aria-label="Olvasott / olvasatlan szűrés">';
$class = !isset($_params->query['olvasatlanok']) ? 'btn-secondary' : 'btn-outline-secondary';
echo $app->Html->link('Minden', '?minden', [
  'class' => 'btn ' . $class,
]);
$class = isset($_params->query['olvasatlanok']) ? 'btn-secondary' : 'btn-outline-secondary';
echo $app->Html->link('Csak olvasatlanok', '?olvasatlanok', [
  'class' => 'btn ' . $class,
]);
echo '</div>';
echo '</div>';
echo '<div class="col-lg-4">';
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Keresés...'
]]);
echo '</div>';

if (date('Y') == 2019 && $old_unreads > 5) {
  echo '<div class="alert alert-warning">';
  echo '<p>A weblap újraindulásakor átvett régi üzenetek esetében sajnos bizonyos esetekben sok olvasatlan üzenet jelenhet meg, amit korábban már olvasottá tettél. Hogy ne kelljen egyesével olvasottá tenni őket, az alábbi linkre kattintva minden április 22. előtti üzenetet olvasottá tehetsz.</p>';
  echo $app->Html->link('Korábbi üzenetek olvasottá tétele', '?regiek-elolvasva', [
    'class' => 'font-weight-bold',
    'ia-confirm' => 'Ezzel minden április 22. előtti üzenet olvasott lesz.',
  ]);
  echo '</div>';
}

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
    echo in_array($_user['id'], $conversation['favored']) ? '<span class="far fa-star text-primary mr-2"></span>' : '';
    echo @$conversation['subject'] != '' ? '<span class="' . $bold . ' mr-2 subject">' . $conversation['subject'] . '</span>' : '';
    echo @count(@$conversation['files']) > 0 ? '<span class="far fa-paperclip mr-2 text-muted"></span>' : '';
    echo end($conversation['messages'])['body'] != '' ?
      '<span class="text-muted">'
        . $app->Text->truncate($app->Text->format(end($conversation['messages'])['body'], [
            'format' => false,
            'nl2br' => false,
          ]), 120)
        . '</span>'
      : '';
    echo '</div>';


    // GOMBOK
    echo '<div class="col-lg-2 col-12 text-right pt-2 pl-0">';

    echo $app->Html->link('', '#', [
      'icon' => $unread ? 'dot-circle fa-lg' : 'circle fa-lg',
      'class' => 'mr-4 mr-md-1 readToggle',
      'ia-bind' => 'conversations.read_toggle',
      'ia-pass' => $conversation['id'],
      'ia-target' => '#{id} .far',
      'ia-toggleclass' => 'fa-dot-circle fa-circle',
    ]);

    echo $app->Html->link('', '#', [
      'icon' => 'archive fa-lg',
      'class' => 'mr-4 mr-md-1',
      'title' => 'Archiválás',
      'ia-bind' => 'conversations.archive',
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