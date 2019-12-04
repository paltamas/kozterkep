<div class="kt-info-box mb-5">
  <p class="lead mb-0">A Köztérkép legfontosabb felhasználói segédleteit mindig az adott űrlapok mellett láthatod. Az alábbi folyamatosan bővülő bejegyzések segítenek elmélyülni egy-egy témában, valamint hasznos tanácsokat, ötleteket tartalmaznak. Ha valamire sehol nem találsz választ, tedd fel kérdésed a <?=$app->Html->link('Köztér fórumában', '/kozter/forum-tema/4')?>.</p>
</div>

<?php
if (count($video_guides) > 0) {
  echo '<div class="row">';
  foreach ($video_guides as $video_guide) {
    echo '<div class="col-12 col-sm-6 col-md-4 mb-4">';
    echo '<div class="embed-responsive embed-responsive-16by9">';
    echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $video_guide['url'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    echo '</div>';
    echo '<h5 class="mt-3 mb-1">' . $app->Html->link($video_guide['title'], 'https://www.youtube.com/watch?v=' . $video_guide['url'], [
      'target' => '_blank',
      'title' => 'Új fülön nyitjuk a videót a Youtube-on',
    ]) . '</h5>';
    echo '<div>';
    echo $video_guide['description'];
    echo '<br />' . $app->Html->link('Videó megnyitása', 'https://www.youtube.com/watch?v=' . $video_guide['url'], [
      'icon_right' => 'external-link',
      'target' => '_blank',
      'title' => 'Új fülön nyitjuk a videót a Youtube-on',
    ]);
    echo '</div>';
    echo '</div>';
  }
  echo '</div>';

  echo '<hr class="highlighter text-center my-5" />';

}


if (count($posts) == 0 && count($posts_highlighted) == 0) {
  echo '<p class="lead"><span class="fal fa-info-circle mr-1"></span>Itt hamarosan segédleteket olvashatsz, most még dolgozunk rajtuk.</p>';
}

echo '<div class="row d-flex">';
echo $app->element('posts/list', [
  'posts' => $posts_highlighted,
  'options' => [
    'container' => 'col-sm-6 mb-5',
    'separator' => false,
  ]
]);
echo '</div>';

if (count($posts) > 0) {

  echo $app->element('posts/search_form', [
    'category' => $postcategory[2],
  ]);

  echo '<div class="mt-5 row d-flex justify-content-center">';
  echo '<div class="col-lg-8">';
  echo '<div class="row d-flex">';
  echo $app->element('posts/list', [
    'posts' => $posts,
    'options' => [
      'separator' => true,
    ]
  ]);

  echo '<div class="col-12 my-5 text-center">';
  echo $app->Html->link('Minden bejegyzés a témában', '/blogok/kereses?tema=' . $postcategory[2], [
    'class' => 'btn btn-outline-secondary',
    'icon_right' => 'arrow-right'
  ]);
  echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';