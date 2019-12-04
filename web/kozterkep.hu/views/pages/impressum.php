<div class="row">
  <div class="col-12 col-md-11 mb-4 lead">
    <p>A Köztérképet egy nagyon lelkes önkéntes és független Közösség építi. Ebben a csapatban dolgoznak páran, akik arra figyelnek, hogy ez a nagy közös játék még sokáig ilyen nagyszerű maradhasson.</p>
    <p>Ők a Köztérkép működtetői, főszerkesztői és fejlesztői, akik mellett külön kiemeljük az egyes területekért felelős tagokat valamint korábbi főszerkesztőinket. <a href="/oldalak/kapcsolat">Kapcsolatfelvételhez kattints ide</a>.</p>
  </div>

  <div class="col-md-6 mb-4">
    <h4 class="title">Főszerkesztők</h4>
    <div class="ml-3">
      <?php
      foreach ($headitors as $headitor) {
        echo '<p>';
        echo $app->Users->name($headitor, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ]);
        if (!in_array($headitor['id'], CORE['USERS']['headveto'])) {
          echo ' <span class="text-muted">tanácsadó főszerkesztő</span>';
        } else {
          echo ' <span class="text-muted">főszerkesztő</span>';
        }
        echo '</p>';
      }
      ?>
    </div>

    <h4 class="title mt-5">Egyes területek felelősei</h4>
    <div class="ml-3">
      <?php
      echo '<p>' . $app->Users->name(772, [
        'link' => true,
        'image' => true,
        'class' => 'font-weight-bold'
      ]) . ' <span class="text-muted">közös gyűjtemények</span></p>';

      echo '<p>' . $app->Users->name(148, [
        'link' => true,
        'image' => true,
        'class' => 'font-weight-bold'
      ]) . ' <span class="text-muted">FB és Akkor&amp;most</span></p>';

      echo '<p>' . $app->Users->name(1, [
        'link' => true,
        'image' => true,
        'class' => 'font-weight-bold'
      ]) . ' <span class="text-muted">településtár</span></p>';

      echo '<p>' . $app->Users->name(665, [
        'link' => true,
        'image' => true,
        'class' => 'font-weight-bold'
      ]) . ' <span class="text-muted">alkotótár</span></p>';
      ?>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <h4 class="title">Korábbi (fő)szerkesztők</h4>
    <div class="ml-3">
      <?php
      foreach ($headitor_were as $headitor) {
        echo '<p>';
        echo $app->Users->name($headitor, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ]);
        switch ($headitor['id']) {
          case 33:
            echo ' <span class="text-muted">első művészeti vezetőnk</span>';
            break;

          case 78:
            echo ' <span class="text-muted">első főszerkesztőnk</span>';
            break;

          case 318:
            echo ' <span class="text-muted">alkotótár létrehozója</span>';
            break;
        }
        echo '</p>';
      }
      ?>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <h4 class="title">Weblapok</h4>
    <div class="ml-3">
      <p>
        <?=$app->Users->name(1, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ])?> <span class="text-muted">alapító, fejlesztő és üzemgazda</span>
      </p>
      <p>
        <?=$app->Users->name(5, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ])?> <span class="text-muted">az előző Köztérkép társfejlesztője</span>
      </p>
      <p>
        <?=$app->Users->name(4, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ])?> <span class="text-muted">a Szoborlap első fejlesztője</span>
      </p>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <h4 class="title">Appok</h4>
    <div class="ml-3">
      <p>
        <?=$app->Users->name(150, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ])?> <span class="text-muted">iOS applikáció</span>
      </p>
      <p>
        <?=$app->Users->name(3720, [
          'link' => true,
          'image' => true,
          'class' => 'font-weight-bold'
        ])?> <span class="text-muted">Android applikáció</span>
      </p>
    </div>
  </div>

  <div class="col-12">
    <div class="kt-info-box">
      <div class="row">
        <div class="col-sm-6">
          <p class="mb-1">Üzemeltető:</p>
          <p class="mb-4">
            <strong>Pál Tamás</strong> <span class="text-muted">&bull; 2014 Csobánka, Vaddisznós utca 4. 🐽</span><br />
            pt@kozterkep.hu
          </p>
        </div>
        <div class="col-sm-6">
          <p class="mb-0">Tárhelyszolgáltató:<br />
            <strong>NLG-System Bt.</strong> <span class="text-muted">&bull; 2135 Csörög, Homokbánya u. 26.</span></p>
        </div>
      </div>
    </div>
  </div>
</div>
