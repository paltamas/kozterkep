<div class="row">
  <div class="col-md-5 mb-4">
    <p>Biztonsági okból bekérjük jelszavadat a profilod törléséhez.<br /><strong>A gomb megnyomása után lefutó törlési műveletek nem visszavonhatók!</strong></p>
    <?php
    echo $app->Form->create(null, [
      'method' => 'post',
      'class' => 'mt-4'
    ]);

    echo $app->Form->input('deleted_name', [
      'type' => 'select_button',
      'label' => 'Válaszd ki, milyen név legyen látható munkád mellett a törlés után',
      'required' => true,
      'options' => [
        1 => $_user['name'],
        2 => 'Törölt Tag - ' . $_user['id'],
      ],
      'value' => 1,
      'class' => 'btn',
    ]);

    echo $app->Form->input('pass', [
      'type' => 'password',
      'label' => 'Add meg jelszavad a törlés véglegesítéséhez',
      'required' => true,
      'class' => 'focus'
    ]);

    echo $app->Form->end('Profil törlés jóváhagyása', [
      'class' => 'btn-primary',
    ]);
    ?>
  </div>
  <div class="col-md-7 mb-4">
    <h4 class="title">Mi történik, ha törlöd a profilod?</h4>

    <p>A profil törlés elve az, hogy törlődjön adatbázisunkból minden személyes adatod, de sértetlen és egységes maradjon az a munka, amivel a közösségi adatbázisunkat bővítetted. Ennek értelmében:</p>

    <ul>
      <li>Töröljük profilodról a személyes adataidat, profilképedet és fejlécképedet. A "lecsupaszított" profiloldalad megmarad, egyben tartva munkádat.</li>
      <li>Töröljük az értesítéseidet, érintéseidet, követéseidet és minden nem adatbázist építő tevékenységedet.</li>
      <li>Ami marad: műlapok, fotók, hozzászólások, blogposztok, beküldött események és más közösségi munka.</li>
      <li>Semmilyen email értesítést nem küldünk a jövőben.</li>
      <li>Neved megjelenése azon múlik, mit választasz.</li>
    </ul>

    <div class="kt-info-box">
      <strong>Csak az emailek zavarnak?</strong> <?=$app->Html->link('Ezen az oldalon', '/tagsag/beallitasok#ertesitesek')?> pár kattintással könnyedén leiratkozhatsz mindenről, vagy módosíthatod, hogy miről kapj értesítéseket.
    </div>

  </div>
</div>