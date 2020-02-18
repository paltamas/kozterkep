<?php
// Site dolgok
define('APP_MENUS', [

  // Főmenü
  'main' => [

    /*
    'Menüpont szövege' => [
      '/link', || 'Szöveg' => ['link', jog_szint], // URL vagy almenüpontok; jogszint: 1 usereknek, 2 head, 3 admin
      0, // user level: 0 - mindenkinek, 1 - auth = false, 2 - auth = true, 3  headitors
      1, // reszponzív logika: 0 - mindenhol, 1 - csak desktop, 2 - csak mobil
      0, // 0, string: osztott dropdown (csak a caretre nyitható le), ha nem 0,
      akkor a link kell, amire a menüpont mutat
    ],
    */

    /*'Kezdőlap' => [
      '/',
    ],*/


    /*
    'Beszélgetések' => [
      [
        'Új beszélgetés' => ['/beszelgetesek/inditas'],
        'Beszélgetések' => ['/beszelgetesek/aktiv'],
        'Archívum' => ['/beszelgetesek/archivum'],
        'Kuka' => ['/beszelgetesek/kuka'],
      ],
      2,
      2
    ],*/

    '<span class="far fa-users"></span>' => [
      '/kozter/attekintes',
      2, // csak auth = true esetén
      1, // reszponzívan mindenhol
    ],

    'Köztér' => [
      [
        'Köztér' => ['/kozter/attekintes'],
        'FőszerkSzoba' => ['/kozter/headitorium', 2],
        'Fórum' => ['/kozter/forum'],
        'Műlapjaim' => ['/kozter/mulapjaim'],
        'Szerkesztések' => ['/kozter/szerkesztesek'],
        'Laptörténet' => ['/kozter/laptortenet'],
      ],
      2, // csak auth = true esetén
      0, // reszponzívan mindenhol
    ],

    'FSz' => [
      '/kozter/headitorium',
      3,
      0
    ],

    'Műlapok' => [
      [
        'Áttekintés' => ['/mulapok/attekintes'],
        'Műlap keresés' => ['/kereses'],
        'Saját műlapok' => ['/kereses?r=1&sajat=1#hopp=lista', 1],
        'Fotókereső' => ['/mulapok/fotok'],
        'Gyűjtemények' => ['/gyujtemenyek/attekintes'],
        'Statisztikák' => ['/mulapok/statisztikak'],
      ],
    ],

    'Térkép' => [
      '/terkep',
      0,
      1
    ],


    'Alkotók' => [
      [
        'Áttekintés' => ['/alkotok/attekintes'],
        'Alkotók keresése' => ['/alkotok/kereses'],
        'Évfordulók' => ['/alkotok/evfordulok'],
        'Képkereső' => ['/alkotok/kepkereso'],
      ],
    ],


    'Helyek' => [
      [
        'Áttekintés' => ['/helyek/attekintes'],
        'Települések keresése' => ['/helyek/kereses'],
        'Megyék' => ['/helyek/megyek'],
        'BP kerületek' => ['/helyek/budapesti-keruletek'],
        'Országok' => ['/helyek/orszagok'],
      ],
    ],

    'Adattár' => [
      [
        'Áttekintés' => ['/adattar/attekintes'],
        'Lexikon' => ['/adattar/lexikon'],
        'Mappák' => ['/mappak/attekintes'],
        'Könyvtér' => ['/adattar/konyvter'],
        'Kutatási partnerek' => ['/adattar/kutatasi-partnerek'],
        'Hősi Emlék' => ['/adattar/hosi-emlek'],
      ],
    ],

    'Hírek' => [
      [
        //'Eseménynaptár' => ['/hirek/esemenynaptar'],
        'Gépház hírek' => ['/blogok/tema/gephaz'],
        'Minerva hírlevelek' => ['/minerva/bemutatkozas'],
      ],
    ],

    'Játék' => [
      '/jatekok/erinto',
      0,
      1
    ],

    /*'Játék' => [
      [
        'Mire játszunk?' => ['/jatekok/mire-jatszunk'],
        'Érintő' => ['/jatekok/erinto'],
        'Térkapszulák' => ['/jatekok/terkapszulak'],
        //'Havi futam' => ['/jatekok/havi-futam'],
      ],
    ],*/

    'Közösség' => [
      [
        'Mi' => ['/kozosseg/mi'],
        'Tagjaink' => ['/kozosseg/tagok'],
        'Blogok ' => ['/blogok/friss'],
        'Statisztikák' => ['/kozosseg/statisztikak'],
      ],
    ],

    'Miez?' => [
      [
        'Röviden rólunk' => ['/oldalak/roviden-rolunk'],
        //'Történetünk' => ['/oldalak/tortenetunk'],
        'Segédlet' => ['/oldalak/segedlet'],
        'Köztérkép Mozgalom' => ['/oldalak/kozterkep-mozgalom'],
        'Támogass minket!' => ['/oldalak/tamogass-minket'],
        '',
        'Működési elvek' => ['/oldalak/mukodesi-elvek'],
        'Jogi nyilatkozat' => ['/oldalak/jogi-nyilatkozat'],
        'Adatkezelési szabályzat' => ['/oldalak/adatkezelesi-szabalyzat'],
        '',
        'Webstat' => ['/webstat/attekintes'],
        'Impresszum' => ['/oldalak/impresszum'],
        'Kapcsolat' => ['/oldalak/kapcsolat'],
      ],
    ],

  ],


  /**
   * Usermenük
   * fejléc / láblécben is ez van
   */

  'usermenu' => [
    'logged' => [
      [
        'Profilom',
        '/kozosseg/profil/{user.link}',
        ['icon' => 'address-card']
      ],
      [
        'Műlapjaim',
        '/kozter/mulapjaim',
        ['icon' => 'map-marker-smile']
      ],
      [
        'Követéseim',
        '/tagsag/koveteseim',
        ['icon' => 'star']
      ],
      [
        'Beszélgetések',
        '/beszelgetesek/aktiv',
        ['icon' => 'comments-alt']
      ],
      [
        'Értesítések',
        '/tagsag/ertesitesek',
        ['icon' => 'bell']
      ],
      [
        'Statisztikáim',
        '/kozosseg/tag_statisztikak/{user.link}',
        ['icon' => 'user-chart']
      ],
      [
        'Saját mappák',
        '/mappak/sajat',
        ['icon' => 'folder']
      ],
      [
        'Blogom',
        '/blogok/tag/{user.link}',
        ['icon' => 'pen-nib']
      ],
      [
        'Gyűjteményeim',
        '/gyujtemenyek/sajat',
        ['icon' => 'tags']
      ],
      [
        'Könyvjelzőim',
        '/tagsag/konyvjelzoim',
        ['icon' => 'bookmark']
      ],
      [
        'Beállítások',
        '/tagsag/beallitasok',
        ['icon' => 'cog']
      ],
      [
        'Kilépés',
        '/tagsag/kilepes',
        [
          'icon' => 'sign-out',
          'ia-run' => 'users.logoutClear'
        ]
      ],
    ],
    'public' => [
      [
        'Belépés',
        '/tagsag/belepes',
        ['icon' => 'sign-in']
      ],
      [
        'Regisztráció',
        '/tagsag/regisztracio',
        ['icon' => 'user-plus']
      ],
      //'',
      /*[
        'Információk a tagságról',
        '/tagsag/info',
        ['icon' => 'info-circle']
      ],*/
      /*[
        'Hírlevél-kezelés',
        '/minerva/hirlevel-kezeles',
        ['icon' => 'newspaper']
      ],*/
    ]
  ],


  /**
   *
   * Sitemap struktúrái
   * A kulcs az URL első szintje
   * Ebből merít a sidemenu és a breadcrumbs is.
   *
   * A menu attribuutumok
   * [link, sidemenu_show (0: mindenkinek, 1: rejtett, 2: csak tagoknak, 3: csak főszerkesztőknek, 4: csak adminoknak), icon]
   *
   */
  'sitemap' => [

    'mulapok' => [
      'title' => 'Műlapok',
      'startpage' => '/mulapok/attekintes',
      'menu' => [
        'Áttekintés' => ['/mulapok/attekintes'],
        'Keresés' => ['/kereses'],
        'Közeli alkotások' => ['/mulapok/kozelben', 1],
        'Gyűjtemények' => ['/gyujtemenyek/attekintes'],
        'Statisztikák' => ['/mulapok/statisztikak'],
        'Létrehozás' => ['/mulapok/letrehozas', 1],
        'Szerkesztés' => ['/mulapok/szerkesztes', 1],
        'Megtekintés' => ['/mulapok/megtekintes', 1],
      ]
    ],


    'alkotok' => [
      'title' => 'Alkotók',
      'startpage' => '/alkotok/attekintes',
      'menu' => [
        'Áttekintés' => ['/alkotok/attekintes'],
        'Alkotók keresése' => ['/alkotok/kereses'],
        'Évfordulók' => ['/alkotok/evfordulok'],
        'Képkereső' => ['/alkotok/kepkereso'],
        'Szerkesztés' => ['/alkotok/szerkesztes', 1],
        'Megtekintés' => ['/alkotok/megtekintes', 1],
      ]
    ],

    'helyek' => [
      'title' => 'Helyek',
      'startpage' => '/helyek/attekintes',
      'menu' => [
        'Áttekintés' => ['/helyek/attekintes'],
        'Települések keresése' => ['/helyek/kereses'],
        'Megyék' => ['/helyek/megyek'],
        'BP kerületek' => ['/helyek/budapesti-keruletek'],
        'Országok' => ['/helyek/orszagok'],
        'Felderítetlen' => ['/helyek/felderitetlen-helyek', 1],
        'Szerkesztés' => ['/helyek/szerkesztes', 1],
        'Megtekintés' => ['/helyek/megtekintes', 1],
      ]
    ],

    'kereses' => [
      'title' => 'Keresés',
      'startpage' => '/kereses',
      'menu' => [
        'Keresés' => ['/kereses'],
      ]
    ],

    'terkep' => [
      'title' => 'Térkép',
      'startpage' => '/terkep',
      'menu' => [
        'Térkép' => ['/terkep'],
      ]
    ],

    'oldalak' => [
      'title' => 'Oldalak',
      'startpage' => '/oldalak/roviden-rolunk',
      'menu' => [
        'Röviden rólunk' => ['/oldalak/roviden-rolunk'],
        'Történetünk' => ['/oldalak/tortenetunk', 1],
        'Segédlet' => ['/oldalak/segedlet'],
        'Köztérkép Mozgalom' => ['/oldalak/kozterkep-mozgalom'],
        'Támogass minket!' => ['/oldalak/tamogass-minket'],
        'Támogatások' => ['/oldalak/tamogatasok', 1],
        'Működési elvek' => ['/oldalak/mukodesi-elvek'],
        'Jogi nyilatkozat' => ['/oldalak/jogi-nyilatkozat'],
        'Adatkezelési szabályzat' => ['/oldalak/adatkezelesi-szabalyzat'],
        'Fejlesztőknek' => ['/oldalak/fejlesztoknek', 1],
        'Impresszum' => ['/oldalak/impresszum'],
        'Kapcsolat' => ['/oldalak/kapcsolat'],
        'Szerkesztés' => ['/oldalak/szerkesztes', 1],
        'Szerkesztési történet' => ['/oldalak/szerkesztesi-tortenet', 1],
      ]
    ],

    'tagsag' => [
      'title' => 'Tagság',
      'startpage' => '/tagsag/belepes',
      'menu' => [
        'Információk' => ['/tagsag/info', 1],
        'Bejelentkezés' => ['/tagsag/belepes'],
        'Belépési segítség' => ['/tagsag/bejelentkezesi-segitseg'],
        'Regisztráció' => ['/tagsag/regisztracio'],
      ]
    ],

    'hirek' => [
      'title' => 'Hírek',
      'startpage' => '/hirek/friss',
      'menu' => [
        'Friss' => ['/hirek/friss'],
        'Eseménynaptár' => ['/hirek/esemenynaptar'],
      ]
    ],

    'jatekok' => [
      'title' => 'Játékok',
      'startpage' => '/jatekok/erinto',
      'menu' => [
        'Mire játszunk?' => ['/jatekok/mire-jatszunk'],
        'Érintő' => ['/jatekok/erinto'],
        'Térkapszulák' => ['/jatekok/terkapszulak'],
        //'Havi futam' => ['/jatekok/havi-futam'],
      ]
    ],

    'minerva' => [
      'title' => 'Minerva',
      'startpage' => '/minerva/bemutatkozas',
      'menu' => [
        'Bemutatkozás' => ['/minerva/bemutatkozas'],
        'Hírlevél-kezelés' => ['/minerva/hirlevel-kezeles', 1],
        'Archívum' => ['/minerva/archivum'],
        'Archív hírlevél' => ['/minerva/archiv_hirlevel', 1],
      ]
    ],

    'kozosseg' => [
      'title' => 'Közösség',
      'startpage' => '/kozosseg/mi',
      'menu' => [
        'Mi' => ['/kozosseg/mi'],
        'Tagjaink' => ['/kozosseg/tagok'],
        'Statisztikák' => ['/kozosseg/statisztikak'],
        'Saját stat' => ['/kozosseg/tag_statisztikak/{user.link}', 2],
        'Profil' => ['/kozosseg/profil', 1],
      ]
    ],


    'blogok' => [
      'title' => 'Blogok',
      'startpage' => '/blogok/friss',
      'menu' => [
        'Friss' => ['/blogok/friss'],
        'Keresés' => ['/blogok/kereses'],
        'Saját' => ['/blogok/sajat', 2],
        'Megtekintés' => ['/blogok/megtekintes', 1],
        'Tagi blog' => ['/blogok/tag', 1],
        'Téma' => ['/blogok/tema', 1],
      ]
    ],

    'kozter' => [
      'title' => 'Köztér',
      'startpage' => '/kozter/attekintes',
      'menu' => [
        'Köztér' => ['/kozter/attekintes'],
        'FőszerkSzoba' => ['/kozter/headitorium', 3],
        'Fórum' => ['/kozter/forum'],
        'Műlapjaim' => ['/kozter/mulapjaim'],
        'Szerkesztések' => ['/kozter/szerkesztesek'],
        'Laptörténet' => ['/kozter/laptortenet'],
        'Párbeszéd' => ['/kozter/parbeszed', 1],
      ]
    ],

    'adattar' => [],

    'beszelgetesek' => [
      'title' => 'Beszélgetések',
      'startpage' => '/beszelgetesek/aktiv',
      'menu' => [
        'Új beszélgetés' => ['/beszelgetesek/inditas', 0, 'comment-alt-plus'],
        'Beszélgetés' => ['/beszelgetesek/folyam', 1],
        'Beszélgetések<sup class="conversation-count rounded text-white font-weight-bold">&nbsp;</sup>' => ['/beszelgetesek/aktiv', 0, 'inbox'],
        'Kedvencek' => ['/beszelgetesek/kedvencek', 0, 'star fas'],
        'Archívum' => ['/beszelgetesek/archivum', 0, 'archive'],
        'Kuka' => ['/beszelgetesek/kuka', 0, 'trash'],
      ]
    ],

    'mappak' => [
      'title' => 'Mappák',
      'startpage' => '/mappak/attekintes',
      'menu' => [
        'Áttekintés' => ['/mappak/attekintes'],
        'Keresés' => ['/mappak/kereses'],
        'Saját mappák' => ['/mappak/sajat', 2],
        'Szerkesztés' => ['/mappak/szerkesztes', 1],
        'Megtekintés' => ['/mappak/megtekintes', 1],
      ]
    ],

    'gyujtemenyek' => [
      'title' => 'Gyűjtemények',
      'startpage' => '/gyujtemenyek/attekintes',
      'menu' => [
        'Áttekintés' => ['/gyujtemenyek/attekintes'],
        'Keresés' => ['/gyujtemenyek/kereses'],
        'Saját gyűjtemények' => ['/gyujtemenyek/sajat', 2],
        'Szerkesztés' => ['/gyujtemenyek/szerkesztes', 1],
        'Megtekintés' => ['/gyujtemenyek/megtekintes', 1],
      ]
    ],

    'webstat' => [
      'title' => 'Webstat',
      'startpage' => '/webstat/attekintes',
      'menu' => [
        'Áttekintés' => ['/webstat/attekintes'],
        'Oldalak' => ['/webstat/oldalak'],
        'Szerverállapot' => ['/webstat/szerverallapot'],
      ]
    ],

    'adattar' => [
      'title' => 'Adattár',
      'startpage' => '/adattar/attekintes',
      'menu' => [
        'Áttekintés' => ['/adattar/attekintes'],
        'Lexikon' => ['/adattar/lexikon'],
        'Mappák' => ['/mappak/attekintes'],
        'Könyvtér' => ['/adattar/konyvter'],
        'Könyv' => ['/adattar/konyv', 1],
        'Kutatási partnerek' => ['/adattar/kutatasi-partnerek'],
        'Lexikon szócikk' => ['/adattar/lexikon_szocikk', 1],
        'Hősi Emlék' => ['/adattar/hosi-emlek'],
        'Hősi Emlékmű' => ['/adattar/hosi-emlekmu', 1],
      ]
    ],

  ],

]);