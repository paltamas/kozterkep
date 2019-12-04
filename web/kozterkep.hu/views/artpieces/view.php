<div class="tab-content">

  <div class="tab-pane show active" id="mulap" role="tabpanel" aria-labelledby="mulap-tab">

    <div class="row">

      <div class="col-md-4 col-lg-3 mb-4">

        <?=$app->element('artpieces/view/hug_space')?>
        <?=$app->element('artpieces/view/place_intro')?>
        <?=$app->element('artpieces/view/photo_cover')?>
        <?=$app->element('artpieces/view/photos_highlighted')?>

      </div>



      <div class="col-md-5 col-lg-6 mb-4">
        <hr class="mt-0 mb-3 d-md-none" />
        <?=$app->element('artpieces/view/condition')?>

        <?=$app->element('artpieces/view/titles')?>

        <div class="row">
          <div class="col-sm-6 col-md-12 col-lg-6 mb-0 mb-md-3 mb-lg-0">
            <?=$app->element('artpieces/view/place')?>
          </div>
          <div class="col-sm-6 col-md-12 col-lg-6">
            <hr class="d-sm-none d-lg-none my-3" />
            <?=$app->element('artpieces/view/dates')?>
            <hr class="my-2" />
            <?=$app->element('artpieces/view/artists')?>
          </div>
        </div>

        <hr class="my-3" />
        <!--<h6 class="subtitle">Paraméterek</h6>-->
        <?=$app->element('artpieces/view/parameters')?>

        <?=$app->element('artpieces/view/parent_child')?>

        <!--<h6 class="subtitle mb-2">Alkotás története, saját sztorik és adalékok</h6>-->
        <?=$app->element('artpieces/view/stories')?>

        <?=$app->element('artpieces/view/comments')?>

        <?=$app->element('artpieces/view/posts')?>

        <div class="ajaxdiv-latogatoinfok" ia-ajaxdiv="/mulapok/latogatoinfok/<?=$artpiece['id']?>"></div>

      </div>



      <div class="col-md-3">
        <hr class="mt-0 mb-3 d-md-none" />
        <?=$app->element('artpieces/view/steps')?>

        <?=$app->element('artpieces/view/creator')?>

        <?=$app->element('artpieces/view/map')?>

        <?=$app->element('artpieces/view/basic_info')?>

        <?=$app->element('artpieces/view/connections')?>

        <?=$app->element('artpieces/view/similars')?>

        <div class="editor-boxes d-none"><?=_loading()?></div>
      </div>

    </div>

  </div>

  <div class="tab-pane" id="fotolista" role="tabpanel" aria-labelledby="fotolista-tab">
    <?=$app->element('artpieces/view/photos_showroom')?>
  </div>

  <div class="tab-pane" id="tortenet" role="tabpanel" aria-labelledby="tortenet-tab">
    <?=$app->element('artpieces/view/events')?>
  </div>

  <div class="tab-pane" id="szerkkomm" role="tabpanel" aria-labelledby="szerkkomm-tab">
    <div class="ajaxdiv-szerkkomm" ia-ajaxdiv="/mulapok/szerkkomm/<?=$artpiece['id']?>"></div>
  </div>

</div>
