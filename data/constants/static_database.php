<?php
// Konstans adatok, amiket nem kell sql-be, vagy más dologba írni.
return [

  'user_roles' => [
    99 => 'főszerkesztői tanácsadó',
    148 => 'főszerkesztő, alkotótár, településtár, gyűjtemény, FB és Instagram kezelő',
    1 => 'üzemgazda',
  ],


  'cookie_descriptions' => [
    'KT-latogato' => 'Az ebben tárolt adattal azonosít téged a Köztérkép saját nyilvános webstatisztikája.',
    'KT-belepvetarto' => 'Ha bejelentkezel és pipálod, hogy jegyezze meg a belépésedet a böngésző, akkor ennek segítségével tudjuk, hogy te vagy te.',
    'KT-munkamenet' => 'Ez azonosítja az aktuális munkamenetedet, ami segít abban, hogyha egyik oldalról a másikra kattintasz, vagy épp egy űrlapot mentesz, akkor is tudja a szerver, hogy te vagy az, aki az előbb megnyomta a gombot.',
    'OpenStreetMap, Wikimedia és Google' => 'Amennyiben a térképet használod, a kiválasztott réteg szolgáltatója írhat sütiket eszközödre a saját térképrétegeinek megfelelő megjelenítése érdekében.',
  ],

  // Limitek
  'limits' => [
    'artpieces' => [
      'weekly_max' => 5,
      'top_photo_min' => 8,
      'top_photo_max' => 24,
    ],
    'photos' => [
      'min_size' => 1400, // nem archív fotó min mérete
      'archive_min_size' => 400, // archív fotó min. mérete
      'min_count' => 5, // meglévő alkotás min fotója
      'min_count_na' => 1, // nem meglévő alkotás min. fotója
      'max_upload_total' => 50, // 50 mega
      'exif_taken_shown' => 6 * 30 * 24 * 60 * 60, // ennyivel korábbi exif taken időt mutatunk csak
    ],
    'descriptions' => [
      'min_hun' => 225,
      'min_eng' => 100,
    ],
    'comments' => [
      'highlight_months' => 6, // ennyi ideig lehet kiemelt egy komment
    ],
    'edits' => [
      'inactive_after_months' => 6, // ennyi hónap errenemjárás után lesz valaki "inaktív", vagyis került a szerkesztése azonnal a köztérre + ennyi hónap inaktivitás után átállítjuk nemkezelőre.
      'wait_days' => 5, // ennyi nap után kerül köztérre a szerk
    ],
    'headitors' => [
      'superb_revote' => '-4 years', // ennyi idő után újra megjelennek a lapok a szobában
    ],
    'games' => [
      'hug_container_distance' => 1500,
      'hug_distance' => 50,
      'hug_days' => 14,
    ],
    'posts' => [
      'intro_max_length' => 350,
      'text_min_length' => 100,
    ]
  ],


  // Feltöltött képek minősége
  'photo_quality' => 70,

  // Erre vágjuk a feltöltött műlap fotókat
  'photo_sizes' => [
    1 => 1200,
    2 => 800,
    3 => 500,
    4 => 500,
    5 => 250,
    6 => 150,
    7 => 75,
    8 => 16,
  ],


  // Állapotok (name, rank, simple, in_place, color, icon, huggable)
  'artpiece_conditions' => [
    1 => ['Meglévő', 1, 1, 1, 'success', 'check', 1],
    2 => ['Elpusztult', 2, 0, 0, 'warning', 'empty-set', 0],
    3 => ['Tudsz róla?', 3, 0, 0, 'warning', 'question-square', 0],
    4 => ['Veszélyben', 4, 1, 1, 'danger', 'exclamation-square', 1],
    10 => ['Áthelyezve innen', 5, 0, 0, 'info', 'truck-loading', 0],
    5 => ['Lebontott', 6, 0, 0, 'info', 'arrow-to-bottom', 0],
    6 => ['Átmenetileg nincs a helyén', 7, 1, 0, 'warning', 'retweet-alt', 0],
    7 => ['Ellopták, eltűnt', 8, 1, 0, 'danger', 'user-secret', 0],
    8 => ['Megrongálódott', 9, 0, 1, 'danger', 'monument fa-rotate-90', 1],
    9 => ['Megrongálták', 10, 1, 1, 'danger', 'user-ninja', 1],
    11 => ['Letakart, megközelíthetetlen', 11, 0, 1, 'info', 'eye-slash', 0],
  ],

  // Státuszok
  'artpiece_statuses' => [
    1 => ['Szerkesztés alatt', 'warning'],
    2 => ['Ellenőrzésre küldve', 'success'],
    3 => ['Visszaküldött', 'danger'],
    4 => ['Visszavett', 'secondary'], // ezt nem kell használni
    5 => ['Publikus', 'primary'],
    6 => ['Elvetett', 'danger'],
    //7 => ['...'],
  ],
  'edit_statuses' => [
    1 => ['Szerkesztés alatt'],
    2 => ['Várakozó'],
    3 => ['Újranyitott'],
    4 => ['Visszavont'],
    5 => ['Beemelt'],
    6 => ['Elvetett'],
    7 => ['Szerkesztéssel felülírt'],
  ],


  // Rejtett szerkesztés mezők, amiket nem mutatunk a szerk. részleteknél
  'hidden_edit_fields' => ['id', 'hug_id', 'artpiece_id', 'prev_data', 'status_id', 'user_id', 'manage_user_id', 'receiver_user_id', 'created', 'modified', 'approved', 'edit_type_id', 'before_shared', 'own_edit', 'invisible'],

  // ezek üresen is kellenek
  'artpiece_fields_empties' => ['title_alternatives', 'title_en', 'place_description', 'address', 'connected_sets', 'connected_artpieces'],

  // Műlap mezők
  'artpiece_fields' => [
    'id' => 'AZ',
    'status_id' => 'Státusz',
    'user_id' => 'Tag',
    'created' => 'Létrehozás',
    'modified' => 'Módosítás',
    'approved' => 'Elfogadás',
    'address' => 'Utca, hsz.',
    'photo_id' => 'Borító AZ',
    'photo_slug' => 'Borító kép',
    'place_description' => 'Hely leírása',
    'title' => 'Cím',
    'title_alternatives' => 'Alternatív, helyi cím',
    'title_en' => 'Angol cím',
    'text' => 'Leírás',
    'text_en' => 'Angol leírás',
    'source' => 'Forrás',
    'links' => 'Linkek',
    'parameters' => 'Paraméterek',
    'descriptions' => 'Leírások',
    'unveil_date' => 'Avatás ideje',
    'place_id' => 'Település',
    'country_id' => 'Ország',
    'county_id' => 'Megye',
    'district_id' => 'Kerület',
    'lat' => 'Lat.',
    'lon' => 'Lon.',
    'lng' => 'Lon.',
    'manage_user_id' => 'Kezelő',
    'artpiece_condition_id' => 'Állapot',
    'artpiece_location_id' => 'Elhelyezkedés',
    'not_public_type_id' => 'Közösségi tér típusa',
    'hun_related' => 'Magyar vonatkozású',
    'not_artistic' => 'Nincs művészi elem',
    'dates' => 'Dátumok',
    'photolist' => 'Fotók adatai',
    'photos' => 'Fotó sorrend változása',
    'top_photo_count' => 'Kiemelt képek száma',
    'unveil_date' => 'Avatási dátum',
    'original_unveil_date' => 'Eredeti avatási dátum',
    'original_unveil_date_unknown' => 'Eredeti avatási dátum ismeretlen',
    'temporary' => 'Átmeneti felállítás',
    'dismantling_date' => 'Elbontás időpontja',
    'anniversary' => 'Évfordulós',
    'local_importance' => 'Helyi jelentőség',
    'national_heritage' => 'Nyilvántartott műemlék',
    'copy' => 'Másolat',
    'reconstruction' => 'Rekonstrukció',
    'connected_artpieces' => 'Kapcsolódó műlapok',
    'connected_sets' => 'Kapcsolt gyűjtemények',
  ],


  // Fotó mezők
  'photo_fields' => [
    'rank' => 'Sorszám',
    'top' => 'Kiemelt',
    'text' => 'Leírás',
    'source' => 'Forrás',
    'other' => 'Adalék',
    'other_place' => 'Nem a helyszínről',
    'joy' => 'Élménykép',
    'artist' => 'Alkotó a képen',
    'artist_id' => 'Alkotó a képen',
    'sign_artist_id' => 'Alkotó',
    'unveil' => 'Avatást látunk',
    'sign' => 'Szignó a képen',
    'archive' => 'Archív fotó',
    'cover' => 'Borítókép',
    'year' => 'Évszám',
    'license_type_id' => 'Kép felhasználhatósága',
  ],


  // Műlap param csoportok (name, icon)
  'parameter_groups' => [
    1 => ['Típus', 'list-alt'],
    2 => ['Stílus', 'pencil-ruler'],
    3 => ['Ábrázolt formák', 'dice-d4'],
    4 => ['Anyag', 'cube'],
    5 => ['Vallási kapcsolódás', 'place-of-worship'],
    6 => ['Történelmi kapcsolódás', 'monument'],
    7 => ['Műemlék jelleg', 'landmark'],
  ],


  // Alkotó professziók (megnevezés, mű alkotásában betöltött szerep, alkotói szerep (0 = közr.))
  'artist_professions' => [
    1 => ['Szobrász', 1, 1],
    2 => ['Kőfaragó', 1, 1],
    3 => ['Építész', 1, 1],
    4 => ['Tervező', 1, 1],
    5 => ['Kivitelező', 1, 0], // csak közreműködő lehet
    6 => ['Festőművész', 1, 1],
    7 => ['Címerfestő', 0, 1],
    8 => ['Keramikus', 1, 1],
    9 => ['Képzőművész', 1, 1],
    10 => ['Grafikus', 0, 1],
    11 => ['Díszítőszobrász', 0, 1],
    12 => ['Bronzművész', 0, 1],
    13 => ['Mozaikkészítő', 1, 1],
    14 => ['Ötvösművész', 1, 1],
    15 => ['Iparművész', 1, 1],
    16 => ['Üvegművész', 1, 1],
    17 => ['Díszműkovács', 1, 1],
    18 => ['Fafaragó', 1, 1],
    19 => ['Aranyműves', 0, 1],
    20 => ['Textilművész', 1, 1],
    21 => ['Népművész', 0, 1],
    22 => ['Egyéb', 0, 1],
    23 => ['Bronzöntő', 1, 0], // csak közreműködő lehet
    24 => ['Építész', 0, 1],
    25 => ['Restaurátor', 1, 1],
    26 => ['Porcelánművész', 0, 1],
  ],

  'before_names' => [
    1 => 'ifj.',
    2 => 'id.',
    3 => 'Dr.',
    4 => 'Prof.',
    5 => 'DLA',
    6 => 'legifj.'
  ],

  // Közösségi tér típusok (name, icon)
  'not_public_types' => [
    1 => ['Templombelső', 'place-of-worship'],
    2 => ['Temető, sírkert', 'tombstone-alt'],
    3 => ['Múzeum területe', 'university'],
    4 => ['Oktatási intézmény területe', 'school'],
    5 => ['Egyéb intézmény területe', 'building'],
    6 => ['Egyéb kert, park vagy udvar', 'trees'],
  ],

  // Típus alcsoportok
  'artpiece_type_groups' => [
    1 => 'Átfogó besorolások',
    2 => 'Alapvető típusok',
    3 => 'Murália típusok',
    4 => 'Vízzel kapcsolatos típusok',
    5 => 'Egyéb típusok',
  ],

  // Forma alcsoportok
  'artpiece_form_groups' => [
    1 => '',
    2 => 'Alakos (figurális)',
  ],

  // Műlap kapcsolás típusok
  'artpiece_connection_types' => [
    0 => 'Kapcsolódó',
    1 => 'Példány',
    2 => 'Előzmény',
    3 => 'Utód',
  ],


  // Műlap jelölés típusok
  'artpiece_flagtypes' => [
    1 => ['Publikálás'],
    2 => ['Kitüntetés'], // ez a régi badge volt
    3 => ['Duplikáció'],
    4 => ['Átadás'],
    5 => ['Nyitott kérdés'],
    6 => ['Szétszedhető'],
    7 => ['Fotóéhes'],
    8 => ['Elvekbe ütköző'],
  ],


  // név, headitorial, ikon
  'forum_topics' => [
    6 => ['FőszerkSzoba', 1, 'glasses-alt'],
    4 => ['Beszéljük meg', 0, 'comments'],
    24 => ['Jövőnk', 0, 'road'],
    16 => ['Feltöltési ötletek', 0, 'lightbulb'],
    9 => ['Sajtófigyelő és ajánló', 0, 'newspaper'],
    7 => ['Tudsz róla? Keressük!', 0, 'question'],
    //8 => ['Pólók és rendelés', 0, 'tshirt'],
  ],

  // Könyvjelző típusok
  'bookmark_types' => [
    1 => ['Műlapok', 'artpieces'],
    2 => ['Linkek', 'links'],
    3 => ['Jegyzetek', 'notes'],
  ],


  // Idődolgok
  'month_names' => [
    1 => 'Január',
    2 => 'Február',
    3 => 'Március',
    4 => 'Április',
    5 => 'Május',
    6 => 'Június',
    7 => 'Július',
    8 => 'Augusztus',
    9 => 'Szeptember',
    10 => 'Október',
    11 => 'November',
    12 => 'December',
  ],
  'day_names' => [
    1 => 'Hétfő',
    2 => 'Kedd',
    3 => 'Szerda',
    4 => 'Csütörtök',
    5 => 'Péntek',
    6 => 'Szombat',
    7 => 'Vasárnap',
  ],

  'date_types' => [
    'created' => 'Készítés',
    'erection' => 'Felállítás',
    'unveil' => 'Avatás',
    'dismantle' => 'Elbontás',
  ],

  'numbers' => [
    1 => 'egy',
    2 => 'kettő',
    3 => 'három',
    4 => 'négy',
    5 => 'öt',
    6 => 'hat',
    7 => 'hét',
    8 => 'nyolc',
    9 => 'kilenc',
    10 => 'tíz',
    11 => 'tizenegy',
    12 => 'tizenkettő',
    13 => 'tizenhárom',
    14 => 'tizennégy',
    15 => 'tizenöt',
    16 => 'tizenhat',
    17 => 'tizenhét',
    18 => 'tizennyolc',
    19 => 'tizenkilenc',
    20 => 'húsz',
    21 => 'huszonegy',
    22 => 'huszonkettő',
    23 => 'huszonhárom',
    24 => 'huszonnégy',
    25 => 'huszonöt',
    26 => 'huszonhat',
    27 => 'huszonhét',
    28 => 'huszonnyolc',
    29 => 'huszonkilenc',
    30 => 'húsz',
  ],


  // Gyűjtemények típusai
  'set_types' => [
    1 => 'Közös',
    2 => 'Tagi',
    3 => 'Múlt Kincse',
  ],

  // A választhatók
  'set_types_public' => [
    1 => 'Közös',
    2 => 'Tagi',
  ],


  // Blogposzt kategóriák
  // név, adminblog, téma slug
  'post_categories' => [
    1 => ['Gépház', 1, 'gephaz'],
    2 => ['Alkotók', 0, 'alkotok'],
    3 => ['Helyek', 0, 'helyek'],
    4 => ['Műlapok', 0, 'mulapok'],
    5 => ['Ajánló', 0, 'ajanlo'],
    6 => ['Interjú', 0, 'interju'],
    7 => ['Kutatás', 0, 'kutatas'],
    8 => ['Szubjektív', 0, 'szubjektiv'],
    9 => ['Pályázat', 0, 'palyazat'],
    10 => ['Heti szüret', 1, 'heti-szuret'],
    11 => ['Segédlet', 1, 'segedlet'],
  ],


  // User pont számítás
  // Ha változtatod, futtasd újra a UsersJob scores cuccát, feltétel nélkül
  'user_scores' => [
    'settings' => [
      'min_points' => 1,
      'max_points' => 5,
      'headitor_points' => 6,
      'artpiece_limit' => 30, // ennyi műlap után lehet törztsagnak lenni
    ],
    'values' => [
      'artpiece' => 15,
      'photo' => 1,
      'description' => 4,
      'edit' => 3,
    ]
  ],


  // Műlap szavazat típusok (id, név, headitor-csak-e, pontszám/szavazatszám (ha van)
  'artpiece_vote_types' => [
    'publish' => [1, 'Publikálás', 1, 15],
    'publish_pause' => [2, 'Publikálás szüneteltetése', 1, false],
    'praise' => [3, 'Szép műlap!', 0, false],
    'superb' => [4, 'Példás műlap!', 1, 1],
    'question' => [5, 'Nyitott kérdés', 1, false],
    'edit_accept' => [6, 'Szerkesztés elfogadás', 1, 10],
    'harvest' => [7, 'Szüretelve', 1, false],
    'checked' => [8, 'Átvnézve', 1, false],
    'underline' => [9, 'Aláhúzva', 1, false],
  ],

  // Események típusai (név, group, public, robotmondja)
  'event_types' => [
    1 => ['Ellenőrzésre küldés', 'artpiece', 0, 1],
    2 => ['Ellenőrzésről visszavétel', 'artpiece', 0, 1],
    3 => ['Visszaküldés', 'artpiece', 0, 1],
    4 => ['Saját publikálás', 'artpiece', 1, 1],
    5 => ['Publikálás', 'artpiece', 1, 1],
    6 => ['Fotó feltöltés', 'artpiece', 1, 0],
    7 => ['Érintés', 'artpiece', 1, 0],
    8 => ['Térkapszula feltörése', 'artpiece', 1, 0],
    9 => ['Példás műlap megszavazása', 'artpiece', 0, 1],
    10 => ['Szép munka! jelölés', 'artpiece', 1, 0],
    11 => ['Műlap átadása', 'artpiece', 1, 1],
    12 => ['Szerkesztés jóváhagyás', 'edit', 1, 1],
    13 => ['Szerkesztés megszavazása', 'edit', 1, 1],
    14 => ['Szerkesztés elutasítása', 'edit', 0, 1],
    15 => ['Szerkesztés visszavonása', 'edit', 0, 1],
    16 => ['Szerkesztés újranyitása', 'edit', 0, 1],
    17 => ['Szerkesztés visszaállítása', 'edit', 0, 1],
    18 => ['Új tag regisztrációja', 'user', 1, 1],
    19 => ['Tag saját publikálóvá vált', 'user', 1, 1],
    20 => ['Heti kiemelt szerkesztő', 'user', 1, 1],
    21 => ['Tag mérföldkő', 'user', 1, 1],
    22 => ['Új blogbejegyzés', 'post', 1, 0],
    23 => ['Gépház blogbejegyzés', 'post', 1, 1],
    24 => ['Új könyv a könyvtéren', 'book', 1, 0],
    25 => ['Új mappa publikálása', 'folder', 1, 0],
    26 => ['Új naptáresemény', 'calendar', 1, 0],
    27 => ['KöztérGép mondja', 'robot', 1, 1],
    28 => ['Fotó feltöltés', 'artist', 1, 0],
    29 => ['Visszanyitás', 'artpiece', 0, 1],
  ],

  'events_hidden_from_artpage_history' => [
    9
  ],


  'notification_types' => [
    'artpieces' => ['Műlapok', 'Saját műlapjaidhoz érkező hozzászólások, szerkesztések, fotók, dicséretek, státuszváltozások és egyéb események, valamint műlap szerkesztési meghívások.'],
    'edits' => ['Szerkesztés', 'Saját szerkesztéseddel kapcsolatos hozzászólások és események.'],
    'comments' => ['Válaszok', 'Hozzászólásaidra adott válaszok bárhol.'],
    'things' => ['Saját dolgaim', 'Saját mappáidat, gyűjteményeidet, könyveidet, posztjaidat érintő események.'],
    'games' => ['Játék', 'Saját műlapjaid érintése, térkapszula feltörése és más játékos értesítések.'],
    'others' => ['Egyéb', 'Figyelt fórumok hozzászólásai és egyéb téged érintő értesítések.'],
  ],


  // Dolgok a lapon, és pár paraméterük, hogy valamit modell név alapján megfoghassunk
  // Név, Többeszszám, URL kezdet, említés (vars.php-ban használtnál teljesebb, és a JS is eléri)
  'model_parameters' => [
    'artpieces' => ['műlap', 'műlapok', '/'],
    'artpiece_edits' => ['szerkesztés', 'szerkesztések'],
    'artists' => ['alkotó', 'alkotók', '/alkotok/megtekintes/'],
    'places' => ['hely', 'helyek', '/helyek/megtekintes/'],
    'users' => ['profil', 'profilok', '/kozosseg/profil/'],
    'folders' => ['mappa', 'mappák', '/mappak/megtekintes/'],
    'books' => ['könyv', 'könyvek', '/adattar/konyv/'],
    'posts' => ['blogposzt', 'blogposztok', '/blogok/megtekintes/'],
    'pages' => ['oldal', 'oldalak', '/oldal/'],
    'forum_topics' => ['fórum téma', 'fórum témák', '/kozter/forum-tema/'],
    'sets' => ['gyűjtemeny', 'gyűjtemények', '/gyujtemenyek/megtekintes/'],
  ],


  // Licenszek; többnyire CC; 4.0 jelenleg
  'license_types' => [
    1 => 'Nevezd meg! 4.0 Nemzetközi',
    2 => 'Nevezd meg! - Így add tovább! 4.0 Nemzetközi',
    3 => 'Nevezd meg! - Ne változtasd! 4.0 Nemzetközi',
    4 => 'Nevezd meg! - Ne add el! 4.0 Nemzetközi',
    5 => 'Nevezd meg! - Ne add el! - Így add tovább! 4.0 Nemzetközi',
    6 => 'Nevezd meg! - Ne add el! - Ne változtasd! 4.0 Nemzetközi',
    7 => 'Nincs engedély',
  ],

  // Licenszek átjárhatósága
  'license_transmissions' => [
    1 => ['parents' => [2, 3, 4, 5, 6, 7], 'children' => []],
    2 => ['parents' => [5, 7], 'children' => [1]],
    3 => ['parents' => [6, 7], 'children' => [1]],
    4 => ['parents' => [5, 6, 7], 'children' => [1]],
    5 => ['parents' => [7], 'children' => [1, 2, 4]],
    6 => ['parents' => [7], 'children' => [1, 3, 4]],
    7 => ['parents' => [], 'children' => [1, 2, 3, 4, 5, 6]],
  ],

  // Licenszek ikonjai és linkje
  'license_infos' => [
    1 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by'],
      'https://creativecommons.org/licenses/by/4.0/deed.hu'
    ],
    2 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by', 'fab fa-creative-commons-sa'],
      'https://creativecommons.org/licenses/by-sa/4.0/deed.hu'
    ],
    3 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by', 'fab fa-creative-commons-nd'],
      'https://creativecommons.org/licenses/by-nd/4.0/deed.hu'
    ],
    4 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by', 'fab fa-creative-commons-nc'],
      'https://creativecommons.org/licenses/by-nc/4.0/deed.hu'
    ],
    5 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by', 'fab fa-creative-commons-nc', 'fab fa-creative-commons-sa'],
      'https://creativecommons.org/licenses/by-nc-sa/4.0/deed.hu'
    ],
    6 => [
      ['fab fa-creative-commons', 'fab fa-creative-commons-by', 'fab fa-creative-commons-nc', 'fab fa-creative-commons-nd'],
      'https://creativecommons.org/licenses/by-nc-nd/4.0/deed.hu'
    ],
    7 => [
      ['fas fa-ban'],
      ''
    ],
  ],


  // Ezeket töröljük a hasonló keresésekkor a címek szövegéből
  // ignorandus néven is tolom -- ez egy örök bővítendő
  'similar_excludes' => [' a ', ' az ', ' és ', 'egy ', 'két ', ' and ', ' or ', ' the ', ' in ', '-ház', ' for ', ' to ', ' is ', ' at ', ' with ', 'I.', 'II.', 'III.', 'V.', 'VI.', 'VIII.', 'IX.', 'X.', 'gróf', 'Gróf', 'emlékműve', '-emlékmű', 'dombormű', '-emléktábla', 'szent', '-szobor', ' szobor', ' szobra', 'egykori'],


  // Segédlet videók
  'video_guides' => [
    [
      'rank' => 1, // guide oldalon sorrend, logika végigvezetés sorrendje
      'time' => '2019-05-03 08:49',
      'highlighted' => 1, // utolsó kiemelt kerül köztérre 2 hétig
      'url' => 'eFV23_pNLSI',
      'title' => 'Bevezetés tagoknak a felületről',
      'description' => 'A menü, az oldal felépítési logikája és működése. A videó főként tagok számára készült.',
    ],
    [
      'rank' => 2, // guide oldalon sorrend, logika végigvezetés sorrendje
      'time' => '2019-05-25 13:25',
      'highlighted' => 1, // utolsó kiemelt kerül köztérre 2 hétig
      'url' => 'Tsawo6rOa4s',
      'title' => 'Műlap készítése',
      'description' => 'Egy műlap létrehozása, kitöltése és minden ehhez kapcsolódó általános információ.',
    ],
  ],

  // Blogbarátaink
  'blog_friends' => [
    1 => [
      'id' => 1,
      'title' => 'Lásd Budapestet!',
      'url' => 'http://lasdbudapestet.blogspot.com/feeds/posts/default',
      'home' => 'http://lasdbudapestet.blogspot.com',
      'user_id' => 237,
      'active' => 1,
    ],
    2 => [
      'id' => 2,
      'title' => 'Csuhai.com',
      'url' => 'http://csuhai.com/feed/',
      'home' => 'http://csuhai.com/',
      'user_id' => 241,
      'active' => 1,
    ],
    3 => [
      'id' => 3,
      'title' => 'Urbanista',
      'url' => 'http://index.hu/urbanista/rss/default/',
      'home' => 'http://index.hu/urbanista/',
      'user_id' => 4310,
      'active' => 1,
    ],
  ],


  // Lakosság!! :) e főben
  // forrás: https://hu.wikipedia.org/wiki/Budapest_ker%C3%BCletei
  // ut. friss.: 2019.02.23.
  'districts' => [
    1 => ['I. kerület', 25],
    2 => ['II. kerület', 90],
    3 => ['III. kerület', 131],
    4 => ['IV. kerület', 101],
    5 => ['V. kerület', 26],
    6 => ['VI. kerület', 38],
    7 => ['VII. kerület', 53],
    8 => ['VIII. kerület', 76],
    9 => ['IX. kerület', 59],
    10 => ['X. kerület', 78],
    11 => ['XI. kerület', 146],
    12 => ['XII. kerület', 58],
    13 => ['XIII. kerület', 120],
    14 => ['XIV. kerület', 124],
    15 => ['XV. kerület', 80],
    16 => ['XVI. kerület', 74],
    17 => ['XVII. kerület', 88],
    18 => ['XVIII. kerület', 102],
    19 => ['XIX. kerület', 60],
    20 => ['XX. kerület', 66],
    21 => ['XXI. kerület', 76],
    22 => ['XXII. kerület', 55],
    23 => ['XXIII. kerület', 23],
    24 => ['Margitsziget', 0], // hjajj; hova tegyelek?
    /**
     * De azért nem vagyok olyan szomorú, hogy nem tudok megoldani ilyen
     * helyproblémákat. Gondoljunk csak az országonként, de még országrészenként
     * is eltérő logikájú Nominatimra. Asszem ez a foldrajzi egységesség
     * világszinten megugorhatatlan :) nyugodt legyek? Hogy mennek így majd az
     * önvezető autók normálisan? Hát mire építjük a logikákat, ha semmi
     * sem egységes. Mindmeghalunk.
     */
  ],

  // megnevezés és lakosság, ez izgi lesz ;)
  // lakosság: https://hu.wikipedia.org/wiki/Magyarorsz%C3%A1g_megy%C3%A9i
  // kimásolva: 2019.02.23.
  'counties' => [
    1 => ['Budapest', 1756],
    2 => ['Pest megye', 1234],
    3 => ['Győr-Moson-Sopron megye', 455],
    4 => ['Vas megye', 253],
    5 => ['Zala megye', 275],
    6 => ['Veszprém megye', 344],
    7 => ['Komárom-Esztergom megye', 297],
    8 => ['Fejér megye', 418],
    9 => ['Nógrád megye', 193],
    10 => ['Heves megye', 299],
    11 => ['Borsod-Abaúj-Zemplén megye', 660],
    12 => ['Jász-Nagykun-Szolnok megye', 376],
    13 => ['Szabolcs-Szatmár-Bereg megye', 563],
    14 => ['Hajdú-Bihar megye', 534],
    15 => ['Somogy megye', 309],
    16 => ['Tolna megye', 223],
    17 => ['Baranya megye', 363],
    18 => ['Bács-Kiskun megye', 511],
    19 => ['Csongrád-Csanád megye', 404],
    20 => ['Békés megye', 347],
  ],

  'countries' => [
    // [ angol, magyar, nominatim országkód, fop ]
    1 => ['Afghanistan', 'Afghanistan', 'af', 0],
    2 => ['Aland Islands', 'Aland Islands', 'ax', 1],
    3 => ['Albania', 'Albánia', 'al', 0],
    4 => ['Algeria', 'Algeria', 'dz', 1],
    5 => ['American Samoa', 'American Samoa', 'as', 1],
    6 => ['Andorra', 'Andorra', 'ad', 0],
    7 => ['Angola', 'Angola', 'ao', 1],
    8 => ['Anguilla', 'Anguilla', 'ai', 1],
    9 => ['Antarctica', 'Antarctica', 'aq', 1],
    10 => ['Antigua And Barbuda', 'Antigua And Barbuda', 'ag', 1],
    11 => ['Argentina', 'Argentina', 'ar', 1],
    12 => ['Armenia', 'Armenia', 'am', 1],
    13 => ['Aruba', 'Aruba', 'aw', 1],
    14 => ['Australia', 'Australia', 'au', 1],
    15 => ['Austria', 'Ausztria', 'at', 1],
    16 => ['Azerbaijan', 'Azerbaijan', 'az', 1],
    17 => ['Bahamas', 'Bahamas', 'bs', 1],
    18 => ['Bahrain', 'Bahrain', 'bh', 0],
    19 => ['Bangladesh', 'Bangladesh', 'bd', 1],
    20 => ['Barbados', 'Barbados', 'bb', 1],
    21 => ['Belarus', 'Belarus', 'by', 0],
    22 => ['Belgium', 'Belgium', 'be', 0],
    23 => ['Belize', 'Belize', 'bz', 1],
    24 => ['Benin', 'Benin', 'bj', 1],
    25 => ['Bermuda', 'Bermuda', 'bm', 1],
    26 => ['Bhutan', 'Bhutan', 'bt', 1],
    27 => ['Bolivia, Plurinational State Of', 'Bolivia, Plurinational State Of', 'bo', 1],
    28 => ['Bonaire, Sint Eustatius And Saba', 'Bonaire, Sint Eustatius And Saba', 'bq', 1],
    29 => ['Bosnia And Herzegovina', 'Bosznia-Hercegovina', 'ba', 0],
    30 => ['Botswana', 'Botswana', 'bw', 1],
    31 => ['Bouvet Island', 'Bouvet Island', 'bv', 1],
    32 => ['Brazil', 'Brazília', 'br', 1],
    33 => ['British Indian Ocean Territory', 'British Indian Ocean Territory', 'io', 1],
    34 => ['Brunei Darussalam', 'Brunei Darussalam', 'bn', 1],
    35 => ['Bulgaria', 'Bulgária', 'bg', 0],
    36 => ['Burkina Faso', 'Burkina Faso', 'bf', 0],
    37 => ['Burundi', 'Burundi', 'bi', 1],
    38 => ['Cambodia', 'Cambodia', 'kh', 0],
    39 => ['Cameroon', 'Cameroon', 'cm', 0],
    40 => ['Canada', 'Kanada', 'ca', 1],
    41 => ['Cape Verde', 'Cape Verde', 'cv', 1],
    42 => ['Cayman Islands', 'Cayman Islands', 'ky', 1],
    43 => ['Central African Republic', 'Central African Republic', 'cf', 1],
    44 => ['Chad', 'Chad', 'td', 1],
    45 => ['Chile', 'Chile', 'cl', 1],
    46 => ['China', 'Kína', 'cn', 1],
    47 => ['Christmas Island', 'Christmas Island', 'cx', 1],
    48 => ['Cocos (keeling) Islands', 'Cocos (keeling) Islands', 'cc', 1],
    49 => ['Colombia', 'Colombia', 'co', 1],
    50 => ['Comoros', 'Comoros', 'km', 1],
    51 => ['Congo', 'Congo', 'cg', 1],
    52 => ['Congo, The Democratic Republic Of The', 'Congo, The Democratic Republic Of The', 'cd', 0],
    53 => ['Cook Islands', 'Cook Islands', 'ck', 1],
    54 => ['Costa Rica', 'Costa Rica', 'cr', 0],
    55 => ['Cote D\'ivoire', 'Cote D\'ivoire', 'ci', 1],
    56 => ['Croatia', 'Horvátország', 'hr', 1],
    57 => ['Cuba', 'Cuba', 'cu', 1],
    58 => ['Curazao', 'Curazao', 'cw', 1],
    59 => ['Cyprus', 'Cyprus', 'cy', 1],
    60 => ['Czech Republic', 'Cseh Köztársaság', 'cz', 1],
    61 => ['Denmark', 'Dánia', 'dk', 1],
    62 => ['Djibouti', 'Djibouti', 'dj', 1],
    63 => ['Dominica', 'Dominica', 'dm', 1],
    64 => ['Dominican Republic', 'Dominican Republic', 'do', 1],
    65 => ['Ecuador', 'Ecuador', 'ec', 1],
    66 => ['Egypt', 'Egyiptom', 'eg', 1],
    67 => ['El Salvador', 'El Salvador', 'sv', 1],
    68 => ['Equatorial Guinea', 'Equatorial Guinea', 'gq', 1],
    69 => ['Eritrea', 'Eritrea', 'er', 1],
    70 => ['Estonia', 'Észtország', 'ee', 0],
    71 => ['Ethiopia', 'Ethiopia', 'et', 0],
    72 => ['Falkland Islands (malvinas)', 'Falkland Islands (malvinas)', 'fk', 1],
    73 => ['Faroe Islands', 'Faroe Islands', 'fo', 1],
    74 => ['Fiji', 'Fiji', 'fj', 1],
    75 => ['Finland', 'Finnország', 'fi', 1],
    76 => ['France', 'Franciaország', 'fr', 0],
    77 => ['French Guiana', 'French Guiana', 'gf', 1],
    78 => ['French Polynesia', 'French Polynesia', 'pf', 1],
    79 => ['French Southern Territories', 'French Southern Territories', 'tf', 1],
    80 => ['Gabon', 'Gabon', 'ga', 1],
    81 => ['Gambia', 'Gambia', 'gm', 0],
    82 => ['Georgia', 'Georgia', 'ge', 0],
    83 => ['Germany', 'Németország', 'de', 1],
    84 => ['Ghana', 'Ghana', 'gh', 1],
    85 => ['Gibraltar', 'Gibraltar', 'gi', 1],
    86 => ['Greece', 'Görögország', 'gr', 0],
    87 => ['Greenland', 'Greenland', 'gl', 1],
    88 => ['Grenada', 'Grenada', 'gd', 1],
    89 => ['Guadeloupe', 'Guadeloupe', 'gp', 1],
    90 => ['Guam', 'Guam', 'gu', 1],
    91 => ['Guatemala', 'Guatemala', 'gt', 1],
    92 => ['Guernsey', 'Guernsey', 'gg', 1],
    93 => ['Guinea', 'Guinea', 'gn', 1],
    94 => ['Guinea-bissau', 'Guinea-Bissau', 'gw', 1],
    95 => ['Guyana', 'Guyana', 'gy', 1],
    96 => ['Haiti', 'Haiti', 'ht', 1],
    97 => ['Heard Island And Mcdonald Islands', 'Heard Island And Mcdonald Islands', 'hm', 1],
    98 => ['Holy See (vatican City State)', 'Holy See (vatican City State)', 'va', 0],
    99 => ['Honduras', 'Honduras', 'hn', 1],
    100 => ['Hong Kong', 'Hong Kong', 'hk', 1],
    101 => ['Hungary', 'Magyarország', 'hu', 1],
    102 => ['Iceland', 'Izland', 'is', 0],
    103 => ['India', 'India', 'in', 1],
    104 => ['Indonesia', 'Indonézia', 'id', 0],
    105 => ['Iran, Islamic Republic Of', 'Irán', 'ir', 0],
    106 => ['Iraq', 'Irak', 'iq', 0],
    107 => ['Ireland', 'Írország', 'ie', 1],
    108 => ['Isle Of Man', 'Isle Of Man', 'im', 1],
    109 => ['Israel', 'Izrael', 'il', 1],
    110 => ['Italy', 'Olaszország', 'it', 0],
    111 => ['Jamaica', 'Jamaica', 'jm', 1],
    112 => ['Japan', 'Japán', 'jp', 1],
    113 => ['Jersey', 'Jersey', 'je', 1],
    114 => ['Jordan', 'Jordan', 'jo', 0],
    115 => ['Kazakhstan', 'Kazakhstan', 'kz', 0],
    116 => ['Kenya', 'Kenya', 'ke', 1],
    117 => ['Kiribati', 'Kiribati', 'ki', 1],
    118 => ['Korea, Democratic People\'s Republic Of', 'Korea, Democratic People\'s Republic Of', 'kp', 1],
    119 => ['Korea, Republic Of', 'Korea, Republic Of', 'kr', 0],
    120 => ['Kuwait', 'Kuwait', 'kw', 1],
    121 => ['Kyrgyzstan', 'Kyrgyzstan', 'kg', 0],
    122 => ['Lao People\'s Democratic Republic', 'Lao People\'s Democratic Republic', 'la', 0],
    123 => ['Latvia', 'Latvia', 'lv', 0],
    124 => ['Lebanon', 'Lebanon', 'lb', 0],
    125 => ['Lesotho', 'Lesotho', 'ls', 1],
    126 => ['Liberia', 'Liberia', 'lr', 1],
    127 => ['Libya', 'Libya', 'ly', 0],
    128 => ['Liechtenstein', 'Liechtenstein', 'li', 1],
    129 => ['Lithuania', 'Litvánia', 'lt', 0],
    130 => ['Luxembourg', 'Luxemburg', 'lu', 0],
    131 => ['Macao', 'Macao', 'mo', 1],
    132 => ['North Macedonia, The Former Yugoslav Republic Of', 'Észak-Macedónia', 'mk', 1],
    133 => ['Madagascar', 'Madagascar', 'mg', 0],
    134 => ['Malawi', 'Malawi', 'mw', 1],
    135 => ['Malaysia', 'Malaysia', 'my', 1],
    136 => ['Maldives', 'Maldives', 'mv', 1],
    137 => ['Mali', 'Mali', 'ml', 0],
    138 => ['Malta', 'Málta', 'mt', 1],
    139 => ['Marshall Islands', 'Marshall Islands', 'mh', 1],
    140 => ['Martinique', 'Martinique', 'mq', 1],
    141 => ['Mauritania', 'Mauritania', 'mr', 1],
    142 => ['Mauritius', 'Mauritius', 'mu', 1],
    143 => ['Mayotte', 'Mayotte', 'yt', 1],
    144 => ['Mexico', 'Mexico', 'mx', 1],
    145 => ['Micronesia, Federated States Of', 'Micronesia, Federated States Of', 'fm', 1],
    146 => ['Moldova, Republic Of', 'Moldova', 'md', 1],
    147 => ['Monaco', 'Monaco', 'mc', 1],
    148 => ['Mongolia', 'Mongolia', 'mn', 0],
    149 => ['Montenegro', 'Montenegro', 'me', 0],
    150 => ['Montserrat', 'Montserrat', 'ms', 1],
    151 => ['Morocco', 'Marokkó', 'ma', 0],
    152 => ['Mozambique', 'Mozambique', 'mz', 0],
    153 => ['Myanmar', 'Myanmar', 'mm', 1],
    154 => ['Namibia', 'Namibia', 'na', 0],
    155 => ['Nauru', 'Nauru', 'nr', 1],
    156 => ['Nepal', 'Nepal', 'np', 0],
    157 => ['Netherlands', 'Hollandia', 'nl', 1],
    158 => ['New Caledonia', 'New Caledonia', 'nc', 1],
    159 => ['New Zealand', 'Új-Zéland', 'nz', 1],
    160 => ['Nicaragua', 'Nicaragua', 'ni', 1],
    161 => ['Niger', 'Niger', 'ne', 1],
    162 => ['Nigeria', 'Nigeria', 'ng', 1],
    163 => ['Niue', 'Niue', 'nu', 1],
    164 => ['Norfolk Island', 'Norfolk Island', 'nf', 1],
    165 => ['Northern Mariana Islands', 'Northern Mariana Islands', 'mp', 1],
    166 => ['Norway', 'Norvégia', 'no', 1],
    167 => ['Oman', 'Oman', 'om', 0],
    168 => ['Pakistan', 'Pakisztán', 'pk', 1],
    169 => ['Palau', 'Palau', 'pw', 1],
    170 => ['Palestinian Territory, Occupied', 'Palestinian Territory, Occupied', 'ps', 1],
    171 => ['Panama', 'Panama', 'pa', 1],
    172 => ['Papua New Guinea', 'Papua New Guinea', 'pg', 1],
    173 => ['Paraguay', 'Paraguay', 'py', 1],
    174 => ['Peru', 'Peru', 'pe', 1],
    175 => ['Philippines', 'Philippines', 'ph', 0],
    176 => ['Pitcairn', 'Pitcairn', 'pn', 1],
    177 => ['Poland', 'Lengyelország', 'pl', 1],
    178 => ['Portugal', 'Portugália', 'pt', 1],
    179 => ['Puerto Rico', 'Puerto Rico', 'pr', 1],
    180 => ['Qatar', 'Qatar', 'qa', 0],
    181 => ['Reunion', 'Reunion', 're', 1],
    182 => ['Romania', 'Románia', 'ro', 0],
    183 => ['Russian Federation', 'Oroszország', 'ru', 1],
    184 => ['Rwanda', 'Rwanda', 'rw', 1],
    185 => ['Saint Barthelemy', 'Saint Barthelemy', 'bl', 1],
    186 => ['Saint Helena, Ascension And Tristan Da Cunha', 'Saint Helena, Ascension And Tristan Da Cunha', 'sh', 1],
    187 => ['Saint Kitts And Nevis', 'Saint Kitts And Nevis', 'kn', 1],
    188 => ['Saint Lucia', 'Saint Lucia', 'lc', 1],
    189 => ['Saint Martin (french Part)', 'Saint Martin (french Part)', 'mf', 1],
    190 => ['Saint Pierre And Miquelon', 'Saint Pierre And Miquelon', 'pm', 1],
    191 => ['Saint Vincent And The Grenadines', 'Saint Vincent And The Grenadines', 'vc', 1],
    192 => ['Samoa', 'Samoa', 'ws', 1],
    193 => ['San Marino', 'San Marino', 'sm', 1],
    194 => ['Sao Tome And Principe', 'Sao Tome And Principe', 'st', 1],
    195 => ['Saudi Arabia', 'Saudi Arabia', 'sa', 0],
    196 => ['Senegal', 'Senegal', 'sn', 0],
    197 => ['Serbia', 'Szerbia', 'rs', 1],
    198 => ['Seychelles', 'Seychelles', 'sc', 1],
    199 => ['Sierra Leone', 'Sierra Leone', 'sl', 1],
    200 => ['Singapore', 'Szingapúr', 'sg', 1],
    201 => ['Sint Maarten (dutch Part)', 'Sint Maarten (dutch Part)', 'sx', 1],
    202 => ['Slovakia', 'Szlovákia', 'sk', 1],
    203 => ['Slovenia', 'Szlovénia', 'si', 0],
    204 => ['Solomon Islands', 'Solomon Islands', 'sb', 1],
    205 => ['Somalia', 'Somalia', 'so', 1],
    206 => ['South Africa', 'South Africa', 'za', 0],
    207 => ['South Georgia And The South Sandwich Islands', 'South Georgia And The South Sandwich Islands', 'gs', 1],
    208 => ['South Sudan', 'South Sudan', 'ss', 1],
    209 => ['Spain', 'Spanyolország', 'es', 1],
    210 => ['Sri Lanka', 'Sri Lanka', 'lk', 0],
    211 => ['Sudan', 'Sudan', 'sd', 0],
    212 => ['Suriname', 'Suriname', 'sr', 1],
    213 => ['Svalbard And Jan Mayen', 'Svalbard And Jan Mayen', 'sj', 1],
    214 => ['Swaziland', 'Swaziland', 'sz', 1],
    215 => ['Sweden', 'Svédország', 'se', 1],
    216 => ['Switzerland', 'Svájc', 'ch', 1],
    217 => ['Syrian Arab Republic', 'Syrian Arab Republic', 'sy', 1],
    218 => ['Taiwan, Province Of China', 'Taiwan, Province Of China', 'tw', 1],
    219 => ['Tajikistan', 'Tajikistan', 'tj', 0],
    220 => ['Tanzania, United Republic Of', 'Tanzania, United Republic Of', 'tz', 1],
    221 => ['Thailand', 'Thaiföld', 'th', 1],
    222 => ['Timor-leste', 'Timor-leste', 'tl', 1],
    223 => ['Togo', 'Togo', 'tg', 1],
    224 => ['Tokelau', 'Tokelau', 'tk', 1],
    225 => ['Tonga', 'Tonga', 'to', 1],
    226 => ['Trinidad And Tobago', 'Trinidad And Tobago', 'tt', 1],
    227 => ['Tunisia', 'Tunézia', 'tn', 1],
    228 => ['Turkey', 'Törökország', 'tr', 1],
    229 => ['Turkmenistan', 'Turkmenistan', 'tm', 0],
    230 => ['Turks And Caicos Islands', 'Turks And Caicos Islands', 'tc', 1],
    231 => ['Tuvalu', 'Tuvalu', 'tv', 1],
    232 => ['Uganda', 'Uganda', 'ug', 1],
    233 => ['Ukraine', 'Ukrajna', 'ua', 0],
    234 => ['United Arab Emirates', 'Egyesült Arab Emirátusok', 'ae', 0],
    235 => ['United Kingdom', 'Egyesült Királyság', 'gb', 1],
    236 => ['United States', 'Amerikai Egyesült Államok', 'us', 1],
    237 => ['United States Minor Outlying Islands', 'United States Minor Outlying Islands', 'um', 1],
    238 => ['Uruguay', 'Uruguay', 'uy', 1],
    239 => ['Uzbekistan', 'Üzbegisztán', 'uz', 0],
    240 => ['Vanuatu', 'Vanuatu', 'vu', 1],
    241 => ['Venezuela, Bolivarian Republic Of', 'Venezuela, Bolivarian Republic Of', 've', 1],
    242 => ['Viet Nam', 'Viet Nam', 'vn', 1],
    243 => ['Virgin Islands, British', 'Virgin Islands, British', 'vg', 1],
    244 => ['Virgin Islands, U.S.', 'Virgin Islands, U.S.', 'vi', 1],
    245 => ['Wallis And Futuna', 'Wallis And Futuna', 'wf', 1],
    246 => ['Western Sahara', 'Western Sahara', 'eh', 1],
    247 => ['Yemen', 'Yemen', 'ye', 1],
    248 => ['Zambia', 'Zambia', 'zm', 0],
    249 => ['Zimbabwe', 'Zimbabwe', 'zw', 1],
    250 => ['Vatican', 'Vatikán', 'va', 1],
  ],


  // HE paraméterek (név, db field)
  'ww_parameter_types' => [
    1 => ['Helyek', 'place_id'],
    2 => ['Országok', 'country_id'],
    3 => ['Megyék', 'county_id'],
    4 => ['Források', 'sources'],
    5 => ['Kerületek', 'district_id'],
    6 => ['Emlékmű típus', 'type_id'],
    7 => ['Kapcsolódó emlékművek', 'connected_monuments'],
    8 => ['Témák', 'topics'],
    9 => ['Kapcsolódó hadseregek', 'connected_corps'],
    10 => ['Alkotók', 'artists,second_artists,creator_artists'],
    11 => ['Alapítók', 'founders'],
    12 => ['Nemzetségek', 'nationalities'],
    13 => ['Jelképek', 'symbols'],
    14 => ['Kapcsolódó épületek', 'connected_buildings'],
    15 => ['Kapcsolódó események', 'connected_events'],
    16 => ['Állapotváltozások', 'states'],
    17 => ['Fenntartók', 'maintainers'],
    18 => ['Katonai egységek', 'corps'],
    19 => ['Avatók', 'unveilers'],
  ],


  // Botocskák; lista innen: https://github.com/JayBizzle/Crawler-Detect
  'bots' => [
    'KTBot', // mink vagyunk, amikor kesselünk!
    '.*Java.*outbrain', ' YLT', '^b0t$', '^bluefish ', '^Calypso v\/', '^COMODO DCV', '^DangDang', '^DavClnt', '^FDM ', '^git\/', '^Goose\/', '^Grabber', '^HTTPClient\/', '^Java\/', '^Jeode\/', '^Jetty\/', '^Mail\/', '^Mget', '^Microsoft URL Control', '^NG\/[0-9\.]', '^NING\/', '^PHP\/[0-9]', '^RMA\/', '^Ruby|Ruby\/[0-9]', '^VSE\/[0-9]', '^WordPress\.com', '^XRL\/[0-9]', '^ZmEu', '008\/', '13TABS', '192\.comAgent', '2ip\.ru', '404enemy', '7Siters', '80legs', 'a\.pr-cy\.ru', 'a3logics\.in', 'A6-Indexer', 'Abonti', 'Aboundex', 'aboutthedomain', 'Accoona-AI-Agent', 'acebookexternalhit\/', 'acoon', 'acrylicapps\.com\/pulp', 'Acunetix', 'AdAuth\/', 'adbeat', 'AddThis', 'ADmantX', 'AdminLabs', 'adressendeutschland', 'adreview\/', 'adscanner', 'Adstxtaggregator', 'adstxt-worker', 'adstxt\.com', 'agentslug', 'AHC', 'aihit', 'aiohttp\/', 'Airmail', 'akka-http\/', 'akula\/', 'alertra', 'alexa site audit', 'Alibaba\.Security\.Heimdall', 'Alligator', 'allloadin', 'AllSubmitter', 'alyze\.info', 'amagit', '^Amazon Simple Notification Service Agent$', 'Anarchie', 'AndroidDownloadManager', 'Anemone', 'AngleSharp', 'annotate_google', 'Ant\.com', 'Anturis Agent', 'AnyEvent-HTTP\/', 'Apache Droid', 'Apache OpenOffice', 'Apache-HttpAsyncClient', 'Apache-HttpClient', 'ApacheBench', 'Apexoo', 'APIs-Google', 'AportWorm\/', 'AppBeat\/', 'AppEngine-Google', 'AppleSyndication', 'Aprc\/[0-9]', 'Arachmo', 'arachnode', 'Arachnophilia', 'aria2', 'Arukereso', 'asafaweb', 'AskQuickly', 'Ask Jeeves', 'ASPSeek', 'Asterias', 'Astute', 'asynchttp', 'Attach', 'attohttpc', 'autocite', 'AutomaticWPTester', 'Autonomy', 'axios\/', 'AWS Security Scanner', 'B-l-i-t-z-B-O-T', 'Backlink-Ceck', 'backlink-check', 'BacklinkHttpStatus', 'BackStreet', 'BackupLand', 'BackWeb', 'Bad-Neighborhood', 'Badass', 'baidu\.com', 'Bandit', 'basicstate', 'BatchFTP', 'Battleztar Bazinga', 'baypup\/', 'BazQux', 'BBBike', 'BCKLINKS', 'BDFetch', 'BegunAdvertising', 'Bewica-security-scan', 'Bidtellect', 'BigBozz', 'Bigfoot', 'biglotron', 'BingLocalSearch', 'BingPreview', 'binlar', 'biNu image cacher', 'Bitacle', 'biz_Directory', 'Black Hole', 'Blackboard Safeassign', 'BlackWidow', 'BlockNote\.Net', 'BlogBridge', 'Bloglines', 'Bloglovin', 'BlogPulseLive', 'BlogSearch', 'Blogtrottr', 'BlowFish', 'boitho\.com-dc', 'Boost\.Beast', 'BPImageWalker', 'Braintree-Webhooks', 'Branch Metrics API', 'Branch-Passthrough', 'Brandprotect', 'BrandVerity', 'Brandwatch', 'Brodie\/', 'Browsershots', 'BUbiNG', 'Buck\/', 'Buddy', 'BuiltWith', 'Bullseye', 'BunnySlippers', 'Burf Search', 'Butterfly\/', 'BuzzSumo', 'CAAM\/[0-9]', 'CakePHP', 'Calculon', 'Canary%20Mail', 'CaretNail', 'catexplorador', 'CC Metadata Scaper', 'Cegbfeieh', 'censys', 'centuryb.o.t9[at]gmail.com', 'Cerberian Drtrs', 'CERT\.at-Statistics-Survey', 'cg-eye', 'changedetection', 'ChangesMeter', 'Charlotte', 'CheckHost', 'checkprivacy', 'CherryPicker', 'ChinaClaw', 'Chirp\/', 'chkme\.com', 'Chlooe', 'Chromaxa', 'CirrusExplorer', 'CISPA Vulnerability Notification', 'Citoid', 'CJNetworkQuality', 'Clarsentia', 'clips\.ua\.ac\.be', 'Cloud mapping', 'CloudEndure', 'CloudFlare-AlwaysOnline', 'Cloudflare-Healthchecks', 'Cloudinary', 'cmcm\.com', 'coccoc', 'cognitiveseo', 'colly -', 'CommaFeed', 'Commons-HttpClient', 'commonscan', 'contactbigdatafr', 'contentkingapp', 'convera', 'CookieReports', 'copyright sheriff', 'CopyRightCheck', 'Copyscape', 'cortex\/', 'Cosmos4j\.feedback', 'Covario-IDS', 'Craw\/', 'Crescent', 'Crowsnest', 'Criteo', 'CSHttp', 'CSSCheck', 'Cula', 'curb', 'Curious George', 'curl', 'cuwhois\/', 'cybo\.com', 'DAP\/NetHTTP', 'DareBoost', 'DatabaseDriverMysqli', 'DataCha0s', 'Datafeedwatch', 'Datanyze', 'DataparkSearch', 'dataprovider', 'DataXu', 'Daum(oa)?[ \/][0-9]', 'dBpoweramp', 'ddline', 'deeris', 'delve\.ai', 'Demon', 'DeuSu', 'developers\.google\.com\/\+\/web\/snippet\/', 'Devil', 'DHSH', 'Digg', 'Digincore', 'DigitalPebble', 'Dirbuster', 'Discourse Forum Onebox', 'Disqus\/', 'Dispatch\/', 'DittoSpyder', 'dlvr', 'DMBrowser', 'DNSPod-reporting', 'docoloc', 'Dolphin http client', 'DomainAppender', 'DomainLabz', 'Donuts Content Explorer', 'dotMailer content retrieval', 'dotSemantic', 'downforeveryoneorjustme', 'Download Wonder', 'downnotifier', 'DowntimeDetector', 'Drip', 'drupact', 'Drupal \(\+http:\/\/drupal\.org\/\)', 'DTS Agent', 'dubaiindex', 'DuplexWeb-Google', 'DynatraceSynthetic', 'EARTHCOM', 'Easy-Thumb', 'EasyDL', 'Ebingbong', 'ec2linkfinder', 'eCairn-Grabber', 'eCatch', 'ECCP', 'eContext\/', 'Ecxi', 'EirGrabber', 'ElectricMonk', 'elefent', 'EMail Exractor', 'EMail Wolf', 'EmailWolf', 'Embarcadero', 'Embed PHP Library', 'Embedly', 'endo\/', 'europarchive\.org', 'evc-batch', 'EventMachine HttpClient', 'Everwall Link Expander', 'Evidon', 'Evrinid', 'ExactSearch', 'ExaleadCloudview', 'Excel\/', 'exif', 'ExoRank', 'Exploratodo', 'Express WebPictures', 'Extreme Picture Finder', 'EyeNetIE', 'ezooms', 'facebookexternalhit', 'facebookexternalua', 'facebookplatform', 'fairshare', 'Faraday v', 'fasthttp', 'Faveeo', 'Favicon downloader', 'faviconkit', 'faviconarchive', 'FavOrg', 'Feed Wrangler', 'Feedable\/', 'Feedbin', 'FeedBooster', 'FeedBucket', 'FeedBunch\/', 'FeedBurner', 'feeder', 'Feedly', 'FeedshowOnline', 'Feedspot', 'FeedViewer\/', 'Feedwind\/', 'FeedZcollector', 'feeltiptop', 'Fetch API', 'Fetch\/[0-9]', 'Fever\/[0-9]', 'FHscan', 'Filestack', 'Fimap', 'findlink', 'findthatfile', 'FlashGet', 'FlipboardBrowserProxy', 'FlipboardProxy', 'FlipboardRSS', 'Flock\/', 'fluffy', 'Flunky', 'flynxapp', 'forensiq', 'FoundSeoTool', 'http:\/\/www.neomo.de\/', 'free thumbnails', 'Freeuploader', 'Funnelback', 'Fuzz Faster U Fool', 'G-i-g-a-b-o-t', 'g00g1e\.net', 'ganarvisitas', 'geek-tools', 'Genieo', 'GentleSource', 'GetCode', 'Getintent', 'GetLinkInfo', 'getprismatic', 'GetRight', 'getroot', 'GetURLInfo\/', 'GetWeb', 'Geziyor', 'Ghost Inspector', 'GigablastOpenSource', 'GIS-LABS', 'github-camo', 'github\.com', 'Goldfire Server', 'Go [\d\.]* package http', 'Go http package', 'Go-Ahead-Got-It', 'Go-http-client', 'Go!Zilla', 'gobyus', 'gofetch', 'GomezAgent', 'gooblog', 'Goodzer\/', 'Google AppsViewer', 'Google Desktop', 'Google favicon', 'Google Keyword Suggestion', 'Google Keyword Tool', 'Google Page Speed Insights', 'Google PP Default', 'Google Search Console', 'Google Web Preview', 'Google-Ads-Overview', 'Google-Adwords', 'Google-Apps-Script', 'Google-Calendar-Importer', 'Google-HotelAdsVerifier', 'Google-HTTP-Java-Client', 'Google-Publisher-Plugin', 'Google-Read-Aloud', 'Google-SearchByImage', 'Google-Site-Verification', 'Google-speakr', 'Google-Structured-Data-Testing-Tool', 'Google-Youtube-Links', 'google-xrawler', 'GoogleDocs', 'GoogleHC\/', 'GoogleProducer', 'GoogleSites', 'Google-Transparency-Report', 'Gookey', 'GoSpotCheck', 'gosquared-thumbnailer', 'Gotit', 'GoZilla', 'grabify', 'GrabNet', 'Grafula', 'Grammarly', 'GrapeFX', 'GreatNews', 'Gregarius', 'GRequests', 'grokkit', 'grouphigh', 'grub-client', 'gSOAP\/', 'GT::WWW', 'GTmetrix', 'GuzzleHttp', 'gvfs\/', 'HAA(A)?RTLAND http client', 'Haansoft', 'hackney\/', 'Hadi Agent', 'HappyApps-WebCheck', 'Hatena', 'Havij', 'HaxerMen', 'HeadlessChrome', 'HEADMasterSEO', 'HeartRails_Capture', 'help@dataminr\.com', 'heritrix', 'Hexometer', 'historious', 'hkedcity', 'hledejLevne\.cz', 'Hloader', 'HMView', 'Holmes', 'HonesoSearchEngine', 'HootSuite Image proxy', 'Hootsuite-WebFeed', 'hosterstats', 'HostTracker', 'ht:\/\/check', 'htdig', 'HTMLparser', 'htmlyse', 'HTTP Banner Detection', 'HTTP_Compression_Test', 'http_request2', 'http_requester', 'http-get', 'HTTP-Header-Abfrage', 'http-kit', 'http-request\/', 'HTTP-Tiny', 'HTTP::Lite', 'http\.rb\/', 'http_get', 'HttpComponents', 'httphr', 'HTTPMon', 'HTTPie', 'httpRequest', 'httpscheck', 'httpssites_power', 'httpunit', 'HttpUrlConnection', 'httrack', 'huaweisymantec', 'HubSpot ', 'Humanlinks', 'i2kconnect\/', 'Iblog', 'ichiro', 'Id-search', 'IdeelaborPlagiaat', 'IDG Twitter Links Resolver', 'IDwhois\/', 'Iframely', 'igdeSpyder', 'iGooglePortal', 'IlTrovatore', 'Image Fetch', 'Image Sucker', 'ImageEngine\/', 'ImageVisu\/', 'Imagga', 'imagineeasy', 'imgsizer', 'InAGist', 'inbound\.li parser', 'InDesign%20CC', 'Indy Library', 'InetURL', 'infegy', 'infohelfer', 'InfoTekies', 'InfoWizards Reciprocal Link', 'inpwrd\.com', 'instabid', 'Instapaper', 'Integrity', 'integromedb', 'Intelliseek', 'InterGET', 'internet_archive', 'Internet Ninja', 'InternetSeer', 'internetVista monitor', 'internetwache', 'intraVnews', 'IODC', 'IOI', 'iplabel', 'ips-agent', 'IPS\/[0-9]', 'IPWorks HTTP\/S Component', 'iqdb\/', 'Iria', 'Irokez', 'isitup\.org', 'iskanie', 'isUp\.li', 'iThemes Sync\/', 'IZaBEE', 'iZSearch', 'JAHHO', 'janforman', 'Jaunt\/', 'Jbrofuzz', 'Jersey\/', 'JetCar', 'Jigsaw', 'Jobboerse', 'JobFeed discovery', 'Jobg8 URL Monitor', 'jobo', 'Jobrapido', 'Jobsearch1\.5', 'JoinVision Generic', 'JolokiaPwn', 'Joomla', 'Jorgee', 'JS-Kit', 'JustView', 'Kaspersky Lab CFR link resolver', 'Kelny\/', 'Kerrigan\/', 'KeyCDN', 'Keyword Density', 'Keywords Research', 'khttp\/', 'KickFire', 'KimonoLabs\/', 'Kml-Google', 'knows\.is', 'KOCMOHABT', 'kouio', 'kubectl', 'kube-probe', 'kulturarw3', 'KumKie', 'L\.webis', 'Larbin', 'Lavf\/', 'LeechFTP', 'LeechGet', 'letsencrypt', 'Lftp', 'LibVLC', 'LibWeb', 'Libwhisker', 'libwww', 'Licorne', 'Liferea\/', 'Lightspeedsystems', 'Lighthouse', 'Likse', 'limber\.io', 'Link Valet', 'link_thumbnailer', 'LinkAlarm\/', 'linkCheck', 'linkdex', 'LinkExaminer', 'linkfluence', 'linkpeek', 'LinkPreviewGenerator', 'LinkScan', 'LinksManager', 'LinkTiger', 'LinkWalker', 'Lipperhey', 'Litemage_walker', 'livedoor ScreenShot', 'LoadImpactRload', 'localsearch-web', 'LongURL API', 'longurl-r-package', 'looid\.com', 'looksystems\.net', 'ltx71', 'lua-resty-http', 'lwp-request', 'lwp-trivial', 'LWP::Simple', 'lycos', 'LYT\.SR', 'mabontland', 'Mag-Net', 'MagpieRSS', 'Mail\.Ru', 'MailChimp', 'Majestic12', 'makecontact\/', 'Mandrill', 'MapperCmd', 'marketinggrader', 'MarkMonitor', 'MarkWatch', 'Mass Downloader', 'masscan\/', 'Mata Hari', 'Mediametric', 'Mediapartners-Google', 'mediawords', 'MegaIndex\.ru', 'MeltwaterNews', 'Melvil Rawi', 'MemGator', 'Metaspinner', 'MetaURI', 'MFC_Tear_Sample', 'MicroMessenger\/', 'Microsearch', 'Microsoft Office ', 'Microsoft Outlook', 'Microsoft Windows Network Diagnostics', 'Microsoft-WebDAV-MiniRedir', 'Microsoft Data Access', 'MIDown tool', 'MIIxpc', 'Mindjet', 'Miniature\.io', 'Miniflux', 'Mister PiX', 'mixdata dot com', 'mixed-content-scan', 'Mixmax-LinkPreview', 'mixnode', 'Mnogosearch', 'mogimogi', 'Mojeek', 'Mojolicious \(Perl\)', 'Monit\/', 'monitis', 'Monitority\/', 'montastic', 'MonTools', 'Moreover', 'Morfeus Fucking Scanner', 'Morning Paper', 'MovableType', 'mowser', 'Mr\.4x3 Powered', 'Mrcgiguy', 'MS Web Services Client Protocol', 'MSFrontPage', 'mShots', 'MuckRack\/', 'muhstik-scan', 'MVAClient', 'MxToolbox\/', 'nagios', 'Najdi\.si', 'Name Intelligence', 'Nameprotect', 'Navroad', 'NearSite', 'Needle', 'Nessus', 'Net Vampire', 'NetAnts', 'NETCRAFT', 'NetLyzer', 'NetMechanic', 'NetNewsWire', 'Netpursual', 'netresearch', 'NetShelter ContentScan', 'Netsparker', 'NetTrack', 'Netvibes', 'NetZIP', 'Neustar WPM', 'NeutrinoAPI', 'NewRelicPinger', 'NewsBlur .*Finder', 'NewsGator', 'newsme', 'newspaper\/', 'NetSystemsResearch', 'Nexgate Ruby Client', 'NG-Search', 'Nibbler', 'NICErsPRO', 'Nikto', 'nineconnections', 'NLNZ_IAHarvester', 'Nmap Scripting Engine', 'node-superagent', 'node-urllib', 'node\.io', 'Nodemeter', 'NodePing', 'nominet\.org\.uk', 'nominet\.uk', 'Norton-Safeweb', 'Notifixious', 'notifyninja', 'NotionEmbedder', 'nuhk', 'nutch', 'Nuzzel', 'nWormFeedFinder', 'nyawc\/', 'Nymesis', 'NYU', 'Ocelli\/', 'Octopus', 'oegp', 'Offline Explorer', 'Offline Navigator', 'OgScrper', 'okhttp', 'omgili', 'OMSC', 'Online Domain Tools', 'OpenCalaisSemanticProxy', 'Openfind', 'OpenLinkProfiler', 'Openstat\/', 'OpenVAS', 'OPPO A33', 'Optimizer', 'Orbiter', 'OrgProbe\/', 'orion-semantics', 'Outlook-Express', 'Outlook-iOS', 'ow\.ly', 'Owler', 'Owlin', 'ownCloud News', 'OxfordCloudService', 'Page Valet', 'page_verifier', 'page scorer', 'page2rss', 'PageFreezer', 'PageGrabber', 'PagePeeker', 'PageScorer', 'Pagespeed\/', 'Panopta', 'panscient', 'Papa Foto', 'parsijoo', 'Pavuk', 'PayPal IPN', 'pcBrowser', 'Pcore-HTTP', 'Pearltrees', 'PECL::HTTP', 'peerindex', 'Peew', 'PeoplePal', 'Perlu -', 'PhantomJS Screenshoter', 'PhantomJS\/', 'Photon\/', 'phpservermon', 'Pi-Monster', 'Picscout', 'Picsearch', 'PictureFinder', 'Pimonster', 'ping\.blo\.gs', 'Pingability', 'PingAdmin\.Ru', 'Pingdom', 'Pingoscope', 'PingSpot', 'pinterest\.com', 'Pixray', 'Pizilla', 'Plagger\/', 'Ploetz \+ Zeller', 'Plukkie', 'plumanalytics', 'PocketImageCache', 'PocketParser', 'Pockey', 'POE-Component-Client-HTTP', 'Polymail\/', 'Pompos', 'Porkbun', 'Port Monitor', 'postano', 'PostmanRuntime', 'PostPost', 'postrank', 'PowerPoint\/', 'Prebid', 'Priceonomics Analysis Engine', 'PrintFriendly', 'PritTorrent', 'Prlog', 'probethenet', 'Project 25499', 'prospectb2b', 'Protopage', 'ProWebWalker', 'proximic', 'PRTG Network Monitor', 'pshtt, https scanning', 'PTST ', 'PTST\/[0-9]+', 'Pump', 'python-httpx', 'Python-httplib2', 'python-requests', 'Python-urllib', 'Qirina Hurdler', 'QQDownload', 'QrafterPro', 'Qseero', 'Qualidator', 'QueryN Metasearch', 'queuedriver', 'Quora Link Preview', 'Qwantify', 'Radian6', 'RankActive', 'RankFlex', 'RankSonicSiteAuditor', 'Re-re Studio', 'ReactorNetty', 'Readability', 'RealDownload', 'RealPlayer%20Downloader', 'RebelMouse', 'Recorder', 'RecurPost\/', 'redback\/', 'ReederForMac', 'Reeder\/', 'ReGet', 'RepoMonkey', 'request\.js', 'reqwest\/', 'ResponseCodeTest', 'RestSharp', 'Riddler', 'Rival IQ', 'Robosourcer', 'Robozilla', 'ROI Hunter', 'RPT-HTTPClient', 'RSSOwl', 'RyowlEngine', 'safe-agent-scanner', 'SalesIntelligent', 'Saleslift', 'Sendsay\.Ru', 'SauceNAO', 'SBIder', 'sc-downloader', 'scalaj-http', 'Scamadviser-Frontend', 'scan\.lol', 'ScanAlert', 'Scoop', 'scooter', 'ScoutJet', 'ScoutURLMonitor', 'ScrapeBox Page Scanner', 'Scrapy', 'Screaming', 'ScreenShotService', 'Scrubby', 'Scrutiny\/', 'search\.thunderstone', 'Search37', 'searchenginepromotionhelp', 'Searchestate', 'SearchExpress', 'SearchSight', 'Seeker', 'semanticdiscovery', 'semanticjuice', 'Semiocast HTTP client', 'Semrush', 'sentry\/', 'SEO Browser', 'Seo Servis', 'seo-nastroj\.cz', 'seo4ajax', 'Seobility', 'SEOCentro', 'SeoCheck', 'SEOkicks', 'SEOlizer', 'Seomoz', 'SEOprofiler', 'SEOsearch', 'seoscanners', 'seositecheckup', 'SEOstats', 'servernfo', 'sexsearcher', 'Seznam', 'Shelob', 'Shodan', 'Shoppimon', 'ShopWiki', 'shortURL lengthener', 'ShortLinkTranslate', 'shrinktheweb', 'Sideqik', 'Siege', 'SimplePie', 'SimplyFast', 'Siphon', 'SISTRIX', 'Site-Shot\/', 'Site Sucker', 'Site24x7', 'SiteBar', 'Sitebeam', 'Sitebulb\/', 'SiteCondor', 'SiteExplorer', 'SiteGuardian', 'Siteimprove', 'SiteIndexed', 'Sitemap(s)? Generator', 'SitemapGenerator', 'SiteMonitor', 'Siteshooter B0t', 'SiteSnagger', 'SiteSucker', 'SiteTruth', 'Sitevigil', 'sitexy\.com', 'SkypeUriPreview', 'Slack\/', 'slider\.com', 'slurp', 'SlySearch', 'SmartDownload', 'SMRF URL Expander', 'SMUrlExpander', 'Snake', 'Snappy', 'SnapSearch', 'Snarfer\/', 'SniffRSS', 'sniptracker', 'Snoopy', 'SnowHaze Search', 'sogou web', 'SortSite', 'Sottopop', 'sovereign\.ai', 'SpaceBison', 'SpamExperts', 'Spammen', 'Spanner', 'spaziodati', 'SPDYCheck', 'Specificfeeds', 'speedy', 'SPEng', 'Spinn3r', 'spray-can', 'Sprinklr ', 'spyonweb', 'sqlmap', 'Sqlworm', 'Sqworm', 'SSL Labs', 'ssl-tools', 'StackRambler', 'Statastico\/', 'StatusCake', 'Steeler', 'Stratagems Kumo', 'Stroke\.cz', 'StudioFACA', 'StumbleUpon', 'suchen', 'Sucuri', 'summify', 'SuperHTTP', 'Surphace Scout', 'Suzuran', 'Symfony BrowserKit', 'Symfony2 BrowserKit', 'SynHttpClient-Built', 'Sysomos', 'sysscan', 'Szukacz', 'T0PHackTeam', 'tAkeOut', 'Tarantula\/', 'Taringa UGC', 'TarmotGezgin', 'Teleport', 'Telesoft', 'Telesphoreo', 'Telesphorep', 'Tenon\.io', 'teoma', 'terrainformatica', 'Test Certificate Info', 'testuri', 'Tetrahedron', 'TextRazor Downloader', 'The Drop Reaper', 'The Expert HTML Source Viewer', 'The Knowledge AI', 'The Intraformant', 'theinternetrules', 'TheNomad', 'Thinklab', 'Thumbshots', 'ThumbSniper', 'Thumbor', 'timewe\.net', 'TinEye', 'Tiny Tiny RSS', 'TLSProbe\/', 'Toata', 'topster', 'touche\.com', 'Traackr\.com', 'tracemyfile', 'Trackuity', 'TrapitAgent', 'Trendiction', 'Trendsmap', 'trendspottr', 'truwoGPS', 'TryJsoup', 'TulipChain', 'Turingos', 'Turnitin', 'tweetedtimes', 'Tweetminster', 'Tweezler\/', 'twibble', 'Twice', 'Twikle', 'Twingly', 'Twisted PageGetter', 'Typhoeus', 'ubermetrics-technologies', 'uclassify', 'UdmSearch', 'unchaos', 'unirest-java', 'UniversalFeedParser', 'Unshorten\.It', 'Untiny', 'UnwindFetchor', 'updated', 'updown\.io daemon', 'Upflow', 'Uptimia', 'Urlcheckr', 'URL Verifier', 'URLitor', 'urlresolver', 'Urlstat', 'URLTester', 'UrlTrends Ranking Updater', 'URLy Warning', 'URLy\.Warning', 'Vacuum', 'Vagabondo', 'VB Project', 'vBSEO', 'VCI', 'via ggpht\.com GoogleImageProxy', 'Virusdie', 'visionutils', 'vkShare', 'VoidEYE', 'Voil', 'voltron', 'voyager\/', 'VSAgent\/', 'VSB-TUO\/', 'Vulnbusters Meter', 'VYU2', 'w3af\.org', 'W3C_Unicorn', 'W3C-checklink', 'W3C-mobileOK', 'WAC-OFU', 'Wallpapers\/[0-9]+', 'WallpapersHD', 'wangling', 'Wappalyzer', 'WatchMouse', 'WbSrch\/', 'WDT\.io', 'web-capture\.net', 'Web-sniffer', 'Web Auto', 'Web Collage', 'Web Enhancer', 'Web Fetch', 'Web Fuck', 'Web Pix', 'Web Sauger', 'Web spyder', 'Web Sucker', 'Webalta', 'Webauskunft', 'WebAuto', 'WebCapture', 'WebClient\/', 'webcollage', 'WebCookies', 'WebCopier', 'WebCorp', 'WebDataStats', 'WebDoc', 'WebEnhancer', 'WebFetch', 'WebFuck', 'WebGazer', 'WebGo IS', 'WebImageCollector', 'WebImages', 'WebIndex', 'webkit2png', 'WebLeacher', 'webmastercoffee', 'webmon ', 'WebPix', 'WebReaper', 'WebSauger', 'webscreenie', 'Webshag', 'Webshot', 'Website Quester', 'websitepulse agent', 'WebsiteQuester', 'Websnapr', 'WebSniffer', 'Webster', 'WebStripper', 'WebSucker', 'Webthumb\/', 'WebThumbnail', 'WebWhacker', 'WebZIP', 'WeLikeLinks', 'WEPA', 'WeSEE', 'wf84', 'Wfuzz\/', 'wget', 'WhatCMS', 'WhatsApp', 'WhatsMyIP', 'WhatWeb', 'WhereGoes\?', 'Whibse', 'WhoAPI\/', 'WhoRunsCoinHive', 'Whynder Magnet', 'WinHttp-Autoproxy-Service', 'Windows-RSS-Platform', 'WinPodder', 'wkhtmlto', 'wmtips', 'Woko', 'Wolfram HTTPClient', 'woorankreview', 'Word\/', 'WordPress\/', 'worldping-api', 'WordupinfoSearch', 'wotbox', 'WP Engine Install Performance API', 'wpif', 'wprecon\.com survey', 'WPScan', 'wscheck', 'Wtrace', 'WWW-Collector-E', 'WWW-Mechanize', 'WWW::Document', 'WWW::Mechanize', 'www\.monitor\.us', 'WWWOFFLE', 'x09Mozilla', 'x22Mozilla', 'XaxisSemanticsClassifier', 'Xenu Link Sleuth', 'XING-contenttabreceiver', 'xpymep([0-9]?)\.exe', 'Y!J-(ASR|BSC)', 'Y\!J-BRW', 'Yaanb', 'yacy', 'Yahoo Link Preview', 'YahooCacheSystem', 'YahooYSMcm', 'YandeG', 'Yandex(?!Search)', 'yanga', 'yeti', 'Yo-yo', 'Yoleo Consumer', 'yoogliFetchAgent', 'YottaaMonitor', 'Your-Website-Sucks', 'yourls\.org', 'YoYs\.net', 'YP\.PL', 'Zabbix', 'Zade', 'Zao', 'Zauba', 'Zemanta Aggregator', 'Zend_Http_Client', 'Zend\\Http\\Client', 'Zermelo', 'Zeus ', 'zgrab', 'ZnajdzFoto', 'ZnHTTP', 'Zombie\.js', 'Zoom\.Mac', 'ZyBorg', '[a-z0-9\-_]*(bot|crawl|archiver|transcoder|spider|uptime|validator|fetcher|cron|checker|reader|extractor|monitoring|analyzer|scraper)'
  ]
];
