<?php
$options = (array)@$options + [
  'class' => '',
  'iframe' => false,
  'lat' => false,
  'lon' => false,
  'lat0' => false,
  'lon0' => false,
  'markpos' => true,
  'zoom' => false,
  'height' => false,
  'google_link' => false,
  'showmarkers' => false, // by_bounds szerint
  'artpiece_ids' => false, // id array, ezt töltjük be
  'map_link_query' => false, // térkép oldalra linkeljünk-e, és ha igen, akkor milyen lekérdezéssel
];

?>
<div class="map-container <?=$options['class']?>">

  <?php if ($options['iframe'] != true) { ?>
  <div class="z-900" style="position: absolute; right: 25px; margin-top: 10px;">
    <a href="#" class="btn btn-secondary d-inline-block dropdown-toggle"
       data-toggle="dropdown">
      <i class="far fa-layer-group mr-2"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-right">
      <?=$app->element('maps/layer_list')?>
    </div>
  </div>
  <?php } ?>

  <?php
  if ($options['iframe']) {

    $http_query = http_build_query([
      'lat' => $options['lat'],
      'lon' => $options['lon'],
      'lat0' => $options['lat0'],
      'lon0' => $options['lon0'],
      'zoom' => $options['zoom'],
      'height' => $options['height'],
      'artpiece_ids' => $options['artpiece_ids'] ? implode(',', $options['artpiece_ids']) : 'false',
    ]);

    $height = $options['height'] + 10;
    echo '<iframe id="simple-map-iframe" src="/terkep/iframe?' . $http_query . '" style="border: 0; width: 100%; height: ', $options['height'] > 0 ? $height : 300 , 'px;"></iframe>';

  } else {
  ?>

    <div id="map" class="simple-map mb-2"
         ia-maps-nozoom="true"
         ia-maps-edit="false"
         ia-maps-showme="false"
         ia-maps-showdist="false"
         ia-maps-markpos="<?=$options['markpos'] ? 'true' : 'false'?>"
         ia-maps-artpiece_ids="<?=$options['artpiece_ids'] ? implode(',', $options['artpiece_ids']) : 'false'?>"
         ia-maps-artpieces="<?=$options['showmarkers'] ? 'true' : 'false'?>"
         ia-maps-position="<?=$options['lat'] ? '[' . $options['lat'] . ',' . $options['lon'] . ']' : ''?>"
         ia-maps-position0="<?=$options['lat0'] ? '[' . $options['lat0'] . ',' . $options['lon0'] . ']' : ''?>"
         ia-maps-zoom="<?=$options['zoom'] > 0 ? $options['zoom'] : 19?>"
         ia-maps-layer="osm.streets"
         style="height: <?=$options['height'] > 0 ? $options['height'] : 300?>px;"
    ></div>

  <?php } ?>


  <?php
  if ($options['map_link_query']) {
    $url_query = '';
    if (is_array($options['map_link_query']) && count($options['map_link_query']) > 0) {
      $url_query = '?'. http_build_query($options['map_link_query']);
    }
    echo '<div class="mt-3 text-center">';
    echo $app->Html->link('Mutasd nagyobb térképen', '/terkep' . $url_query, [
      'class' => 'btn btn-outline-primary',
    ]);
    echo '</div>';
  }
  ?>


  <?php
  if ($options['google_link']) {
    echo '<div class="mt-2 small text-muted">' . $app->Html->link('Google Maps megnyitása', '#', [
      'target' => '_blank',
      'class' => 'gmap-link',
    ]) . ' (Ha megnéznéd az utcaképet, vagy még közelebb mennél.)</div>';
  }
  ?>
</div>