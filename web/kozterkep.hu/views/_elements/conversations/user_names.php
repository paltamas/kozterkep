<?php
$user_names = [];
$i = 0;

// Vastag, ha
foreach ($conversation['users'] as $user_id) {
  if ($_user['id'] != $user_id) {
    $i++;
    $user_names[$i] = '';

    $user = $app->MC->t('users', $user_id);

    $user_names[$i] .= $app->Users->profile_image($user);

    /*
     * Vastagítjuk az írót, de csak azt, ha van új.
     * Ez a logika szép, Gmail-es, de nehezen behozható, mert a
     * read_toggle togglézáskor már nagyon sok infót kell visszakapni, hogy a visszavastagításnál
     * tudjuk, hogy kell. De kérdés: hogy kell? Mármint amikor olvasatlanná teszünk egy levelet,
     * akkor mit? Most csak a folyamot lehet, így ez a vastagítás itt akkor lesz érdekes, ha csak _egyes_
     * üzeneteket is olvasatlanná lehet tenni egy folyamban; ami kb. őrjítő :)
     * ...és ez a Gmail-ben sem eléggé konzekvensen van megoldva.
     *
     * Most a vastagítás a meghívó helyen van.
     */

    /*$bold = @$unread && $conversation['messages'][count($conversation['messages']) - 1]['user_id'] == $user_id
      ? 'font-weight-bold' : '';*/

    if (@$user_link) {
      $name = $app->Users->name($user_id);
      //$name = $app->Html->link($app->MC->t('users', $user_id)['name'], '/kozosseg/profil/' . $app->MC->t('users', $user_id)['link']);
    } else {
      $name = $user ? $user['name'] : texts('torolt_tag_neve');
    }

    $user_names[$i] .= '<span>' . $name . '</span>';
  }
}
echo count($user_names) == 0 ? 'Saját magam' : implode(', ', $user_names);