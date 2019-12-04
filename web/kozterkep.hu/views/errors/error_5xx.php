<div class="row">
  <div class="col-md-8">
    <p class="lead font-weight-bold">Váratlan hiba történt.</p>

    <p>Amennyiben a jelenség nem átmeneti, hanem bizonyos helyzetekben mindig ez
      a hibaoldal jelenik meg, kérjük <a href="/oldalak/kapcsolat">jelezd
        nekünk ezen az űrlapon!</a></p>

    <p><?= $app->Html->link('Visszalépés a hivatkozó oldalra', $_params->referer, [
        'class' => 'btn btn-secondary'
      ]) ?></p>

  </div>

  <div class="col-md-4">

    <?php
    echo $app->Html->image('gyermeknevelesi-hiba-oslo-vigeland-park.jpg', [
      'class' => 'img-fluid rounded'
    ]);
    ?>

  </div>
</div>