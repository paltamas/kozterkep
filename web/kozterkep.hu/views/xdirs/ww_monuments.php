<div class="kt-info-box mb-4">
  <p><strong>Az I. világháború magyar vonatkozású köztéri, valamint közösségi hősi emlékeinek lezárt és nem frissített adatbázisa.</strong> Mindezt Somfay Örs gyűjtése alapján készítettünk el webes, könnyen kereshető formában.</p>
  <p>További információkért <?=$app->Html->link('görgess a lap aljára', '#hosi-emlek-informaciok', ['icon_right' => 'arrow-down'])?></p>
</div>

<?php
if (isset($category)) {
  $_params->query['tema'] = $category;
}
if (isset($blogger)) {
  $_params->query['tag'] = $blogger;
}

echo $app->Form->create($_params->query, [
  'class' => 'form-inline p-4 bg-gray-kt d-fley justify-content-center unsetEmptyFields',
]);

echo $app->Form->input('hely', [
  'placeholder' => 'Helység',
  'class' => 'mr-4 my-2'
]);

echo $app->Form->input('alkoto', [
  'placeholder' => 'Alkotó',
  'class' => 'mr-4 my-2'
]);

echo $app->Form->input('tema', [
  'type' => 'select',
  'options' => ['barmilyen' => 'Bármilyen téma']
    + $app->Arrays->id_list($parameters[8], 'description', ['sort' => 'ASC']),
  'class' => 'mr-4 my-2 limited',
]);

echo $app->Form->input('tipus', [
  'type' => 'select',
  'options' => ['barmilyen' => 'Bármilyen típus']
    + $app->Arrays->id_list($parameters[6], 'description', ['sort' => 'ASC']),
  'class' => 'mr-4 my-2 limited',
]);


echo $app->Form->submit('Keres', [
  'class' => 'btn-secondary my-2',
]);

echo $app->Form->end();


if ($was_search) {
  echo '<div class="my-2 text-center">';
  echo $app->Html->link('Szűrés törlése', '/adattar/hosi-emlek', [
    'icon' => 'times',
  ]);
  echo '</div>';
}


echo $app->Html->pagination(count($monuments), $pagination);

if (count($monuments) > 0) {

  echo $app->Html->table('create', [
    'Település',
    'Téma',
    'Típus',
    'Avatás',
  ]);

  foreach ($monuments as $monument) {
    echo '<tr>';
    echo '<td>' . $monument['place_name'] . '</td>';
    echo '<td class="font-weight-bold">' . $app->Html->link($app->Arrays->json_list($monument['topics'], $parameters[8], 'description'), '/adattar/hosi-emlekmu/' . $monument['id'])  . '</td>';
    echo '<td>' . $parameters[6][$monument['type_id']]['description']  . '</td>';
    echo '<td>' . $monument['unveil_year'] . '</td>';
    echo '</tr>';
  }

  echo $app->Html->table('end');


} else {
  echo $app->element('layout/partials/empty', [
    'class' => 'text-center my-5'
  ]);
}

if (count($monuments) > 25) {
  echo $app->Html->pagination(count($monuments), $pagination);
}
?>

<hr class="highlighter text-center my-5" />

<div id="hosi-emlek-informaciok" class="mt-5">

  <h2 class="title">Adatbázissal kapcsolatos általános információk</h2>

  <div class="row">

    <div class="col-md-7 col-lg-8">
      <p class="mt-3">Somfay Örs, az adatbázis gazdájának bevezetője:</p>

      <p>A "Nagy Háború" kitörésének századik évfordulója alkalmából, több mint 10 éves kutatómunka eredményeként jött létre az itt látható online, ingyenes adatbázis. Induláskor 3557 magyar vonatkozású, a Kárpát-medence magyar lakta területein található hősi emléket tartalmaz a gyűjtemény, ami egy nagyságrenddel nagyobb minden eddigi összefoglalónál, és reményeim szerint a jövőben mind számbelileg, mind pedig adattartalmát illetően tovább fog bővülni, pontosabbá válik. Az eddig készült legnagyobb országos szintű magán és hivatalos gyűjtések adatait és képi anyagát is tartalmazza az adatbázis, és közel 15.000 db fotó illusztrálja. Olyan hősi emlékekről is van információnk, amikhez jelenleg még nem sikerült képet társítani. A Köztérkép rendszerén belüli online publikálás reményeim szerint újabb lehetőséget teremt majd arra, hogy képileg és tartalmilag is újabb értékes információkkal gazdagodhasson a gyűjtemény.</p>

      <p>Az adatbázissal párhuzamosan elkészült egy, a témát több oldalról is részletesen feldolgozó doktori disszertáció is. Ebben a történeti háttér, a részletes statisztikai és formai-tartalmi elemzés mellett, fontos fejezetet képez az I. világháború első hősi emlékei körének meghatározása térben, időben és tartalmi szempontból is. Jelen online adatbázisba is csak azon alkotások kerülhettek és kerülhetnek be, amelyek megfelelnek az alább megfogalmazott kritériumoknak:</p>

      <p class="lead font-italic">"I. világháborús hősi emléknek tekintem azokat a tárgyiasult, maradandó anyagú és formájú alkotásokat, amelyek tartalmukban köthetőek az első világháborúhoz és az ott elhunyt katonákhoz. Egyértelműen kifejezik az alapítók kegyeleti és emlékeztető szándékát a háború hősi halottai iránt, továbbá megjelenésükben és kifejezéstárukban a közösségi, és nem csak a személyes emlékeztetés eszközeiként funkcionálnak. Sem az alapítói szándék tekintetében, sem pedig a tartalmi megjelenés szempontjából nem követelmény a fentiek önálló megjelenése, vagyis a hősi emlék tartalmát és funkcióját tekintve többrétű is lehet, de az egyéb tulajdonságai nem befolyásolhatják az alapvető kegyeleti, emlékeztető funkciót. Azon speciális esetekben, amikor a fenti kritériumok alapján sem egyértelmű a besorolás, döntő fontossággal bír a befogadó közeg viszonyulása az emlékműhöz, amit legkönnyebben az emlékmű köré szerveződő ünnepek, események alapján lehet meghatározni."</p>

    </div>


    <div class="col-md-5 col-lg-4">

      <div class="kt-info-box mt-5">
        <h4 class="subtitle">Köztérképes kapcsolódás</h4>

        <p>2011-ben kezdtünk Örssel egyeztetni, hogy disszertációjának adatbázisát az akkori Szoborlap aloldalaként nyitjuk ki, a két adatbázis kölcsönös épülését célul kitűzve. Sok munka és viszontagság után megszületett a "Hősi Emlék" tematikus testvér-adatbázisunk, amit szeretettel ajánlok minden látogatónak és Köztérkép-tagnak.</p>

        <p>Kellemes böngészést kívánok!</p>

        <p>Pál Tamás<br />üzemgazda</p>
      </div>

    </div>

    <div class="col-12">

      <hr class="highlighter text-center my-5" />

      <h4 class="subtitle">Jogi nyilatkozat</h4>

      <h5>Alapvetések</h5>
      <p>A jelen webcímen található adatbázis Somfay Örs (továbbiakban: Adatbázisgazda) gyűjteményes munkája. Az informatikai hátteret, valamint a www.kozterkep.hu-ról származó adatok megjelenítését a Köztérkép Mozgalom biztosítja, amelynek képviselője Pál Tamás Üzemgazda (továbbiakban: Üzemeltető).</p>

      <h5>Adatbázisban szereplő adatok</h5>
      <p>Az adatbázisban szereplő adatok helyességéért sem az Üzemeltető, sem az Adatbázisgazda nem vállal felelősséget. Adatbázisgazda mindent elkövet, hogy az adatok a lehető legpontosabb megismerhető állapotot rögzítsék, de az egyes alkotások egy részének több évtizeddel ezelőtti elpusztulása miatt, és más földrajzi vagy egyéb akadályok miatt a teljesükörű bemutatás nem lehetséges.</p>

      <h5>Kiegészítések</h5>
      <p>Az adatbázishoz érkezett kiegészítések minden esetben a kiegészítést beküldő felhasználó véleményét és saját ismereteit tükrözi, így az Üzemeltető és az Adatbázisgazda ezek pontosságáért nem tud felelősséget vállalni.</p>

      <p>A kiegészítésekre bármilyen Köztérképen regisztrált, bejelentkezett felhasználónak lehetősége van. A kiegészítések törzsadat szintre való felvételét Adatbázisgazda szubjektív bírálat alapján végzi.</p>

      <h5>Adatok felhasználása</h5>
      <p>Az adatok felhasználása kizárólag az Adatbázisgazda írásbeli engedélyével lehetséges.</p>

      <h5>Képek felhasználása</h5>
      <p>Az oldalon megjelenő képek több gyűjtési forrásból származnak, így felhasználhatóságuk is eszerint alakul.</p>

      <h5>Képek a Köztérképről</h5>
      <p>Az egyes képek bal vagy jobb alsó sarkában látható vízjel alapján eldönthető, hogy Köztérképről számrmazik-e a felvétel ("szoborlap" vagy "köztérkép" felirat olvasható a képen). Amennyiben a kép Köztérképről származik, úgy az emlékmű adatlapjának jobb hasábjában elérhető linken elérhető köztérképes műlapon megjelenő képlistában a képre kattintva találod meg a kép pontos felhasználhatóságának módját.</p>

      <p>Minden köztérképes kép esetében általánosan igaz, hogy ha szabad is a felhasználás, a kép akkor sem használható üzleti céllal és a vízjel eltávolítása sem megengedett.</p>

      <h5>Gyűjtött képek</h5>
      <p>Minden nem köztérképes képet ide sorolunk, és ezek összességére igaz, hogy felhasználásuk csak az Adatbázisgazda írásbeli engedélyével történhet meg.</p>

    </div>
  </div>


</div>
