<?php

echo '<div class="row">';
echo '<div class="col-md-7">';
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Értesítő szövegében'
]]);
echo '</div>';
echo '<div class="col-md-5 text-center text-md-right mb-3">';
echo $app->Html->link('Minden legyen olvasott', '#', [
  'class' => 'btn btn-secondary',
  'ia-bind' => 'notifications.read_all',
]);
echo '</div>';
echo '</div>';

if (count($notifications) > 0) {

  foreach ($notifications as $notification) {

    $bold =  $notification['unread'] == 1 ? ' font-weight-bold ' : '';

    echo '<div class="list-notification list-notification-' . $notification['id'] . ' row px-1 py-2 rounded ' , $notification['unread'] == 1 ? 'bg-yellow-light' : '' , ' border-bottom fade-icons">';


    // KÉP és IDŐ
    echo '<div class="col-lg-2 col-8">';
    // Kép, ha van @todo - szerintem itt nem lesz szép
    echo @$notification['img'] != '' ? '<img class="rounded ml-3 align-self-center" src="' . $notification['img'] . '" alt="' . @$notification['title'] . '"><br />' : '';
    echo '<span class="text-muted">' . _time($notification['created'], ['ago' => true]) . '</span>';
    echo '</div>';


    // SZÖVEGEK
    echo '<div class="col-lg-8 col-12">';

    if (@$notification['link'] != '') {
      echo '<a href="' . $notification['link'] . '" title="Ugrás a linkelt aloldalra" data-toggle="tooltip">';
    }
    echo @$notification['title'] != '' ? '<span class="mr-3 ' . $bold . ' title">' . $notification['title'] . '</span>' : '';
    if (@$notification['link'] != '') {
      echo '</a>';
    }

    echo @$notification['content'] != '' ? '<span class="text-muted">' . $app->Text->format($notification['content']) . '</span>' : '';
    echo '</div>';


    // GOMBOK
    echo '<div class="col-lg-2 col-12 text-right pl-0">';

    echo $app->Html->link('', '#', [
      'icon' => $notification['unread'] == 1 ? 'dot-circle fa-lg' : 'circle fa-lg',
      'class' => 'mr-1 readToggle',
      'ia-bind' => 'notifications.read_toggle',
      'ia-pass' => $notification['id'],
      'ia-target' => '#{id} .far',
      'ia-toggleclass' => 'fa-dot-circle fa-circle',
    ]);

    echo '</div>'; // gombok

    echo '</div>'; // row
  }

  echo $app->Html->pagination(count($notifications), $pagination);

  echo '<p>';
  echo 'Az olvasatlan értesítések a lista elején láthatóak, utána időrendben mutatjuk a többit. Az olvasott értesítéseket 90 napig őrizzük meg, utána automatikusan töröljük.';
  echo '</p>';
} else {
  echo '<p class="text-muted">Itt most semmi sincs...</p>';
}