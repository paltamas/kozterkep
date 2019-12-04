<?php
if (!$_user) {

  echo '<div class="row">';
  echo '<div class="col-md-6 mb-4">';
  echo '<h4 class="subtitle mb-3">Áttekintés</h4>';

  echo $app->Html->dl('create');
  echo $app->Html->dl(['Hozzászólások', $comment_count . ' db']);
  echo $app->Html->dl('end');

  echo '<div class="text-muted">További részletek belépés után.</div>';

  echo '</div>';
  echo '<div class="col-md-6">';
  echo $app->element('users/only_users', [
    'options' => ['alert' => [
      '<strong>Új infórmációd van?</strong> Jelentkezz be, ha segítenél ezzel az adatlappal kapcsolatban.',
      'info',
    ]]
  ]);
  echo '</div>';
  echo '</div>';

} else {
  echo $app->element('artists/edit/editcom');
}