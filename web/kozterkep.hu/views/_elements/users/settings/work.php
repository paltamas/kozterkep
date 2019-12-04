<div class="row m-0">
  <?php
  echo $app->Form->create($_user, [
    'method' => 'post',
    'class' => 'col-md-5 col-lg-4 mb-4',
    'id' => 'Form-Users-Settings-Community-Work',
    'ia-form-change-alert' => 1,
  ]);

  echo $app->Form->input('editor_on', [
    'type' => 'select_button',
    'options' => [
      1 => 'Részt veszek',
      0 => 'Nem veszek részt',
    ],
    'label' => 'Részvétel a közösségi szerkesztésben',
  ]);

  echo $app->Form->input('managing_on', [
    'type' => 'select_button',
    'options' => [
      1 => 'Kezelem',
      0 => 'Nem kezelem',
    ],
    'label' => 'Saját műlapok kezelése',
  ]);

  echo $app->Form->input('license_type_id', [
    'options' => $app->Users->licenses_selectable($_user),
    'label' => 'Saját képeim és más feltöltött fájljaim felhasználhatósága',
    'help' => 'Ez az alapértelmezett jog kerül beállításra feltöltéskor, de mindenhol egyedileg módosíthatod. Csak megengedőbb irányba módosíthatod, tehát ha változtatsz, nem tudsz később szigorúbb irányba visszaváltani.',
  ]);

  echo $app->Form->end('Mentés', [
    'name' => 'kozos-munka',
    'class' => 'btn-primary'
  ]);
  ?>

  <div class="col-md-7 col-lg-8 mb-5">
    <h4 class="title">Mit jelent a közösségi szerkesztés?</h4>
    <p>A "Részt veszek" választásakor eléred a "Köztér" aloldal összes funkcióját és részt vehetsz a weblap közösségi alapú szerkesztésében. Szavazhatsz szerkesztés jóváhagyásra és műlap publikálásra, ha pontszámod nagyobb, mint 0.</p>
    <div class="kt-info-box border mb-2">
      <?php if ($_user['score'] == 0) { ?>
        Jelenleg még nem vehetsz részt a szavazásokban. Amennyiben legalább <?=sDB['user_scores']['settings']['artpiece_limit']?> műlapot feltöltesz, aktivitásodtól függő pontszámmal te is szavazhatsz majd.
      <?php } else { ?>
        Jelenleg <strong><?=$_user['score']?> ponttal</strong> szavazhatsz. A pontértéked az aktivitásod alapján kerül kiszámításra.
      <?php } ?>
    </div>
    <p>Ha a "Nem veszek részt" állapotot mented, akkor nem jelennek meg más műlapjain a közösségi szerkesztési funkciók és a főmenüből kivesszük a "Köztér" menüpontot. A saját menüdból persze eléred, és részt is vehetsz a munkában, de picit rejtettebb a dolog így.</p>

    <h4 class="title">Mit jelent a saját műlapok kezelése?</h4>
    <p>Ha azt jelölöd, hogy "Nem kezelem", akkor a műlapjaidra érkező szerkesztések várakozási idő nélkül a közösség elé kerülnek, és ők kezelik azokat helyetted. A szerkesztésekről értesítést sem kapsz.</p>
    <p>Ha <?=sDB['limits']['edits']['inactive_after_months']?> hónapig nem jelentkezel be, automatikusan átáll "Nem kezelem"-re az állapotod és utána manuálisan kell visszaállítanod.</p>

    <?php if ($_user['license_type_id'] == 7) { ?>
      <h4 class="title">Kérünk, tedd szabaddá képeidet!</h4>
      <p>Képeid felhasználhatóságát te szabályozod. Tekintettel arra, hogy ez egy közösségi adatbázis, és többnyire mi is ingyenesen elérhető információkra támaszkodunk, kérünk arra, hogy képeid felhasználását tedd szabaddá, ezzel is terjesztve a köztéri alkotások hírét.</p>
    <?php } else { ?>
      <h4 class="title text-success font-weight-bold"><span class="fab fa-creative-commons mr-1"></span><span class="fas fa-check-circle mr-2"></span>Nagyon köszönjük, hogy szabaddá tetted képeidet!</h4>
      <p>Tekintettel arra, hogy ez egy közösségi adatbázis, és többnyire mi is ingyenesen elérhető információkra támaszkodunk, nagyon örülünk, hogy te is lehetővé teszed másoknak, hogy a megadott licensz alatt felhasználhassák képeidet.</p>
    <?php } ?>
    <p>A képek, leírások és további adatok felhasználhatóságáról a <?=$app->Html->link('Jogi nyilatkozat', '/oldalak/jogi-nyilatkozat')?> alatt olvashatsz el mindent.</p>

    <?=$app->Html->link('Felhasználhatósági licenszek értelmezése', '#licensz-informaciok', [
      'icon' => 'angle-down',
      'data-toggle' => 'collapse',
      'class' => 'font-weight-bold',
    ])?>
    <div class="collapse" id="licensz-informaciok">
      <p class="text-muted">Az alábbi táblázatban a Köztérkép által használt licenszeket látjátok. Ezek közül a "Nincs engedély"-en kívül minden licensz Creative Commons alatt definiált.</p>
      <?=$app->element('layout/etc/cc_licenses')?>
    </div>

  </div>

  <div class="col-md-7 mb-4">

  </div>
  <div class="col-md-5 col-lg-4">

  </div>

</div>