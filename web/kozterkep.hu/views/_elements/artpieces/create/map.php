<div class="map-container">

  <div class="row">
    <div class="col-md-6 mb-4">
      <?php
      echo '<div class="geocoder input-group">';
      echo $app->Form->input('place_search', [
        'class' => 'keyword',
        'placeholder' => 'Cím vagy GPS koordináták',
        'ia-maps-search-marker' => true,
        'divs' => false,
      ]);
      echo '<div class="input-group-append">';
      echo $app->Html->link('Keres', '#', [
        'class' => 'btn btn-secondary geocode',
      ]);
      echo '</div>';
      echo '</div>';
      echo $app->Form->help('A GPS koordinátákat egymástól vesszővel elválasztva add meg (lat, lng).');
      ?>
    </div>
    <div class="col-md-6 mb-4 text-right">
      <a href="#" class="btn btn-secondary d-inline-block dropdown-toggle"
         data-toggle="dropdown">
        <i class="far fa-layer-group mr-2"></i><span class="d-none d-md-inline">Térképréteg</span>
      </a>

      <div class="dropdown-menu dropdown-menu-right z-10000">
        <?=$app->element('maps/layer_list')?>
      </div>

      <a href="#" class="btn btn-secondary d-inline-block mapHome">
        <i class="far fa-crosshairs mr-2"></i><span class="d-none d-md-inline">Pozíciómhoz</span>
      </a>
    </div>
  </div>

  <div id="map" class="edit-map mb-2" ia-maps-nozoom="true" ia-maps-edit="true" ia-maps-showme="true" ia-maps-showdist="true" ia-maps-position="<?=@$artpiece ? '[' . $artpiece['lat'] . ',' . $artpiece['lon'] . ']' : ''?>"></div>

  <?php

  echo '<div class="mt-2 small text-muted">' . $app->Html->link('Google Maps megnyitása', '#', [
    'target' => '_blank',
    'class' => 'gmap-link',
  ]) . ' (Ha megnéznéd az utcaképet, vagy még közelebb mennél.)</div>';

  echo $app->Form->help('Ha nincs kint a jelölő, kattints a térképre! A földrajzi cím megtalálása után húzd a jelölőt a pontos helyre. A térkép kívánt pontjára kattintva is megadhatod a helyet. Ha bizonytalan vagy, nézd meg más térképrétegekkel a helyszínt, hátha könnyebben beméred.');

  echo '<div class="mt-3">OSM Nominatim szerint ez a hely: <span class="nominatim-address">...</span></div>';
  ?>

</div>