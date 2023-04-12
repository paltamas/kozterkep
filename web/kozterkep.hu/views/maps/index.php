<span id="get-location"></span>
<div id="leaflet-progress" class="d-none"><div id="leaflet-progress-bar"></div></div>
<div class="row p-0 m-0">
  <div class="col-md-8 col-12 p-0 m-0 map-container">
    <div id="map" class="fullPage position-fixed z-0"
       ia-maps-showme="true"
       ia-maps-artpieces="true"
       <?=$artpiece_ids && count($artpiece_ids) > 0 ? 'ia-maps-artpiece_ids="' . implode(',', $artpiece_ids) . '"' : ''?>
    ></div>
    <script>
        function geocoderInit() {
            window.geocoder = new google.maps.Geocoder();
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?=C_WS_GOOGLE['maps']?>&v=<?=C_WS_GOOGLE['js_version']?>&callback=geocoderInit"></script>

    <?=$app->element('maps/index/tools')?>
  </div>
  <div class="col-md-4 col-0 artpiece-container d-none d-md-block scrollable-y">
    <?=$list_page_type ? $app->element('maps/index/list_artpieces') : $app->element('maps/index/map_artpieces')?>
  </div>
</div>