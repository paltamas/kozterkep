<div class="kt-info-box mb-4">
  <h5><?=$app->Html->icon('medal fas text-primary mr-1')?>Heti kiemelt szerkesztőnk</h5>
  <?=$app->Users->name($highlighted_user, [
    'image' => true,
    'class' => 'font-weight-bold'
  ])?>, köszönjük munkádat!
  <div class="mt-3">
    <?php
    foreach ($highlighted_user_artpieces as $highlighted_user_artpiece) {
      echo $app->Image->photo($highlighted_user_artpiece, [
        'size' => 7,
        'class' => 'img-thumbnail img-fluid mr-2 mb-2',
        'style' => 'width: 57px;',
        'artpiece_tooltip' => $highlighted_user_artpiece['id'],
        'link' => $app->Html->link_url('', ['artpiece' => $highlighted_user_artpiece])
      ]);
    }
    ?>
  </div>
</div>