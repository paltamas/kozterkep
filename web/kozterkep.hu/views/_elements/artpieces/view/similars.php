<div class="similar-artpieces d-none">
  <hr class="my-3" />
  <h6 class="subtitle">Cím alapján hasonló</h6>
  <div class="items"><?=_loading()?></div>
  <div class="text-center text-md-left">
    <?php
    echo $app->Html->link('Hasonló műlapok listája', '/kereses?hasonlo=1&r=1&kulcsszo=' . $artpiece['title'] . '#hopp=lista', [
      'icon' => 'search',
      'class' => '',
    ]);
    ?>
  </div>
</div>