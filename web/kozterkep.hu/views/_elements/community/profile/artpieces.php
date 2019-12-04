<?php
if (count($artpieces) > 0) {
  //echo '<div class="row pt-3 border-top mt-3">';
  echo '<div class="row">';

  echo '<div class="col-12">';
  echo '<h5 class="subtitle">Legfrissebb műlapok</h5>';
  echo '</div>';

  echo $app->element('artpieces/list/list', [
    'artpieces' => $artpieces,
    'options' => [
      'top_count' => 6,
    ]
  ]);

  echo '<div class="col-12 text-center text-md-left">';
  echo $app->Html->link('Műlapok listája', '/kereses?r=1&letrehozo=' . $user['id'] . '#hopp=lista', [
    'class' => 'btn btn-outline-primary',
    'icon_right' => 'arrow-right'
  ]);
  echo '</div>';

  echo '</div>';

  echo '<hr class="my-3" />';
}