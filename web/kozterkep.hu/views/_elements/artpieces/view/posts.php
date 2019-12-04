<?php
// itt próbálkoztam azzal, hogy csak a kiemelten kapcsolt műlap legyen,
// de túlságosan rágyógyultunk, és leírásként kezdték a tagok használni,
// így minden műlap kapcsolásnak itt kell lennie.

/*if (count($connected_posts) > 0) {
  $highlighted_posts = [];
  foreach ($connected_posts as $connected_post) {
    if ($connected_post['artpiece_id'] == $artpiece['id']) {
      $highlighted_posts[] = $connected_post;
    }
  }

  if (count($highlighted_posts) > 0) {
    echo '<hr class="my-3" />';
    echo '<h6 class="subtitle mb-2">Blogbejegyzések a műlapról</h6>';

    echo $app->element('posts/list', ['posts' => $highlighted_posts]);
  }
}*/
if (count($connected_posts) > 0) {
  echo '<hr class="my-3" />';
  echo '<h6 class="subtitle mb-2">Blogbejegyzések a műlapról</h6>';

  echo $app->element('posts/list', ['posts' => $connected_posts]);
}