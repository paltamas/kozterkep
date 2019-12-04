<h5 class="subtitle"><?=$title?></h5>
<?php
foreach ($posts as $post) {
  echo '<div class="p-2 mb-3 border rounded">';
  echo $app->Blog->intro($post);
  echo '</div>';
}
?>