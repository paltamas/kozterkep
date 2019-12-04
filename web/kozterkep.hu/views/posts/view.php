<div class="row mt-md-4">


  <div class="col-12 mb-3">
    <h1 class="mt-0 display-1 font-weight-semibold"><?= $post['title'] ?></h1>
  </div>

  <div class="<?=$post['comments_blocked'] == 1 ? 'col-md-9' : 'col-md-8 col-lg-7'?> mb-4">

    <div class="mb-3">
      <?=$app->element('posts/view/subtitle')?>
    </div>

    <?php

    if ($post['status_id'] != 5) {
      echo '<div class="alert alert-secondary my-4"><span class="fal fa-info-circle mr-1"></span>Ez a bejegyzés jelenleg szerkesztés alatt áll, még nem publikáltad.</div>';
    }


    echo $app->element('posts/view/texts');


    if ($post['status_id'] == 5) {
      echo '<div class="my-5 text-muted">';
      echo 'Bejegyzés megtekintés: <span class="font-weight-bold model-view-stats">' . _loading() . '</span>';
      echo '</div>';
    }

    echo $app->element('posts/view/connected_artpieces');

    ?>


  </div>

  <div class="<?=$post['comments_blocked'] == 1 ? 'col-md-3' : 'col-md-4 col-lg-5'?> mb-4 pt-3">

    <?php
    echo $app->element('posts/view/connected_things');

    echo $app->element('posts/view/comments');
    ?>

  </div>

</div>