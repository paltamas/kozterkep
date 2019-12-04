<?php
$options = (array)@$options + [
  'count' => 0,
  'map_artpieces' => [],
  'height' => 350,
  'zoom' => 15,
  'center' => [
    'lat' => 47.4977973,
    'lon' => 19.0381338,
  ],
  'filter_query' => [],
  'filter_title' => $_title,
  'filter_back' => $_params->here,
  'title' => '<h4 class="subtitle">Térképen</h4>',
  'div_class' => '',
];

/**
 *
 * Szimpla térképet ad vissza műlap listára, címmel és gombbal.
 * Ha a műlapok száma nagyobb, mint a megengedett max, akkor a kapott
 * centrumhoz ugrik a megadott zoommal, és azt a szeletet mutatja.
 *
 */


if ($options['count'] > APP['map']['max_id']) {
  $map_options = [
    'markpos' => false,
    'showmarkers' => true,
    'lat' => $options['center']['lat'],
    'lon' => $options['center']['lon'],
    'height' => $options['height'],
    'zoom' => $options['zoom'],
  ];

  // Nem a hely_az-s linket adjuk, mert az csak 1000-at ad ki
  $map_query = '#lat=' . $options['center']['lat'] . '&lon=' . $options['center']['lon'] . '&zoom=' . $options['zoom'];

} else {

  $map_options = [
    'markpos' => false,
    'artpiece_ids' => $options['map_artpieces'],
    'height' => $options['height'],
  ];

  $http_qery = [
    'oldalcim' => $options['filter_title'],
    'visszalepes' => $options['filter_back']
  ] + $options['filter_query'];

  $map_query = '?' . http_build_query($http_qery);
}


echo '<div class="' . $options['div_class'] . '">';

echo $app->Html->link('Nagyobb térképen', '/terkep'. $map_query, [
  'class' => 'btn btn-outline-primary float-right',
  'hide_text' => true,
  'icon' => 'map-marked'
]);

echo $options['title'];

echo $app->element('maps/simple', ['options' => $map_options]);

echo '</div>';