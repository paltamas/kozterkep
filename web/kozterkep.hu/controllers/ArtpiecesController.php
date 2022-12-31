<?php
use Kozterkep\AppBase as AppBase;

class ArtpiecesController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Műlapok',
      '_sidemenu' => false,
    ]);
  }


  /**
   *
   * MŰLAPOK ÁTTEKINTÉSE
   *
   */
  public function index() {
    $latests = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 44,
    ]);

    $artpieces_daily = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'view_day >' => 0,
      ],
      'order' => 'view_day DESC',
      'limit' => 10,
    ]);

    $artpieces_weekly = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'view_week >' => 0,
      ],
      'order' => 'view_week DESC',
      'limit' => 10,
    ]);

    $updated_artpieces = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'published <' => strtotime('-1 year'),
      ],
      'order' => 'long_updated DESC',
      'limit' => 16,
    ]);

    $this->set([
      'latests' => $latests,
      'artpieces_daily' => $artpieces_daily,
      'artpieces_weekly' => $artpieces_weekly,
      'updated_artpieces' => $updated_artpieces,

      '_title' => 'Áttekintés',
      '_active_submenu' => 'Áttekintés',
      '_breadcrumbs_menu' => false,
      '_sidemenu' => false,
      '_title_row' => false,
    ]);

  }


  public function random() {
    $artpiece = $this->DB->first('artpieces', [
      'status_id' => 5
    ], [
      'order' => 'RAND()'
    ]);
    $this->set(compact('artpiece'));
  }


  /**
   *
   * MŰLAPOK STATISZTIKÁI
   *
   */
  public function statistics() {
    $artpieces_daily = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'view_day >' => 0,
      ],
      'order' => 'view_day DESC',
      'limit' => 50,
    ]);

    $artpieces_weekly = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
        'view_week >' => 0,
      ],
      'order' => 'view_week DESC',
      'limit' => 50,
    ]);


    $artpieces_total = $this->DB->find('artpieces', [
      'conditions' => ['status_id' => 5],
      'order' => 'view_total DESC',
      'limit' => 50,
    ]);

    $this->set([
      '_title' => 'Statisztikák',
      '_active_submenu' => 'Statisztikák',
      '_sidemenu' => false,

      'artpieces_daily' => $artpieces_daily,
      'artpieces_weekly' => $artpieces_weekly,
      'artpieces_total' => $artpieces_total,
    ]);

  }


  /**
   *
   * MŰLAP LÉTREHOZÁS
   *
   */
  public function create() {
    $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_edit_list::' . $this->user['id']);

    $this->users_only();

    if ($this->Request->is('post')) {
      // Megpróbáljuk létrehozni
      $address = $this->Places->parse_address(
        json_decode(html_entity_decode($this->params->data['address_json']), true),
        true, // létrehozunk, ha kell
        $this->user['id'] // létrehozó, ha az
      );

      $artpiece_data = [
        'title' => 'Egy új műlap...',
        'lat' => $this->params->data['lat'],
        'lon' => $this->params->data['lon'],
        'artpiece_condition_id' => 1, // egyelőre meglévő
        'artpiece_location_id' => $this->params->data['artpiece_location_id'],
        'not_public_type_id' => $this->params->data['not_public_type_id'],
        'place_id' => $address['place_id'],
        'country_id' => (int)$address['country_id'],
        'county_id' => (int)$address['county_id'],
        'district_id' => (int)$address['district_id'],
        'address' => $address['address'],
        'created' => time(),
        'modified' => time(),
      ];

      $id = $this->DB->insert('artpieces', [
        'status_id' => 1,
        'creator_user_id' => $this->user['id'],
        'user_id' => $this->user['id'],
        'updated' => time(),
      ] + $artpiece_data);

      $this->Mongo->insert('artpiece_edits', [
        'artpiece_id' => $id,
        'user_id' => (int)$this->user['id'],
        'status_id' => 5,
        'approved' => time(),
        'before_shared' => 1,
        'receiver_user_id' => (int)$this->user['id'],
      ] + $artpiece_data);

      if ($id) {
        $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_edit_list::' . $this->user['id']);
        $this->redirect('/mulapok/szerkesztes/' . $id, ['Sikeresen létrehoztad új műlapodat!', 'success']);
      } else {
          $this->redirect('referer', [texts('mentes_hiba'), 'danger']);
      }
    }

    $this->set([
      '_breadcrumbs_menu' => false,
      '_shareable' => false,
      '_bookmarkable' => false,
      '_title' => 'Műlap létrehozása',
    ]);
  }



  /**
   *
   * MŰLAP SZERKESZTÉS
   *
   */
  public function edit() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id, [
      'connect' => ['places' => ['id', 'place_id']]
    ]);

    if (!$artpiece) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if (!in_array($artpiece['status_id'], [2,5]) && !$this->Users->owner_or_head_or_invited($artpiece, $this->user)) {
      $this->redirect('/' . $artpiece['id'], [texts('nem_szerkesztheto_mulap', ['status' => sDB['artpiece_statuses'][$artpiece['status_id']][0]]), 'warning']);
    }

    $edit_conditions = [
      'artpiece_id' => $artpiece['id'],
    ];

    /*if ($artpiece['status_id'] == 5) {
      $edit_conditions['own_edit'] = 0;
    }*/

    $edits = $this->Mongo->find_array('artpiece_edits', $edit_conditions, [
      'sort' => ['modified' => -1]
    ]);

    $artpiece_parameters = $this->DB->find('parameters', [
      'conditions' => ['hidden' => 0],
      'order' => 'parameter_group_id ASC, parameter_subgroup_id ASC, rank ASC'
    ]);

    $edit_edit = false;


    $artpiece_photos = $this->Arrays->sort_by_key(_json_decode($artpiece['photos']), 'rank', 1);

    $artpiece_photos_by_id = [];
    foreach ($artpiece_photos as $artpiece_photo) {
      $artpiece_photos_by_id[$artpiece_photo['id']] = $artpiece_photo;
    }

    $photos = $this->DB->find('photos', [
      'type' => 'list',
      'conditions' => ['artpiece_id' => $artpiece['id']],
      'order' => 'rank'
    ]);

    $this->set([
      'artpiece' => $artpiece,
      'artists' => $this->Artpieces->artists_array($artpiece),
      'parameters' => _json_decode($artpiece['parameters']),
      'artpiece_user' => $this->MC->t('users', $artpiece['user_id']),
      'artpiece_parameters' => $artpiece_parameters,
      'descriptions' => $this->Mongo->find_array('artpiece_descriptions',
        ['artpieces' => $artpiece['id']],
        ['sort' => ['main' => -1, 'approved' => 1]]
      ),
      'photos' => $photos,
      'artpiece_photos' => $artpiece_photos,
      'artpiece_photos_by_id' => $artpiece_photos_by_id,
      'connected_artpieces' => $this->Artpieces->get_connected_artpieces($artpiece),
      'connected_sets' => $this->Artpieces->get_connected_sets($artpiece),
      'sets' => $this->Mongo->find_array('sets', [], [
        'sort' => ['name' => 1],
        'projection' => ['name' => 1, 'set_type_id' => 1, 'user_id' => 1],
      ]),
      'possible_sets' => $this->Artpieces->get_possible_sets($artpiece),
      'folders' => $this->DB->find('folders', ['user_id' => $this->user['id']], ['order' => 'name ASC']),
      'edit_edit' => $edit_edit,
      'edits' => $edits,
      'validations' => $this->Artpieces->check($artpiece, $this->user, false),
      '_viewable' => $this->Html->link_url('', ['artpiece' => $artpiece]),
      '_model' => 'artpieces',
      '_model_id' => $artpiece['id'],
      '_shareable' => false,
      '_bookmarkable' => false,
      '_breadcrumbs_menu' => true,
      '_title' => $artpiece['title'],
    ]);

  }


  public function edit_info() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    if (!in_array($artpiece['status_id'], [2,5]) && !$this->Users->owner_or_head_or_invited($artpiece, $this->user)) {
      $this->redirect('/' . $artpiece['id'], [texts('nem_szerkesztheto_mulap', ['status' => sDB['artpiece_statuses'][$artpiece['status_id']][0]]), 'warning']);
    }

    $validation = $this->Artpieces->check($artpiece, false, false);

    $this->set([
      'artpiece' => $artpiece,
      'validation' => $validation,

      '_title' => $artpiece['title'] . ' Szerkinfó',
    ]);
  }


  /**
   *
   * SZERKESZTÉS RÉSZLETEI
   *
   */
  public function edit_details() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece
      || (!in_array($artpiece['status_id'], [2,5]) && !$this->Users->owner_or_head_or_invited($artpiece, $this->user))) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $artpiece_user = $this->DB->first('users', $artpiece['user_id']);

    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id
    ]);

    // Van műlap, nincs szerkesztés; valszeg közben töroölték/visszavonták
    // és pl. értesítő emailből kattintottunk
    if (!$edit) {
      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'], ['<strong>Hopp, ez már nincs meg.</strong> Úgy tűnik, hogy a keresett szerkesztést közben visszavonták vagy törölték.', 'warning']);
    }

    // Nem saját vagy láthatatlan
    if (@$edit['invisible'] == 1 && !in_array($this->user['id'], [$edit['user_id'], $edit['receiver_user_id']])) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $edit_details = $this->Artpieces->edit_details($edit, [
      'simple' => false,
      'excluded' => sDB['hidden_edit_fields'],
      'full_values' => true,
    ]);

    $this->set([
      'artpiece' => $artpiece,
      'artpiece_user' => $artpiece_user,
      'edit' => $edit,
      'edit_details' => $edit_details,

      '_viewable' => $this->Html->link_url('', ['artpiece' => $artpiece]),
      '_bookmarkable' => false,
      '_shareable' => false,
      '_breadcrumbs_menu' => [
        'Műlapok' => '/mulapok',
        $artpiece['title'] . ' műlap szerkesztése' => '/mulapok/szerkesztes/' . $artpiece['id']
      ],
      '_title' => 'Szerkesztés részletei',
    ]);
  }


  public function edit_edit() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id,
    ]);

    if (!$artpiece || !$edit ||
      (!$this->Users->is_head($this->user) && $edit['user_id'] != $this->user['id'])
      || !in_array($edit['status_id'], [1,2,3])) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $edit_details = $this->Artpieces->edit_details($edit, [
      'simple' => false,
      'excluded' => sDB['hidden_edit_fields'],
      'full_values' => true,
    ]);

    if ($this->Request->is('post')) {
      //debug($this->params->data);

      $updates = [];

      // Csak az engedélyezett mezőkket mentjük vissza
      foreach ($this->params->data as $field => $value) {
        // Sima rámentések
        if (in_array($field, [
          'title',
          'title_alternatives',
          'title_en',
          'address',
          'place_description',
          'links',
        ])) {
          $updates[$field] = $value;
        }

        // Ezekbe bele kell bújni
        // Sztorik
        if ($field == 'descriptions') {
          $updates['descriptions'] = $edit['descriptions'];
          foreach ($value as $id => $description) {
            foreach ($updates['descriptions'] as $desc_key => $desc_item) {
              if ($desc_item['id'] == $id) {
                $updates['descriptions'][$desc_key] = array_merge($desc_item, $description);
              }
            }
          }
        }
      }

      if (count($updates) > 0) {
        $updates['modified'] = time();
        $this->Mongo->update('artpiece_edits', $updates, ['_id' => $edit['id']]);
      }

      $this->redirect('referer', [texts('sikeres_mentes'), 'success']);
    }

    $this->set([
      'artpiece' => $artpiece,
      'edit' => $edit,
      'edit_details' => $edit_details,

      '_viewable' => false,
      '_bookmarkable' => false,
      '_shareable' => false,
      '_breadcrumbs_menu' => true,
      '_title' => 'Szerkesztés módosítása',
    ]);
  }


  public function edit_to_comment() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id,
    ]);

    if (!$artpiece || !$edit ||
      (!$this->Users->owner_or_head($artpiece, $this->user) && $edit['user_id'] != $this->user['id'])
      || !isset($edit['descriptions'][0]['text'])) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $source = $edit['descriptions'][0]['source'] != '' ? $edit['descriptions'][0]['source'] : '';

    $data = [
      'user_id' => $edit['user_id'],
      'user_name' => $this->MC->t('users', $edit['user_id'])['name'],
      'text' => strip_tags($edit['descriptions'][0]['text']) . $source,
      'created' => (int)$edit['created'],
      'modified' => (int)$edit['modified'],
      'artpiece_id' => (int)$artpiece['id']
    ];

    $inserted = $this->Mongo->insert('comments', $data);

    if ($inserted) {
      // Töröljük a kommentet
      $this->Mongo->delete('artpiece_edits', ['_id' => $edit['id']]);
      // Műlap cache-t ürítünk
      $this->Artpieces->generate($artpiece['id']);

      // Értesítések
      // Műlap tulajnak, ha nem ő csinálta a módosítást
      if ($artpiece['user_id'] != $this->user['id']) {
        $this->Notifications->create($artpiece['user_id'], 'Szerkesztésből hozzászólás lett', '"' . $artpiece['title'] . '" c. műlapodon egy sztorit tartalmazó szerkesztést hozzászólássá változtattunk.', [
          'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm',
          'type' => 'artpieces',
        ]);
      }

      if ($this->user['id'] != $edit['user_id']) {
        $this->Notifications->create($edit['user_id'], 'Szerkesztésedből hozzászólás lett', '"' . $artpiece['title'] . '" c. műlapon egy sztorit tartalmazó szerkesztésed hozzászólássá változott.', [
          'link' => '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm',
          'type' => 'edits',
        ]);
      }

      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', 'A sztorit tartalmazó szerkesztést hozzászólássá változtattuk.');
    } else {
      $this->redirect('back', texts('mentes_hiba'), 'danger');
    }
  }


  /**
   *
   * SZERKESZTÉS TÖRLÉSE
   *
   */
  public function edit_delete() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id,
      'status_id' => ['$lt' => 4],
    ]);

    if (!$artpiece || !$edit || ($edit['user_id'] != $this->user['id'] && !$this->Users->owner_or_head($artpiece, $this->user))) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    if ($edit['user_id'] == $this->user['id']) {
      // Saját: visszavont
      $new_status_id = 4;
      $what = 'visszavont státuszba tettük.';
      $event_type_id = 15;
    } else {
      // Másé: elvetett
      $new_status_id = 6;
      $what = 'elvetett státuszba tettük.';
      $event_type_id = 14;
    }

    if (@$edit['invisible'] == 1) {
      $update = $this->Mongo->delete('artpiece_edits', ['_id' => $edit['id']]);
    } else {
      $update = $this->Mongo->update('artpiece_edits', [
        'status_id' => $new_status_id,
        'manage_user_id' => $this->user['id'],
        'modified' => time(),
      ], ['_id' => $edit['id']]);
    }

    if ($update) {

      if (@$edit['invisible'] != 1 && in_array($artpiece['status_id'], [2,5])) {
        $this->Events->create($event_type_id, [
          'artpiece_id' => $artpiece['id'],
          'artpiece_edits_id' => $edit['id'],
          'related_users' => [$artpiece['user_id'], $edit['user_id']],
        ]);
      }

      // Értesítések
      if ($new_status_id == 6) {
        // Elutasításkor a küldőnek
        $this->Notifications->create($edit['user_id'], 'Szerkesztésed elutasítva', '"' . $artpiece['title'] . '" c. műlapon elutasításra került egy szerkesztésed', [
          'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'],
          'type' => 'edits',
        ]);
      }

      // Műlap tulajnak, ha nem ő utasította el
      if ($artpiece['user_id'] != $this->user['id']) {
        $body_end = $new_status_id == 6 ? 'elutasítottak egy szerkesztést.' : 'visszavontak egy szerkesztést.';
        $this->Notifications->create($artpiece['user_id'], 'Szerkesztés státuszváltozás', '"' . $artpiece['title'] . '" c. műlapodon ' . $body_end, [
          'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'],
          'type' => 'artpieces',
        ]);
      }

      $this->Artpieces->generate($artpiece['id']);

      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', 'A szerkesztést ' . $what);
    } else {
      $this->redirect('back', texts('torles_hiba'), 'danger');
    }
  }


  /**
   *
   * SZERKESZTÉS JÓVÁHAGYÁSA
   *
   */
  public function edit_accept() {
    $this->users_only();
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', $this->Request->uri_level(4));

    if ($artpiece && $edit && $this->Users->owner_or_head($artpiece, $this->user)) {
      $saved = $this->Artpieces->approve_edit($artpiece['id'], $edit['id'], $this->user['id']);
      if ($saved) {

        if (@$edit['invisible'] == 1) {
          $this->Mongo->delete('artpiece_edits', ['_id' => $edit['id']]);
        }

        if (@$edit['invisible'] != 1 && in_array($artpiece['status_id'], [2,5])) {
          $this->Events->create(12, [
            'artpiece_id' => $artpiece['id'],
            'artpiece_edits_id' => $edit['id'],
            'related_users' => [$artpiece['user_id'], $edit['user_id']],
          ]);
        }

        $this->Artpieces->generate($artpiece['id']);

        $redirect = 'back';
        if (@$edit['invisible'] == 1) {
          $redirect = '/mulapok/' . $artpiece['id'];
        }
        $this->redirect($redirect, 'A szerkesztés elfogadásra került.');
      }
    } else {
      $this->redirect('/', [texts('jogosultsagi_hiba'), 'danger']);
    }
  }


  /**
   *
   * SZERKESZTÉS ÚJRANYITÁSA VISSZAÁLLÍTÁSSAL
   *
   * @todo or die
   *
   * tutira tutira kell? és van értelme?
   * a lezárás óta változhatott olyan eleme a műlapnak, ami a visszanyitott szerkesztésben
   * benne van és az elfogadással esetleg rossz állapotra áll át.
   *
   * Szerintem ez a funkció tökre nem kellene.
   * Megvan az előzmény, így ha mégis kell valami, akkor ki lehet bogarászni.
   * Jellemzően egy-egy adat kellhet csak.
   *
   */
  public function edit_rollback() {
    $this->users_only();

    // Egyelőre
    return;

    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id,
      'status_id' => ['$gt' => 4],
    ]);

    if (!$artpiece || !$edit || !$this->Users->owner_or_head($artpiece, $this->user)) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    // @todo

    $this->Events->create(17, [
      'artpiece_id' => $artpiece['id'],
      'artpiece_edits_id' => $edit['id'],
      'related_users' => [$artpiece['user_id'], $edit['user_id']],
    ]);

    $this->Artpieces->generate($artpiece['id']);

    $this->redirect('back', 'A műlap visszaállt a szerkesztés előtti állapotba és a szerkesztés újranyitott státuszba került.');
  }


  /**
   *
   * SZERKESZTÉS ÚJRANYITÁSA
   *
   * ...és ez kell?(???)
   * Ugyanaz a véleményem a kockázatokról, mint a ~ rollback esetében!
   *
   * btw, jól eldumálok magammal, hogy valami, amit kitaláltam,
   * szuper-e vagy nem az. muhaha.
   *
   * kérem, kapcsolják ki.
   *
   */
  public function edit_reopen() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);
    $edit = $this->Mongo->first('artpiece_edits', [
      '_id' => $this->Request->uri_level(4),
      'artpiece_id' => (int)$this->params->id,
      'status_id' => ['$in' => [4,6]],
    ]);

    if (!$artpiece || !$edit || !$this->Users->owner_or_head($edit, $this->user)) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $update = $this->Mongo->update('artpiece_edits', [
      'status_id' => 3,
      'manage_user_id' => $this->user['id'],
      'modified' => time(),
    ], ['_id' => $edit['id']]);

    if ($update) {

      $this->Events->create(16, [
        'artpiece_id' => $artpiece['id'],
        'artpiece_edits_id' => $edit['id'],
        'related_users' => [$artpiece['user_id'], $edit['user_id']],
      ]);

      // Értesítések

      // A küldőnek
      $this->Notifications->create($edit['user_id'], 'Szerkesztésed újranyitása', '"' . $artpiece['title'] . '" c. műlapon újranyitása került egy szerkesztésed', [
        'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'],
        'type' => 'edits',
      ]);

      // Műlap tulajnak, ha nem ő nyitotta újra
      if ($artpiece['user_id'] != $this->user['id']) {
        $this->Notifications->create($artpiece['user_id'], 'Szerkesztés újranyitása', '"' . $artpiece['title'] . '" c. műlapodon újranyitása került egy szerkesztés', [
          'link' => '/mulapok/szerkesztes_reszletek/' . $artpiece['id'] . '/' . $edit['id'],
          'type' => 'artpieces',
        ]);
      }

      $this->Artpieces->generate($artpiece['id']);

      $this->redirect('back', 'A szerkesztés újranyitott státuszba került.');
    } else {
      $this->redirect('back', texts('mentes_hiba'), 'danger');
    }
  }



  /**
   * SAJÁT SZERKESZTÉSEK TÖRLÉSE
   */
  public function delete_my_edits() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece || $artpiece['user_id'] != $this->user['id']) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $conditions = [
      'user_id' => $this->user['id'],
      'artpiece_id' => $artpiece['id'],
    ];

    if ($artpiece['submitted'] > 0) {
      $conditions['created'] = ['$lt' => $artpiece['submitted']];
    } elseif ($artpiece['published'] > 0) {
      $conditions['created'] = ['$lt' => $artpiece['published']];
    }

    if ($this->Mongo->delete('artpiece_edits', $conditions)) {
      $this->Artpieces->generate($artpiece['id']);
      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', texts('sikeres_torles'));
    } else {
      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', texts('mentes_hiba'), 'danger');
    }
  }


  /**
   * MEGOSZTÁS ELŐTTI KOMMENTEK TÖRLÉSE
   */
  public function delete_comments() {
    $this->users_only();

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece || $artpiece['user_id'] != $this->user['id']) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    $conditions = [
      'artpiece_id' => $artpiece['id'],
    ];

    if ($artpiece['submitted'] > 0) {
      $conditions['created'] = ['$lt' => $artpiece['submitted']];
    } elseif ($artpiece['published'] > 0) {
      $conditions['created'] = ['$lt' => $artpiece['published']];
    }

    if ($this->Mongo->delete('comments', $conditions)) {
      $this->Artpieces->generate($artpiece['id']);
      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', texts('sikeres_torles'));
    } else {
      $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-szerkkomm', texts('mentes_hiba'), 'danger');
    }
  }


  /**
   *
   * MŰLAP TÖRLÉS
   *
   */
  public function delete() {
    $this->users_only();
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (!$artpiece || in_array($artpiece['status_id'], [2, 5]) || !$this->Users->owner_or_head($artpiece, $this->user)) {
      $this->redirect('/', [texts('hibas_url'), 'danger']);
    }

    // Műlap és kapcsolódók
    $this->DB->delete('artpieces', $artpiece['id']);
    // @todo ha többes, és máson is van, akkor csak erről levenni
    $descriptions = $this->Mongo->find_array('artpiece_descriptions',
      ['artpieces' => $artpiece['id']]
    );
    if (count($descriptions) > 0) {
      foreach ($descriptions as $description) {
        $k = array_search($artpiece['id'], $description['artpieces']);
        unset($description['artpieces'][$k]);
        // Ha a műlaphoz kapcsolás törlése után nem maradt más műlap, akkor töröljük a leírást is
        if (count($description['artpieces']) == 0) {
          $this->Mongo->delete('artpiece_descriptions', ['_id' => $description['id']]);
        }
      }
    }
    $this->Mongo->delete('artpiece_edits', ['artpiece_id' => $artpiece['id']]);
    $this->Mongo->delete('artpiece_flags', ['artpiece_id' => $artpiece['id']]);
    $this->Mongo->delete('artpiece_spacecapsules', ['artpiece_id' => $artpiece['id']]);
    $this->Mongo->delete('artpieces', ['artpiece_id' => $artpiece['id']]);
    $this->Mongo->delete('comments', ['artpiece_id' => $artpiece['id']]);

    // Fotók
    $photos = $this->DB->find('photos', [
      'type' => 'list',
      'conditions' => [
        'artpiece_id' => $artpiece['id'],
      ],
      'order' => 'approved DESC'
    ]);
    foreach ($photos as $photo) {
      // Csak akor töröljük a fotót, ha csak ez a műlap volt hozzá kapcsolva
      $photo_artpieces = $this->Photos->artpiece_remove($photo, $artpiece['id'], false);
      if (count($photo_artpieces) == 0) {
        $this->Photos->delete($photo, $this->user);
      } else {
        $json = _json_encode(array_values($photo_artpieces), false, false);
        $this->DB->update('photos', ['artpieces' => $json, 'modified' => time()], $photo['id']);
      }
    }

    // Gyűjtemények @todo
    // Kapott követések @todo
    // Kapott szépmunkák @todo
    // Kapott bookmark @todo

    $this->Cache->delete('Kozterkep\ArtpiecesLogic::get_edit_list::' . $this->user['id']);

    $this->redirect('/kozter/mulapjaim', 'A műlapot töröltük.');
  }


  /**
   *
   * MŰLAP MEGTEKINTÉS
   *
   */
  public function view() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if ($artpiece && $this->user
      && !in_array($artpiece['status_id'], [5]) && !$this->Users->owner_or_head_or_invited($artpiece, $this->user)) {
      $this->redirect('/', [texts('nem_publikus_mulap_tagoknak', ['status' => sDB['artpiece_statuses'][$artpiece['status_id']][0]]), 'warning']);
    } elseif ($artpiece && !$this->user && $artpiece['status_id'] != 5) {
      $this->redirect('/', [texts('nem_publikus_mulap'), 'warning']);
    } elseif (!$artpiece) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $edits = $this->Mongo->find_array('artpiece_edits', [
      'artpiece_id' => $artpiece['id'],
      'status_id' => 5
    ], [
      'sort' => ['modified' => -1]
    ]);

    $edits_open = $this->Mongo->count('artpiece_edits', [
      'artpiece_id' => (int)$artpiece['id'],
      'status_id' => 2,
      'invisible' => ['$ne' => 1],
    ]);

    $open_count = $this->user && $edits_open > 0 ? ' (' . $edits_open . ')' : '';
    $open_question = $artpiece['open_question'] == 1
      ? ' <span class="fas fa-question-circle" title="Nyitott szerkesztői kérdés szerepel a lapon." data-toggle="tooltip"></span>' : '';

    $comment_count = '<span class="comment-' . $artpiece['id'] . '-count"></span>';

    $dates = _json_decode($artpiece['dates']);
    $dates = $this->Arrays->sort_by_key($dates, 'date', -1);

    $tabs = [
      'list' => [
        'Műlap' => [
          'hash' => 'mulap',
          'icon' => 'file',
        ],
        'Fotólista' => [
          'hash' => 'fotolista',
          'icon' => 'images',
        ],
        'Történet' => [
          'hash' => 'tortenet',
          'icon' => 'history',
        ],
        'SzerkKomm' . $open_count . $open_question . $comment_count => [
          'hash' => 'szerkkomm',
          'icon' => 'comment-edit',
        ],
      ],
      'options' => [
        'type' => 'pills',
        'selected' => 'mulap',
        'class' => '',
      ]
    ];


    $artists = $this->Artpieces->artists_array($artpiece, ['separated' => true]);

    // Meta összeszedés
    $description = '';
    $description .= strip_tags($this->Places->name($artpiece['place_id'])) . ', ';
    if (isset($artists['artists'][0]['name'])) {
      $description .= strip_tags($artists['artists'][0]['name']) . ', ';
    }
    $p = explode('-', $artpiece['first_date']);
    if ((int)@$p[0] > 0) {
      $description = rtrim($description, ', ') . ' (' . (int)@$p[0] . ')';
    } else {
      $description = rtrim($description, ', ');
    }

    $meta = [
      'title' => $artpiece['title'],
      'description' => $description,
      'image' => CORE['BASE_URL'] . '/eszkozok/kepmutato/' . $artpiece['photo_id'] . '?meret=1',
    ];

    $this->set([
      'artpiece' => $artpiece,

      'artists' => $artists,
      'parameters' => _json_decode($artpiece['parameters']),
      'artpiece_parameters' => $this->DB->find('parameters', [
        'type' => 'list',
        'conditions' => ['hidden' => 0],
        'order' => 'parameter_group_id ASC, parameter_subgroup_id ASC, rank ASC'
      ]),
      'dates' => $dates,
      'descriptions' => $this->Mongo->find_array('artpiece_descriptions',
        ['artpieces' => $artpiece['id']],
        ['sort' => [/*'main' => -1,*/ 'lang' => -1, 'approved' => 1]]
      ),
      'cover' => $this->DB->find_by_id('photos', $artpiece['photo_id']),
      'photos' => $this->DB->find('photos', [
        'type' => 'list',
        'conditions' => ['artpiece_id' => $artpiece['id']],
        'order' => 'approved DESC'
      ]),
      'artpiece_photos' => $this->Arrays->sort_by_key(_json_decode($artpiece['photos']), 'rank', 1),
      'connected_artpieces' => $this->Artpieces->get_connected_artpieces($artpiece),
      'connected_posts' => $this->DB->find('posts', [
        'conditions' => [
          'OR' => [
            'connected_artpieces LIKE' => '%"' . $artpiece['id'] . '"%',
            'artpiece_id' => $artpiece['id'],
          ],
          'status_id' => 5,
        ],
        'order' => 'published DESC',
      ]),
      'connected_sets' => $this->Artpieces->get_connected_sets($artpiece),
      'edits' => $edits,
      'edits_open' => $edits_open,
      'comment_count' => $this->Mongo->count('comments', [
        'artpiece_id' => $artpiece['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      'events' => $this->Mongo->find_array('events', [
        'artpiece_id' => $artpiece['id'],
        'type_id' => ['$nin' => sDB['events_hidden_from_artpage_history']],
        'public' => 1
      ], [
        'sort' => ['created' => -1],
        'limit' => 30,
      ]),
      'comments' => $this->Mongo->find_array('comments', [
        'artpiece_id' => $artpiece['id'],
        'highlighted' => ['$gt' => strtotime('-' . sDB['limits']['comments']['highlight_months'] . ' months')]
      ], [
        'sort' => ['created' => -1],
        'limit' => 3,
      ]),

      '_meta' => $meta,
      '_title' => $artpiece['title'],
      '_breadcrumbs_menu' => $this->Artpieces->get_breadcrumbs_menu($artpiece),
      '_tabs' => $tabs,
      '_model' => 'artpieces',
      '_model_id' => $artpiece['id'], // hogy ne számoljunk view-kat
      '_bookmarkable' => in_array($artpiece['status_id'], [2,5]) ? true : false,
      '_shareable' => $artpiece['status_id'] == 5 ? true : false,
      '_followable' => $artpiece['status_id'] == 5 ? true : false,
      '_editable' => '/mulapok/szerkesztes/' . $artpiece['id'],
      '_block_caching' => in_array($artpiece['status_id'], [2,5]) ? false : true,
    ]);
  }

  /**
   * Szerkkomm ajaxdiv
   */
  public function view_editcom() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      'artpiece' => $artpiece,
      'edits' => $this->Mongo->find_array('artpiece_edits', [
        'artpiece_id' => $artpiece['id']
      ], [
        'sort' => ['modified' => -1]
      ]),
      'comment_count' => $this->Mongo->count('comments', [
        'artpiece_id' => $artpiece['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      '_title' => $artpiece['title'] . ' SzerkKomm',
    ]);
  }


  /**
   * Ajaxszal betöltött szerkdobozok
   */
  public function editor_boxes () {
    if ($this->Request->is('ajax') && $this->user) {
      $artpiece = $this->DB->first('artpieces', $this->params->id);
      if ($artpiece) {
        return $this->set([
          'artpiece' => $artpiece,
          '_praisable' => in_array($artpiece['status_id'], [5])
            && $artpiece['user_id'] != $this->user['id'] ? true : false,
        ]);
      }
    }
    $this->redirect('/');
  }


  /**
   * Tagmemo
   */
  public function user_memo () {
    $artpiece = false;

    if ($this->Request->is('ajax') && $this->user) {
      $artpiece = $this->DB->first('artpieces', [
        'id' => $this->params->id,
        'user_id' => $this->user['id']
      ], [
        'fields' => ['id', 'user_memo']
      ]);
    }

    return $this->set([
      'artpiece' => $artpiece,
    ]);
  }

  /**
   * Adminmemo
   */
  public function admin_memo () {
    $artpiece = false;

    if ($this->Request->is('ajax') && $this->user) {
      $artpiece = $this->DB->first('artpieces', [
        'id' => $this->params->id,
        'open_question' => 1,
      ], [
        'fields' => ['id', 'admin_memo', 'admin_memo_updated']
      ]);
    }

    return $this->set([
      'artpiece' => $artpiece,
    ]);
  }






  /**
   * Műlap beküldés
   */
  public function submission() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (!in_array(@$artpiece['status_id'], [3,5]) && $this->Users->owner_or_head($artpiece, $this->user)) {

      $not_uploaded = $this->DB->count('photos', [
        'copied' => 0,
        'artpiece_id' => $artpiece['id'],
      ]);

      if ($not_uploaded > 0) {
        $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'], ['Néhány fotó még nem került feldolgozásra. Kérjük, pár perc múlva próbáld újra. Ha a fotóknál nem látsz "Feldolgozás alatt" feliratot, mégsem tudod beküldeni, jelezz az üzemgazdának.', 'warning']);
      }

      $submitted = $this->Artpieces->submit($artpiece['id'], $this->user);

      if ($submitted) {
        $redirect = $this->Html->link_url('', ['artpiece' => $artpiece]);
        $this->redirect($redirect, ['<strong>Sikeres beküldés!</strong> Mostantól a közösség segít a publikálásban.', 'info']);
      }
    }

    $this->redirect('/', [texts('varatlan_hiba'), 'warning']);
  }



  /**
   * Műlap visszahívás
   */
  public function call_back() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (@$artpiece['status_id'] == 2 && $this->Users->owner_or_head($artpiece, $this->user)) {
      $got_back = $this->Artpieces->call_back($artpiece['id'], $this->user);

      if ($got_back) {
        $redirect = $this->Html->link_url('', ['artpiece' => $artpiece]);
        $this->redirect($redirect, ['<strong>Sikeres visszahívás!</strong> Mostantól a Köztéren nem jelenik meg a publikálandók között.', 'info']);
      }
    }

    $this->redirect('/', [texts('varatlan_hiba'), 'warning']);
  }


  /*
   *
   *
   * Műlap publikálása :)
   * !! :)
   * ! :)
   * :)
   *
   *
   */
  public function publish() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (!in_array(@$artpiece['status_id'], [3,5]) && $this->Users->owner_or_head($artpiece, $this->user)) {

      $not_uploaded = $this->DB->count('photos', [
        'copied' => 0,
        'artpiece_id' => $artpiece['id'],
      ]);

      if ($not_uploaded > 0) {
        $this->redirect('/mulapok/szerkesztes/' . $artpiece['id'], ['Néhány fotó még nem került feldolgozásra. Kérjük, pár perc múlva próbáld újra. Ha a fotóknál nem látsz "Feldolgozás alatt" feliratot, mégsem tudsz publikálni, jelezz az üzemgazdának.', 'warning']);
      }

      $publication = $this->Artpieces->publish($artpiece['id'], $this->user);

      if (in_array($publication, [1, 2])) {
        $redirect = $this->Html->link_url('', ['artpiece' => $artpiece]);

        if ($publication == 1) {
          // Publikálás történt
          $messages = [
            '<strong>Sikeres publikáció!</strong> A Köztérkép és a világ újabb műlappal gazdagodott, köszönjük!',
            '<strong>Hipp-hipp hurrá!</strong> Közösségi adatbázisunk újabb gyöngyszemmel gazdagodott, köszönjük!',
            '<strong>Ez igen!</strong> Ezzel a gombnyomással újabb értéket teremtettél, köszönjük!',
            '<strong>Egy újabb alkotást örökítettél meg</strong> a jövendő nemzedék számára, köszönjük munkádat!',
            '<strong>Gratulálunk újabb műlapodhoz!</strong> Reméljük, sok-sok ilyen hozzájárulással gazdagítod még a Köztérképet és a világot!',
          ];
          $this->redirect($redirect, [$messages[rand(0, count($messages) - 1)], 'success']);
        } elseif ($publication == 2) {
          // Késleltetett
          $this->redirect($redirect, ['Műlapod publikálható, de a heti limited betelt, így hétfő reggel automatikusan publikálni fogjuk.', 'success']);
        }
      }
    }

    $this->redirect('/', [texts('varatlan_hiba'), 'warning']);
  }


  /**
   * Frissítés
   * nincs semmi extra vizsgálat, mert ez nem veszélyes; lehet, hogy a tagoknak is bevezetjük.
   * MI AZ A CACHE??? :)
   */
  public function refresh() {
    $this->Artpieces->generate($this->params->id);
    $this->redirect('/mulapok/szerkesztes/' . $this->params->id, ['<strong>A műlapot frissítettük.</strong> A következő megtekintéskor már minden adatában friss lesz.', 'info']);
  }


  /**
   * Visszaküldés
   */
  public function send_back() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (in_array(@$artpiece['status_id'], [2, 5]) && $this->Users->is_vetohead($this->user)) {
      $sent_back = $this->Artpieces->send_back($artpiece['id'], $this->user);

      if ($sent_back) {
        $redirect = $this->Html->link_url('', ['artpiece' => $artpiece]);
        $this->redirect($redirect, ['<strong>A műlapot visszaküldtük!</strong> Az eseményről a műlap létrehozóját is értesítettük.', 'info']);
      }
    }

    $this->redirect('/', [texts('varatlan_hiba'), 'warning']);
  }

  /**
   * Visszanyitás
   */
  public function reopen() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (@$artpiece['status_id'] == 3 && $this->Users->is_vetohead($this->user)) {
      $reopened = $this->Artpieces->reopen($artpiece['id'], $this->user);

      if ($reopened) {
        $redirect = $this->Html->link_url('', ['artpiece' => $artpiece]);
        $this->redirect($redirect, ['<strong>A műlapot visszanyitottuk!</strong> Az eseményről a műlap létrehozóját is értesítettük.', 'info']);
      }
    }

    $this->redirect('/', [texts('varatlan_hiba'), 'warning']);
  }


  /**
   * Szerkesztésre meghívás
   *
   * Rögzítés, törlés stb.
   *
   */
  public function invite_edit() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);
    if (!$artpiece || $artpiece['status_id'] == 5 || !$this->Users->owner_or_head($artpiece, $this->user)) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $invited_users = _json_decode($artpiece['invited_users']);

    // Rögzítés és noti beszúrása
    if (@$this->params->data['user_id'] > 0) {
      $user_id = $this->params->data['user_id'];
      if ($this->user['id'] == $user_id) {
        $this->redirect('/mulapok/szerkesztes_meghivas/' . $artpiece['id'], ['Biztosan magadat hívod segítségül? Ha eddig nem ment egyedül, érdemes lehet valaki <em>másnak</em> szólni, hogy segítsen.', 'warning']);
      } else {

        if (!in_array($user_id, $invited_users)) {
          $invited_users[] = (string)$user_id;
          $this->DB->update('artpieces', [
            'invited_users' => _json_encode(array_unique(array_values($invited_users)), false, false)
          ], $artpiece['id']);

          $this->Notifications->create((int)$user_id, $this->user['name'] . ' meghívott a' . _z($artpiece['title'], true) . ' műlapjára', 'A meghívás értelmében a szerkesztés alatti lapot is eléred és segíthetsz saját képekkel és információkkal bővíteni a lapot.', [
            'link' => '/' . $artpiece['id'],
            'type' => 'artpieces',
          ]);

          $this->redirect('/mulapok/szerkesztes_meghivas/' . $artpiece['id'], ['A meghívást rögzítettük.', 'success']);
        } else {
          $this->redirect('/mulapok/szerkesztes_meghivas/' . $artpiece['id'], ['Őt már meghívtad...', 'warning']);
        }
      }
    }

    // Törlés
    if (@$this->params->query['torles'] > 0) {
      $key = array_search($this->params->query['torles'], $invited_users);
      unset($invited_users[$key]);
      $this->DB->update('artpieces', [
        'invited_users' => _json_encode(array_values($invited_users), false, false)
      ], $artpiece['id']);

      $this->redirect('/mulapok/szerkesztes_meghivas/' . $artpiece['id'], texts('sikeres_torles'));
    }


    $this->set([
      '_breadcrumbs_menu' => false,
      '_title' => 'Közreműködők meghívása a' . _z($artpiece['title'], true) . ' műlapra',
      'artpiece' => $artpiece,
      'invited_users' => $invited_users,
    ]);
  }



  /*
   * Léptetés
   */
  public function step() {
    $q = $this->params->query;

    if ($this->params->id > 0) {
      $artpiece = $this->DB->first('artpieces', $this->params->id, [
        'fields' => ['id', 'status_id', 'published', 'submitted', 'title'],
      ]);
    } else {
      $artpiece = false;
    }

    if ($artpiece || isset($q['elso']) || isset($q['utolso']) || isset($q['veletlen'])) {

      // Ha közteres lapról kattoltunk, akkor azok közt lépkedjünk
      $conditions = [
        'status_id' => @$artpiece['status_id'] == 2 ? 2 : 5,
        'id <>' => @$artpiece['id'],
      ];

      $sort_field = @$artpiece['status_id'] == 2 ? 'submitted' : 'published';

      if (isset($q['elozo'])) {
        $conditions[$sort_field . ' <='] = $artpiece[$sort_field];
        $order = $sort_field . ' DESC';
      } elseif (isset($q['kovetkezo'])) {
        $conditions[$sort_field . ' >='] = $artpiece[$sort_field];
        $order = $sort_field . ' ASC';
      } elseif (isset($q['elso'])) {
        $conditions[$sort_field . ' >'] = 0;
        $order = $sort_field . ' ASC';
      } elseif (isset($q['utolso'])) {
        $conditions[$sort_field . ' <'] = strtotime('+1 year');
        $order = $sort_field . ' DESC';
      } elseif (isset($q['veletlen'])) {
        $order = 'RAND()';
      }

      $target_artpiece = $this->DB->first('artpieces', $conditions, [
        'order' => $order,
        'fields' => ['id', 'title'],
      ]);

      if (!$target_artpiece && $artpiece) {
        $target_artpiece = $artpiece;
      }

      if ($target_artpiece['id'] == @$artpiece['id']) {
        $url_end = isset($q['elozo']) || isset($q['elso'])
          ? '?elso&regen' : '?utolso&regen';
      } else {
        $url_end = '';
      }

      $redirect = $this->Html->link_url('', [
        'artpiece' => $target_artpiece,
        'url_end' => $url_end,
      ]);
    } else {
      $redirect = '/';
    }

    $this->redirect($redirect);
  }



  public function photos() {
    // Keresés
    $conditions = [];

    $conditions['before_shared'] = 0;

    if (@$this->params->query['tag'] > 0) {
      $conditions['user_id'] = (int)$this->params->query['tag'];
    }

    if (@$this->params->query['kihez'] == 'mashoz') {
      $conditions['added'] = 1;
    } elseif (@$this->params->query['kihez'] == 'magahoz') {
      $conditions['added'] = 0;
    }

    if (@$this->params->query['archiv'] == 1) {
      $conditions['archive'] = 1;
    }


    if (@$this->params->query['adalek'] == 1) {
      $conditions['other'] = 1;
    }

    if (@$this->params->query['elmenyfoto'] == 1) {
      $conditions['joy'] = 1;
    }

    if (@$this->params->query['mas_helyrol'] == 1) {
      $conditions['other_place'] = 1;
    }

    if (@$this->params->query['mulap_az'] > 0) {
      $conditions['artpiece_id'] = (int)$this->params->query['mulap_az'];
    }


    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $this->DB->count('photos', $conditions)
    ];

    $photos = $this->DB->find('photos', array(
      'fields' => array('id', 'slug', 'artpiece_id', 'user_id', 'approved', 'created', 'portrait_artist_id'),
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'approved DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ));

    $this->set([
      '_title' => 'Műlapfotók listája',
      '_active_menu' => 'Műlapok',
      '_active_submenu' => 'Fotók',
      '_sidemenu' => false,

      'filtered' => count($conditions) > 1 ? true : false,
      'photos' => $photos,
      'pagination' => $pagination,
    ]);
  }


  /**
   * Látogatói infó ajaxdiv
   */
  public function visitor_info() {
    $artpiece = $this->DB->first('artpieces', $this->params->id);

    if (!$artpiece) {
      $this->redirect('/', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      'artpiece' => $artpiece,
      'edit_count' => $this->Mongo->count('artpiece_edits', [
        'artpiece_id' => $artpiece['id']
      ]),
      'comment_count' => $this->Mongo->count('comments', [
        'artpiece_id' => $artpiece['id'],
        'forum_topic_id' => ['$exists' => false],
      ]),
      '_title' => $artpiece['title'] . ' Látogatói információk',
    ]);
  }




  /**
   *
   * KÖZELI MŰLAPOK LISTÁJA
   *
   */
  public function nearby() {

    $this->set([
      '_title' => 'Közeli alkotások',
      '_active_submenu' => 'Közeli alkotások',
      '_simple_mobile' => true,
      '_sidemenu' => false,
      '_breadcrumbs_menu' => false,
    ]);

  }



  /**
   *
   * ÉRINTÉS AJAX OLDALA
   *
   */
  public function hug() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/');
    }

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    // Friss érintés kiolvasása
    $last_hug = $this->Mongo->first('artpiece_hugs', [
      'id' => $artpiece['id'],
      'user_id' => $this->user['id'],
      'created' => ['$gt' => strtotime('-' . sDB['limits']['games']['hug_days'] . ' days')]
    ]);

    //

    $this->set([
      '_title' => 'Alkotás megérintése',
      'artpiece' => $artpiece,
      'last_hug' => $last_hug,
    ]);

  }

  /**
   *
   * TÉRKAPSZULA AJAX OLDALA
   *
   */
  public function spacecapsule() {
    if (!$this->Request->is('ajax')) {
      $this->redirect('/');
    }

    $artpiece = $this->DB->first('artpieces', $this->params->id);

    // Tényleg van-e

    $this->set([
      '_title' => 'Térkapszula feltörése',
      'artpiece' => $artpiece,
    ]);

  }

}
