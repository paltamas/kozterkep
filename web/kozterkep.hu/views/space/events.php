<?php
$options = (array)@$options + [
  'hide_after' => false,
  'highlight' => false,
];

echo $app->Form->create($_params->query, [
  'class' => 'unsetEmptyFields form-inline mb-4'
]);

echo $app->Form->input('tipus', [
  'empty' => ['' => 'Minden eseménytípus'],
  'options' => $app->Arrays->id_list(sDB['event_types'], 0, [
    'sort' => 'ASC',
    'excluded_keys' => sDB['events_hidden_from_artpage_history'],
  ]),
  'class' => 'mr-3',
]);

echo $app->Form->input('engem', [
  'label' => 'Engem érintő események',
  'type' => 'checkbox',
  'value' => 1,
]);

echo $app->Form->end('Mehet', [
  'class' => 'btn btn-secondary ml-3'
]);

?>

<div class="row">
  <div class="col-md-7 order-2 order-md-1">
    <?php
    foreach ($events as $event) {
      echo $app->element('events/item', ['event' => $event, 'options' => [
        'highlight' => $options['highlight'],
      ]]);
    }

    echo $app->Html->pagination(count($events), $pagination);
    ?>
  </div>
  <div class="col-md-5 order-1 order-md-2">
    <div class="kt-info-box">Ebben a listában időrendi csökkenő sorrendben nyomon követheted a KT összes lényeges eseményét. Ez a lista a nem publikus műlap eseményeket is tartalmazza.</div>
  </div>
</div>
