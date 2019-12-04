<?php
namespace Kozterkep;

class SearchHelper {

  public function __construct($DB, $Mongo) {
    $this->DB = $DB;
    $this->Mongo = $Mongo;
    $this->MC = new MemcacheComponent();
    $this->Html = new HtmlHelper();

    // Paramétereken kívüli pipálós dolgok
    $this->artpiece_flags = [
      'masolat' => 'Másolat',
      'rekonstrukcio' => 'Rekonstrukció',
      'nem_muveszi_emlekorzo' => 'Emlékőrző',
      'atmeneti_felallitas' => 'Átmeneti',
      'muemlek' => 'Műemlék',
    ];
  }



  /**
   *
   * Keresési előzmény egy elemének kibogozása
   *
   * @param $search
   * @param array $options
   * @return string
   */
  public function history_item($search, $options = []) {
    $options = (array)$options + [
      'parameters' => []
    ];

    parse_str($search['query_string'], $query_string);
    $s = '';
    foreach ($query_string as $key => $value) {

      // Sima szöveges dolgok
      if (in_array($key, ['kulcsszo', 'alkoto', 'hely', 'cim'])) {
        $s .= $value . ', ';
      } elseif ($key == 'orszag') {
        $s .= @sDB['countries'][$value][0] . ', ';
      } elseif ($key == 'megye') {
        $s .= @sDB['counties'][$value][0] . ', ';
      } elseif ($key == 'kerulet') {
        $s .= @sDB['districts'][$value][0] . ', ';
      } elseif ($key == 'letrehozo') {
        $s .= $this->MC->t('users', $value)['name'] . ', ';
      } elseif ($key == 'sajat' && $value == 1) {
        $s .= 'Sajátok, ';
      } elseif ($key == 'kovetettek' && $value == 1) {
        $s .= 'Követettek, ';
      } elseif ($key == 'peldas') {
        $s .= $value == 'igen' ? 'Példás, ' : 'Nem példás, ';
      } elseif (in_array($key, ['kozos_gyujtemeny', 'gyujtemeny'])) {
        $s .= $value == 'egyikben-sem' ? 'Gyűjtemény nélküliek, ' : $this->MC->t('sets', $value)['name'] . ', ';
      } elseif (strpos($key, 'p_') !== false && $value == 1) {
        $parameter_id = str_replace('p_', '', $key);
        $p = $this->MC->t('parameters', $parameter_id);
        $s .= $p['name'] . ', ';
      } elseif (isset($this->artpiece_flags[$key])) {
        $s .= $this->artpiece_flags[$key] . ', ';
      }
    }


    $s = rtrim($s, ', ');

    // Ha nem tudtuk visszafejteni, akkor ezt írjuk
    return $s == '' ? 'Keresés...' : $s;
  }


  /**
   *
   * Kiolvassa a paraméter listát és a többi, az alá csapott pipálós dolog állapotát
   *
   * @param array $query
   * @return array
   */
  public function selected_parameters($query = []) {
    $selected_parameters = [
      'parameters' => [],
      'generals' => [],
    ];
    foreach ($query as $key => $value) {
      if (strpos($key, 'p_') !== false && $value == 1) {
        $parameter_id = str_replace('p_', '', $key);
        $parameter = $this->MC->t('parameters', $parameter_id);
        $selected_parameters['parameters'][] = $parameter['name'];
      } elseif (isset($this->artpiece_flags[$key])) {
        $selected_parameters['generals'][] = $this->artpiece_flags[$key];
      }
    }

    return $selected_parameters;
  }



  /**
   *
   * Műlap keresési feltétel tömb építés
   *
   * @param $q
   * @param $parameters
   * @return array
   */
  public function build_artpiece_search_conditions($q, $parameters, $user = false) {
    $conditions = [];

    $status_filtered = false;

    // FŐSZERK SZŰRÉSEK
    if (@$user['headitor'] == 1 || @$user['admin'] == 1) {

      // STÁTUSZ
      if (@$q['statusz'] > 0 || @$q['statusz'] == 'nem-publikusak') {
        if (@$q['statusz'] == 'nem-publikusak') {
          $conditions[] = 'status_id <> 5';
        } else {
          $conditions[] = 'status_id = ' . (int)$q['statusz'];
        }
        $status_filtered = true;
      }

      // PÉLDÁS SZAVAZÁS IDEJE
      if (@$q['peldas_szavazas_kezdete'] != '') {
        $conditions[] = 'superb_time >= ' . strtotime($q['peldas_szavazas_kezdete'] . ' 00:00:00');
      }
      if (@$q['peldas_szavazas_vege'] != '') {
        $conditions[] = 'superb_time <= ' . strtotime($q['peldas_szavazas_vege'] . ' 00:00:00');
      }

      // NYITOTT KÉRDÉS
      if (@$q['nyitott_kerdes'] == 1) {
        $conditions[] = 'open_question = 1';
      }
    }


    // TAGI SZŰRÉSEK
    if ($user) {
      // Csak saját
      if (@$q['sajat'] == 1) {
        $conditions[] = 'user_id = ' . $user['id'];
      }

      // Követettek; kellenek az ID-k
      if (@$q['kovetettek'] == 1) {
        $me = $this->Mongo->first('users', [
          'user_id' => $user['id']
        ]);
        $followeds = _json_decode(@$me['follow_artpieces']);
        if (count($followeds) > 0) {
          $conditions[] = 'id IN (' . implode(',', $followeds) . ')';
        }
      }

      // STÁTUSZ, csak sajátok közt szűrhetünk így
      if (@$q['sajat_statusz'] > 0 || @$q['sajat_statusz'] == 'nem-publikusak') {
        if (@$q['sajat_statusz'] == 'nem-publikusak') {
          $conditions[] = 'status_id <> 5 AND user_id = ' . $user['id'];
        } else {
          $conditions[] = 'status_id = ' . (int)$q['sajat_statusz'] . ' AND user_id = ' . $user['id'];
        }
        $status_filtered = true;
      }

    }


    if (!$status_filtered) {
      $conditions[] = 'status_id = 5';
    }

    // KULCSSZÓ
    // ha hasonlóság van pipálva, nem vesszük itt figyelembe,
    // csak az indexben.
    if (@$q['kulcsszo'] != '' && @$q['hasonlo'] != 1) {

      // Kulcsszó javítások
      // pont után szóköz
      $q['kulcsszo'] = preg_replace('/(?<!\.)\.(?!(\s|$|\,|\w\.))/', '. ', $q['kulcsszo']);

      $artpiece_ids = [];

      // Keresés leírásokban; előre kiszedem az ID-ket, hogy beletehessük a későbbi OR tömbbe
      if (@$q['leirasban'] == 1) {
        $descriptions = $this->Mongo->find_array('artpiece_descriptions', [
          //'$text' => ['$search' => $q['kulcsszo']]
          'text' => ['$regex' => $q['kulcsszo'], '$options' => 'i']
        ], [
          'projection' => ['artpieces' => 1]
        ]);

        foreach ($descriptions as $description) {
          $artpiece_ids = array_merge($artpiece_ids, $description['artpieces']);
        }

        $artpiece_ids = array_unique($artpiece_ids);
      }

      // Keresés címekben
      if (@$q['kulcsszo_reszben'] == 1) {
        // Kivesszük a triviális szavakat
        $ignorandus = sDB['similar_excludes'];
        $string = addslashes(trim(str_replace($ignorandus, '', mb_strtolower($q['kulcsszo']))));
        // Darabolunk
        $words = explode(' ', $string);
      } else {
        $words = [$q['kulcsszo']];
      }
      $or_array = [];
      foreach ($words as $word) {
        $word = trim($word);
        $or_array[] = "(title LIKE '%" . $word . "%' 
          OR title_alternatives LIKE '%" . $word . "%'
          OR title_en LIKE '%" . $word . "%')";
      }

      // Szóköz nélkül is, aka "kis királylány" szabály
      $or_array[] = "title LIKE '%" . str_replace(' ', '', $q['kulcsszo']) . "%'";
      $or_array[] = "title_alternatives LIKE '%" . str_replace(' ', '', $q['kulcsszo']) . "%'";
      $or_array[] = "title_en LIKE '%" . str_replace(' ', '', $q['kulcsszo']) . "%'";
      // Kötőjellel is, aka "Lenin-mozaik" szabály
      $or_array[] = "title LIKE '%" . str_replace(' ', '-', $q['kulcsszo']) . "%'";
      $or_array[] = "title_alternatives LIKE '%" . str_replace(' ', '-', $q['kulcsszo']) . "%'";
      $or_array[] = "title_en LIKE '%" . str_replace(' ', '-', $q['kulcsszo']) . "%'";

      if (@$q['hasonlo'] == 100) {
        $artpiece_ids = $this->Artpieces->get_similars($word, [
          'limit' => 3000,
          'type' => 'fieldlist',
        ]);
      }

      // Ha van ID tömb, akkor be az OR-ba
      if (count($artpiece_ids) > 0) {
        $or_array[] = 'id IN (' . implode(',', $artpiece_ids) . ')';
      }

      if (count($or_array) > 1) {
        $conditions[] = '(' . implode(' OR ', $or_array) . ')';
      } else {
        $conditions[] = $or_array[0];
      }
    }


    // Közös gyűjtemény, de itt kezeljük a bármilyen ~t is
    if (@$q['kozos_gyujtemeny'] != '' || @$q['gyujtemeny'] != '') {
      if (@$q['kozos_gyujtemeny'] == 'egyikben-sem') {
        $conditions[] = "connected_sets IS NULL";
      } else {
        $set_id = @$q['kozos_gyujtemeny'] != '' ? $q['kozos_gyujtemeny'] : $q['gyujtemeny'];
        $conditions[] = "connected_sets LIKE '%\"" . $set_id . "\"%'";
      }
    }


    /**
     * Alkotó és település
     * Ugyanezeket a logikákat használjunk az instant actionben is.
     * Érdemes együtt módosítani, fejleszteni, bővíteni.
     */

    // ALKOTÓ
    if (@$q['alkoto'] != '' || @$q['alkoto_az'] > 0) {

      // Ha jött szerep szűrés, akkor azzal is dolgozunk
      $contributor_filter = '';
      if (@$q['alkotoi_szerep'] == 'alkoto') {
        $contributor_filter = ', "contributor": 0';
      } elseif (@$q['alkotoi_szerep'] == 'kozremukodo') {
        $contributor_filter = ', "contributor": 1';
      }

      if (@$q['alkoto_az'] > 0) {
        // Ha van ID, az erősebb
        $conditions[] = "JSON_CONTAINS(artists, '{\"id\": " . $q['alkoto_az'] . "" .  $contributor_filter. "}')";
      } else {
        // Különben kiolvassuk a hasonló nevű személyeket, és azok ID-ivel megyünk
        $artist_ids = $this->DB->find('artists', [
          'type' => 'fieldlist',
          'conditions' => [
            'checked' => 1,
            'OR' => [
              'name LIKE' => $q['alkoto'] . '%',
              'artist_name LIKE' => $q['alkoto'] . '%',
              'alternative_names LIKE' => $q['alkoto'] . '%',
            ]
          ],
          'fields' => 'id',
        ]);
        if (count($artist_ids) > 0) {
          $or_array = [];
          foreach ($artist_ids as $artist_id) {
            // itt valszeg a LIKE a gyorsabb
            $or_array[] = "JSON_CONTAINS(artists, '{\"id\": " . $artist_id . "" .  $contributor_filter. "}')";
            //$or_array[] = "artists LIKE '%\"id\": " . $artist_id . "%'";
          }
          $conditions[] = '(' . implode(' OR ', $or_array) . ')';
        }
      }
    }



    // TELEPÜLÉS
    if (@$q['hely'] != '' || @$q['hely_az'] > 0) {
      if (@$q['hely_az'] > 0) {
        // Ha van ID, az erősebb
        $conditions[] = 'place_id = ' . $q['hely_az'];
      } else {
        // Különben kiolvassuk a hasonló nevű személyeket, és azok ID-ivel megyünk
        $place_ids = $this->DB->find('places', [
          'type' => 'fieldlist',
          'conditions' => [
            'checked' => 1,
            'OR' => [
              'name LIKE' => $q['hely'] . '%',
              'original_name LIKE' => '%' . $q['hely'] . '%',
              'alternative_names LIKE' => '%' . $q['hely'] . '%',
            ]
          ],
          'fields' => 'id',
        ]);
        if (count($place_ids) > 0) {
          $conditions[] = 'place_id IN (' . implode(',', $place_ids) . ')';
        }
      }
    }


    // CÍM, aka UTCA HÁZSZÁM
    if (@$q['cim'] != '') {
      $conditions[] = "address LIKE '%" . $q['cim'] . "%'";
    }

    // PÉLDÁS
    if (@$q['peldas'] != '') {
      $what = $q['peldas'] == 'igen' ? 1 : 0;
      $conditions[] = "superb = " . $what;
    }

    // FOTÓ HIÁNY
    if (@$q['foto_hiany'] == 1) {
      $conditions[] = "photo_count < 3";
    }
    if (@$q['alkoto_hiany'] == 1) {
      $conditions[] = "(artists LIKE '[]' OR artists LIKE '' OR artists IS NULL)";
    }
    if (@$q['datum_hiany'] == 1) {
      $conditions[] = "(dates LIKE '[]' OR dates LIKE '' OR dates IS NULL)";
    }


    // Mielőtt ID-kre megyünk
    // ha nem közösségi tér van választva, akkor nullázzuk
    // a nemközteres (esetleg rejtés miatt bent maradt) szűrést
    if (@$q['elhelyezkedes'] != 2) {
      unset($q['kozossegi_ter']);
    }


    // Megye, Ország, Kerület; sima ID-s dolgok
    foreach ([
               'orszag' => 'country_id',
               'megye' => 'county_id',
               'kerulet' => 'district_id',
               'allapot' => 'artpiece_condition_id',
               'elhelyezkedes' => 'artpiece_location_id',
               'kozossegi_ter' => 'not_public_type_id',
               'letrehozo' => 'creator_user_id',
               'masolat' => 'copy',
               'rekonstrukcio' => 'reconstruction',
               'nem_muveszi_emlekorzo' => 'not_artistic',
               'atmeneti_felallitas' => 'temporary',
               'muemlek' => 'national_heritage',
             ] as $query_field => $db_field) {

      if (@$q[$query_field] != '' && is_numeric($q[$query_field])) {
        $conditions[] = $db_field . " = " . $q[$query_field] . "";
      }
    }


    // ÉVSZÁMOK
    if (@$q['evszam_ettol'] > 0 || @$q['evszam_eddig'] > 0) {
      // ha valamelyik nincs, beállítjuk
      $y_start = !isset($q['evszam_ettol']) ? 1 : $q['evszam_ettol'];
      $y_end = !isset($q['evszam_eddig']) ? date('Y')+10 : $q['evszam_eddig']; // későbbi elbontásokra gondolva ;)

      if (@$q['evszam_kereses'] == 'legutolsok') {
        // Legutolsó dátumokat nézzük csak
        $conditions[] = "last_date >= '" . $y_start . "-0-0' AND last_date <= '" . $y_end . "-12-31'";
      } elseif (@$q['evszam_kereses'] == 'legelsok') {
        // legelső dátumokat nézzük csak
        $conditions[] = "first_date >= '" . $y_start . "-0-0' AND first_date <= '" . $y_end . "-12-31'";
      } else {
        // Legtágabb
        $conditions[] = "((first_date >= '" . $y_start . "-0-0' AND first_date <= '" . $y_end . "-12-31') "
          . "OR (last_date >= '" . $y_start . "-0-0' AND last_date <= '" . $y_end . "-12-31'))";
      }
    }


    // ÁLLAPOT extra
    if (in_array(@$q['allapot'], ['fellelheto', 'nincsott'])) {
      $condition_ids = [
        'fellelheto' => [],
        'nincsott' => []
      ];
      foreach (sDB['artpiece_conditions'] as $key => $condition) {
        if ($condition[3] == 1) {
          $condition_ids['fellelheto'][] = $key;
        } else {
          $condition_ids['nincsott'][] = $key;
        }

      }
      $conditions[] = 'artpiece_condition_id IN (' . implode(',', $condition_ids[$q['allapot']]) . ')';
    }


    // MAGYAR VONATKOZÁS
    if (@$q['magyar_vonatkozas'] != '') {
      switch ($q['magyar_vonatkozas']) {

        case 'minden_magyar':
          $conditions[] = '(country_id = 101 OR hun_related = 1)';
          break;

        case 'hazai':
          $conditions[] = 'country_id = 101';
          break;

        case 'kulfoldi_magyar':
          $conditions[] = 'country_id <> 101 AND hun_related = 1';
          break;

        case 'kulfoldi_nem_magyar':
          $conditions[] = 'country_id <> 101 AND hun_related = 0';
          break;

        case 'kulfoldi':
          $conditions[] = 'country_id <> 101';
          break;
      }
    }


    // PARAMÉTEREK
    $parameter_conditions = [];
    foreach ($parameters as $parameter) {
      if (@$q['p_' . $parameter['id']] == 1) {
        $parameter_conditions[] = "parameters LIKE '%\"" . $parameter['id'] . "\"%'";
      }
    }
    if (count($parameter_conditions) > 0) {
      if (@$q['parameter_kapcsolas'] == 'vagy') {
        $conditions[] = '(' . implode(' OR ', $parameter_conditions) . ')';
      } else {
        $conditions[] = '(' . implode(' AND ', $parameter_conditions) . ')';
      }
    }

    return $conditions;
  }



  public function build_order($query, $default_order = '', $custom_fields = []) {
    $order = $default_order;
    if (@$query['sorrend'] != '') {
      $vars = explode('-', $query['sorrend']);
      $order = '';
      switch ($vars[0]) {
        case 'abc':
          // Egyedi is lehet
          $order .= @$custom_fields['abc'] != '' ? $custom_fields['abc'] : 'name';
          break;

        case 'mulap':
          $order .= 'artpiece_count';
          break;

        case 'rogzites':
          $order .= 'created';
          break;

        case 'nezettseg':
          $order .= 'view_total';
          break;

        case 'napi_nezettseg':
          $order .= 'view_day';
          break;

        case 'publikalas':
          $order .= 'published';
          break;

        case 'evszam_elso':
          $order .= 'first_date';
          break;

        case 'evszam_utolso':
          $order .= 'last_date';
          break;

        case 'hasonlosag':
          $order .= 'score2';
          break;

        case 'aktivitas':
          $order .= 'points';
          break;

        case 'foto':
          $order .= 'photo_count';
          break;

        case 'szerkesztes':
          $order .= 'edit_other_count';
          break;

        default:
          $vars = explode(' ', $default_order);
          $order .= $vars[0];
          break;
      }
      $order .= @$vars[1] == 'novekvo' ? ' ASC' : ' DESC';
    }

    return $order;
  }


}
