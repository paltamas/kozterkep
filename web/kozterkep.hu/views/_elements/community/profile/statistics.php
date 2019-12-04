<?php
if ($user['harakiri'] == 1) {
  return;
}

$options = (array)@$options + [
  'stat_link' => true,
  'container' => 'col-6 col-sm-3 col-md-6 col-lg-4 my-3',
];

?>
<div class="row">
  <?php
  $numbers = [
    'artpiece_count' => ['műlap', 'map-marker', '/kereses?r=1&letrehozo=' . $user['id']],
    'photo_count' => ['fotó összesen', 'image', '/fotok/kereses?tag=' . $user['id']],
    'photo_other_count' => ['fotó máshoz', 'images', '/fotok/kereses?tag=' . $user['id'] . '&kihez=mashoz'],
    'edit_other_count' => ['szerk. másnál', 'edit', '/kozter/szerkesztesek?tag=' . $user['id']],
    'description_other_count' => ['leírás másnál', 'paragraph', '/kozter/szerkesztesek?tag=' . $user['id']],
    'set_count' => ['gyűjtemény', 'tags', '/gyujtemenyek/kereses?tag=' . $user['id']],
    'comment_count' => ['hozzászólás', 'comments', '/kozter/forum?tag=' . $user['id']],
    'post_count' => ['blogposzt', 'pen-nib', '/blogok/tag/' . $user['link']],
    'folder_count' => ['mappa', 'folders', '/mappak/kereses?tag=' . $user['id']],
    'hug_count' => ['érintés', 'hand-point-up', false],
    'spacecapsule_count' => ['térkapszula', 'box-open', false],
    'book_count' => ['könyv', 'book', '/adattar/konyvter'],
  ];

  foreach ($numbers as $field => $params) {
    if ($user[$field] == 0) {
      continue;
    }
    echo '<div class="' . $options['container'] . ' text-center d-flex">';
    if ($params[2]) {
      echo '<a href="' . $params[2] . '" class="nu bg-gray-kt rounded p-2 w-100 d-block">';
    } else {
      echo '<span class="bg-gray-kt rounded p-2 w-100 d-block">';
    }
    echo '<span class="text-nowrap text-dark">';
    echo $app->Html->icon($params[1], ['class' => 'mr-1']);
    echo _n($user[$field]);
    echo '</span>';
    echo '<br/><span class="text-muted small">' . $params[0] . '</span>';
    echo $params[2] ? '</a>' : '</span>';
    echo '</div>';
  }

  if ($options['stat_link']) {
    echo '<div class="col-12 text-center mt-2">' . $app->Html->link('Részletes statisztikák', '/kozosseg/tag_statisztikak/' . $user['link'], [
        'icon' => 'user-chart'
      ]) . '</div>';
  }
  ?>
</div>