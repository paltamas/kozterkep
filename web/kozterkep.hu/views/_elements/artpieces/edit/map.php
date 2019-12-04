<div class="map-container">
  <div class="row">
    <div class="col-md-8 mb-4">
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
    <div class="col-md-4 mb-4 text-right">
      <a href="#" class="btn btn-secondary d-inline-block dropdown-toggle"
         data-toggle="dropdown">
        <i class="far fa-layer-group mr-2"></i><span class="d-none d-md-inline">Réteg</span>
      </a>

      <div class="dropdown-menu dropdown-menu-right z-10000">
        <?= $app->element('maps/layer_list') ?>
      </div>
    </div>
  </div>

  <div id="map" class="edit-map mb-2" ia-maps-nozoom="true" ia-maps-edit="true"
       ia-maps-showme="true" ia-maps-showdist="true"
       ia-maps-position="<?= @$artpiece ? '[' . $artpiece['lat'] . ',' . $artpiece['lon'] . ']' : '' ?>"></div>

  <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?=C_WS_GOOGLE['maps']?>"></script>

  <?php
  echo $app->Form->help('Ha a művet áthelyezték, ne itt módosítsd, hanem a Működési elveinket követve készíts új műlapot az új helyszínen!');

  echo '<div class="mt-3 nominatim-container d-none">OSM Nominatim szerint ez a hely: <span class="nominatim-address">...</span></div>';
  echo '<div class="mt-3">' . $app->Html->link('Google Maps megnyitása', '#', [
      'target' => '_blank',
      'class' => 'gmap-link',
    ]) . ' (Ha megnéznéd az utcaképet, vagy még közelebb mennél.)</div>';

  echo $app->Form->create($artpiece, [
    'class' => 'w-100 artpiece-edit-form ajaxForm',
  ]);
  echo $app->Form->input('id', ['type' => 'hidden', 'value' => $artpiece['id']]);
  echo $app->Form->input('country_code', ['type' => 'hidden']);
  echo $app->Form->input('address_json', ['type' => 'hidden']);
  echo $app->Form->input('lat', ['type' => 'hidden', 'value' => $artpiece['lat']]);
  echo $app->Form->input('lon', ['type' => 'hidden', 'value' => $artpiece['lon']]);
  echo $app->Form->end();

  ?>
</div>