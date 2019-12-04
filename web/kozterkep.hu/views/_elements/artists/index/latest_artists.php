<?php
$i = 0;
foreach ($latest_artists as $artist) {
  $i++;
  echo '<span class="mr-2">';
  echo $app->Artists->name($artist, [
    'class' => 'font-weight-bold',
    'tooltip' => true,
    'profession' => true,
  ]);
  echo $i < count($latest_artists) ? ',</span>' : '</span>';
}