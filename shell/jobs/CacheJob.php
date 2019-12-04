<?php
class CacheJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  /**
   *
   * SQL táblák bizonyos mezőit eltárolja *** cache-be
   *
   * @return bool
   */
  public function tables () {

    //$start = time();

    //while ((time() - $start) < 55) {

      // MYSQL
      $tables = [
        'artpieces' => [
          'id',
          'title',
          'photo_id',
          'photo_slug',
          'user_id',
          'place_id',
          'lat',
          'lon',
          'dates',
          'artists',
          'status_id',
          'view_total',
          'published',
          'submitted',
          'updated',
          'superb',
        ],
        'users' => [
          'id',
          'name',
          'email',
          'nickname',
          'link',
          'license_type_id',
          'managing_on',
          'game_notifications_pause',
          'profile_photo_filename',
          'active',
          'blog_title',
          'active',
          'passed_away',
        ],
        'artists' => [
          'id', 'name', 'first_name', 'last_name', 'english_form', 'before_name', 'artist_name',
          'profession_id', 'user_id',
          "IF(born_date <> '', CONCAT(' (', SUBSTRING(born_date,1,4), ')'), '') AS born_year"
        ],
        'places' => [
          'id', 'name', 'original_name', 'country_id', 'county_id', 'user_id',
        ],
        'parameters' => [
          'id', 'name', 'parameter_group_id', 'rank'
        ],
        'forum_topics' => [
          'id', 'title', 'editorial'
        ],
        'posts' => [
          'id', 'title', 'user_id', 'published'
        ],
        'folders' => [
          'id', 'name', 'file_count', 'user_id', 'public',
        ],
        'books' => [
          'id', 'title', 'writers'
        ],
      ];

      foreach ($tables as $table => $fields) {
        $result = $this->DB->find($table, [
          'type' => 'list',
          'fields' => $fields,
        ]);
        foreach ($result as $id => $row) {
          $r = $this->MC->set('tables.' . $table . '.' . $id, $row);
        }
      }


      // Usereket link alapján is
      $result = $this->DB->find('users', [
        'type' => 'list',
        'fields' => ['id', 'link', 'name', 'profile_photo_filename'],
        'key' => 'link'
      ]);
      foreach ($result as $id => $row) {
        $r = $this->MC->set('tables.users_by_link.' . $id, $row);
      }


      // MONGO
      // @todo: ha többes kell, akkor majd átalakítani úgy, mint a MySQL esetén
      // Közös és tagi gyűjtemények
      $sets = $this->Mongo->find_array('sets', ['set_type_id' => ['$in' => [1, 2]]], [
        'projection' => [
          'name' => 1,
          'set_type_id' => 1,
          'user_id' => 1,
        ],
        'sort' => ['name' => 1],
      ]);
      foreach ($sets as $row) {
        $r = $this->MC->set('tables.sets.' . $row['id'], $row);
      }

      //sleep(3);
    //}

    return true;
  }



  /**
   * Törlünk minden cache-t. Csak.
   * @return bool
   */
  public function reset() {
    // Ezt is megoldottuk.
    exec('rm -r ' . CORE['PATHS']['CACHE'] . '/* -f');
    $this->DB->update('artpieces', ['cached' => 0], ['cached >' => 0]);
    return true;
  }



  /*
   * Cache generálás URL megnyitással
   * Olyan lassú, hogy ejj. Csak TESZT fázis után futtatjuk,
   * mert addig nem lehet belépés nélkül megnyitni a lapokat ugye.
   * Már most beállítottam az 5 perces futást a cronban. 300-at csinál meg ennyi idő alatt,
   * szóval élesítés után egy darabig elfutogat.
   */
  public function build() {
    // MŰLAPOK
    $cacheables = $this->DB->find('artpieces', [
      'conditions' => ['cached' => 0],
      'fields' => 'id',
      'limit' => 300,
    ]);

    if (count($cacheables) > 0) {
      $updates = [];

      foreach ($cacheables as $artpiece) {
        // Biztos, ami biztos, töröljük
        $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
        $done = $this->_open_url('/' . $artpiece['id']);
        if ($done) {
          $updates[] = $artpiece['id'];
        }
      }

      if (count($updates) > 0) {
        $this->DB->update('artpieces', ['cached' => time()], ['id' => $updates]);
      }
    }


    /**
     * Az alábbiaknál nincs tárolva a cache idő, ezért megnézzük, megvan-e a cache fájl
     * és ha nincs, 100-at megcsinálunk belőlük típusonként.
     */

    // GYŰJTEMÉNYEK
    $sets = $this->Mongo->find('sets', [], [
      'projection' => ['_id' => 1]
    ]);
    $i = 0;
    foreach ($sets as $set) {
      if (!$this->Cache->get('cached-view-sets-view-' . (string)$set->_id)
        && $i < 100) {
        $done = $this->_open_url('/gyujtemenyek/megtekintes/' . (string)$set->_id);
        if ($done) {
          $i++;
        }
      }
    }


    // ALKOTÓK
    $artists = $this->DB->find('artists', [
      'fields' => ['id']
    ]);
    $i = 0;
    foreach ($artists as $artist) {
      if (!$this->Cache->get('cached-view-artists-view-' . $artist['id'])
        && $i < 100) {
        $done = $this->_open_url('/alkotok/megtekintes/' . $artist['id']);
        if ($done) {
          $i++;
        }
      }
    }


    // HELYEK
    $places = $this->DB->find('places', [
      'fields' => ['id']
    ]);
    $i = 0;
    foreach ($places as $place) {
      if (!$this->Cache->get('cached-view-places-view-' . $place['id'])
        && $i < 100) {
        $done = $this->_open_url('/helyek/megtekintes/' . $place['id']);
        if ($done) {
          $i++;
        }
      }
    }


    return true;
  }



  /**
   *
   * URL megnyitogató
   *
   * @param $url_end
   * @return mixed
   */
  private function _open_url($url_end) {
    $url = CORE['BASE_URL'] . $url_end;

    $options = [
      CURLOPT_POST => 0,
      CURLOPT_HEADER => 1,
      CURLOPT_USERAGENT => 'KTBot',
      CURLOPT_URL => $url,
      CURLOPT_FRESH_CONNECT => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_TIMEOUT => 4,
    ];

    if (CORE['ENV'] == 'dev') {
      $options[CURLOPT_USERPWD] = CORE['DEV_HTTP_AUTH'];
    }

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $done = curl_exec($ch);
    curl_close($ch);

    return $done;
  }

}