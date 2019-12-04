<?php
if (count($places) > 0) {
  echo '<div class="border-bottom mb-2 pb-2">';
  echo '<h5 class="subtitle">Hasonló települések</h5>';
  $i = 0;
  foreach ($places as $place) {
    $i++;
    if ($i > 7) {
      break;
    }
    echo '<div class="font-weight-bold mb-1">' . $app->Places->name($place['id']) . '</div>';
  }
  if ($i > 7) {
    echo '<p>' . $app->Html->link('Továbbiak', '/helyek/kereses?kulcsszo=' . $query['kulcsszo'], [
        'icon_right' => 'arrow-right'
      ]) . '</p>';
  }
  echo '</div>';
}


if (count($artists) > 0) {
  echo '<div class="border-bottom mb-2 pb-2">';
  echo '<h5 class="subtitle">Hasonló alkotók</h5>';
  $i = 0;
  foreach ($artists as $artist) {
    $i++;
    if ($i > 7) {
      break;
    }
    echo '<div class="font-weight-bold mb-1">' . $app->Artists->name($artist['id'], ['year' => true]) . '</div>';
  }
  if ($i > 7) {
    echo '<p>' . $app->Html->link('Továbbiak', '/alkotok/kereses?kulcsszo=' . $query['kulcsszo'], [
      'icon_right' => 'arrow-right'
    ]) . '</p>';
  }
  echo '</div>';
}


if (count($sets) > 0) {
  echo '<div class="border-bottom mb-2 pb-2">';
  echo '<h5 class="subtitle">Hasonló gyűjtemények</h5>';
  $i = 0;
  foreach ($sets as $set) {
    $i++;
    if ($i > 7) {
      break;
    }
    echo '<div class="font-weight-bold mb-1">' . $app->Html->link($set['name'], '', ['set' => $set]) . '</div>';
  }
  if ($i > 7) {
    echo '<p>' . $app->Html->link('Továbbiak', '/gyujtemenyek/kereses?kulcsszo=' . $query['kulcsszo'], [
        'icon_right' => 'arrow-right'
      ]) . '</p>';
  }
  echo '</div>';
}



if (count($users) > 0) {
  echo '<div class="kt-info-box mt-3">';
  echo '<h5 class="subtitle">Hasonló tagok</h5>';
  $i = 0;
  foreach ($users as $user) {
    $i++;
    if ($i > 7) {
      break;
    }
    echo '<div class="font-weight-bold mb-1">' . $app->Html->link($user['name'], '', ['user' => $user]) . '</div>';
  }
  if ($i > 7) {
    echo '<p>' . $app->Html->link('Továbbiak', '/tagok/kereses?kulcsszo=' . $query['kulcsszo'], [
        'icon_right' => 'arrow-right'
      ]) . '</p>';
  }
  echo '<div class="small mt-2 text-muted">Csak tagok látják ezt a dobozt.</div>';
  echo '</div>';
}


if (count($places) > 0 || count($artists) > 0 || count($sets) > 0 || count($users) > 0) {
  // Mobilon válasszuk el a részt
  echo '<div class="d-block d-md-none mt-4">';
  echo '<h5 class="subtitle">Műlapok</h5>';
  echo '</div>';
}