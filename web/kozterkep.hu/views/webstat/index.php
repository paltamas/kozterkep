<div class="row mt-4">
  <div class="col-lg-3 col-md-6 col-6 text-center mb-3">
    <span
      class="display-4 text-nowrap text-success"><?= _n($current_visitors) ?></span>
    <br/><span class="text-muted">látogató most</span>
  </div>

  <div class="col-lg-3 col-md-6 col-6 text-center mb-3">
    <span class="display-4 text-nowrap"><?= _n($today_sessions) ?></span>
    <br/><span class="text-muted">látogatás ma</span>
  </div>

  <div class="col-lg-3 col-md-6 col-6 text-center mb-3">
    <span class="display-4 text-nowrap"><?= _n($today_pageviews) ?></span>
    <br/><span class="text-muted">oldalletöltés ma</span>
  </div>

  <div class="col-lg-3 col-md-6 col-6 text-center mb-3">
    <span class="display-4 text-nowrap"><?= _n($weekly_sessions) ?></span>
    <br/><span class="text-muted">látogatás a héten</span>
  </div>
</div>

<hr class="my-3 my-mb-5" />

<div class="row pb-4">
  <div class="col-lg-7">
    <h4 class="subtitle">1 nap</h4>
    <p class="text-muted">Oldalletöltések számának alakulása az elmúlt 24 órában.</p>
    <canvas class="chart"
      ia-chart-type="bar"
      ia-chart-labels="<?=htmlentities(json_encode(array_keys($pageviews_24h)))?>"
      ia-chart-data="<?=htmlentities(json_encode(array_values($pageviews_24h)))?>"
    ></canvas>

  </div>
  <div class="col-lg-5">
    <h4 class="subtitle">Mi ez itt?</h4>
    <p>A Köztérkép saját kísérleti webstatisztikája, amit azért készítettünk, hogy a weblapunkra érkező látogatókat úgy mérhessük, hogy azt meg is mutathassuk.<?=date('Y-m-d') < '2019-05-22' ? ' <span class="">A mérés ' . _date(APP['kt2_start'], 'Y.m.d') . '-én indult.</span>' : ''?></p>
    <p>Itt <strong>minden</strong> emberi látogatást követhetsz, tehát a nem publikus lapokét is. Miért? Mert webstatisztikai szempontból minden látogatás látogatás. Az egyes műlapok és más aloldalak publikálás utáni nézettségét is innen számoljuk ki.</p>
    <p>Ez a funkció <strong>tesztüzemben</strong> fut egy darabig, mert nem tudjuk, mennyire gondoltuk jól, hogy saját statisztikát vezetünk. Meglátjuk!</p>
  </div>
</div>

<div class="row pt-3">
  <div class="col-md-12">
    <h4 class="subtitle">30 nap</h4>
    <p class="text-muted">Látogatások számának alakulása az elmúlt 30 napban.<?=date('Y-m-d') < '2019-05-22' ? ' <span class="">A mérés ' . _date(APP['kt2_start'], 'Y.m.d') . '-én indult.</span>' : ''?></p>

    <canvas class="chart"
      ia-chart-type="line"
      ia-chart-labels="<?=htmlentities(json_encode(array_keys($sessions_30d)))?>"
      ia-chart-data="<?=htmlentities(json_encode(array_values($sessions_30d)))?>"
    ></canvas>
  </div>
</div>

<div class="row pt-4">
  <div class="col-md-12">
    <h4 class="subtitle">Havi top hivatkozó URL-ek</h4>
    <p class="text-muted">Ezekről a webcímekről érkeztek látogatók a Köztérkép aloldalaira.<?=date('Y-m-d') < '2019-05-22' ? ' <span class="">A mérés ' . _date(APP['kt2_start'], 'Y.m.d') . '-én indult.</span>' : ''?></p>
    <?php
    if (count($referrers) > 0) {
      echo $app->Html->table('create', [
        '#',
        'Hivatkozó URL',
        'Oldalletöltés',
      ]);
      $i = 0;
      foreach ($referrers as $referrer) {
        $i++;
        $ref_url = $referrer->_id != '' ? $app->Html->link($app->Text->truncate($referrer->_id, 60, 'substr'), $referrer->_id, [
          'target' => '_blank',
          'title' => $referrer->_id,
        ]) : 'Nincs / Direkt';
        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $ref_url . '</td>';
        echo '<td>' . _n($referrer->count) . '</td>';
        echo '</tr>';
      }
      echo $app->Html->table('end');
    }
    ?>
  </div>
</div>


<hr class="my-3 my-md-5" />

<div class="row">
  <div class="col-md-8">
    <h4 class="subtitle">Kifejezések jelentése</h4>
    <p>Az alábbiakban magyarázzuk a fenti kifejezéseket. Ezeket nem mi találtuk fel, több évtizede kialakult szokványokra épülő logika mentén épült fel a mi mérésünk is.</p>
    <ul>
      <li><strong>Látogató:</strong> egy egyedi weblap látogatót jelent. Amikor valaki a lapra lép, a böngészőjébe írunk egy sütit egy véletlenszerűen generált egyedi azonosítóval. Ez alapján tudjuk, ha újra ugyanaz a személy jön. Logikusnak tűnne kijelenteni, hogy a "Látogató" azt jelenti, hány külön személy látogatja a Köztérképet, de nem mondhatjuk, mert ha pl. valaki a mobiltelefonján és az asztali gépén is köztérképezik, akkor már nem tudjuk, hogy ugyanarról a személyről van szó, mert a sütit nem kötjük a belépett felhasználóhoz sem. Tehát ez a szám "megközelítőleg" azonos azzal, hány látogató jön a lapra, de nem pontosan. Nagyságrendi mutatónak viszont tökéletes.</li>
      <li><strong>Látogatás:</strong> egy látogatás az, amikor megnyitja valaki a lapot, szörföl rajta pár percig és bezárja. Ha újra megnyitja, akkor már új látogatásnak vesszük.</li>
      <li><strong>Oldalletöltés:</strong> minden egyes oldal megtekintés. Tehát ha egy műlapról átkattintasz egy másikra, akkor az új oldalletöltés. De az is, ha F5-öt nyomsz egy lapon. A műlapok, alkotók, települések és más adatlapok megtekintésszámát innen számoljuk, de kiszűrjük a frissítéseket, hogy ne lehessen "manuálisan" feljebb tornászni a megtekintésszámokat.</li>
      <li><strong>Hivatkozó URL:</strong> egy oldalra lépve honnan érkeztél? Minden oldalletöltésnél tároljuk ezt, de a statisztikákban a külső, nem köztérképes hivatkozások megjelenítésére fókuszálunk. A "Nincs / Direkt" jelölés itt azt jelenti, hogy vagy le van tiltva, vagy nincs hivatkozó URL, tehát beírta a böngészőbe a látogató a címet.</li>
      <li><strong>Eltöltött idő:</strong> egy oldalletöltéstől az elkattintásig eltelt idő.</li>
    </ul>
  </div>
  <div class="col-md-4">
    <div class="kt-info-box"><span class="far fa-info-circle mr-2"></span>A részletes és mindenki számára megjelenített saját webstatisztikai mérés <?=_date(APP['kt2_start'])?> napon indult.</div>
  </div>
</div>