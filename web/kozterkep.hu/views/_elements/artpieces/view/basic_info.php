<?php
echo '<hr />';

if ($artpiece['publish_at'] > 0) {
  echo '<div class="kt-info-box small">';
  echo '<strong><span class="fas fa-hourglass-half mr-1"></span>Késleltetett publikálásra vár.</strong>';
  echo '<br /><span class="text-muted">Tagunk elérte a heti limitjét, ezért ' . _time($artpiece['publish_at']) . '-kor automatikusan publikáljuk a műlapot, ha minden feltétel adott.</span>';
  echo '</div>';
}

echo $app->Html->dl('create', ['class' => 'row small text-muted link-not-muted mb-0 mt-3']);

if ($artpiece['creator_user_id'] != $artpiece['user_id']) {
  echo $app->Html->dl(['Kezeli', $app->Users->name($artpiece['user_id'])
    . $app->Users->contact_link($artpiece['user_id'], [
      'artpiece_id' => $artpiece['id'],
      'link_options' => [
        'icon' => 'comment-alt',
        'title' => 'Beszélgetés indítása erről a műlapról',
        'class' => 'px-1',
      ]
    ])]);
}


if ($artpiece['status_id'] != 5) {
  $status = sDB['artpiece_statuses'][$artpiece['status_id']];
  echo $app->Html->dl(['Státusz', '<span class="badge badge-lg badge-' . $status[1] . '">' . $status[0] . '</span>']);
}
echo $app->Html->dl(['Azonosító', $artpiece['id']]);
if ($artpiece['status_id'] == 5) {
  echo $app->Html->dl(['Látogatás', '<span class="view-stats" data-toggle="tooltip" title="Publikálás óta mért egyedi megtekintések száma. Valós egyedi megtekintést mér, emiatt nem egyenlő a Webstatban megjelenő oldalletöltés vagy látogatás számmal.">' . _loading() . '</span>']);
}

if ($artpiece['status_id'] != 5 || $artpiece['updated'] > ($artpiece['published']+300)) {
  // Ha publikus, akkor a publikálás után 5 percen belüli rámentegetések miatt még nem mutatjuk
  echo $app->Html->dl(['Frissítve', _time($artpiece['updated'])]);
}
if ($artpiece['status_id'] == 2) {
  echo $app->Html->dl(['Beküldve', _time($artpiece['submitted'])]);
}
echo $app->Html->dl(['Publikálva', _time($artpiece['published'])]);




// Közreműködők összeszedáse
$contributors = [];
foreach ($photos as $photo) {
  if ($photo['user_id'] > 0 && !in_array($photo['user_id'], $contributors) && !in_array($photo['user_id'], [$artpiece['creator_user_id'], $artpiece['user_id']])) {
    $contributors[] = $photo['user_id'];
  }
}
foreach ($edits as $edit) {
  if ($edit['user_id'] > 0 && !in_array($edit['user_id'], $contributors) && !in_array($edit['user_id'], [$artpiece['creator_user_id'], $artpiece['user_id']])) {
    $contributors[] = $edit['user_id'];
  }
}
foreach ($descriptions as $description) {
  if ($description['user_id'] > 0 && !in_array($description['user_id'], $contributors) && !in_array($description['user_id'], [$artpiece['creator_user_id'], $artpiece['user_id']])) {
    $contributors[] = $description['user_id'];
  }
}

if (count($contributors) > 0) {
  echo $app->Html->dl(['Szerkesztések', $app->Users->namelist($contributors)]);
}

echo $app->Html->dl('end');

echo '<div class="praise-votes font-weight-bold"></div>';
