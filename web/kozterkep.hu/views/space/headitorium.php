<div class="row">

  <div class="col-sm-6 mb-4">

    <?php
    echo '<div class="text-center mb-3">';
    echo $app->Html->link('Ellenőrizetlen települések', '/helyek/kereses?ellenorizetlen=1', [
      'icon' => 'map-marker',
      'class' => 'btn btn-outline-secondary btn-sm mr-2 text-center',
    ]);
    echo $app->Html->link('Ellenőrizetlen alkotók', '/alkotok/kereses?ellenorizetlen=1', [
      'icon' => 'users',
      'class' => 'btn btn-outline-secondary btn-sm mr-2 text-center',
    ]);
    echo '</div>';

    echo $app->Html->link('Ugrás a műlapokhoz', '#mulapok', [
      'icon' => 'arrow-down',
      'class' => 'd-block d-md-none btn btn-outline-secondary mb-2 text-center',
      'ia-scrollto' => true
    ]);
    ?>

    <?=$app->element('space/headitorium/wall')?>
  </div>

  <div class="col-sm-6" id="mulapok">
    <div class="row">

      <div class="col-md-12 pb-4 mb-4 border-bottom">
        <h5 class="subtitle mb-3"><span class="fas fa-pause-circle mr-2 text-muted"></span>Megállított publikációk</h5>
        <?=$app->element('space/headitorium/artpieces_list', [
          'artpieces' => $publish_pauseds
        ])?>
      </div>

      <div class="col-md-12 pb-4 mb-4 border-bottom">
        <h5 class="subtitle mb-3"><span class="fas fa-star-christmas mr-2 text-muted"></span>Friss műlapok "Példás" szavazásra</h5>
        <?=$app->element('space/headitorium/artpieces_list', [
          'artpieces' => $latest_artpieces,
          'options' => [
            'show_votes' => true,
          ]
        ])?>
      </div>


      <div class="col-md-12 pb-4 mb-4 border-bottom">
        <?=$app->element('space/headitorium/open_questions')?>
      </div>

      <div class="col-md-12 pb-4 mb-4">
        <h5 class="subtitle mb-3"><span class="fas fa-star-half mr-2 text-muted"></span>Újraszavazható műlapok <span class="text-muted">(<?=_time(strtotime(sDB['limits']['headitors']['superb_revote']), 'Y.m.d.')?> előtt szavazva)</span></h5>
        <?=$app->element('space/headitorium/artpieces_list', [
          'artpieces' => $old_artpieces,
          'options' => [
            'show_votes' => true,
          ]
        ])?>
      </div>

      <div class="col-md-12 pb-4 mb-4">
        <h4 class="subtitle mb-3">Legfrissebb párbeszédek</h4>
        <div class="ajaxdiv-photos" ia-ajaxdiv="/kozter/parbeszedek/6"></div>
      </div>

      <div class="col-md-12 mb-4">
        <?=$app->element('space/headitorium/possible_publishers')?>
      </div>

    </div>
  </div>

</div>