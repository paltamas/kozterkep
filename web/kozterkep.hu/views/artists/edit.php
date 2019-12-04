<div class="tab-content">

  <div class="tab-pane show active" id="szerkesztes" role="tabpanel" aria-labelledby="szerkesztes-tab">
    <?=$app->element('artists/edit/data')?>
  </div>

  <div class="tab-pane" id="adalekok" role="tabpanel" aria-labelledby="adalekok-tab">
    <?=$app->element('artists/edit/descriptions')?>
  </div>

  <div class="tab-pane" id="fotok" role="tabpanel" aria-labelledby="fotok-tab">
    <?=$app->element('artists/edit/photos')?>
  </div>

  <div class="tab-pane" id="szerkkomm" role="tabpanel" aria-labelledby="szerkkomm-tab">
    <?=$app->element('artists/edit/editcom', ['hide_info' => true])?>
  </div>

</div>


