<?php
if (count($descriptions) > 0) {
  $i = 0;

  foreach ($descriptions as $description) {
    $i++;
    echo $app->element('artpieces/view/story_item', [
      'description' => $description,
      'options' => [
        'min_time' => $artpiece['published'] > 0 ? $artpiece['published'] : false,
      ]
    ]);

    if ($i == 1 && $artpiece['links']) {
      echo '<div class="bg-light rounded px-3 py-3 mt-3">';
      echo '<div class="font-weight-semibold text-muted mb-1"><span class="far fa-link mr-1"></span>További linkek</div>';
      echo nl2br($artpiece['links'], false);
      echo '</div>';
    }

  }
}

/*echo '<div class="mt-4">';
echo $app->Html->link('Saját sztori, adalék hozzáadása', $_editable . '#szerk-sztorik', [
  'icon' => 'pencil',
]);
echo '</div>';*/