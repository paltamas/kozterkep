<div class="row m-0">

  <?php
  echo $app->Form->create($_user, [
    'method' => 'post',
    'class' => 'col-md-7 mb-4',
    'id' => 'Form-Users-Settings-Alerts',
    'ia-form-change-alert' => 1,
  ]);

  echo '<h5 class="subtitle mb-3">Szüneteltetések</h5>';

  echo $app->Form->input('pause', [
    'type' => 'checkbox',
    'label' => 'Minden email, beszélgetés és értesítés kijelzés kikapcsolása',
    'value' => 1,
    'help' => 'Nem küldünk semmilyen emailt és nem jelezzük ki a fejlécben sem az új értesítéseket vagy üzeneteket.'
  ]);

  echo $app->Form->input('notification_pause', [
    'type' => 'checkbox',
    'label' => 'Csak az értesítés jelzések kikapcsolása itt',
    'value' => 1,
    'help' => 'Az értesítéseket megkapod, de nem jelezzük ki a fejlécben az új értesítéseket. Ha emailt pipáltál, az mehet, amíg nem vagy belépve.'
  ]);

  echo $app->Form->input('game_notifications_pause', [
    'type' => 'checkbox',
    'label' => 'Játék értesítések kikapcsolása',
    'value' => 1,
    'help' => 'Nem születik értesítés számodra az érintésekről, térkapszulákról és más, játékbeli eseményekről.'
  ]);

  echo $app->Form->input('out_of_work', [
    'type' => 'select_button',
    'options' => [
      0 => 'Kikapcsolva',
      1 => 'Bekapcsolva',
    ],
    'label' => 'Vakáció-válasz',
    'help' => 'Ha ezt bekapcsolod, akkor automatikus választ kap minden neked író személy arról, hogy jelenleg nem vagy online és valószínűleg az üzeneteket sem olvasod.'
  ]);

  echo $app->Form->input('auto_reply', [
    'type' => 'textarea',
    'label' => 'Vakáció-válasz egyedi tartalma',
    'help' => 'Ide írd, ha szeretnéd tudatni a vakáció-válaszban, hogy pl. meddig nem leszel online.',
    'divs' => [
      'ia-toggleelement-parent' => '#Out-of-work',
      'ia-toggleelement-value' => 1,
    ]
  ]);


  echo '<hr class="my-4" />';

  echo '<h5 class="subtitle mb-3">Küldött emailek</h5>';

  echo '<div class="mb-3">';
  echo $app->Html->link('Email az értesítésekről', '#notification-emails', [
    'data-toggle' => 'collapse',
    'icon_right' => 'bell',
    'class' => 'd-block',
  ]);
  echo '</div>';

  echo '<div class="p-2 bg-light rounded mb-3 collapse" id="notification-emails">';

  /*
   * Nincs bevezetve, mert komplex eléggé és sztem át kell írni
   * az alert jobot.
   * echo $app->Form->input('email_notification_interval', [
    'type' => 'select_button',
    'options' => [
      5 => '5p',
      30 => '30p',
      60 => '1ó',
      180 => '3ó',
      480 => '8ó',
      1140 => 'Naponta',
    ],
    'label' => 'Email küldési intenzitás',
    'help' => 'Ha nem vagy itt, ennyi percenként nézzük meg, hogy van-e olyan dolog, amiről értesíthetünk.',
  ]);*/

  foreach (sDB['notification_types'] as $key => $type) {
    echo '<div class="row border-bottom pb-1 mb-1 mx-0">';
    echo '<div class="col col-6 col-sm-4">';
    echo $app->Form->input('alert_settings[notifications_' . $key .']', [
      'label' => $type[0],
      'type' => 'checkbox',
      'value' => 1,
      'checked' => @$alert_settings['notifications_' . $key] == 1 ? true : false,
    ]);
    echo '</div>';
    echo '<div class="col col-6 col-sm-8">';
    echo $app->Form->help($type[1]);
    echo '</div>';
    echo '</div>';
  }

  if ($_user['headitor'] == 1
    || $_user['admin'] == 1
    || $_user['id'] == CORE['USERS']['sets']
    || $_user['id'] == CORE['USERS']['places']
    || $_user['id'] == CORE['USERS']['artists']
  ) {
    echo '<p class="text-muted mt-2 mb-0"><strong>Felelősök, figyelem</strong>: az Alkotótár, a Településtár és a Közös gyűjtemények értesítőiről akkor kapsz emailt, ha a "Saját dolgaim" jelölőt pipálod.</p>';
  }

  echo '</div>';


  echo $app->Form->input('alert_settings[conversations]', [
    'type' => 'select_button',
    'options' => [
      1 => 'Kérem',
      0 => 'Nem kérem',
    ],
    'value' => @$alert_settings['conversations'] == 1 ? true : false,
    'label' => 'Email a beszélgetésekről<span class="fal fa-envelope text-muted ml-2"></span>',
    'help' => 'Minden olyan új üzenetről emailt kapsz, ami akkor érkezik, amikor épp nem vagy itt.'
  ]);


  echo $app->Form->input('alert_settings[work]', [
    'type' => 'select_button',
    'options' => [
      1 => 'Kérem',
      0 => 'Nem kérem',
    ],
    'value' => @$alert_settings['work'] == 1 ? true : false,
    'label' => 'Közös munka emailek<span class="fal fa-wrench text-muted ml-2"></span>',
    'help' => 'Közösségi munkáddal valamint a weblap újdonságaival kapcsolatos emailek.'
  ]);



  echo '<hr class="my-4" />';

  echo '<h5 class="subtitle mb-3">Minerva hírlevelek</h5>';

  echo $app->Form->input('newsletter_settings[weekly_harvest]', [
    'type' => 'select_button',
    'options' => [
      1 => 'Kérem',
      0 => 'Nem kérem',
    ],
    'label' => 'Heti szüret Minerva hírlevél',
    'value' => @$newsletter_settings['weekly_harvest'] == 1 ? true : false,
    'help' => 'Vasárnap este küldjük az elmúlt hét termésének legjavát email hírlevelünkben.'
  ]);

  echo $app->Form->input('newsletter_settings[daily]', [
    'type' => 'select_button',
    'options' => [
      1 => 'Kérem',
      0 => 'Nem kérem',
    ],
    'label' => 'Napi hírmondó Minervától',
    'value' => @$newsletter_settings['daily'] == 1 ? true : false,
    'help' => 'Minden reggel 7:00-kor landol email postaládádban az előző 24 óra műlapjainak listája.'
  ]);

  echo $app->Form->end('Mentés', [
    'name' => 'ertesitesek',
    'class' => 'btn-primary'
  ]);
  ?>


  <div class="col-md-5">
    <h4 class="title">Szünetet szeretnél?</h4>
    <p>Ha egy kis lazításra vágysz, pipáld ki az első jelölőnégyezetet (Minden email és belső értesítés kikapcsolása) és ha van kedved, segíts a tagoknak, hogy most nem annyira vagy elérhető: pipáld ki a "Vakáció-válasz bekapcsolása" jelölőt, és írj néhány szót, hogy ha közölnél valamit. Például hasznos infó, ha megemlíted, mikor leszel legközelebb online.</p>

    <h4 class="title">Na de ki az a Minerva?</h4>
    <p>A Köztérkép hírlevélküldő istennője, aki naponta és hetente közvetíti minden feliratkozónak az újdonságokat. További infókat <?=$app->Html->link('itt találsz', '/minerva/bemutatkozas', ['class' => 'font-weight-bold'])?>.</p>
  </div>
</div>