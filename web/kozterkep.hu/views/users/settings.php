<div class="tab-content">

  <div class="tab-pane show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
    <?=$app->element('users/settings/profile')?>
  </div>

  <div class="tab-pane" id="ertesitesek" role="tabpanel" aria-labelledby="ertesitesek-tab">
    <?=$app->element('users/settings/notifications')?>
  </div>

  <div class="tab-pane" id="kozos-munka" role="tabpanel" aria-labelledby="kozos-munka-tab">
    <?=$app->element('users/settings/work')?>
  </div>

  <div class="tab-pane" id="felulet-mukodese" role="tabpanel" aria-labelledby="felulet-mukodese-tab">
    <?=$app->element('users/settings/interface')?>
  </div>

  <div class="tab-pane" id="jelszo-csere" role="tabpanel" aria-labelledby="jelszo-csere-tab">
    <?=$app->element('users/settings/password')?>
  </div>
</div>

