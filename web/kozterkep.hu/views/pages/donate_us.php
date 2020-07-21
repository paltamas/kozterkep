<div class="">

  <div class="lead mb-5">
    Támogasd működésünket, hogy még sok-sok évtizeden keresztül gyűjthessük össze és mutathassuk be a köztéri műalkotásokat. Szervereink költsége havi 75-<?=_n(90000)?> forint között mozog, valamint az eddig önkéntes munkaként végzett fejlesztések anyagi finanszírozása nagyban hozzájárulna a stabilitás és a függetlenség megtartásához. Erre gyűjtünk!
  </div>

  <div class="row">
    <div class="col-md-5 pr-md-3">
      <h4 class="mb-3"><span class="fab fa-paypal mr-2"></span>Adományozás PayPal-on keresztül</h4>
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick" />
        <input type="hidden" name="hosted_button_id" value="QK77ATG62RAQU" />
        <input type="image" src="/img/paypal-donate-button.png" width="150" border="0" name="submit" title="PayPal - biztonságos online fizetés" alt="Donate with PayPal button" />
        <img alt="" border="0" src="https://www.paypal.com/en_HU/i/scr/pixel.gif" width="1" height="1" />
      </form>
      <div>
        <span class="text-muted">Választhatod a rendszeres adományozást, ha fontosnak tartod hosszú távú működésünket!</span>
      </div>
    </div>
    <div class="col-md-7">
      <h4 class="mb-2"><span class="fal fa-money-check mr-2"></span>Adomány közvetlen átutalása</h4>
      <div class="lead mb-2">
        Magnet Bank, Pál Tamás<br />
        <strong>16200254-10094477</strong>
        <br><small class="text-muted">IBAN: HU32 1620 0254 1009 4477 0000 0000</small>
      </div>
      <div>
        <span class="text-muted">Az átutalás közleményébe írd be email címedet, hogy felvehessük veled a kapcsolatot neved publikálásával kapcsolatban.</span>
      </div>
    </div>

    <div class="col-12 mt-4">Mivel a gyűjtés magánszemélyként történik, ezért <strong>csak magánszemélyek adományát várjuk.</strong> A beérkezett adományokat folyamatosan vezetjük ezen az oldalon (az adomány küldőjét mindig megkérdezzük, névvel, vagy név nélkül szeretne-e szerepelni). A támogatások teljes mértékben a weblap üzemeltetésére és fejlesztésére kerülnek felhasználásra.</div>
  </div>
</div>

<hr class="my-5" />

<?=$app->element('pages/index/short_intro')?>

<div class="">

  <div class="row">
    <div class="col-md-6 col-lg-7">
      <h3>Segítsd munkánkat!</h3>
      <div class="mt-3 lead">2006-ban személyes hobbiként születtünk, majd pár év alatt hatalmas közösségi adatbázissá nőttünk, amelynek legfőbb célja a hazai és magyar vonatkozású köztéri alkotások hiánytalan összegyűjtése és részletes bemutatása. Az elmúlt évek eseményeiből tanulva szeretném, ha a Köztérkép függetlenül és folyamatosan fenntartható formában stabilan üzemelne, ezért 2020-ban állandó támogatási programot indítok, aminek keretében a működési és fejlesztési költségeket adományok formájában tervezem összegyűjteni.<br />Amennyiben úgy érzed, célunk számodra is fontos, akkor lépj te is támogatóink sorába!
        <br /><br />Köszönettel:
        <br /><div class="float-left"><?=$app->Users->profile_image(1, 4)?></div>
        Pál Tamás, üzemeltető
      </div>
    </div>
    <div class="col-md-6 col-lg-5">

      <h3>Eddigi adományok listája</h3>
      <p class="text-muted">Heti 1-2 alkalommal frissítjük az alábbi listát.</p>
      <?php
      echo $app->Html->table('create');
      foreach ($donations as $donation) {
        echo '<tr>';
        echo '<td>' . _time($donation['date'], 'Y.m.d.') . '</td>';
        echo '<td>' . $donation['name'] . '</td>';
        echo '<td>' . _n($donation['amount']) . '</td>';
        echo '</tr>';
      }
      echo $app->Html->table('end');
      ?>
    </div>
  </div>
</div>