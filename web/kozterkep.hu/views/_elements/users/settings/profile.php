<?php
echo $app->Form->create($_user, [
  'method' => 'post',
  'id' => 'Form-Users-Settings-Profile',
  'ia-form-change-alert' => 1,
]);

echo '<div class="row m-0">';


echo '<div class="col-sm-6 col-md-4">';

echo $app->Form->input('name', [
  'label' => 'Megjelenített név',
  'help' => 'Ezt mutatjuk a listákban, mindenhol.'
]);

echo $app->Form->input('nickname', [
  'label' => 'Becenév, vagy amit szeretnél',
  'help' => 'Az infóablakban és a profil oldaladon jelenik meg. Nem kötelező.'
]);

if ($_user['email_to_confirm'] != '') {
  echo '<div class="alert alert-warning"><span class="far fa-exclamation-triangle mr-2"></span><strong>Emailcímed módosítása folyamatban!</strong> Az új <strong>' . $_user['email_to_confirm'] . '</strong> címre küldött aktivációs linkkel véglegesítheted a változást.  ' . $app->Html->link('Módosítás megszakítása', '/tagsag/beallitasok?email-modositas-torlese') . '.</div>';
}

echo $app->Form->input('new_email', [
  'type' => 'email',
  'label' => 'Email cím',
  'value' => $_user['email'],
  'help' => 'Belépéshez és külső értesítésekhez használt címed. Megváltoztatását konfirmálni kell kiküldött aktivációs linkkel.'
]);

echo $app->Form->input('introduction', [
  'type' => 'textarea',
  'label' => 'Bemutatkozásod',
  'help' => 'Miért vagy itt, mi a célod? Bármi, amit elmondanál nekünk magadról!'
]);

echo $app->Form->input('blog_title', [
  'label' => 'KT blogod címe',
  'help' => 'Ha kitöltöd, akkor akkor Köztérképes blogbejegyzéseidnél ez szerepel, egyébként a neved.'
]);

echo $app->Form->input('hide_location_events', [
  'type' => 'checkbox',
  'value' => 1,
  'label' => 'Helyzetemhez kapcsolódó események rejtése a profilomon',
  'help' => 'Ha ezt jelölöd, akkor nem mutatjuk azokat a friss eseményeket a profilodon, amelyek érintésekről vagy térkapszula feltörésekről tanúskodnak. Ez a rejtés csak a profilodra vonatkozik.'
]);

echo '</div>'; // col --



echo '<div class="col-sm-6 col-md-4">';

echo '<h5>Profilkép</h5>';
$image = $app->Users->profile_image($_user, 2);
$link_target = $app->Users->profile_image($_user, 1, ['only_path' => true]);
echo $app->Html->link($image, $link_target, [
  'target' => '_blank',
]);
if ($_user['profile_photo_filename'] != '') {
  echo $app->Form->input('delete_profile_photo', [
    'type' => 'checkbox',
    'value' => 1,
    'label' => 'Jelenlegi profilkép törlése',
    'divs' => 'mt-2',
    'help' => 'Ha törlöd profilképed, akkor az alapértelmezett profilkép jelenik meg a neved mellett és a profilodon.'
  ]);
}

echo $app->Form->input('profile_photo', [
  'type' => 'file',
  'label' => 'Profilkép feltöltése',
  'help' => 'Ha új profilképet töltesz fel, a korábbi törlődik.',
  'divs' => 'mb-5',
  'accept' => 'image/*',
]);



if ($_user['link_changed'] == 0) {
  echo $app->Form->input('link', [
    'label' => 'Profil link',
    'help' => 'Egyszer módosítható link, ami a saját profilodra mutat.'
  ]);
} else {
  echo 'Profil link:';
  echo $app->Html->link($_user['link'], '', [
    'user' => $_user,
    'class' => 'ml-1 font-weight-bold',
  ]);
  echo $app->Form->help('Egyedi profil linked, amit már megváltoztattál egyszer, így ez most már nem módosítható.', ['class' => 'mt-0 mb-3']);
}

echo $app->Form->input('web_links', [
  'type' => 'textarea',
  'label' => 'Weboldalaid',
  'help' => 'Külső blogod, saját weblapod, közösségi profiljaid. Soronként egy linket adj meg.'
]);

echo $app->Form->input('place_name', [
  'label' => 'Lakóhelyed',
  'help' => 'Nem kötelező megadni. Abban segít minket, hogy ha környékbeli alkotással kapcsolatos kérdésünk van, esetleg kereshetünk téged is.'
]);

echo '</div>'; // col --




echo '<div class="col-sm-6 col-md-4">';
echo '<h5>Fejléckép</h5>';
if ($_user['header_photo_filename'] != '') {
  echo '<p>Ez a teljes kép, de a profil oldalon a közepéből mutatunk egy 250 pixel magas sávot normál kijelzőn. Kisebb kijelzőkön a teljes kép látszik.</p>';

  $image = $app->Html->tag('img', '', [
    'src' => '/tagok/' . $_user['header_photo_filename'],
    'class' => 'img-fluid rounded'
  ]);
  echo $app->Html->link($image, '/tagok/' . $_user['header_photo_filename'], [
    'target' => '_blank',
  ]);

  echo $app->Form->input('delete_header_photo', [
    'type' => 'checkbox',
    'value' => 1,
    'label' => 'Jelenlegi fejléckép törlése',
    'divs' => 'mt-2',
    'help' => 'Ha törlöd fejlécképed, nem jelenik meg semmilyen fejléckép a profilodon.'
  ]);
}

echo $app->Form->input('header_photo', [
  'type' => 'file',
  'label' => 'Fejléckép feltöltése',
  'help' => 'Ha új fejlécképet töltesz fel, a korábbi törlődik.',
  'divs' => 'mb-5',
]);

echo '</div>'; // col --


echo '<div class="col-12 text-center">';
echo $app->Form->submit('Módosítások mentése', [
  'name' => 'profil',
  'class' => 'btn-primary'
]);
echo '</div>'; // col --


echo '</div>'; // row --

echo $app->Form->end();
?>

<div class="mt-5">
  <h4 class="title">Szeretnéd megszüntetni a tagságodat?</h4>
  <p>Amennyiben végérvényesen törölnéd hozzáférésedet, kattints az alábbi
    linkre a következő lépéshez.</p>
  <?php
  echo $app->Html->link('Profil törlés kezdeményezése', '/tagsag/profil-torlese', [
    'divs' => '',
    'icon' => 'trash'
  ]);
  ?>
</div>
