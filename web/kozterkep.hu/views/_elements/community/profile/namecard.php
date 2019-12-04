<?php
$options = (array)@$options + [
  'simple' => false,
  'name_link' => false,
];


// kép
$image_path = $app->Users->profile_image($user, 2, ['only_path' => true]);
$image_path_full = $app->Users->profile_image($user, 1, ['only_path' => true]);

$image = $app->Html->tag('img', '', [
  'src' => $image_path,
  'class' => 'img-fluid rounded-circle shadow',
]);

if ($user['profile_photo_filename'] != '') {
  echo $app->Html->link($image, $image_path_full, ['target' => '_blank']);
} else {
  echo $image;
}
// kép --

echo '<div class="kt-info-box pt-5" style="margin-top: -40px">';

// Név
if ($options['name_link']) {
  echo '<h4 class="mb-0">' . $app->Html->link($user['name'], '', [
    'user' => $user
  ]) . '</h4>';
} else {
  echo '<h4 class="mb-0">' . $user['name'] . '</h4>';
}
echo '<h6 class="text-muted mb-0">' . $user['nickname'] . '</h6>';

// Szerep
$user_roles = sDB['user_roles'];
if (isset($user_roles[$user['id']])) {
  echo '<div class="mt-3">';
  echo $app->Html->icon('user-tag', [
    'class' => 'mr-1 text-muted'
  ]);
  echo '<span class="text-muted font-weight-bold">' . $user_roles[$user['id']] . '</span>';
  echo '</div>';
}

if ($user['user_level'] == 1) {
  echo '<div class="my-3 text-dark font-weight-bold" title="Közösségünk legaktívabb tagjai: önállóan publikálják műlapjaikat, szavazhatnak a közösségi szerkesztés során." data-toggle="tooltip">';
  echo $app->Html->icon('id-card-alt', [
    'class' => 'mr-1'
  ]);
  echo 'törzstag</div>';
}

if ($user['highlighted'] >= strtotime('last monday 00:00', strtotime('Sunday'))) {
  echo '<div class="my-3 font-weight-bold p-3 bg-gray-kt rounded text-orange-dark">' . $app->Html->icon('medal fas mr-1 fa-lg') . 'Heti kiemelt szerkesztőnk</div>';
}


if (!$options['simple']) {

  // Bemutatkozás
  if ($user['introduction'] != '') {
    echo '<hr class="highlighter text-center my-4" />';
    echo $app->Text->read_more($user['introduction'], 300, true);
  }

  echo '<hr class="highlighter text-center my-4" />';

  // webes linkek
  if ($user['web_links'] != '') {
    echo '<div class="mb-2"><span class="text-muted">Linkek:</span> ';
    echo $app->Text->format($user['web_links']);
    echo '</div>';
  }

  // Lakhely
  if ($user['place_name'] != '') {
    echo '<div class="mb-2"><span class="text-muted">Lakóhely:</span> ';
    echo $user['place_name'];
    echo '</div>';
  }

  if ($user['harakiri']) {
    echo '<div class="my-5">A profilt törölte a tulajdonosa.</div>';
  } else {
    // Utoljára itt
    echo '<div class="mb-2"><span class="text-muted">Utoljára itt járt:</span> ';
    echo _time($user['last_here'], [
      'ago' => true,
      'privacy' => true
    ]);
    echo '</div>';
  }


  // Reg. ideje
  echo '<div class="mb-2"><span class="text-muted">Regisztráció:</span> ';
  echo _time($user['created']);
  echo '</div>';


  // Közr. ráta, 0.01 felett; de most nem mutatjuk,
  // mert megnézzünk, élünk-e anélkül, hogy túlmatekoznánk az életet
  // miközben azt hisszük, hogy ez motivál, vagy legalábbis leír.
  // de lehet, hogy valójában csak skatulyáz és szögletes határok közé
  // pakol. Ezt meg nem akarjuk: ez tévút. 13 év alatt kiderült, na.
  // programozói agyak túlkapása
  if ($user['contribution_rate'] > 0.1 && 1 == 2) {
    echo '<div class="mt-4 mb-2 bg-gray-kt rounded p-2">';
    echo $user['name'] . ' tagunk munkája a Köztérkép adatbázisának <strong class="text-nowrap">' . _n($user['contribution_rate'], 1) . ' százalékát</strong> teszi ki.';
    echo '</div>';
  }
}

echo '</div>';
