<?php
echo '<div class="my-3 text-break small text-center text-md-left">';
echo $app->Users->name($artpiece['creator_user_id'], [
  'image' => true,
  'class' => 'font-weight-bold'
]);
if ($artpiece['creator_user_id'] == $artpiece['user_id']) {
  echo $app->Users->contact_link($artpiece['creator_user_id'], [
    'artpiece_id' => $artpiece['id'],
    'link_options' => [
      'icon' => 'comment-alt',
      'title' => 'Beszélgetés indítása erről a műlapról',
      'class' => 'px-1',
    ]
  ]);
}
echo $artpiece['creator_user_id'] == $artpiece['user_id'] ? ' műlapja' : ' készítette a műlapot';
echo '</div>';