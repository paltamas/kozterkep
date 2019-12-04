<?php

class SitemapJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }



  /**
   * RSS feed műlapokból
   * éljenek a kilencvenes évek! ;]
   */
  public function build_rss() {

    $rss = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL
      . '<rss version="2.0">' . PHP_EOL
      . '<channel>' . PHP_EOL
      . '<title>Köztérkép Műlap RSS</title>' . PHP_EOL
      . '<link>' . CORE['BASE_URL'] . '</link>' . PHP_EOL ;

    // MŰLAPOK
    $result = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
      ],
      'order' => 'published DESC',
      'limit' => 100,
    ]);

    foreach ($result as $item) {
      $rss .= '<item>' . PHP_EOL;
      $rss .='<title>' . $item['title'] . ' (' . $this->MC->t('places', $item['place_id'])['name'] . ')</title>' . PHP_EOL;
      $rss .= '<link>' . CORE['BASE_URL'] . '/' . $item['id']. '</link>' . PHP_EOL;
      $rss .= '<description>' . PHP_EOL;
      $rss .= $this->MC->t('places', $item['place_id'])['name'];
      $artists = _json_decode($item['artists']);
      if (isset($artists[0]['id'])) {
        $rss .= ', ' . $this->MC->t('artists', $artists[0]['id'])['name'];
      }
      $year = $this->Artpieces->get_artpiece_year($item['dates']);
      $rss .= $year != '' ? ' (' . $year : ')' . PHP_EOL;
      $rss .= '</description>' . PHP_EOL;
      $rss .= '</item>' . PHP_EOL;
    }

    $rss .= '</channel>' . PHP_EOL
      . '</rss>';

    // Megírjuk
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/feed.rss', $rss);
  }






  /*
	* SITEMAP XML
	*
	* Protocol: http://www.sitemaps.org/protocol.html
	* menüpontok, pages, műlapok, alkotók, települések, bejegyzések, profilok
	*	CHANGEFREQ always, hourly, daily, weekly, monthly, yearly, never
	*/
  public function build_xml() {

    // Mod time-ok
    $artpieces_mod = $artists_mod = $places_mod = $posts_mod = $pages_mod = $users_mod = 0;

    // ELKEZDEM
    $artpieces = $artists = $places = $posts = $pages = $users = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $artpieces .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $artists .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $places .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $posts .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $pages .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $users .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;



    // ALAP OLDALAK
    $lastmod = date('Y-m-d', time());
    $changefreq = 'always';

    // Kezdőlap
    $pages .= $this->xml_row('/', $lastmod, $changefreq, '1');

    // Műlap oldalak
    $pages .= $this->xml_row('/mulapok/attekintes', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/kereses', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/mulapok/fotok', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/gyujtemenyek/attekintes', $lastmod, $changefreq, '0.7');
    $pages .= $this->xml_row('/mulapok/statisztikak', $lastmod, $changefreq, '0.6');

    // Térkép
    $pages .= $this->xml_row('/terkep', $lastmod, $changefreq, '1');

    // Alkotók
    $pages .= $this->xml_row('/alkotok/attekintes', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/alkotok/kereses', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/alkotok/evfordulok', $lastmod, $changefreq, '0.7');
    $pages .= $this->xml_row('/alkotok/kepkereso', $lastmod, $changefreq, '1');

    // Helyek
    $pages .= $this->xml_row('/helyek/attekintes', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/helyek/kereses', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/helyek/megyek', $lastmod, $changefreq, '0.9');
    $pages .= $this->xml_row('/helyek/budapesti-keruletek', $lastmod, $changefreq, '0.9');
    $pages .= $this->xml_row('/helyek/orszagok', $lastmod, $changefreq, '0.9');

    // Adattárak
    $pages .= $this->xml_row('/adattar/attekintes', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/mappak/attekintes', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/mappak/kereses', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/adattar/konyvter', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/adattar/lexikon', $lastmod, $changefreq, '0.7');
    $pages .= $this->xml_row('/adattar/hosi-emlek', $lastmod, $changefreq, '0.7');

    // Hírek
    $pages .= $this->xml_row('/blogok/tema/gephaz', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/minerva/bemutatkozas', $lastmod, $changefreq, '0.8');
    $pages .= $this->xml_row('/minerva/archivum', $lastmod, $changefreq, '0.7');

    // Közösség
    $pages .= $this->xml_row('/kozosseg/mi', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/kozosseg/tagok', $lastmod, $changefreq, '1');
    $pages .= $this->xml_row('/kozosseg/statisztikak', $lastmod, $changefreq, '1');

    // Játék
    $pages .= $this->xml_row('/jatekok/erinto', $lastmod, $changefreq, '0.7');

    // Pages, DB-ből
    $result = $this->DB->find('pages', ['fields' => ['path', 'modified']]);
    foreach ($result as $item) {
      $pages .= $this->xml_row($item['path'], date('Y-m-d', $item['modified']), 'weekly', '0.7');
    }


    // TAGOK
    $result = $this->DB->find('users', [
      'conditions' => [
        'active' => 1,
        'blocked' => 0,
        'harakiri' => 0,
      ],
      'fields' => ['link', 'last_here'],
      'order' => 'activated',
    ]);
    foreach ($result as $item) {
      $users .= $this->xml_row('/kozosseg/profil/' . $item['link'], date('Y-m-d', $item['last_here']), 'daily', '0.5');
    }


    // MŰLAPOK
    $result = $this->DB->find('artpieces', [
      'conditions' => [
        'status_id' => 5,
      ],
      'fields' => ['id', 'updated'],
      'order' => 'published',
    ]);
    foreach ($result as $item) {
      $artpieces .= $this->xml_row('/' . $item['id'], date('Y-m-d', $item['updated']), 'weekly', '0.5');
    }
    
    
    // ALKOTÓK
    $result = $this->DB->find('artists', [
      'conditions' => [
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'fields' => ['id', 'modified'],
      'order' => 'id',
    ]);
    foreach ($result as $item) {
      $artists .= $this->xml_row('/alkotok/megtekintes/' . $item['id'], date('Y-m-d', $item['modified']), 'weekly', '0.5');
    }
    

    // HELYEK
    $result = $this->DB->find('places', [
      'conditions' => [
        'checked' => 1,
        'artpiece_count >' => 0,
      ],
      'fields' => ['id', 'modified'],
      'order' => 'id',
    ]);
    foreach ($result as $item) {
      $places .= $this->xml_row('/helyek/megtekintes/' . $item['id'], date('Y-m-d', $item['modified']), 'weekly', '0.5');
    }


    // BLOGBEJEGYZÉSEK
    $result = $this->DB->find('posts', [
      'conditions' => [
        'status_id' => 5,
      ],
      'fields' => ['id', 'modified'],
      'order' => 'published',
    ]);
    foreach ($result as $item) {
      $posts .= $this->xml_row('/blogok/megtekintes/' . $item['id'], date('Y-m-d', $item['modified']), 'monthly', '0.5');
    }

    // LEZÁROM
    $pages .= '</urlset>';
    $artpieces .= '</urlset>';
    $artists .= '</urlset>';
    $places .= '</urlset>';
    $posts .= '</urlset>';

    // KIÍROM a fájlokat
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_pages.xml', $pages);
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_artpieces.xml', $artpieces);
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_artists.xml', $artists);
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_places.xml', $places);
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_posts.xml', $posts);
    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/etc/sitemap_users.xml', $users);

    // SITEMAP INDEX
    $tf = 'Y-m-d\TH:i:s+01:00';
    $index = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_pages.xml</loc><lastmod>' . date($tf, $pages_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_artpieces.xml</loc><lastmod>' . date($tf, $artpieces_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_artists.xml</loc><lastmod>' . date($tf, $artists_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_places.xml</loc><lastmod>' . date($tf, $places_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_posts.xml</loc><lastmod>' . date($tf, $posts_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '<sitemap><loc>' . CORE['BASE_URL'] . '/sitemap_users.xml</loc><lastmod>' . date($tf, $users_mod) . '</lastmod></sitemap>' . PHP_EOL;
    $index .= '</sitemapindex>';

    $this->File->write(CORE['PATHS']['WEB'] . '/kozterkep.hu/webroot/sitemap.xml', $index);
  }


  /**
   *
   * Egy XML sor
   *
   * @param $location
   * @param $lastmod
   * @param $changefreq
   * @param $priority
   * @return string
   */
  private function xml_row($location, $lastmod, $changefreq, $priority) {
    $xml_row = '<url>' . PHP_EOL;
    $xml_row .= '<loc>' . CORE['BASE_URL'] . $location . '</loc>' . PHP_EOL;
    $xml_row .= '<lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
    $xml_row .= '<changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
    if ($priority != '0.5') {
      $xml_row .= '<priority>' . $priority . '</priority>' . PHP_EOL;
    }
    $xml_row .= '</url>' . PHP_EOL;
    return $xml_row;
  }
}