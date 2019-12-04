<?php
// Hova megyünk tovább login után
if (isset($_params->query['hopp'])) {
  $hopp = $_params->query['hopp'];
} elseif (strpos($_params->referer, APP['url']) !== false) {
  $hopp = $_params->referer;
}

echo '<div class="row">';

echo '<div class="col-md-6 mb-4">';

echo $app->Form->create($_params->data, [
  'method' => 'post'
]);

echo $app->Form->input('redirect_url', [
  'type' => 'hidden',
  'value' => @$hopp
]);

echo $app->Form->input('email', [
  'type' => 'email',
  'autocomplete' => 'on',
  'label' => 'Regisztrált email cím',
  'required' => true,
  'class' => 'focus'
]);

echo $app->Form->input('password', [
  'type' => 'password',
  'autocomplete' => 'on',
  'label' => 'Jelszó',
  'required' => true
]);

echo $app->Form->input('remember_me', [
  'type' => 'checkbox',
  'label' => 'Maradjak belépve itt',
  'value' => 1,
  'checked' => false
]);

echo '<p class="mt-1 float-right">';
echo $app->Html->link('Belépési probléma?', '/tagsag/bejelentkezesi-segitseg');
echo '</p>';

echo $app->Form->end('Belépés', ['class' => 'btn-primary']);


echo '</div>'; // col
echo '<div class="col-md-6 mb-4">';

echo '<h4 class="title">Hasznos linkek</h4>';

echo '<p>Nincs még Köztérkép hozzáférésed?<br />';
echo $app->Html->link('Regisztrálj most!', '/tagsag/regisztracio', [
  'class' => 'font-weight-bold'
]);
echo '</p>';

echo '<p>Elfelejtetted a jelszavad vagy más problémád van?<br />';
echo $app->Html->link('Belépési segítség kérése', '/tagsag/bejelentkezesi-segitseg', [
  'class' => 'font-weight-bold'
]);
echo '</p>';

echo '</div>'; // col

echo '<div class="col-md-12 mb-4">';
echo '<p class="text-muted">Csak akkor jelöld az "Maradjak belépve itt" mezőt, ha más által nem használt, saját eszközödön jelentkezel be. A belépéshez engedélyezned kell a cookie-kat böngésződben (<a href="/oldalak/adatkezelesi-szabalyzat#cookie">bővebben a cookie-król</a>).</p>';
echo '</div>'; // col

echo '</div>'; // row


?>
