<?php
// Műlap validáció a működési elvek alapján
// okos tanácsokkal. Ha változnak az elvek, ezeket a szövegeket
// és feltételeket is utána kell húzni.

$valid = true; // ha false, nem menthető a form
$messages = [];

$facts = [];

$conditions = [];
$conditions_submission = [];

$operations = [];

$not_accepted = false;


if ($user == 'community') {
  // Ha közösségi a user, akkor van jog
  $user = [
    'id' => 'community',
    'user_level' => 1
  ];
} elseif (!isset($user) || !$user) {
  // Ha nem kapunk usert
  $user = ['id' => 0];
}


// Hiányzó adatok
$artpiece['country_code'] = @$artpiece['country_code'] == ''
  ? @$saved_artpiece['country_code'] : $artpiece['country_code'];

$artpiece['country_id'] = @$artpiece['country_id'] == ''
  ? @$saved_artpiece['country_id'] : $artpiece['country_id'];




// Ha nam post-ból jött, akkor máshogy vannak a fotók
if (!isset($artpiece['photolist']) && isset($artpiece['photos'])) {
  $artpiece['photolist'] = _json_decode($artpiece['photos']);
}

// Nemarchív képek
$not_archive_photos = 0;
if (isset($artpiece['photolist']) && @count($artpiece['photolist']) > 0) {
  foreach ($artpiece['photolist'] as $photo) {
    if (@$photo['archive'] != 1) {
      $not_archive_photos++;
    }
  }
}


/**
 *
 * A nem valid dolgokkal kell kezdeni, hogy kiüsse
 * a továbbiakat és nem menjünk tovább.
 *
 *
 */

// Nem köztér és nincs alábontás
if (@$artpiece['artpiece_location_id'] == 2 && @$artpiece['not_public_type_id'] == 0) {
  $messages[] = ['Add meg a közösségi terület típusát.', 'danger'];
  $valid = false;
}

// Nem köztér, külföldi és nem jöhet innen
if ((isset($artpiece['country_code']) && @$artpiece['country_code'] != 'hu')
  || @$artpiece['country_id'] != 101) {
  if (@$artpiece['artpiece_location_id'] == 2 && in_array(@$artpiece['not_public_type_id'], [1,2])
    && @$artpiece['hun_related'] != 1) {
    $messages[] = ['<strong>Külföldről</strong> nem várunk alkotásokat templombelsőkből, valamint temetők és sírkertek területéről ha azok nem magyar vonatkozásúak.', 'danger'];
    $valid = false;
  } elseif (@$artpiece['artpiece_location_id'] == 2 && in_array(@$artpiece['not_public_type_id'], [3,4,5])
    && @$artpiece['hun_related'] != 1) {
    $messages[] = ['<strong>Külföldi közösségi terekből</strong> csak akkor küldj műveket, ha azok kertben, parkban, udvaron állnak. Ha az adott intézmény falain belül, akkor nem várjuk.', 'warning'];
  }
}




// Létrehozás előtti infók
if ($valid && @$artpiece['id'] == 0) {

  if (@$artpiece['country_code'] != 'hu') {
    $messages[] = ['<strong>Külföldről</strong> elsősorban magyar vonatkozású alkotásokat gyűjtünk. Ha a mű nem magyar vonatkozású, mindenképpen szükség lesz az alkotóra és az évszámra.', 'info'];
  }

  // Közösségi terek
  if ($valid && @$artpiece['artpiece_location_id'] == 2) {

    $messages[] = ['<strong>Közösségi terekből</strong> csak olyan alkotást tölts fel, amelynek megosztása nem ütközik jogi akadályba! Mindenképpen nézd át az idevágó részeket a Működési elvekben, hogy ne dolgozz feleslegesen.', 'info'];

    // Templom
    if (@$artpiece['not_public_type_id'] == 1) {
      $messages[] = ['<strong>Templomokból</strong> csak olyan önálló alkotásokat (szobrokat, domborműveket, domborműves emléktáblákat, és üvegablakokat ill. mozaikot) várunk, amelyek nem képezik oltárok, szószékek és ikonosztázok részét, és csak akkor, ha az alkotójuk sikerült megállapítani.', 'info'];
    }
    // Temető
    if (@$artpiece['not_public_type_id'] == 2) {
      $messages[] = ['<strong>Temetőkből, sírkertekből</strong> csak olyan önálló alkotásokat (szobrokat, domborműveket, domborműves emléktáblákat és mozaikot) várunk, amelyek alkotójának nevét sikerült megállapítani.', 'info'];
    }
    // Múzeum területe
    if (@$artpiece['not_public_type_id'] == 3) {
      $messages[] = ['<strong>Múzeum területéről</strong> nem várunk kiállítási tárgyakat. Kifejezetten múzeumban avatott alkotások jöhetnek.', 'info'];
    }
    // További intézmények
    if (in_array(@$artpiece['not_public_type_id'], [4, 5])) {
      $messages[] = ['<strong>Oktatási és egyéb intézményekből</strong> jöhetnek a szobrok, domborművek, domborműves emléktáblák, muráliák, üvegablakok, pannók, gobelinek valamint azok a kisplasztikák, amik ezekben a terekben fontos emlékhelynek számítanak (pl. névadót ábrázolják), és/vagy ide készültek.', 'info'];
    }
    // További intézmények
    if (@$artpiece['not_public_type_id'] == 6) {
      $messages[] = ['<strong>Egyéb kertekben, parkokban vagy udvarokban</strong> álló szobrokat, domborműveket, domborműves emléktáblákat és muráliákat is feltölthetsz. Ha nem tudsz bejutni ezekre a helyekre, mert nem nyilvános közösségi helyek, akkor csak akkor töltsd fel, ha közterületről látható az adott mű, tehát a szabad panoráma része.', 'info'];
    }

  }

  // Köztéri
  if ($valid && @$artpiece['artpiece_location_id'] == 1) {
    // ...
  }
}


/**
 * LÉTEZŐ LAPOK
 */
if (@$artpiece['id'] > 0) {

  if (in_array(@$artpiece['title'], ['Egy új műlap...', ''])) {
    $messages[] = ['Add meg az alkotás címét.', 'danger'];
    $valid = false;
  }

  if (@$artpiece['title_en'] == '' && @$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
    $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén adj meg angol címet.', 'danger'];
    $valid = false;
  }

  if (@$artpiece['title_alternatives'] == '' && @$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
    $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén add meg a mű helyi címét "Helyi és/vagy alternatív elnevezések" mezőben.', 'danger'];
    $valid = false;
  }

  if (@$artpiece['place_id'] == 0 && @$artpiece['place_'] != '') {
    $messages[] = ['<strong>Új települést rögzítesz.</strong> Biztosan nem találtad meg a felajánlott lehetőségek közt?', 'info new-address'];
  }


  // Nem aktuális, mert sehonnan se várunk már
  /*if (@$artpiece['country_id'] != 101 && @$artpiece['not_artistic'] == 1 && @$artpiece['hun_related'] == 0) {
    $messages[] = ['<strong>Külföldről</strong> nem várunk nem magyar vonatkozású művészi elem nélküli emlékőrzőket.', 'danger'];
    $valid = false;
    $not_accepted = true;
  }*/


  // Alkotók
  $artist_count = 0;
  if (isset($artpiece['artists']) && @count($artpiece['artists']) > 0) {
    if (!is_array($artpiece['artists'])) {
      $artpiece['artists'] = _json_decode($artpiece['artists']);
    }

    foreach ($artpiece['artists'] as $artist) {
      if (@$artist['contributor'] == 0 && sDB['artist_professions'][@$artist['profession_id']][2] == 0) {
        $messages[] = [sDB['artist_professions'][$artist['profession_id']][0] . ' szereppel csak közreműködő lehet az adott személy.', 'danger'];
        $valid = false;
      }

      // Alkotó alkotók száma
      if (@$artist['contributor'] == 0) {
        $artist_count++;
      }
    }
  } else {
    // Nincs alkotó
    if (@$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
      $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén szükség van alkotóra.', 'danger'];
      $valid = false;
    }
  }

  // Nincs alkotó, max közreműködő
  if ($artist_count == 0) {
    if (@$artpiece['artpiece_location_id'] != 1 && in_array(@$artpiece['not_public_type_id'], [1,2])) {
      $messages[] = ['Az ilyen típusú közösségi terekből csak kikutatott alkotóval várunk alkotásokat.', 'danger'];
      $valid = false;
    }
  }

  // Sehonnan
  /*if (in_array(@$artpiece['not_public_type_id'], [1]) && @$artpiece['not_artistic'] == 1) {
    $messages[] = ['Templomokból nem várunk emlékőrző alkotásokat.', 'danger'];
    $valid = false;
  }*/



  // Dátumok
  if (@$artpiece['dates'] && @count($artpiece['dates']) > 0) {
    if (!is_array($artpiece['dates'])) {
      $artpiece['dates'] = _json_decode($artpiece['dates']);
    }

    foreach ($artpiece['dates'] as $date) {
      if (isset($date['century'])) {
        // Százados
        if ($date['century'] >= 20) {
          $messages[] = ['A 20. századtól kezdve már évszámot várunk.', 'danger'];
          $valid = false;
        }
      } else {
        // Dátumos
        $date['m'] = $date['m'] < 10 ? '0' . $date['m'] : $date['m'];
        $date['d'] = $date['d'] < 10 ? '0' . $date['d'] : $date['d'];
        $date_full = _cdate($date['y'] . '-' . $date['m'] . '-' . $date['d']);
        
        if (!in_array($date['type'], ['dismantle', 'unveil']) && ($date['y'] > date('Y') || $date_full > _cdate(date('Y-m-d')))) {
          $messages[] = ['Jövőbeli dátumot ennél az eseménytípusnál nem tudunk elfogadni (' . $date['y'] . ').', 'danger'];
          $valid = false;
        }

        if ($date['m'] > 0 && $date['d'] > 0) {
          if (strtotime($date_full) == '') {
            $messages[] = ['Csak érvényes dátumot tudunk elfogadni.', 'danger'];
            $valid = false;
          }
        }
      }

      if ($date['type'] == 'dismantle') {
        $facts['dismantle_date'] = @$date_full;
      }
    }
  } elseif (@$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
    $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén szükség van évszámra.', 'danger'];
    $valid = false;
  }

  if (@$artpiece['temporary'] == 1 && !isset($facts['dismantle_date'])) {
    $messages[] = ['Átmeneti felállításoknál írd be az elbontási dátumot, ha már ismert.', 'info'];
  }

  if (isset($facts['dismantle_date']) && $facts['dismantle_date'] < date('Y-m-d')
    && $artpiece['artpiece_condition_id'] == 1) {
    $messages[] = ['Elbontott alkotás állapota nem lehet "Meglévő".', 'danger'];
    $valid = false;
  }


  if (@$artpiece['photolist'] && count($artpiece['photolist']) > 0) {
    foreach ($artpiece['photolist'] as $photo) {
      if (strpos(mb_strtolower(@$photo['source']), 'fortepan') !== false
        && preg_match('~[0-9]~', $photo['source']) !== 1
        && @$photo['user_id'] == $user['id']) {
        $messages[] = ['Fortepan fotók esetén mindig tüntesd fel a fotó azonosítót, és a fotós nevét is.', 'info'];
      }

      if (@$photo['archive'] == 1 && @$photo['source'] == '' && @$photo['user_id'] == $user['id']) {
        $messages[] = ['Archív nem saját fotók esetén mindig add meg a pontos forrást.', 'info'];
      }

      if (@$photo['artist'] == 1 && @$photo['artist_id'] == '' && @$photo['user_id'] == $user['id']) {
        $messages[] = ['Válaszd ki, mely alkotót láthatjuk a fotón.', 'danger'];
        $valid = false;
      }
    }
  }






  /**
   * PUBLIKÁLÁS és BEKÜLDÉS
   * feltételeinek vizsgálata
   */
  if (@$artpiece['id'] > 0 && @$saved_artpiece['status_id'] != 5) {

    // Bemérés
    $conditions['map'] = $artpiece['lat'] != '' && $artpiece['lon'] != '' ? [1, ''] : [0, ''];


    // Fotók
    $conditions['photos'] = [0, ''];
    if (sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']][3] == 1
      && $artpiece['artpiece_condition_id'] != 11 // letakart megvan, de nem fotózható
      && $not_archive_photos < sDB['limits']['photos']['min_count']) {
      // Helyén meglévő alkotások
      $messages[] = ['Legalább ' . sDB['limits']['photos']['min_count'] . ' jelenkori fotó kell helyükön álló alkotások publikálásához.', 'danger'];
      $conditions['photos'][1] = 'min. ' . sDB['limits']['photos']['min_count'];
    } elseif ((sDB['artpiece_conditions'][$artpiece['artpiece_condition_id']][3] != 1
      || $artpiece['artpiece_condition_id'] == 11) // vagy letakart, mert az sem fotózható
      && isset($artpiece['photolist']) && count($artpiece['photolist']) < sDB['limits']['photos']['min_count_na']) {
      // Nem meglévő alkotások
      $messages[] = ['Legalább ' . sDB['limits']['photos']['min_count_na'] . ' fotó kell már nem fotózható alkotások publikálásához.', 'danger'];
      $conditions['photos'][1] = 'min. ' . sDB['limits']['photos']['min_count_na'];
    } else {
      // Minden OK
      $conditions['photos'] = [1, ''];
    }


    // Címek
    $conditions['titles'] = [0, ''];
    if (@$artpiece['title'] == 'Névtelen Műlap') {
      $conditions['titles'][1] = 'nincs cím';
    } elseif (@$artpiece['title_en'] == '' && @$artpiece['country_code'] != 'hu'
      && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
      $conditions['titles'][1] = 'angol cím';
    } elseif (@strlen($artpiece['title']) > 1) {
      $conditions['titles'] = [1, ''];
    }

    // Hely
    $conditions['place'] = [0, ''];
    if (@$artpiece['place_id'] > 0) {
      $conditions['place'] = [1, ''];
    }

    // Alkotó
    $conditions['artist'] = [0, ''];
    $has_artist = isset($artpiece['artists'])
      && count($artpiece['artists']) > 0 ? true : false;
    if ((!$has_artist || $artist_count == 0) &&
      (
        (@$artpiece['artpiece_location_id'] != 1 && in_array(@$artpiece['not_public_type_id'], [1,2])) // templom v. temető
          || (@$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) // külföldi nemmagyar
      )
    ) {
      $conditions['artist'] = [2, ''];
    } elseif (!$has_artist) {
      $conditions['artist'] = [3, ''];
    } else {
      $conditions['artist'] = [1, ''];
    }


    // Emlékőrző már nem
    if (@$artpiece['not_artistic'] == 1) {
      $messages[] = ['Művészi elem nélküli emlékőrzők jelenleg nem publikálhatóak.', 'danger'];
      $valid = false;
    }

    // Dátum
    $conditions['date'] = [0, ''];
    $has_dates = isset($artpiece['dates'])
    && count($artpiece['dates']) > 0
    && (@$artpiece['dates'][0]['y'] > 0 || @$artpiece['dates'][0]['century'] > 0) ? true : false;

    if (!$has_dates &&
      (
        (@$artpiece['country_code'] != 'hu' && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) // külföldi nemmagyar és nincsdátum
      )
    ) {
      $conditions['date'] = [2, ''];
      $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén szükség van dátumra.', 'danger'];
      $valid = false;
    } elseif (!$has_dates) {
      $conditions['date'] = [3, ''];
    } else {
      $conditions['date'] = [1, ''];
    }


    // Paraméterek
    // a kötelezők
    $parameters_done = [
      1 => ['type', false],
      2 => ['style', false],
      3 => ['form', false],
      4 => ['material', false],
    ];
    $parameters_all = $parameters_done + [
      5 => ['religion', false],
      6 => ['history', false],
    ];

    $parachecks = [];

    // végigpörgetem, hogy minden kötelező megvan-e
    if (@count(@$artpiece['parameters']) > 0) {

      if (!is_array($artpiece['parameters'])) {
        // DB-ből jön, át kell alakítani a post-hoz hasonlóvá
        $parameters_ = _json_decode($artpiece['parameters']);
        $artpiece['parameters'] = [];
        foreach ($parameters_ as $p) {
          $artpiece['parameters'][] = [
            'id' => $p,
            'value' => 1,
          ];
        }
      }

      foreach ($artpiece['parameters'] as $parameter) {
        if ($parameter['value'] == 1) {
          $p = _mct('parameters', $parameter['id']);
          if (isset($parameters_done[$p['parameter_group_id']])) {
            // kötelezőbe tartozik => done
            $parameters_done[$p['parameter_group_id']][1] = true;
          }
          // Bármelyikbe tartozik?
          if (isset($parameters_all[$p['parameter_group_id']])) {
            // kötelezőbe tartozik => done
            $parameters_all[$p['parameter_group_id']][1] = true;
          }

          // Háborús? később kell.
          if (in_array($parameter['id'], [82,83,84,85,86,89])) {
            $parachecks['wars'] = true;
          }

          // Feszület
          if (in_array($parameter['id'], [8]) && $artist_count == 0) {
            $messages[] = ['Amennyiben ez az alkotás egy kereszt feszülettel, akkor mindenképp kutass ki alkotót a publikáláshoz.', 'danger'];
            $valid = false;
          }
        }
      }
    }
    // visszaadom
    foreach ($parameters_done as $pd) {
      $conditions[$pd[0]] = [$pd[1] ? 1 : 0, ''];
    }


    // Paraméter javaslatok
    // Szentes címek, de nincs vallás
    if ((strpos(mb_strtolower(@$artpiece['title']), 'szent') !== false
        || strpos(mb_strtolower(@$artpiece['title_alternatives']), 'szent') !== false) && !$parameters_all[5][1]) {
      $messages[] = ['Ha szentet ábrázol a mű, valószínűleg van érvényes <strong>vallási kapcsolódás</strong>, így jelöld a "Kapcsok" fül alatt.', 'info'];
    }
    // Háborús címek, de nincs történelem
    if (_contains(mb_strtolower(@$artpiece['title']), [
        'rákóczi',
        '48',
        'tanácsköztársaság',
        'trianon',
        'világháború',
        'holokauszt',
        'holocaust',
        'felszabadítási',
        'szovjet',
        '56',
    ]) && @$parachecks['wars'] != true) {
      $messages[] = ['A cím alapján úgy tűnik, <strong>történelmi kapcsolódás is jelölhetó</strong> a műlapnál, a "Kapcsok" fül alatt találod ezeket.', 'info'];
    }


    /**
     * Templom és temetőből érkező műv. elem nélküli emlékőrző csak akkor jöhet
     * ha I-II VH, szovjetfelszab, 1848, 1956, holokauszt
     */
    /*if (@$artpiece['artpiece_location_id'] != 1 && in_array(@$artpiece['not_public_type_id'], [2])
      && @$artpiece['not_artistic'] == 1 && @$parachecks['wars'] != true) {
      //
      $messages[] = ['Temetőkből hozott, művészeti elemmel nem rendelkező alkotásokat csak háborús történelmi kapcsolat esetén fogadunk el.', 'danger'];
      $valid = false;
    }*/



    // Sztorik
    $conditions['stories'] = [0, ''];
    $descriptions_done = [
      'hun' => false,
      'eng' => false,
    ];
    // Mentett sztorik
    if (count($saved_descriptions) > 0) {
      foreach ($saved_descriptions as $description) {
        if ($description['lang'] == 'HUN'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_hun']) {
          $descriptions_done['hun'] = true;
        } elseif ($description['lang'] == 'ENG'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_eng']) {
          $descriptions_done['eng'] = true;
        }
      }
    }
    // Szerkesztés alatti sztorik
    if (@count(@$artpiece['descriptions']) > 0) {
      foreach ($artpiece['descriptions'] as $description) {
        if ($description['id'] == 'new_hun'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_hun']) {
          // Most létrehozott magyar
          $descriptions_done['hun'] = true;
        } elseif ($description['id'] == 'new_eng'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_eng']) {
          // Most létrehozott angol
          $descriptions_done['eng'] = true;
        } elseif (@$description['lang'] == 'HUN'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_hun']) {
          // Korábbi, most módosított magyar
          $descriptions_done['hun'] = true;
        } elseif (@$description['lang'] == 'ENG'
          && mb_strlen($description['text']) > sDB['limits']['descriptions']['min_eng']) {
          // Korábbi, most módosított angol
          $descriptions_done['eng'] = true;
        }
      }
    }

    // Már nem kell külkföldinemmagyarnál angol leírás
    /*
    if (!$descriptions_done['eng'] && @$artpiece['country_code'] != 'hu'
      && @$artpiece['country_id'] != 101 && @$artpiece['hun_related'] != 1) {
      $messages[] = ['Külföldi, nem magyar vonatkozású alkotások esetén írj legalább ' . sDB['limits']['descriptions']['min_eng'] . ' karakter angol sztorit is.', 'danger'];
      $valid = false;
      $conditions['stories'] = [0, 'angol is kell'];
    } else {
      $descriptions_done['eng'] = true;
    } */


    if (!$descriptions_done['hun']) {
      $messages[] = ['Saját, legalább ' . sDB['limits']['descriptions']['min_hun'] . ' karakter hosszú magyar leírás mindenképpen szükséges.', 'danger'];
      // nem invalid, mert mi van, ha rövidebbet akar menteni (átmenetileg)
      //$valid = false;
      $conditions['stories'] = [0, ''];
    }
    if ($descriptions_done['hun']) {
      $conditions['stories'] = [1, ''];
    }


    // Erről egyébként hibajelzés is van
    if ($artpiece['artpiece_location_id'] != 1 && $artpiece['not_public_type_id'] == 0) {
      $conditions['place'] = [0, ''];
    }



    // Ellenőrzésre beküldés és Publikálás

    $publishable = $submission = true;

    // Ha akárcsak egy feltétel is teljesületlen, nem publikálható és nem is küldhető be
    foreach ($conditions as $key => $condition) {
      if ($condition[0] == 0) {
        $publishable = false;
        // Ha ez aktív, akkor beküldeni sem lehet lényeges hibával
        //$submission = false;
      }

      // Nem publikálható, de köztérre mehet
      if ($condition[0] != 2
        && isset($conditions_submission[$key]) && $conditions_submission[$key][0] == 1) {
        $submission = true;
      }
    }

    // Köztérre küldhető, ha minden OK
    $operations = [
      'submission' => $submission,
      'publish' => 0,
      'memo' => '',
    ];


    //var_dump($publishable);

    if ($publishable) {
      $operations['publishable'] = 1;
    }

    /*
     * Publikálni csak akkor lehet, ha a user megteheti, és minden OK,
     * és nem esik bele a lap a különleges dolgokba:
     *  (hazai || magyar) + művészeti elemmel rendelkezik
     * és van heti kerete még
     */
    if (@$user['user_level'] == 1 // usernek van joga, törzstag
      && $publishable // publikálható
      ) {

      // Ha közösség nyomja, akkor a publikálható publikálható
      // ha user, akkor csak a magyar (vonatkozású) és a művészi elemes
      if ($user['id'] == 'community' ||
        (@$artpiece['country_code'] == 'hu' || @$artpiece['country_id'] == 101 || @$artpiece['hun_related'] == 1)) {
        $operations['publish'] = 1;
      }
    }

    // Nem törzstag
    if (@$user['user_level'] === 0 && $user['id'] == $artpiece['user_id']) {
      $operations['memo'] .= 'Jelenleg a főszerkesztők segítségével tudod publikálni műlapjaidat. A törzstagság elérése után önállóan publikálhatsz. Erről a főszerkesztők döntenek a munkád alapján.<br />';
    }

    // Csak főszerkesztőkön keresztül publikálható
    if ((@$artpiece['country_id'] != 101 && @$artpiece['country_code'] != 'hu' && @$artpiece['hun_related'] != 1)) {
      $operations['memo'] .= '<strong>Ez az alkotás a paraméterei alapján csak a főszerkesztők átnézése után kerülhet publikálásra.</strong><br />';
    }
  }
}

return [
  'valid' => $valid,
  'messages' => $messages,
  'conditions' => $conditions,
  'operations' => $operations,
];