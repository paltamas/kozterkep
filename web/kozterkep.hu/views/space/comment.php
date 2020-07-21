<?php
if (!$comment) {
  echo 'Nincs ilyen hozzászólás.';
  return;
}


echo $app->element('comments/item', ['comment' => $comment, 'options' => [
  'buttons' => false,
]]);


if ($app->Users->owner_or_head($comment, $_user) && $comment['created'] > strtotime('-10 days')) {
  echo $app->Html->link('Szerkesztés', '#', [
    'icon' => 'edit',
    'class' => 'btn btn-outline-primary btn-sm mr-2 mb-3',
    'ia-bind' => 'comments.edit',
    'ia-pass' => $comment['id'],
  ]);
}

if (@$commented_artpiece['user_id'] > 0) {
  if ($app->Users->owner_or_head($commented_artpiece, $_user) || $comment['user_id'] == $_user['id']) {
    echo $app->Html->link('Legyen sztori!', '#', [
      'icon' => 'paragraph',
      'class' => 'btn btn-outline-primary btn-sm mr-2 mb-3',
      'ia-bind' => 'comments.story_convert',
      'ia-pass' => $comment['id'],
      'ia-vars-artpiece_id' => $comment['artpiece_id'],
      'title' => 'A sztorivá minősítéskor a hozzászólást töröljük és készítünk egy azonos szövegű új szerkesztést, amiben a szöveget sztoriként küldjük a műlapra. Használd akkor, ha értékes adalék van itt, részletesen kifejtve és szükség esetén megfelelően forrásolva.',
    ]);
  }

  if ($app->Users->owner_or_head($commented_artpiece, $_user)) {
    if (@$comment['highlighted'] > 0) {
      echo $app->Html->link('Ne legyen kiemelt', '#', [
        'icon' => 'angle-double-down',
        'class' => 'btn btn-primary btn-sm mr-2 mb-3',
        'ia-bind' => 'comments.highlight_toggle',
        'ia-pass' => $comment['id'],
        'ia-vars-artpiece_id' => $comment['artpiece_id'],
      ]);
    } else {
      echo $app->Html->link('Kiemelés', '#', [
        'icon' => 'angle-double-up',
        'class' => 'btn btn-outline-primary btn-sm mr-2 mb-3',
        'ia-bind' => 'comments.highlight_toggle',
        'ia-pass' => $comment['id'],
        'ia-vars-artpiece_id' => $comment['artpiece_id'],
        'title' => 'A kiemeléssel ' . sDB['limits']['comments']['highlight_months'] . ' hónapig megjelenik ez a hozzászólás a műlap publikus felületén. Használd ezt, ha aktuális és lényeges hírmorzsa ez a komment, de sztorivá nem érdemes minősíteni, mert elavul.',
      ]);
    }
  }
}

if (@$comment['artist_id'] > 0) {
  $commented_artist = $app->MC->t('artists', $comment['artist_id']);
  if ($app->Users->is_head($_user) || $_user['id'] == CORE['USERS']['artists']) {
    echo $app->Html->link('Legyen adalék!', '#', [
      'icon' => 'paragraph',
      'class' => 'btn btn-outline-primary btn-sm mr-2 mb-3',
      'ia-bind' => 'comments.artist_description_convert',
      'ia-pass' => $comment['id'],
      'ia-vars-artist_id' => $comment['artist_id'],
      'ia-confirm' => 'Biztosan adalékká minősíted? Persze utána is vissza tudod minősíteni hozzászólássá.'
    ]);
  }
}

/*echo $app->Html->link('Áthelyezés', '#', [
  'icon' => 'arrow-to-right',
  'class' => 'btn btn-outline-primary mr-2 mb-3'
]);*/

if ($app->Users->is_head($_user)) {
  echo $app->Html->link('Törlés', '#', [
    'icon' => 'trash',
    'class' => 'btn btn-outline-primary btn-sm mr-2 mb-3',
    'ia-confirm' => 'Biztosan törlöd ezt a hozzászálást?',
    'ia-bind' => 'comments.delete',
    'ia-pass' => $comment['id'],
  ]);
}

echo $app->Html->link('Link másolása', '#', [
  'icon' => 'link',
  'class' => 'btn btn-link float-right',
  'ia-copy-this' => APP['url'] . '/kozter/komment/' . $comment['id'],
]);