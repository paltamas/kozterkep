<span class="text-muted float-right font-italic">itt közlünk</span>
<h5 class="subtitle mb-3 text-dark">Friss hírek</h5>

<?php
echo $app->Blog->intro($admin_posts[0], [
  'title_size' => 4,
  'blog_name' => false,
]);
unset($admin_posts[0]);

foreach ($admin_posts as $post) {
  echo '<hr class="my-2" />';
  echo '<div>';
  echo $this->Html->link($post['title'], '', [
    'post' => $post,
    'class' => 'font-weight-bold'
  ]);
  echo ' <span class="text-muted mx-1">' . _date($post['published']) . '</span> ';
  echo $this->Text->truncate($post['intro'], 100, [
    'nl2br' => false,
    'format' => false,
    'strip_tags' => true,
  ]);
  echo '</div>';
}
?>