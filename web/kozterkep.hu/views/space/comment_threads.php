<?php
foreach ($threads as $thread) {

  $latest_comment = $thread['latest_comment'];
  $parent_comment = $thread['parent_comment'];

  echo '<div class="row comment-thread-row border rounded mb-3 py-2 mx-0">';
  echo '<div class="col-md-12 comment-thread-container px-2">';

  // ŐS KOMMENT

  // Idő
  echo '<div class="float-right fade-icons">';
  echo '<small class="text-muted">' . _time($parent_comment['created'], ['ago' => true]) . '</small>';
  echo '</div>';

  // User
  echo '<div class="mb-1">';
  echo $app->Users->profile_image($parent_comment['user_id']);
  echo '<span class="font-weight-bold mr-2 comment-thread-user">' . $app->Users->name($parent_comment['user_id']) . '</span>';
  echo '</div>';

  $connected_things = $app->element('layout/partials/connected_things', [
    'item' => $parent_comment,
    'options' => [
      'item_type' => 'comment',
      'file_previews' => false,
      'things_class' => 'small text-muted ml-1',
    ]
  ]);

  echo $connected_things == '' ? '' : $connected_things . '<hr class="my-2" />';

  // Szöveg
  echo '<div class="comment-thread-text">';
  echo $app->Text->read_more($parent_comment['text'], 100, true);
  echo '</div>';


  // UTOLSÓ GYERMEK
  echo '<div id="comment-row-' . $latest_comment['last_id'] . '" class="comment-row border-left mt-3 mb-3 pl-2 ml-4">';

  // Mennyi
  echo '<div class="text-muted mb-2"><strong>' . $thread['comment_count'] . ' válasz</strong>, ebből a legfrissebb:</div>';


  // User és idő
  echo '<div class="mb-2">';
  echo $app->Users->profile_image($latest_comment['user_id']);
  echo '<span class="font-weight-bold mr-2 comment-user">' . $app->Users->name($latest_comment['user_id']) . '</span>';
  echo '<span class="text-muted">(' . _time($latest_comment['created'], ['ago' => true]) . ')</span>';
  echo '</div>';

  // Szöveg
  echo '<div class="mb-2 comment-text">';
  echo $app->Text->read_more($latest_comment['text'], 100, true);
  echo '</div>';

  echo '<div class="my-1">';

  // Válasz; csak ha ajax, mert akkor ott van a balon a hsz form
  if ($_params->is_ajax) {
    echo $app->Html->link('Válasz', '#', [
      'icon' => 'reply',
      'class' => 'replyComment btn btn-outline-secondary btn-sm',
      'data-id' => $latest_comment['last_id'],
    ]);
  }


  echo $app->Html->link('Párbeszéd', '/kozter/parbeszed/' . $parent_comment['id'], [
    'icon' => 'stream',
    'class' => 'ml-3 text-dark small',
  ]);

  echo '</div>';



  echo '</div>'; // friss komment konténer

  // Kapcsolt fájlok
  echo $app->element('layout/partials/connected_things', [
    'item' => $parent_comment,
    'item_type' => 'comment',
    'options' => [
      'file_previews' => true,
      'only_files' => true,
    ]
  ]);

  echo '</div>'; // container --
  echo '</div>'; // row --
}