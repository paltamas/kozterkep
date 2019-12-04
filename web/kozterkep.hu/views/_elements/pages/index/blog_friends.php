<?php
if (count($blog_friends)) {
  echo '<div class="float-right text-muted fade-icons" title="Blogger barátaink, akik köztéri szobrokról írnak. Itt lennél? Keresd az üzemgazdát!" data-toggle="tooltip">';
  echo $app->Html->icon('info-circle');
  echo '</div>';

  echo '<h5 class="subtitle mb-3">Blogbarátaink friss posztjai</h5>';

  $i = 0;
  foreach ($blog_friends as $post) {
    $i++;
    $post = ['feed_id' => $post['feed_id']] + $post['last_post'];
    $blog = sDB['blog_friends'][$post['feed_id']];
    echo '<h5 class="font-weight-bold mb-1">';
    echo $app->Html->link($post['title'], $post['link'], [
      'target' => '_blank',
    ]);
    echo '</h5>';
    echo $app->Html->link($blog['title'], $blog['home'], [
      'target' => '_blank',
      'class' => 'text-dark',
    ]);
    echo '<span class="ml-2 text-muted">@ ' . _time($post['published']) . '</span>';

    echo '<div class="my-1">';
    echo $app->Text->truncate($post['description'], 250);

    echo $app->Html->link('Tovább...', $post['link'], [
      'class' => 'text-dark ml-2',
      'target' => '_blank',
      'title' => $blog['title'] . ' bejegyzésének megnyitása új böngésző fülön',
      'icon_right' => 'external-link text-muted',
    ]);
    echo '</div>';

    echo '<hr class="my-3" />';
  }

}