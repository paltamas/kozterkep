<?php
echo $app->Form->create($description, [
  'method' => 'post',
  'action' => '/alkotok/szerkesztes/' . $description['artist_id']
]);

echo $app->Form->input('description_id', [
  'value' => $description['id'],
  'type' => 'hidden',
]);

echo $app->Form->input('text', [
  'type' => 'textarea'
]);

echo $app->Form->end('Mentés', ['name' => 'save_description']);