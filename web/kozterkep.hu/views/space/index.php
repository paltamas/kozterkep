<div class="row mt-3">

  <div class="col-sm-6 col-md-7 mb-2 mb-md-4">
    <?php
    echo $app->element('space/index/user_todos');

    if ($_user['headitor'] == 1) {
      echo '<div class="bg-gray-kt mb-3 py-2 px-3 rounded small">';
      echo $app->Html->link('Ellenőrizetlen alkotók', '/alkotok/kereses?ellenorizetlen=1&submit=Keres%C3%A9s&sorrend=rogzites-csokkeno');
      echo '&nbsp;&nbsp;&nbsp;&nbsp;';
      echo $app->Html->link('Ellenőrizetlen települések', '/helyek/kereses?ellenorizetlen=1&submit=Keres%C3%A9s&sorrend=rogzites-csokkeno');

      echo '</div>';

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
    <?=$app->element('space/index/latest_artpieces')?>
    <hr class="my-3 my-md-5" />
    <?=$app->element('space/index/updated_artpieces')?>
  </div>

  <div class="col-sm-6 col-md-5 mb-2 mb-md-4">
    <?php
    if (count($admin_posts) > 0) {
      echo $app->element('space/index/posts_highlighted', ['posts' => $admin_posts]);
      echo '<hr class="my-4">';
    }
    ?>
    <?=$app->element('space/index/wall')?>
  </div>

</div>