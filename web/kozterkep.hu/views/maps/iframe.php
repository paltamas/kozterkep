<div class="z-900" style="position: absolute; right: 25px; margin-top: 10px;">
  <a href="#" class="btn btn-secondary d-inline-block dropdown-toggle"
     data-toggle="dropdown">
    <i class="far fa-layer-group mr-2"></i>
  </a>

  <div class="dropdown-menu dropdown-menu-right">
    <?=$app->element('maps/layer_list')?>
  </div>
</div>

<div id="map" class="simple-map mb-2"
   ia-maps-nozoom="true"
   ia-maps-edit="false"
   ia-maps-showme="false"
   ia-maps-showdist="false"
   ia-maps-markpos="true"
   ia-maps-position="<?=@$_params->query['lat'] ? '[' . $_params->query['lat'] . ',' . $_params->query['lon'] . ']' : ''?>"
   ia-maps-position0="<?=@$_params->query['lat0'] ? '[' . $_params->query['lat0'] . ',' . $_params->query['lon0'] . ']' : ''?>"
   ia-maps-zoom="17"
   ia-maps-layer="osm.streets"
   style="height: <?=@$_params->query['height'] > 0 ? $_params->query['height'] : 300?>px;"
></div>