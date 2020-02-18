<?php
use Kozterkep\AppBase as AppBase;

class UsersController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;


    if (CORE['USERS_BLOCKED']) {
      if (in_array($this->params->action, ['register', 'login', 'login_help', 'repassword', 'activation', 'email_subscriptions'])) {
        $this->redirect('/', ['<strong>Az oldal most nem elérhető.</strong> A belépés, regisztráció és más felhasználói műveletek jelenleg nem elérhetőek.', 'warning']);
      }
    }
  }


  /**
   * Tagság infó
   * statikus oldal
   */
  public function info() {
    $this->set([
      '_title' => 'Információk a tagságról',
    ]);
  }


  /**
   * Login
   */
  public function login() {
    if ($this->user) {
      $this->redirect('/');
    }
    if ($this->Request->is('post')) {
      if ($user = $this->Auth->login($this->params->data)) {
        //$this->Notifications->create($user['id'], 'Helló megint', 'Üdv újra itt. Na, hogy ityeg?');
        $this->flash('Sikeres belépés, üdvözlünk a Köztérképen!', 'success', 'bubble', APP['sessions']['alert_remove']);
        $this->redirect($this->params->data['redirect_url']);
      } else {
        $redirect = $this->params->referer ?: '/';
        $this->redirect($redirect);
      }
    }

    $this->set([
      '_title' => 'Bejelentkezés',
    ]);
  }


  /**
   * Regisztráció
   */
  public function register() {
    if ($this->user) {
      $this->redirect('/');
    }

    if ($this->Request->is('post')) {

      $data = $this->params->data;

      $errors = [];

      if ($data['name'] == '') {
        $errors[] = 'Kérjük, add meg a neved.';
      }

      if (strlen($data['password']) < 5) {
        $errors[] = 'Legalább 5 karakteres jelszót adj meg.';
      }

      if (!$this->Form->check_captcha($data)) {
        $errors[] = 'Az ellenőrző mező értéke nem volt helyes, kérjük próbáld újra.';
      }

      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adj meg egy érvényes email címet.';
      } elseif ($this->DB->find_by_email('users', $data['email'])) {
        $errors[] = 'Ezzel az email címmel már létezik regisztráció. Ha elfelejtetted a jelszavad, használd a "Belépési segítség" aloldalunkat!';
      }

      if (@$data['disclaimer'] != 1) {
        $errors[] = 'A regisztrációhoz el kell fogadnod a hivatkozott szabályzatainkat.';
      }

      if (count($errors) == 0) {
        $activation_hash = bin2hex(random_bytes(42));

        // Reg utáni átirányítás
        $redirect_after = '';
        if (isset($data['redirect_after'])
          && (strpos($data['redirect_after'], 'http') === false)) {
          // külhonba nem dobunk, az valami hekk.
          $redirect_after = $data['redirect_after'];
        }

        $id = $this->DB->insert('users', [
          'email' => $data['email'],
          'password' => password_hash($data['password'], PASSWORD_ARGON2I),
          'name' => $data['name'],
          're_hash' => json_encode([time(), $activation_hash]),
          'redirect_after' => $redirect_after,
          'license_type_id' => 6,
          'changes_accepted' => 1,
          'created' => time(),
          'modified' => time(),
        ]);

        // A többit aktiváláskor
        if ($id) {

          $this->Mongo->insert('jobs', [
            'class' => 'emails',
            'action' => 'send',
            'options' => [
              'template' => 'system',
              'to' => $data['email'],
              'name' => $data['name'],
              'subject' => 'Regisztráció befejezése',
              'body' => texts('emails/regfinish', [
                'name' => $data['name'],
                'link' => APP['url'] . '/tagsag/aktivacio/' . $activation_hash,
                'contact_form' => APP['url'] . '/oldalak/kapcsolat'
              ])
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);

          $email_box_link = $this->Html->email_account_link($data['email']);
          $this->flash('Sikeresen létrejött a hozzáférésed! Hamarosan egy emailt kapsz, amiben aktiváló linket találsz.' . $email_box_link, 'success');
          $this->redirect('/');

        } else {
          $this->flash(texts('varatlan_hiba'), 'danger');
        }
      }

      $this->flash($errors, 'danger');
    }

    $this->set([
      '_title' => 'Regisztráció',
    ]);
  }


  /**
   * Jelszó beállító oldal elavuló link ellenőrzéssel
   * @param null $hash
   */
  public function activation($hash = null) {

    $user = $this->DB->first('users', [
      're_hash LIKE' => '%"' . $hash . '"%',
      'active' => 0
    ]);

    if ($user) {

      $hash = json_decode($user['re_hash'], true);

      if ($hash[0] < strtotime('-72 hours')) {

        // LEJÁRT aktiváló link

        $this->flash('Lejárt aktiváló link. Ha új aktiváló linket szeretnél kérni, add meg ezen az oldalon a regisztrált email címedet.', 'danger');
        $redirect = '/tagsag/bejelentkezesi-segitseg';

      } elseif ($user['active'] == 1) {

        // MÁR aktiváltunk

        $this->flash('Már aktiváltad hozzáférésedet. Jelentkezz be a megadott email cím és jelszó párossal.', 'warning');
        $redirect = '/tagsag/belepes';

      } else {

        // OK, aktiválás
        $this->DB->update('users', [
          'link' => 'user-' . $user['id'],
          'active' => 1,
          'activated' => time(),
          'last_here' => time(),
          'kt2' => time(),
          'redirect_after' => '',
        ], $user['id']);

        // Mongo users
        if (!$this->Mongo->first('users', ['user_id' => $user['id']])) {
          $this->Mongo->insert('users', ['user_id' => $user['id']]);
        }

        $this->flash('Sikeres aktiváció! Üdv a Köztérképen :)', 'success', 'div', APP['sessions']['alert_remove']);
        $this->Auth->login($user['id']);

        $redirect = $user['redirect_after'] != ''? $user['redirect_after'] : '/kozter';
      }

    } else {

      $this->flash('Érvénytelen aktiváló link.', 'danger');
      $redirect = '/';
    }

    $this->redirect($redirect);
  }


  /**
   * Belépési segítség kérése (jelszó reset)
   */
  public function login_help() {
    if ($this->user) {
      $this->redirect('/');
    }

    if ($this->Request->is('post')) {
      if (filter_var($this->params->data['email'], FILTER_VALIDATE_EMAIL)) {
        $this->Auth->repassword($this->params->data['email']);
        $email_box_link = $this->Html->email_account_link($this->params->data['email']);
        $this->flash('Ha létezik rendszerünkben az email cím, hamarosan megérkezik rá a jelszó beállító link!' . $email_box_link, 'info');
        $this->redirect('/');
      } else {
        $this->flash('Kérjük ellenőrizd, hogy helyes formátumú email címet adtál-e meg.', 'danger');
        $this->redirect('referer');
      }
    }

    $this->set([
      '_title' => 'Belépési segítség',
    ]);
  }


  /**
   *
   * Email módosítás jóváhagyása
   * sh1-gyel megnézzük, hogy van-e ilyen várakozó cím
   * aztán a postolt jelszót is csekkoljuk, és ha OK,
   * akkor fogadjuk el a cserét.
   *
   * @param $hash
   */
  public function email_change($hash) {
    $user = $this->DB->first('users', [
      'SHA1(email_to_confirm)' => $hash
    ]);

    if (!$user) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if ($this->Request->is('post')) {
      if (password_verify($this->params->data['pass'], $user['password'])) {
        $this->DB->update('users', [
          'email' => $user['email_to_confirm'],
          'email_to_confirm' => ''
        ], $user['id']);
        // Remélhetőleg ő van belépve ;)
        // ha nem, az sem okoz gondot
        $this->Auth->resession();
        return $this->redirect('/tagsag/beallitasok', ['Sikeresen módosítottuk emailcímedet. Mostantól ezzel az új címeddel jelentkezz be és ide küldjük az értesítőinket is.', 'success']);
      } else {
        $this->redirect('referer', ['Hibás jelszó, kérjük próbáld újra.', 'danger']);
      }
    }

    $this->set([
      'user' => $user,
      '_sidemenu' => false,
      '_title' => 'Email módosítás jóváhagyása',
      '_shareable' => false
    ]);
  }


  /**
   * Jelszó beállító oldal elavuló link ellenőrzéssel
   * @param null $hash
   */
  public function repassword($hash = null) {

    if ($this->user) {
      $this->redirect('/', ['Upsz, itt valami fura. Vagy már megváltoztattad a jelszavadat, vagy más anomália van. Ha valami rejtélyes hibára gyanakszol, jelezd az üzemgazdának.', 'warning']);
    }

    $user = $this->DB->first('users', [
      're_hash LIKE' => '%"' . $hash . '"%'
    ]);

    $valid = false;

    if ($user) {
      $hash = json_decode($user['re_hash'], true);
      if ($hash[0] > strtotime('-24 hours')) {
        // Valid, mehetünk tovább
        $valid = true;
      } else {
        // Lejárt törlése
        $this->DB->update('users',
          [
            're_hash' => '',
            'repassword' => 0,
          ],
          $user['id']
        );
      }
    }

    if (!$user || $valid == false) {
      $this->flash('A jelszó-változtató link érvénytelen vagy lejárt. Kérj újat, amennyiben továbbra is szükséged van rá.', 'warning');
      $this->redirect('/tagsag/bejelentkezesi-segitseg');
    }


    // Űrlapfeldolgozás
    if ($this->Request->is('post')) {

      // Megfelelő-e a post
      if (strlen($this->params->data['pass']) >= 5
        && $this->params->data['pass'] == $this->params->data['pass_confirm']) {
        // OK

        // Mentés és repassword dolgok ürítése és aktiválás, biztos, ami biztos
        $updates = [
          're_hash' => '',
          'repassword' => 0,
          'password' => password_hash($this->params->data['pass'], PASSWORD_ARGON2I)
        ];
        if ($user['active'] == 0) {
          $updates['active'] = 1;
          $updates['activated'] = time();
        }
        if ($user['kt2'] == 0) {
          $updates['kt2'] = time();
        }
        $this->DB->update('users',
          $updates,
          $user['id']
        );

        // Jelezzük neki emailben
        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'template' => 'system',
            'user_id' => $user['id'],
            'subject' => 'Jelszó változtatás',
            'body' => texts('emails/pwchange', [
              'name' => $user['name'],
              'contact_form' => APP['url'] . '/oldalak/kapcsolat'
            ])
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);

        // Beléptetjük
        $this->Auth->login([
          'email' => $user['email'],
          'password' => $this->params->data['pass']
        ]);

        $this->flash('Sikeres jelszó beállítás. Üdv a Köztérképen!', 'success', 'div', APP['sessions']['alert_remove']);
        $this->redirect('/');
      } else {
        // nem egyezik
        $this->flash('Az új jelszó legyen legalább 5 karakteres és a két mező tartalmának egyeznie kell.', 'danger');
        $this->redirect('referer');
      }
    }

    $this->set([
      '_sidemenu' => false,
      '_title' => 'Jelszó beállítás',
      '_shareable' => false,
    ]);
  }


  /**
   * Saját beállítások
   */
  public function settings() {
    $this->users_only();

    if (isset($this->params->query['email-modositas-torlese'])) {
      $this->DB->update('users', ['email_to_confirm' => ''], $this->user['id']);
      $this->Auth->resession();
      $this->redirect('/tagsag/beallitasok', 'Emailcímed módosítását megszakítottuk!');
    }

    if ($this->Request->is('post')) {
      $saved = false;

      $this->params->data['id'] = $this->user['id'];
      if (@$this->params->data['profil']) {

        $tab = 'profil';

        // Név változás kezelése
        if ($this->params->data['name'] != $this->user['name']
          && $this->user['name'] != '') {
          $this->Mongo->insert('jobs', [
            'class' => 'users',
            'action' => 'rename',
            'options' => [
              'user_id' => $this->user['id'],
              'new_name' => $this->params->data['name'],
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
        }

        if ($this->params->data['link'] == '') {
          // Nem jött link, mert már nem változtatható
          unset($this->params->data['link']);
        } elseif ($this->params->data['link'] != $this->user['id']) {
          // Jött link, elmentjük, hogy már változtatva
          $this->params->data['link_changed'] = 1;
        }

        // Email változás kezelése
        if ($this->params->data['new_email'] != $this->user['email']
          && filter_var($this->params->data['new_email'], FILTER_VALIDATE_EMAIL)) {
          if ($this->DB->find_by_email('users', $this->params->data['new_email'])) {
            $this->redirect('/tagsag/beallitasok', ['A választott ' . $this->params->data['new_email'] . ' emailcím már foglalt!', 'danger']);
          }
          $this->Users->email_change($this->user, $this->user['email'], $this->params->data['new_email']);
        }

        list($data, $messages) = $this->Users->manage_settings_images($this->params->data, $this->user);

        if (count($messages) > 0) {
          $this->redirect('referer', [$messages, 'danger']);
        }

        $this->params->data = array_merge($this->params->data, $data);

        $saved = $this->Validation->process($this->params->data,
          [
            'files' => 'unset',
            'profile_photo_filename' => 'string',
            'header_photo_filename' => 'string',
            'header_photo_height' => 'numeric',
            'delete_profile_photo' => 'unset',
            'delete_header_photo' => 'unset',
            'profil' => 'unset',
            'place_name' => '',
            'name' => 'minlength_3',
            'nickname' => '',
            'new_email' => 'unset',
            'link' => 'minlength_3',
            'link_changed' => 'tinyint',
            'hide_location_events' => 'tinyint',
            'birth_year' => 'birth_year',
            'city_name' => '',
            'web_links' => '',
            'introduction' => '',
            'blog_title' => '',
          ],
          'users',
          [ 'defaults' => [ 'modified' => time() ] ]
        );
      }

      if (@$this->params->data['ertesitesek']) {
        $tab = 'ertesitesek';

        $this->params->data['id'] = $this->user['id'];

        $saved = $this->Validation->process($this->params->data,
          [
            'ertesitesek' => 'unset',
            'pause' => 'tinyint',
            'notification_pause' => 'tinyint',
            'game_notifications_pause' => 'tinyint',
            'out_of_work' => 'tinyint',
            'alert_settings' => 'json_array',
            'newsletter_settings' => 'json_array',
            'auto_reply' => 'string',
          ],
          'users',
          [ 'defaults' => [ 'modified' => time() ] ]
        );

      }

      if (@$this->params->data['kozos-munka']) {
        $tab = 'kozos-munka';

        $this->params->data['id'] = $this->user['id'];

        // Licensz változás kezelése
        if ($this->params->data['license_type_id'] != $this->user['license_type_id']) {
          $modified = $this->Users->license_change($this->user['id'], $this->params->data['license_type_id']);
          if (!$modified) {
            $this->redirect('/tagsag/beallitasok#' . $tab, ['Hiba a felhasználhatósági licensz módosításában. Amennyiben a hiba tartósan fennáll, jelezd az üzemgazdának.', 'danger']);
          }
        }

        // Nem kell menteni
        unset($this->params->data['license_type_id']);

        $saved = $this->Validation->process($this->params->data,
          [
            'kozos-munka' => 'unset',
            'editor_on' => 'tinyint',
            'managing_on' => 'tinyint',
          ],
          'users',
          [ 'defaults' => [ 'modified' => time() ] ]
        );
      }

      if (@$this->params->data['felulet-mukodese']) {
        $tab = 'felulet-mukodese';


        $this->params->data['id'] = $this->user['id'];

        $this->params->data['tiny_settings'] = array_merge(_json_decode($this->user['tiny_settings']), [
          'settings_desktop_everything' => @$this->params->data['settings_desktop_everything'] == 1 ? 1 : 0,
          'fluid_view' => @$this->params->data['fluid_view'] == 1 ? 1 : 0,
          'splitted_menu' => @$this->params->data['splitted_menu'] == 1 ? 1 : 0,
          'space_home' => @$this->params->data['space_home'] == 1 ? 1 : 0,
        ]);

        $saved = $this->Validation->process($this->params->data,
          [
            'felulet-mukodese' => 'unset',
            'settings_desktop_everything' => 'unset',
            'fluid_view' => 'unset',
            'splitted_menu' => 'unset',
            'space_home' => 'unset',
            'tiny_settings' => 'json_array',
          ],
          'users',
          [ 'defaults' => [ 'modified' => time() ] ]
        );
      }

      if (@$this->params->data['jelszo-csere']) {
        $tab = 'jelszo-csere';

        // Kell a user, mert a jelszó nincs a session-tömbben; okkal
        $user = $this->DB->find_by_id('users', $this->user['id']);
        if (!password_verify($this->params->data['current_pass'], $user['password'])) {
          $this->redirect('/tagsag/beallitasok#' . $tab, ['Hibás jelenlegi jelszó, kérjük próbáld újra.', 'danger']);
        }
        if (strlen($this->params->data['new_pass']) < 5) {
          $this->redirect('/tagsag/beallitasok#' . $tab, ['Az új jelszó legyen legalább 5 karakteres.', 'danger']);
        }
        if ($this->params->data['new_pass'] != $this->params->data['confirm_new_pass']) {
          $this->redirect('/tagsag/beallitasok#' . $tab, ['Nem egyezik az új jelszó a megismételt alakkal.', 'danger']);
        }

        // OK, mentünk
        $this->DB->update('users', [
          'password' => password_hash($this->params->data['new_pass'], PASSWORD_ARGON2I)
        ], $this->user['id']);

        // Szólunk neki
        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'template' => 'system',
            'user_id' => $user['id'],
            'subject' => 'Jelszó változtatás',
            'body' => texts('emails/pwchange', [
              'name' => $this->user['name'],
              'contact_form' => APP['url'] . '/oldalak/kapcsolat'
            ])
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect('/tagsag/beallitasok#' . $tab, ['Sikeresen módosítottuk jelszavadat!', 'success']);
      }

      if ($saved || $saved === 0) {
        //$this->Notifications->create(static::$user['id'], 'Jó kis mentés', 'Végérvényesen megváltoztak a <i>dolgok</i>.');
        $this->Auth->resession();
        $this->redirect('/tagsag/beallitasok#' . $tab, texts('sikeres_mentes'));
      }
    }

    $tabs = [
      'list' => [
        'Profil' => [
          'hash' => 'profil',
          'icon' => 'address-card'
        ],
        'Értesítések' =>  [
          'hash' => 'ertesitesek',
          'icon' => 'envelope',
        ],
        'Közös munka' =>  [
          'hash' => 'kozos-munka',
          'icon' => 'users',
        ],
        'Felület működése' =>  [
          'hash' => 'felulet-mukodese',
          'icon' => 'wrench',
        ],
        'Jelszó csere' =>  [
          'hash' => 'jelszo-csere',
          'icon' => 'lock-alt',
        ]
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'profil',
      ]
    ];

    $this->set([
      'alert_settings' => json_decode($this->user['alert_settings'], true),
      'newsletter_settings' => json_decode($this->user['newsletter_settings'], true),
      '_title' => 'Beállítások',
      '_tabs' => $tabs,
      '_sidemenu' => false,
      '_shareable' => false,
      '_viewable' => '/kozosseg/profil/' . $this->user['link'],
    ]);
  }


  /**
   * Minden lényegesebb elv változás esetén dobunk ide,
   * elfogadás után resession és továbbdobás a kezdőlapra
   *
   * A szabály változás szövegét meg kell írni itt:
   * web/kozterkep.hu/views/_elements/layout/etc/accept_changes.php
   */
  public function accept_changes() {
    $this->users_only();
    if ($this->_user['changes_accepted'] == 0) {
      $this->DB->update('users', [
        'disclaimer' => 1,
        'changes_accepted' => 1,
      ], $this->user['id']);
      $this->Notifications->create($this->user['id'], 'Köszönjük a nyugtázást!', 'A szabályzatainkat a "Miez" oldalak között keresd.');
      $this->flash('Sikeres mentés, további jó munkát!', 'info', 'div', APP['sessions']['alert_remove']);
      $this->Auth->resession();
      $this->redirect('/');
    }
  }

  /**
   * Alapvető és komplex esetén dobunk ide,
   * elfogadás után resession és továbbdobás a kezdőlapra
   *
   * A szabály változás szövegét meg kell írni itt:
   * web/kozterkep.hu/views/users/disclaimer_accept.php
   *
   */
  public function disclaimer_accept() {
    $this->users_only();

    if ($this->Request->is('post')) {
      $this->DB->update('users', [
        'disclaimer' => 1,
        'changes_accepted' => 1,
      ], $this->user['id']);
      $this->Notifications->create($this->user['id'], 'Üdv újra a Köztérképen!', 'A szabályzatainkat a "Miez" oldalak között keresd.');
      $this->flash('Sikeres mentés, további jó munkát!', 'info', 'div', APP['sessions']['alert_remove']);
      $this->Auth->resession();
      $this->redirect('/');
    }

    $this->set([
      '_title' => 'Üdvözlünk az új Köztérképen!',
      '_title_row' => false,
      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_shareable' => false,
      '_bookmarkable' => false,
    ]);
  }


  /**
   * Saját követések kezelése
   */
  public function follows() {

    $this->users_only();

    $tabs = [
      'list' => [
        'Friss' => [
          'hash' => 'friss',
          'icon' => 'stars'
        ],
        'Műlapok' => [
          'hash' => 'mulapok',
          'icon' => 'map-marker'
        ],
        'Alkotók' =>  [
          'hash' => 'alkotok',
          'icon' => 'user-alt',
        ],
        'Helyek' =>  [
          'hash' => 'helyek',
          'icon' => 'map-pin',
        ],
        'Tagok' =>  [
          'hash' => 'tagok',
          'icon' => 'users',
        ],
        'Mappák' =>  [
          'hash' => 'mappak',
          'icon' => 'folders',
        ]
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'friss',
      ]
    ];

    $me = $this->Mongo->first('users', [
      'user_id' => $this->user['id']
    ]);

    // Ha valami nincs
    $me += [
      'follow_users' => [],
      'follow_artpieces' => [],
      'follow_artists' => [],
      'follow_places' => [],
      'follow_folders' => [],
    ];

    $me['follow_folders'] = (object)$me['follow_folders'];

    foreach ($me as $key => $items) {
      if (!is_array($items)) {
        $me[$key] = (array)$items;
      }
      // Ha nincs 0. elem, a mongo szerint nem tömb.. :(
      if (!isset($me[$key][0])) {
        $me[$key][0] = 0;
      }
      if (@count($items) == 0) {
        $me[$key] = [0];
      }
      sort($me[$key]);
    }

    // URL-en keresztül kapott paraméterek
    $day_count = isset($this->params->query['napszam']) ? $this->params->query['napszam'] : 30;
    $day_count = min(30, $day_count);
    $from_time = strtotime('-' . $day_count . ' days');

    $item_limit = isset($this->params->query['elemszam']) ? $this->params->query['elemszam'] : 50;
    $item_limit = min(300, $item_limit);

    // Friss műlap szerkesztések
    $artpiece_edits = $this->Mongo->find('artpiece_edits', [
      'artpiece_id' => ['$in' => $me['follow_artpieces']],
      'approved' => ['$gt' => $from_time]
    ], [
      'sort' => ['created' => -1],
      'limit' => $item_limit
    ]);

    // Friss leírások
    $artpiece_descriptions = $this->Mongo->find_array('artpiece_descriptions', [
      'artpieces' => ['$in' => $me['follow_artpieces']],
      'approved' => ['$gt' => $from_time]
    ], [
      'sort' => ['created' => -1],
      'limit' => $item_limit
    ]);

    // Friss műlapok
    $conditions = [];
    if (count($me['follow_users']) > 0) {
      $conditions[] = 'user_id IN (' . implode(',', $me['follow_users']) . ')';
    }
    if (count($me['follow_places']) > 0) {
      $conditions[] = 'place_id IN (' . implode(',', $me['follow_places']) . ')';
    }
    if (count($me['follow_artists']) > 0) {
      foreach ($me['follow_artists'] as $artist_id) {
        $conditions[] = "JSON_CONTAINS(artists, '{\"id\": " . $artist_id . "}')";
      }
    }

    if (count($conditions) > 0) {
      $artpieces = $this->DB->find('artpieces', [
        'conditions' => '(' . implode(' OR ', $conditions) . ') AND status_id = 5 AND published > ' . $from_time,
        'order' => 'published DESC',
        'limit' => $item_limit,
      ]);
    } else {
      $artpieces = [];
    }

    // Friss kommentek mindenhol
    $comments = $this->Mongo->find_array('comments', [
      '$or' => [
        ['user_id' => ['$in' => $me['follow_users']]],
        ['artpiece_id' => ['$in' => $me['follow_artpieces']]],
        ['artist_id' => ['$in' => $me['follow_artists']]],
        ['place_id' => ['$in' => $me['follow_places']]],
        ['folder_id' => ['$in' => $me['follow_folders']]],
      ],
      'forum_topic_id' => ['$ne' => 6],
      'created' => ['$gt' => $from_time]
    ], [
      'sort' => ['created' => -1],
      'limit' => $item_limit
    ]);

    // Friss képek alkotóknál és műlapokon
    $photo_conditions = [];
    if (count($me['follow_artpieces']) > 0) {
      $photo_conditions['artpiece_id'] = $me['follow_artpieces'];
    }
    if (count($me['follow_artists']) > 0) {
      $photo_conditions['artist_id'] = $me['follow_artists'];
      $photo_conditions['portrait_artist_id'] = $me['follow_artists'];
    }

    if (count($photo_conditions) > 0) {
      $photos = $this->DB->find('photos', [
        'conditions' => [
          'OR' => $photo_conditions,
          'approved >' => $from_time
        ],
        'order' => 'approved DESC',
        'limit' => $item_limit,
      ]);
    } else {
      $photos = [];
    }

    // Friss alkotó leírások
    $artist_descriptions = $this->Mongo->find_array('artist_descriptions', [
      'artist_id' => ['$in' => $me['follow_artists']],
      'approved' => ['$gt' => $from_time]
    ], [
      'sort' => ['created' => -1],
      'limit' => $item_limit
    ]);

    // Friss fájlok mappákban
    if (count($me['follow_folders']) > 0) {
      $files = $this->DB->find('files', [
        'conditions' => [
          'folder_id' => $me['follow_folders'],
          'created >' => $from_time
        ],
        'order' => 'created DESC',
        'limit' => $item_limit
      ]);
    } else {
      $files = [];
    }

    // Friss blogok
    $conditions = [];
    if (count($me['follow_users']) > 0) {
      $conditions[] = 'user_id IN (' . implode(',', $me['follow_users']) . ')';
    }
    if (count($me['follow_places']) > 0) {
      $conditions[] = 'place_id IN (' . implode(',', $me['follow_places']) . ')';
    }
    if (count($me['follow_artists']) > 0) {
      $conditions[] = 'artist_id IN (' . implode(',', $me['follow_artists']) . ')';
    }
    if (count($conditions) > 0) {
      $posts = $this->DB->find('posts', [
        'conditions' => '(' . implode(' OR ', $conditions) . ') AND status_id = 5 AND published > ' . $from_time,
        'order' => 'published DESC',
        'limit' => $item_limit,
      ]);
    } else {
      $posts = [];
    }

    $this->set([
      '_breadcrumbs_menu' => false,
      '_shareable' => false,
      '_bookmarkable' => false,
      '_sidemenu' => false,
      '_title_row' => true,
      '_title' => 'Követéseim',
      '_tabs' => $tabs,

      'day_count' => $day_count,
      'day_count' => $day_count,
      'item_limit' => $item_limit,
      'artpiece_edits' => $artpiece_edits,
      'artpiece_descriptions' => $artpiece_descriptions,
      'artpieces' => $artpieces,
      'comments' => $comments,
      'photos' => $photos,
      'artist_descriptions' => $artist_descriptions,
      'files' => $files,
      'posts' => $posts,
      'me' => $me,
    ]);
  }


  /**
   * Saját értesítések listája
   */
  public function notifications() {

    $filters = ['user_id' => $this->user['id']];

    if (@$this->params->query['kulcsszo'] != '') {
      $filters['$and'] = [
        ['$or' => [
          ['title' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
          ['content' => ['$regex' => $this->params->query['kulcsszo'], '$options' => 'i']],
        ]],
      ];
    }

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0 ? $this->params->query['oldal'] : 1,
      'limit' => 50
    ];

    //debug($filters);

    $notifications = $this->Mongo->find_array(
      'notifications',
      $filters,
      [
        'sort' => ['unread' => -1, 'created' => -1],
        'limit' => $pagination['limit'],
        'skip' => ($pagination['page']-1) * $pagination['limit']
      ]
    );

    $this->set([
      '_breadcrumbs_menu' => false,
      '_shareable' => false,
      '_bookmarkable' => false,
      '_sidemenu' => false,
      '_title' => 'Értesítések',

      'pagination' => $pagination,
      'notifications' => $notifications,
    ]);

  }


  public function bookmarks() {
    $this->users_only();

    $bookmarks = $this->Mongo->find_array('bookmarks', [
      'user_id' => $this->user['id'],
    ], [
      'order' => ['name' => 1],
    ]);

    $this->set([
      'bookmarks' => $bookmarks,

      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_title' => 'Könyvjelzőim kezelése'
    ]);
  }


  public function email_subscriptions() {
    if ($this->user) {
      $this->redirect('/tagsag/beallitasok#ertesitesek');
    }

    if (!isset($this->params->query['kulcs'])) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if ($this->Request->is('post') && $this->params->data['unsubscribe'] == 1) {
      $hash = explode('.', $this->params->data['hash']);
      if (count($hash) == 2) {
        $user = $this->DB->first('users', [
          'SHA1(id)' => $hash[0],
          'SHA1(email)' => $hash[1],
        ], [
          'debug' => true
        ]);

        if ($user) {
          $this->DB->update('users', [
            'alert_settings' => json_encode([
              'conversations' => 0,
              'work' => 0,
              'notifications_artpieces' => 0,
              'notifications_edits' => 0,
              'notifications_comments' => 0,
              'notifications_games' => 0,
              'notifications_others' => 0,
            ]),
            'newsletter_settings' => json_encode([
              'weekly_harvest' => 0,
              'daily' => 0,
            ])
          ], $user['id']);

          $this->Mongo->insert('jobs', [
            'class' => 'emails',
            'action' => 'send',
            'options' => [
              'template' => 'system',
              'user_id' => $user['id'],
              'subject' => 'Feliratkozásaidat töröltük',
              'body' => texts('emails/unsubscribe_notice', [
                'name' => $user['name'],
                'contact_form' => APP['url'] . '/oldalak/kapcsolat'
              ])
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);

          $this->redirect('/', ['Email feliratkozásaidat töröltük.', 'info']);
        }
      }
      $this->redirect('/', [texts('jogosultsagi_hiba'), 'danger']);
    }

    $this->set([
      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_shareable' => false,
      '_title' => 'Email feliratkozások kezelése'
    ]);
  }




  /**
   * Profil törlés, aka harakiri
   * KT ♥ GDPR, szóval anonimizálunk
   *
   */
  public function delete() {
    $this->users_only();

    if ($this->Request->is('post')) {
      // A legaktuálisabb adathalmaz kiolvasása
      $user = $this->DB->first('users', $this->user['id']);

      if (password_verify($this->params->data['pass'], $user['password'])) {

        $name = $this->params->data['deleted_name'] == 1
          ? $user['name'] : 'Törölt Tag - ' . $user['id'];

        $update = $this->DB->update('users', [
          'email' => 'torolt_profil_' . $user['id'],
          'link' => 'torolt_profil_' . $user['id'],
          'name' => $name,
          'login_tokens' => '',
          'profile_photo_filename' => '',
          'header_photo_filename' => '',
          'header_photo_height' => 0,
          'blog_title' => '',
          'web_links' => '',
          'introduction' => '',
          'place_name' => '',
          'auto_reply' => '',
          'tiny_settings' => '',
          'alert_settings' => '',
          'last_here' => 0,
          'last_here_before' => 0,
          'harakiri' => 1,
        ], $user['id']);

        // Profil fotó és Fejléc fotó törlése
        $users_folder = CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/tagok/';
        for ($i = 1; $i <= 5; $i++) {
          $path = $users_folder . $user['profile_photo_filename'] . '_' . $i . '.jpg';
          if (is_file($path)) {
            unlink($path);
          }
          $path = $users_folder . $user['header_photo_filename'] . '_' . $i . '.jpg';
          if (is_file($path)) {
            unlink($path);
          }
        }

        // Érintések törlése
        $this->Mongo->delete('artpiece_hugs', [
          'user_id' => (int)$user['id'],
        ]);

        // Érintés és térkapszula feltörési események törlése
        $this->Mongo->delete('events', [
          'user_id' => (int)$user['id'],
          'type_id' => [7,8],
        ]);

        // Értesítések
        $this->Mongo->delete('notifications', [
          'user_id' => (int)$user['id'],
        ]);

        // Követések
        $this->Mongo->delete('users', [
          'user_id' => (int)$user['id'],
        ]);

        // Átnevezés futtatása, ha kell
        if ($this->params->data['deleted_name'] == 2) {
          $this->Mongo->insert('jobs', [
            'class' => 'users',
            'action' => 'rename',
            'options' => [
              'user_id' => $user['id'],
              'new_name' => $name,
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
        }

        // Kiléptetjük
        $this->Auth->logout();

        // Írunk neki
        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'template' => 'system',
            'user_id' => $user['id'],
            'subject' => 'Hozzáférésedet töröltük',
            'body' => texts('emails/profile_delete_notice', [
              'name' => $user['name'],
            ])
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);

        return $this->redirect('/', ['Hozzáférésedet töröltük.', 'success']);
      } else {
        $this->redirect('referer', ['Hibás jelszó, kérjük próbáld újra.', 'danger']);
      }
    }


    $this->set([
      '_title' => 'Profilod törlése',
      '_sidemenu' => false,
      '_shareable' => false,
    ]);
  }


  /**
   * Kilépés
   */
  public function logout() {
    $redirect = $this->params->referer ?: '/';
    if ($this->Auth->logout()) {
      $this->flash('Sikeres kilépés!', 'info', 'bubble', APP['sessions']['alert_remove']);
      $this->redirect($redirect);
    } else {
      $this->flash('<strong>Nem sikerült a kilépés.</strong> Ha a probléma tartósan fennáll, jelezd az üzemgazdának! Addig próbálkozz a manuális cookie-törléssel böngésződben.', 'warning');
      $this->redirect($redirect);
    }
  }
}