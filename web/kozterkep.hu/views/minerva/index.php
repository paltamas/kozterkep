<p class="lead mb-4">Minerva hírleveleink jelenleg regisztrált tagjaink számára érhetőek el. Most az alábbi 2 típusú hírlevelünk készül rendszeresen. Feliratkozásaidat jelenleg a Beállítások / Értesítések alatt módosíthatod.<br /><?=$app->Html->link('Hírlevél feliratkozásaid kezelése', '/tagsag/beallitasok#ertesitesek', [
  'class' => 'font-weight-bold',
  'icon_right' => 'arrow-right',
])?></p>

<div class="card-deck">

  <div class="card bg-light border-0 shadow mb-5">
    <div class="card-body p-4">
      <h2 class="display-2 my-3"><?=$app->Html->icon('apple-crate fal mr-1')?>Heti szüret</h2>
      <p>Minden <strong>vasárnap este</strong> kézbesítjük az elmúlt hét termésének legjavát.</p>
      <ul>
        <li>Érdekes új és aktuálissá vált korábbi műlapok</li>
        <li>Heti avatások és hírek</li>
        <li>Blogbejegyzések és Gépház hírek</li>
        <li>Követett dolgokkal kapcsolatos események</li>
      </ul>

      <p><?=$app->Html->icon('users mr-1') . _n($receiver_count['weekly'])?> feliratkozó</p>

      <p><?=$app->Html->link('Heti szüret archívum', '/minerva/archivum')?></p>

    </div>
  </div>

  <div class="card bg-gray-kt border-0 shadow mb-5">
    <div class="card-body p-4">
      <h2 class="display-2 my-3"><?=$app->Html->icon('newspaper fal mr-1')?>Napi hírmondó</h2>
      <p><strong>Minden reggel 7:00-kor</strong> landol postaládádban az előző 24 óra feltöltéseinek listája.</p>
      <p>A közeljövőben ez egy személyre szabható hírlevéllé válik, addig akkor érdemes megrendelned, ha gyorsan képbe akarsz kerülni az oldalra látogatás nélkül.</p>

      <p><?=$app->Html->icon('users mr-1') . _n($receiver_count['daily'])?> feliratkozó</p>
    </div>
  </div>
</div>

<div class="mt-md-5 text-center">
  <img class="img-fluid" src="/img/minerva.png" />
  <div class="text-muted">A képen Minerva, hírlevél-istennőnk látható.<br />Az első ismert archaikus ábrázolás a Szoborlap-korszakból.</div>
</div>