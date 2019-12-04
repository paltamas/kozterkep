<div class="d-md-none">
  <?=$app->element('users/follows/home/settings')?>
</div>
<div class="row">
  <div class="col-md-4 mb-4">
    <?=$app->element('users/follows/home/artpieces')?>
  </div>
  <div class="col-md-4 mb-4">
    <?=$app->element('users/follows/home/comments')?>
  </div>
  <div class="col-md-4 mb-4">
    <div class="d-none d-md-flex">
      <?=$app->element('users/follows/home/settings')?>
    </div>
    <?=$app->element('users/follows/home/photos')?>
    <hr class="my-4" />
    <?=$app->element('users/follows/home/artist_descriptions')?>
    <hr class="my-4" />
    <?=$app->element('users/follows/home/artpiece_descriptions')?>
    <hr class="my-4" />
    <?=$app->element('users/follows/home/artpiece_edits')?>
    <hr class="my-4" />
    <?=$app->element('users/follows/home/folder_files')?>
    <hr class="my-4" />
    <?=$app->element('users/follows/home/posts')?>
  </div>
</div>