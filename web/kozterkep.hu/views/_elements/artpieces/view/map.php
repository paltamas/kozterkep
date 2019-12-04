<?php
echo '<span id="get-location-touch"></span>';
echo $app->element('maps/simple', ['options' => [
  'lat' => $artpiece['lat'],
  'lon' => $artpiece['lon'],
  'zoom' => 16,
  'height' => 150,
]]);

echo '<div id="nearby-list" 
  class="text-center text-md-left"
  ia-alist-lat="' . $artpiece['lat'] . '"  
  ia-alist-lon="' . $artpiece['lon'] . '"  
  ia-alist-excluded="' . $artpiece['id'] . '"
  ia-alist-limit="4" 
  ia-alist-img-width="75" 
  ia-alist-showdir="false"></div>';

echo '<div class="text-center text-md-left ml-1">';
echo $app->Html->link('Környék bejárása', '/terkep#mulap=' . $artpiece['id']
  . '&lat=' . $artpiece['lat'] . '&lon=' . $artpiece['lon'], [
  'icon' => 'map-marked-alt',
]);
echo '</div>';