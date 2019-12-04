<div class="col-lg-12 my-4 text-center">
  <?php
  echo 'Menet közben is szobroznál? Töltsd le az';
  echo $app->Html->link('Szoborkeresőt iOS-re', 'https://itunes.apple.com/hu/app/szoborkereso/id1150066881', [
    'target' => '_blank',
    'icon' => 'app-store-ios fab',
    'class' => 'font-weight-bold ml-1 text-nowrap',
  ]);
  echo ', vagy a ';
  echo $app->Html->link('Köztérképet Androidra', 'https://play.google.com/store/apps/details?id=hu.idealap.kt2', [
    'target' => '_blank',
    'icon' => 'google-play fab',
    'class' => 'font-weight-bold ml-1 text-nowrap',
  ]);
  echo '!';
  ?>
</div>