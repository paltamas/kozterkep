<?php
if ($_user['admin'] == 0) {
  echo '<div class="alert alert-secondary">Jelenleg az oldalak szerkesztése nem érhető el számodra.</div>';
} else {

  echo $app->Form->create($page, [
    'method' => 'post'
  ]);

  echo $app->Form->input('title', [
    'label' => 'Cím'
  ]);

  echo $app->Form->input('content', [
    'type' => 'textarea',
    'label' => 'Tartalom',
    'class' => 'html-editor',
  ]);

  echo $app->Form->end('Módosítások mentése');

}
?>
<?=$app->element('pages/tabs', ['selected' => 3])?>
