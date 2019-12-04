
    <div class="kt-info-box mb-4">

      <h4 class="display-4 mb-4">Örömmel vesszük konstruktív ötleteidet, de a kérdéseket is szeretjük. A Köztérképen szinte mindenben segít: a közösség!</h4>

      <div class="mb-3"><span class="far fa-users fa-lg mr-2"></span><span class="font-weight-bold">Ha egy tagot keresel, akkor javasoljuk, hogy regisztrálj, és írj neki belső üzenetet.</span> Tagi adatokat nem adhatunk ki, és a közvetítés is esetleges.</div>
      <div class="mb-3"><span class="far fa-map-marker fa-lg mr-2"></span><span class="font-weight-bold">Ha alkotással kapcsolatos információd van vagy hibás adatot jeleznél, kérünk, hogy regisztrálj és jelezd az adott műlapon.</span> Beküldött képeket nem áll módunkban feltölteni, valamint ha adatokat tudsz, akkor te vagy a leghitelesebb forrás azok rögzítéséhez. Kérjük, vedd ki te is a részed!</div>
      <div class="mb-4"><span class="far fa-images fa-lg mr-2"></span><span class="font-weight-bold">Ha fotót szeretnél felhasználni, akkor tedd a kép mellett megadott felhasználási licensz szerint,</span> megfelelően meghivatkozva a forrást (Köztérkép / Feltöltő neve). Egyedi engedély esetén keresd a feltöltőt, csak ő jogosult ebben nyilatkozni.</div>

      <hr />

      <div class="mb-3">A közösséget kérdeznéd? <?=$app->Html->link('Regisztrálj itt!', '/tagsag/regisztracio', [
        'class' => 'font-weight-bold',
      ])?></div>

    </div>

    <div class="my-3">
      <?=$app->Html->link('Ha a fentiek nem segítettek, írj nekünk!', '#kapcsolatfelvetel', [
        'data-toggle' => 'collapse',
        'class' => 'font-weight-bold',
      ])?> Ha a weboldal működésével kapcsolatos technikai hibát jeleznél, akkor is innen írj.
    </div>

    <div class="collapse my-4" id="kapcsolatfelvetel">


      <div class="row">
        <div class="col-md-7">
          <p class="">Email: <strong>hello@kozterkep.hu</strong><br />
            Cím: <strong>Köztérkép Mozgalom</strong> &bull; 2014 Csobánka, Vaddisznós utca 4.</p>

          <hr />

          <?php if ($_user) { ?>
            <p><strong>Bejelentkezett felhasználóként</strong> írhatsz <a href="/beszelgetesek/inditas?tag=1">közvetlenül az üzemgazdának is</a>, mert ezt a kapcsolati űrlapot is ő fogja olvasni, csak emailben válaszol rá.</p>
          <?php } else { ?>
            <p>Kérjük, minden mezőt tölts ki, hogy a lehető legpontosabb választ kaphasd. Az adataidat nem tároljuk, és az űrlap küldése és feldolgozása során az <a href="/oldalak/adatkezelesi-szabalyzat">Adatkezelési szabályzatban</a> megadottak szerint járunk el.</p>
          <?php } ?>

          <p class="mb-4">Az alábbi űrlap kitöltése után a megadott email címre válaszolunk megkeresésedre. Az üzenetet a weblap üzemgazdája olvassa.</p>
          <?=$app->element('pages/contact_form')?>

        </div>
      </div>
    </div>

  </div>
</div>
