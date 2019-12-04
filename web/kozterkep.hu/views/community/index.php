<div class="row">
  <div class="col-md-4 mb-4">
    <?=$app->element('community/index/search_form')?>
    <div class="ajaxdiv-wall" ia-ajaxdiv="/kozosseg/mi_falunk"></div>
  </div>
  <div class="col-md-4 mb-4">
    <?=$app->element('community/index/highlighted_user')?>
    <?=$app->element('community/index/posts', [
      'title' => 'Friss hírek és segédletek',
      'posts' => $admin_posts
    ])?>
    <?=$app->element('community/index/top_users')?>
    <?=$app->element('community/index/sets')?>
    <?=$app->element('community/index/folders')?>
  </div>
  <div class="col-md-4 mb-4">
    <?=$app->element('community/index/posts', [
      'title' => 'Friss blogposztok',
      'posts' => $user_posts
    ])?>
  </div>
</div>