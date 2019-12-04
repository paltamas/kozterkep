<?php
/**
 * Ez ugyanazt csinálja meg, mint a comments.js add_row függvénye
 */
$options = (array)@$options + [
  'row_class' => '',
  'hide_connecteds' => false,
];

if (@$_params->query['esemeny'] == $event['id']) {
  $options['row_class'] .= ' bg-gray-kt ';
}

echo '<div id="event-row-' . $event['id'] . '" data-type="' . $event['type_id'] . '" data-user="' . $event['user_id'] . '" data-time="' . $event['created'] . '" class="event-row row border rounded mb-3 py-2 mx-0 ' . $options['row_class'] . '">';
echo '<div class="col-md-12 event-container">';

// User + szöveg
echo '<div class="event-text-box">';

// Dolgok
echo '<div class="float-right fade-icons">';
// Időbélyeg
echo '<small class="text-muted mr-2">' . _time($event['created'], ['ago' => true]) . '</small>';
echo '</div>';

// User
echo '<div class="mb-2"><span class="font-weight-bold mr-2 event-user">';
echo $app->Users->name($event['user_id'], [
  'image' => true
]);
echo '</span></div>';

// Kapcsolt dolgok
echo !$options['hide_connecteds'] ? $app->element('layout/partials/connected_things', [
  'item' => $event,
  'options' => [
    'item_type' => 'event',
  ]
]) . '<hr class="my-1" />' : '';

// Szöveg
echo '<span class="event-text">';
echo $app->Events->text($event);
echo '</span>';
echo '</div>'; // event-text-box --


if (isset($event['photos'])) {
  echo '<div class="my-2">';
  foreach (_json_decode($event['photos']) as $event_photo) {
    $link = @$event['artpiece_id'] > 0 ?
      '/' . $event['artpiece_id'] : '/alkotok/megtekintes/' . $event['artist_id'];
    echo $app->Image->photo($event_photo, [
      'size' => 6,
      'class' => 'img-fluid img-thumbnail mr-2',
      'link' => $link . '#vetito=' . $event_photo['id']
    ]);
  }
  echo '</div>';
}

echo '</div>'; // col --
echo '</div>'; // row --