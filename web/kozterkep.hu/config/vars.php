<?php
// Site dolgok
define('APP', [

  'kt2_start' => '2019-04-22',

  'kt2_rate' => 100, // szupertudományosan kiszámolt indulás előtti állapot

  // URL
  'url' => CORE['ENV'] == 'dev' ? 'https://dev.kozterkep.hu' : 'https://www.kozterkep.hu',

  // Email, aki felad, ó-óó-óóó
  'site_email' => 'nevalaszolj@kozterkep.hu',

  // Útvonal appok közt
  'path' => 'kozterkep.hu',

  // App neve, azonosít itt-ott
  'name' => 'kozterkep.hu',

  // Oldal címe
  'title' => 'Köztérkép',

  // Oldal leírása, ha nincs
  'description' => 'Köztéri művészeti alkotások közösségi adatbázisa',

  // Oldal nyelve
  'lang' => 'hun',

  // Hmm, néha kell
  'szoborlap_birth' => '2006-04-18',

  // Megy-e a minified
  'minify' => CORE['ENV'] == 'dev' ? false : true,

  // Alapértelmezett route
  'default_controller' => 'pages',
  'default_index_action' => 'index',
  'default_view_action' => 'view',

  // Alapértelmezett hiba oldalak
  'error_4xx' => 'errors' . DS . 'error_4xx',
  'error_5xx' => 'errors' . DS . 'error_5xx',

  // Alapértelmezett layout
  'default_layout' => 'default',


  // Térkép dolgok
  'map' => [
    'max_id' => 1000, // ennyi műlap jöhet keresésből a térképre
  ],


  // Komment szám komment lista oldalakon (fórumban)
  'comments' => [
    'thread_count' => 30,
  ],

  // Frissítési intenzitások, másodpercben
  'intervals' => [
    'latest' => 17, // user update-ek (notifications és conversations)
    'conversation_thread' => 13, // megnyitott beszélgetés frissítése
    'comment_thread' => 15, // komment folyam frissítése
    'times' => 10, // időbélyegek frissítése
    'visit' => 30, // látogatások hosszabbítása,
  ],

  // Cookie beállítások
  'cookies' => [
    'webstat_name' => 'KT-Latogato',
    'remember_name' => 'KT-Belepvetarto',
    'expiration' => 60 * 60 * 24 * 90, // 3 hónap
    'domain' => 'kozterkep.hu',
    'secure' => true,
  ],

  // Session beállítások
  'sessions' => [
    'lifetime' => 60 * 60 * 18, // 18 óra
    'cookie_name' => 'KT-Munkamenet',
    'message_name' => 'titkosuzenet', // flash message session kulcsa
    'form_message_name' => 'csakformalitas', // form field errorok session kulcsa
    'alert_remove' => 5, // alert default eltüntetési késleltetése
  ],


  // Biztonsági dolgok
  'security' => [
    // Ez a KT 1.0 sója, így be tudunk lépni
    'salt' => CORE['SECURITY']['SALT'],
    'black_hole' => '/hibak/biztonsagi-hiba'
  ],


  // Engedélyezett modellek (követésben, webstatban, stb.)
  'models' => [
    'artpieces' => ['Műlapok', '/'],
    'artists' => ['Alkotók', '/alkotok/megtekintes/'],
    'places' => ['Helyek', '/helyek/megtekintes/'],
    'users' => ['Profilok', '/kozosseg/profil/'],
    'subpages' => ['Aloldalak', '/aloldal/'],
    'folders' => ['Mappák', '/mappak/megtekintes/'],
    'books' => ['Könyvek', '/adattar/konyv/'],
    'posts' => ['Blogposztok', '/blogok/megtekintes/'],
    'pages' => ['Oldalak', '/oldal/'],
    // a mongo ID miatt itt nem tud menni a view számolás;
    // Ha kell egyszer, akkor sztem át kell állni a seteknél
    // mysql-re... és a bepakolást tárolni csak mongoban. ehh, haha.
    'sets' => ['Gyűjtemények', '/gyujtemeny/megtekintes/'],
  ],


  // CACHE
  'cache' => [
    // ezeket tesszük el a view content mellett; ezek kellenek a layout építéshez
    'view_vars' => ['_generated', '_title', '_meta', '_header', '_tabs', '_simple_mobile', '_mobile_header', '_sidemenu', '_title_prefix', '_title_row', '_active_menu', '_active_submenu', '_active_sidemenu', '_breadcrumbs_menu', '_footer', '_editable', '_viewable', '_followable', '_praisable', '_shareable', '_model', '_model_id', '_block_caching'],
    // Ez az ID prefix minden view cache-nél
    // !!!artpiecesjob / generate-ben kézzel írd át, oda nem hat ez a var!
    'view_prefix' => 'cached-view-',
  ],
  // cache-elendő controller/action-ök
  // Megadhatunk időt, de szöveges értéket is eszerint: C_CACHE_TYPES
  'cacheables' => [
    'artpieces' => [
      'view' => 360*24*60*60,
      'index' => 15,
      'statistics' => 2*60*60,
    ],
    'pages' => [
      'index' => 15,
    ],
    'sets' => [
      'index' => 360*24*60*60,
      'view' => 360*24*60*60,
    ],
    'folders' => [
      'index' => 360*24*60*60,
      'view' => 360*24*60*60,
    ],
    'places' => [
      'index' => 24*60*60,
      'view' => 360*24*60*60,
      'country' => 24*60*60,
      'county' => 24*60*60,
      'bp_district' => 24*60*60,
    ],
    'artists' => [
      'index' => 1*60*60,
      'view' => 360*24*60*60,
    ],
    'search' => [
    ],
    'community' => [
      'index' => 60*60,
      'statistics' => 6*60*60,
      'member_statistics' => 6*60*60,
    ],
    'posts' => [
      'index' => 60*60,
      'view' => 6*60*60,
      'category' => 6*60*60,
    ],
    'xdirs' => [
      'index' => 360*24*60*60,
      'book_view' => 360*24*60*60,
      'ww_monuments_view' => 360*24*60*60,
    ],
  ],


  // SITE routing és controller action megfeleltetés
  'routes' => [
    '' => [
      'users' => [
        '' => 'index'
      ]
    ],
    'tagsag' => [
      'users',
      [
        'informaciok' => 'info',
        'szabalyzatok-elfogadasa' => 'disclaimer_accept',
        'valtozasok-elfogadasa' => 'accept_changes',
        'belepes' => 'login',
        'bejelentkezesi-segitseg' => 'login_help',
        'jelszo-beallitas' => 'repassword',
        'email-modositas' => 'email_change',
        'regisztracio' => 'register',
        'aktivacio' => 'activation',
        'beallitasok' => 'settings',
        'profil-torlese' => 'delete',
        'ertesitesek' => 'notifications',
        'koveteseim' => 'follows',
        'statisztikak' => 'statistics',
        'konyvjelzoim' => 'bookmarks',
        'email-feliratkozasok' => 'email_subscriptions',
        'kilepes' => 'logout',
      ]
    ],
    'mulapok' => [
      'artpieces',
      [
        'kozelben' => 'nearby',
        'statisztikak' => 'statistics',
        'fotok' => 'photos',
        'erintes' => 'hug',
        'terkapszula' => 'spacecapsule',
        'attekintes' => 'index',
        'letrehozas' => 'create',
        'szerkesztes' => 'edit',
        'szerkesztes_reszletek' => 'edit_details',
        'szerkesztes_szerkesztese' => 'edit_edit',
        'szerkesztes_torlese' => 'edit_delete',
        'szerkesztes_jovahagyasa' => 'edit_accept',
        'szerkesztes_visszaallitasa' => 'edit_rollback',
        'szerkesztes_ujranyitasa' => 'edit_reopen',
        'szerkesztes_hozzaszolas' => 'edit_to_comment',
        'szerkeszteseim_torlese' => 'delete_my_edits',
        'hozzaszolasok_torlese' => 'delete_comments',
        'szerkinfo' => 'edit_info',
        'latogatoinfok' => 'visitor_info',
        'megtekintes' => 'view',
        'szerkkomm' => 'view_editcom',
        'torles' => 'delete',
        'publikalas' => 'publish',
        'frissites' => 'refresh',
        'visszakuldes' => 'send_back',
        'visszanyitas' => 'reopen',
        'visszahivas' => 'call_back',
        'kozterre_kuldes' => 'submission',
        'szerkesztes_meghivas' => 'invite_edit',
        'kepfeltoltes_meghivas' => 'invite_photos',
        'torles' => 'delete',
        'leptetes' => 'step',
        'szerkesztoi_dobozok' => 'editor_boxes',
        'tagmemo' => 'user_memo',
        'adminmemo' => 'admin_memo',
        'veletlen' => 'random',
      ]
    ],
    'alkotok' => [
      'artists',
      [
        'attekintes' => 'index',
        'kereses' => 'search',
        'evfordulok' => 'anniversaries',
        'kepkereso' => 'photosearch',
        'szerkesztes' => 'edit',
        'megtekintes' => 'view',
        'szerkkomm' => 'view_editcom',
        'adalek_szerkesztes' => 'edit_description',
      ]
    ],
    'helyek' => [
      'places',
      [
        'attekintes' => 'index',
        'kereses' => 'search',
        'budapesti-keruletek' => 'bp_districts',
        'megyek' => 'counties',
        'orszagok' => 'countries',
        'felderitetlen-helyek' => 'discoverables',
        'szerkesztes' => 'edit',
        'megtekintes' => 'view',
        'szerkkomm' => 'view_editcom',
      ]
    ],
    'orszagok' => ['places', ['megtekintes' => 'country']],
    'megyek' => ['places', ['megtekintes' => 'county']],
    'budapesti-keruletek' => ['places', ['megtekintes' => 'bp_district']],

    'fotok' => [
      'photos',
      [

        'kereses' => 'search',
      ]
    ],
    'kereses' => [
      'search',
      [
        'lista' => 'index',
        'kereses' => 'index',
        'instant' => 'instant',
      ]
    ],
    'terkep' => [
      'maps',
      [
        'terkep' => 'index',
        'iframe' => 'iframe',
      ]
    ],
    'kozter' => [
      'space',
      [
        'attekintes' => 'index',
        'headitorium' => 'headitorium',
        'forum' => 'forum',
        'forum-tema' => 'forum_topic',
        'komment' => 'comment',
        'laptortenet' => 'events',
        'mulapjaim' => 'my_artpieces',
        'szerkdoboz' => 'index_editorbox',
        'friss_fotok' => 'index_photos',
        'friss_hozzaszolasok' => 'index_comments',
        'friss_esemenyek' => 'index_events',
        'parbeszedek' => 'comment_threads',
        'parbeszed' => 'comment_thread',
        'szerkesztesek' => 'edits',
      ]
    ],
    'beszelgetesek' => [
      'conversations',
      [
        'inditas' => 'start',
        'aktiv' => 'index',
        'kedvencek' => 'favorites',
        'folyam' => 'thread',
        'archivum' => 'archive',
        'kuka' => 'trash',
      ]
    ],
    'kozosseg' => [
      'community',
      [
        'mi' => 'index',
        'mi_falunk' => 'index_wall',
        'tagok' => 'members',
        'statisztikak' => 'statistics',
        'tag_statisztikak' => 'member_statistics',
        'profil' => 'profile',
      ]
    ],
    'hirek' => [
      'news',
      [
        'friss' => 'index',
        'esemenynaptar' => 'calendar',
      ]
    ],
    'blogok' => [
      'posts',
      [
        'friss' => 'index',
        'tag' => 'member',
        'kereses' => 'search',
        'sajat' => 'my',
        'tema' => 'category',
        'szerkesztes' => 'edit',
        'megtekintes' => 'view',
      ]
    ],
    'minerva' => [
      'minerva',
      [
        'bemutatkozas' => 'index',
        'hirlevel-kezeles' => 'subscription',
        'archivum' => 'archive',
        'archiv_hirlevel' => 'archive_view',
      ]
    ],
    'jatekok' => [
      'games',
      [
        'mire-jatszunk' => 'index',
        'erinto' => 'hugs',
        'terkapszulak' => 'spacecapsules',
        'havi-futam' => 'race',
      ]
    ],
    'oldalak' => [
      'pages',
      [
        'szerkesztes' => '_edit',
        'szerkesztesi-tortenet' => '_history',

        'kezdolap' => 'index',
        'roviden-rolunk' => 'about_us',
        'kozterkep-mozgalom' => 'movement',
        'tamogass-minket' => 'donate_us',
        'mukodesi-elvek' => 'contribution_terms',
        'jogi-nyilatkozat' => 'legal_terms',
        'adatkezelesi-szabalyzat' => 'privacy_policy',
        'impresszum' => 'impressum',
        'segedlet' => 'user_guides',
        'technologiai-kolofon' => 'technology_colophon',
        'kapcsolat' => 'contact_us',
        'kozremukodes-menete' => 'contribution_help',
      ]
    ],
    'mappak' => [
      'folders',
      [
        'attekintes' => 'index',
        'kereses' => 'search',
        'sajat' => 'my',
        'szerkesztes' => 'edit',
        'megtekintes' => 'view',
        'tag' => 'user',
        'fajl_mutato' => 'display_file',
      ]
    ],
    'gyujtemenyek' => [
      'sets',
      [
        'attekintes' => 'index',
        'kereses' => 'search',
        'sajat' => 'my',
        'szerkesztes' => 'edit',
        'megtekintes' => 'view',
      ]
    ],
    'eszkozok' => [
      'tools',
      [
        'profilfoto' => 'user_image',
        'tag_ablak' => 'user_tooltip',
        'mulap_ablak' => 'artpiece_tooltip',
        'alkoto_ablak' => 'artist_tooltip',
        'hely_ablak' => 'place_tooltip',
        'gyujtemeny_ablak' => 'set_tooltip',
        'foto_ablak' => 'photo_tooltip',
        'suti_hozzajarulas' => 'cookie_consent',
        'csak_bejelentkezessel' => 'only_users',
        'kepkezelo' => 'image_editor',
        'kepletolto' => 'photo_download',
        'kepmutato' => 'photo_fetch',
        'atmeneti_foto_hely' => 'temporary_photo',
      ]
    ],
    'webstat' => [
      'webstat',
      [
        'attekintes' => 'index',
        'oldalak' => 'pages',
        'szerverallapot' => 'serverstatus',
      ]
    ],
    'adattar' => [
      'xdirs',
      [
        'attekintes' => 'index',
        'kutatasi-partnerek' => 'research_partners',
        'konyvter' => 'books',
        'konyv' => 'book_view',
        'hosi-emlek' => 'ww_monuments',
        'hosi-emlekmu' => 'ww_monument_view',
        'absztrakt-kiscelli' => 'abstract_kiscell',
        'lexikon' => 'lexicon',
        'lexikon_szocikk' => 'lexicon_view',
      ]
    ],
    'hibak' => [
      'errors',
      [
        'biztonsagi-hiba' => 'black_hole',
        '404' => 'error_4xx',
        'szerverhiba' => 'error_5xx',
      ]
    ],
    'rest_api' => [
      'legacyapi',
      [
        'v1' => 'router',
      ]
    ],
  ]

]);