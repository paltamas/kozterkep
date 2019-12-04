<div class="fixed-top ml-5 text-right pt-2 pt-md-3 mr-2 mr-md-3 z-0 d-none" id="map-topnav">

  <div class="d-inline-block">

    <?php

    // Visszalépés, na de hova
    $back_link = @$_params->query['visszalepes'] != '' ? $_params->query['visszalepes'] : '/';
    echo $app->Html->link('', $back_link, [
      'icon' => 'angle-left fa-fw',
      'class' => 'mr-3 btn btn-white btn-map d-inline-block d-md-none',
    ]);

    echo $app->Html->link('', '#', [
      'icon' => 'th-list',
      'class' => 'showArtpieceList mr-3 btn btn-white btn-map d-inline-block d-md-none',
    ]);


    ?>

    <a href="/mulapok/kozelben" class="mr-3 btn btn-white btn-map d-inline-block d-md-none"><i
        class="far fa-compass fa-fw"></i></a>

    <div class="dropdown d-inline">
      <a href="#" class="btn btn-white btn-map dropdown-toggle place-search"
         data-toggle="dropdown">
        <i class="far fa-search"></i>
      </a>

      <div class="dropdown-menu dropdown-menu-right wider">
        <div class="input-group p-2 geocoder">
          <input type="text" class="form-control form-control-sm keyword"
                 placeholder="Földrajzi cím">
          <div class="input-group-append">
            <?php
            echo $app->Html->link('Keres', '#', [
              'class' => 'btn btn-secondary btn-sm geocode'
            ]);
            ?>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!--<a href="#mapLinkModal" class="ml-3 btn btn-white btn-map d-none d-md-inline-block"
     data-toggle="modal"><i class="far fa-link fa-fw"></i></a>-->

  <a href="#" class="ml-3 btn btn-white btn-map show-artpieces-pane d-none"><i class="far fa-th-list fa-fw"></i></a>
</div>

<div class="fixed-bottom m-2 mb-2 m-md-3 dropup z-0">

  <a href="#" class="btn btn-white btn-map d-inline-block dropdown-toggle"
     data-toggle="dropdown">
    <span class="far fa-cog map-settings-icon"></span>
  </a>

  <div class="dropdown-menu dropdown-menu-left dont-close-this">

    <div class="dropdown-header">
      <strong><span class="far fa-info-circle mr-1"></span>Helyzeted</strong><br/>
      Pontosság: <span class="mapAccurancy">?</span>m<br/>
      Magasság: <span class="mapAltitude">?</span>
    </div>

    <a class="dropdown-item follow-me-on-map d-none py-2" href="#">
      <span class="far fa-walking mr-1"></span>Kövessen a térkép
    </a>

    <div class="dropdown-divider"></div>

    <!--<div class="dropdown-header">
      <strong><i class="far fa-layer-group mr-2"></i>Térkép rétegek</strong>
    </div>-->

    <?=$app->element('maps/layer_list')?>
  </div>

  <a href="#" class="btn btn-white btn-map d-inline-block mapHome px-3">
    <span class="far fa-crosshairs"></span>
  </a>
</div>