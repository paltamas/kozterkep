<?php
$options = (array)@$options + [
  'link' => true,
  'introduction' => false,
  'class' => 'rounded border p-1 w-100',
];

$highlighted = $user['highlighted'] >= strtotime('last monday 00:00', strtotime('Sunday'))
  ? '<span class="fas fa-medal fas text-primary mr-1" title="Heti kiemelt szerkesztőnk" data-toggle="tooltip"></span>' : '';

echo '<div class="' . $options['class'] . '">';

echo '<div class="row mx-0">';

$profile_image = $app->Users->profile_image($user, 3);

if ($profile_image == '') {
  $profile_image = '<span class="far fa-user-circle fa-2x text-muted d-block mr-5"></span>';
}

echo '<div class="col-3 pr-0 pt-1">' . $profile_image . '</div>';

echo '<div class="col-9 pl-1">';
echo '<h4 class="mb-1">' . $highlighted . $app->Users->name($user, [
  'link' => $options['link'],
  'tooltip' => false,
]) . '</h4>';
echo $user['nickname'] != '' ? $user['nickname'] . ', ' : '';
echo '<span class="text-muted">itt járt: <span class="font-weight-bold">' . _time($user['last_here'], [
    'ago' => true,
    'privacy' => true
  ]) . '</span></span>';
echo '</div>';


$activity = [];
if ($user['artpiece_count'] > 0) {
  $activity[] = '<strong>' . _n($user['artpiece_count']) . '</strong>'
    . $app->Html->icon('map-marker ml-1 mr-2');
}
if ($user['photo_count'] > 0) {
  $activity[] = _n($user['photo_count']) . $app->Html->icon('images ml-1 mr-2');
}
if ($user['edit_other_count'] > 0) {
  $activity[] = _n($user['edit_other_count']) . $app->Html->icon('edit ml-1 mr-2');
}
if ($user['description_other_count'] > 0) {
  $activity[] = _n($user['description_other_count']) . $app->Html->icon('paragraph ml-1 mr-2');
}
if (count($activity) > 0) {
  echo '<div class="col-12 mt-2 mb-1 pt-2 small text-muted border-top">';
  echo implode('', $activity);
  echo '</div>';
}


echo '</div>'; // row --

if ($options['introduction']) {
  echo $user['introduction'] != '' ? '<div class="mt-1 pt-2 border-top">' . $app->Text->truncate($user['introduction'], 150) . '</div>' : '';
}

echo '<div class="mb-2"></div>';

echo '</div>'; // container --