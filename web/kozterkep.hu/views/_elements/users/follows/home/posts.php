<h4 class="subtitle">Blogbejegyzések</h4>
<?=$app->Form->help('Követett településekhez, alkotókhoz kapcsolódó, vagy követett tagok által írt blogbejegyzések.')?>

<?php
if (count($posts) > 0) {
  echo '<div class="mt-2">';
  foreach ($posts as $post) {
    echo '<div class="p-2 mb-3 border rounded">';
    echo $app->Blog->intro($post);
    echo '</div>';
  }
  echo '</div>'; // --
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>