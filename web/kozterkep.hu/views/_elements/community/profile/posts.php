<?php
if (count($posts) > 0) {
  echo '<hr class="my-3" />';
  echo '<h5 class="subtitle">Legutolsó blogbejegyzések</h5>';
  echo $app->element('posts/list');
  echo '<hr class="my-3" />';
}