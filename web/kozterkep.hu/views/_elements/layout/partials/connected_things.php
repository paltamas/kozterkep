<?php
$options = (array)@$options + [
  'item_type' => 'comment',
  'only_files' => false,
  'things_class' => 'mt-2 small text-muted',
  'file_previews' => true,
];

if (!$options['only_files'] && (@$item['artpiece_id'] > 0 || @$item['artpiece_edits_id'] != ''
  || @$item['forum_topic_id'] > 0 || @$item['post_id'] > 0
  || @$item['artist_id'] > 0 || @$item['place_id'] > 0
  || @$item['folder_id'] > 0 || @$item['book_id'] > 0)) {

  echo '<div class="' . $options['things_class'] . '">';

  // Műlap
  if (@$item['artpiece_id'] > 0) {
    $artpiece = $app->MC->t('artpieces', $item['artpiece_id']);
    $margin = @$item['artpiece_edits_id'] == '' ? ' mr-3 ' : '';
    echo '<span class="' . $margin . $options['item_type'] . '-connected-artpiece" data-id="' . $item['artpiece_id'] . '">';
    echo $app->Html->link($artpiece['title'], '', [
      'artpiece' => $artpiece,
      'icon' => 'map-marker',
      'ia-tooltip' => 'mulap',
      'ia-tooltip-id' => $artpiece['id'],
      'class' => 'font-weight-semibold',
    ]);
    echo '</span>';
  }

  // Szerkesztés
  if (@$item['artpiece_edits_id'] != '') {
    echo '<span class="far fa-arrow-right mx-2"></span><span class="mr-3 ' . $options['item_type'] . '-connected-edit" data-id="' . $item['artpiece_edits_id'] . '">';
    echo $app->Html->link('Szerkesztéshez', '/mulapok/szerkesztes_reszletek/' . $item['artpiece_id'] . '/' . $item['artpiece_edits_id'], [
      'icon' => 'comment-edit',
      'class' => 'text-nowrap',
      'class' => 'font-weight-semibold',
    ]);
    echo '</span>';
  }


  // Fórumtéma
  if (@$item['forum_topic_id'] > 0) {
    $forum = $app->MC->t('forum_topics', $item['forum_topic_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-forum_topic" data-id="' . $item['forum_topic_id'] . '">';
    echo $app->Html->link($forum['title'], '/kozter/forum-tema/' . $item['forum_topic_id'], [
      'icon' => 'comments',
      'class' => 'text-nowrap font-weight-semibold',
    ]);
    echo '</span>';
  }


  // Poszt
  if (@$item['post_id'] > 0) {
    $post = $app->MC->t('posts', $item['post_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-post" data-id="' . $item['post_id'] . '">';
    echo $app->Html->link($post['title'], '', [
      'icon' => 'newspaper',
      'class' => 'text-nowrap font-weight-semibold',
      'post' => $post,
    ]);
    echo '</span>';
  }


  // Alkotó
  if (@$item['artist_id'] > 0) {
    $artist = $app->MC->t('artists', $item['artist_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-artist" data-id="' . $item['artist_id'] . '">';
    echo $app->Html->link($artist['name'], '', [
      'icon' => 'user',
      'class' => 'text-nowrap font-weight-semibold',
      'artist' => $artist,
    ]);
    echo '</span>';
  }


  // Hely
  if (@$item['place_id'] > 0) {
    $place = $app->MC->t('places', $item['place_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-place" data-id="' . $item['place_id'] . '">';
    echo $app->Html->link($place['name'], '', [
      'icon' => 'map-pin',
      'class' => 'text-nowrap font-weight-semibold',
      'place' => $place,
    ]);
    echo '</span>';
  }


  // Mappa
  if (@$item['folder_id'] > 0) {
    $folder = $app->MC->t('folders', $item['folder_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-folder" data-id="' . $item['folder_id'] . '">';
    echo $app->Html->link($folder['name'], '/mappak/megtekintes/' . $item['folder_id'], [
      'icon' => 'folder',
      'class' => 'text-nowrap font-weight-semibold',
    ]);
    echo '</span>';
  }

  // Könyv
  if (@$item['book_id'] > 0) {
    $folder = $app->MC->t('books', $item['book_id']);
    echo '<span class="mr-3 ' . $options['item_type'] . '-connected-book" data-id="' . $item['book_id'] . '">';
    echo $app->Html->link($folder['title'], '/adattar/konyv/' . $item['book_id'], [
      'icon' => 'book',
      'class' => 'text-nowrap font-weight-semibold',
    ]);
    echo '</span>';
  }

  echo '</div>';
}

// Kapcsolt fájl
if (@count(@$item['files']) > 0 && $options['file_previews']) {
  //echo '<hr class="my-2" />';
  echo '<div class="">';
  foreach ($item['files'] as $file) {
    echo $app->Html->link($file[1], '/mappak/fajl_mutato/' . $file[0], [
      'icon' => 'paperclip',
      'target' => '_blank',
      'class' => 'mr-3 small file-attachment font-weight-semibold',
    ]);
  }
  echo '</div>';
}