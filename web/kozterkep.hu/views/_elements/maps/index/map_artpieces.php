<div class="row pl-0 pr-0 py-4">
  <div class="col-md-12">

    <a href="#" class="float-right btn btn-secondary btn-sm hide-artpieces-pane"><span class="far fa-times"></span></a>

    <?php
    echo $app->Html->tabs([
      'Itt' => [
        'hash' => 'mulapok',
        'icon' => 'map-marked-alt',
      ],
      'Keresés' => [
        'hash' => 'kereses',
        'icon' => 'search-location'
      ],
    ], [
      'hide_labels' => false,
      'type' => 'tabs',
      'align' => 'left',
      'selected' => 'mulapok',
      'class' => 'mb-4'
    ])
    ?>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane show active" id="mulapok" role="tabpanel">
        <div class="artpiece-list row"></div>
      </div>
      <div class="tab-pane" id="kereses" role="tabpanel">
        <div class="artpiece-search">
          <?=$app->element('maps/index/artpieces_search')?>
        </div>
      </div>
    </div>

  </div>
</div>