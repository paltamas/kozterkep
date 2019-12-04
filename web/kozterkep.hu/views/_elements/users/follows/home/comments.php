<h4 class="subtitle">Hozzászólások</h4>
<?=$app->Form->help('Követett tagok általi, vagy követett műlapokra, alkotóhoz, helyekhez és mappákhoz érkező hozzászólások.', ['class' => 'mb-3'])?>

<?php
if (count($comments) > 0) {
  foreach ($comments as $comment) {
    echo $app->element('comments/item', [
      'comment' => $comment,
      'options' => ['buttons' => false]
    ]);
  }
} else {
  echo $app->element('layout/partials/empty', ['class' => 'my-1']);
}
?>