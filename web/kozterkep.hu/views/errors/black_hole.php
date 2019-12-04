<div class="row">
  <div class="col-md-8">
    <p class="lead font-weight-bold">A hiba meggátolta a folyamat lefutását.</p>
    <p>Biztonsági hiba 2 esetben történhet:</p>
    <ul>
      <li>mi rontottunk el valamit a kódban;</li>
      <li>te szeretnél olyan űrlapot / adatot beküldeni, ami nem
        engedélyezett.
      </li>
    </ul>
    <p>Amennyiben biztos vagy abban, hogy mi rontottunk el valamit, jelezd
      nekünk a hibát a <a href="/oldalak/kapcsolat">kapcsolatfelvételi
        űrlapon</a>!</p>

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