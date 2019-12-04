<h5 class="subtitle mb-3">Legutolsó események</h5>
<?php
if (count($events)) {
  $i = 0;
  foreach ($events as $event) {
    $i++;
    echo $app->element('events/item', ['event' => $event, 'options' => [
      'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
    ]]);
  }
} else {
  echo $app->Html->icon('ellipsis-h');
}