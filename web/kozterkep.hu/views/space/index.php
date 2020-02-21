<div class="row mt-3">

  <div class="col-md-4 mb-2 mb-md-4">
    <?=$app->element('space/index/latest_artpieces')?>
    <hr class="my-3 my-md-5" />
    <?=$app->element('space/index/updated_artpieces')?>
  </div>

  <div class="col-sm-6 col-md-4 mb-2 mb-md-4">
    <?=$app->element('space/index/wall')?>
  </div>

  <div class="col-sm-6 col-md-4 mb-2 mb-md-4">
    <?=$app->element('space/index/important_message')?>
    <?=$app->element('space/index/user_todos')?>
    <?php
    if ($_user['headitor'] == 1) {
      echo $app->Html->link('Frissít', '#', [
        'class' => 'float-right btn btn-outline-primary btn-sm',
        'icon_right' => 'sync',
        'hide_text' => true,
        'ia-ajaxdiv-load-simple' => '/kozter/szerkdoboz',
        'ia-ajaxdiv-target' => '.ajaxdiv-editorbox',
      ]);
      echo '<div class="ajaxdiv-editorbox" ia-ajaxdiv="/kozter/szerkdoboz" style="min-height: 250px;"></div>';
    }
    ?>
    <?=$app->element('space/index/posts_highlighted', ['posts' => $admin_posts])?>
    <hr class="my-4" />
    <?=$app->element('space/index/posts_blogs', ['posts' => $posts])?>
  </div>

</div>