<h5 class="subtitle mb-3">Blogbejegyzések</h5>
<?=$app->element('posts/list', [
  'posts' => $member_posts,
  'options' => [
    'intro' => [
      //'image' => false,
    ],
  ]
])?>