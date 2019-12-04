<h5 class="subtitle">Friss hírek és segédletek</h5>
<?php
if (count($video_guides) > 0) {
  foreach ($video_guides as $video_guide) {
    if ($video_guide['highlighted'] == 1
      && strtotime($video_guide['time']) > strtotime('-2 weeks')) {
      echo '<div class="row align-items-center">';
      echo '<div class="col-7 pr-1">';
      echo '<div class="embed-responsive embed-responsive-16by9 border rounded">';
      echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $video_guide['url'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
      echo '</div>'; // embed-resp --
      echo '</div>'; // col --
      echo '<div class="col-5 pl-1">';
      echo '<div class="text-muted">Friss videó</div>';
      echo $app->Html->link($video_guide['title'], 'https://www.youtube.com/watch?v=' . $video_guide['url'], [
        'icon' => 'video',
        'class' => 'font-weight-bold',
        'target' => '_blank',
        'title' => 'Új fülön nyitjuk a videót a Youtube-on',
      ]);
      echo '<div class="text-muted"><small>' . _time($video_guide['time']) . '</small></div>';
      echo '</div>'; // col --
      echo '</div>'; // row --
      echo '<hr class="my-3" />';
      break;
    }
  }
}

echo $app->element('posts/list');
?>