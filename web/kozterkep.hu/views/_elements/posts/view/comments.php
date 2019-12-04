<?php
echo '<h4 class="subtitle">Hozzászólások</h4>';
if ($post['comments_blocked'] == 1) {
  echo '<p class="text-muted">A bejegyzésnél jelenleg nincs lehetőség hozzászólás rögzítésére.</p>';
} elseif ($post['status_id'] == 5) {
  echo $app->element('comments/thread', [
    'model_name' => 'post',
    'model_id' => $post['id'],
    'files' => false,
  ]);
} else {
  echo '<p class="text-muted">Publikus bejegyzéseknél lehetséges a hozzászólás.</p>';
}