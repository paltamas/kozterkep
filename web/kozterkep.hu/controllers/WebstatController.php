<?php

use Kozterkep\AppBase as AppBase;

class WebstatController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Miez?',
      '_active_submenu' => 'Webstat',
    ]);
  }

  public function index() {

    /**
     * Óránként frissüljön a cache-elt stat
     *
     * VÁLTOZÁS
     * úgy tűnik, a cache lassabb + havi 200.000 bejegyzésnél meg
     * is döglik a mostani beállításokkal. Nem akarom emelni
     * a memóriát, mert nem kellene 10 megás cache fájlokat írni.
     * A tesztelés alapján ugyanannyi idő ekkora mongo aggregálásokat
     * futtatni és kiolvasni, mint ilyen nagy fájlokat.
     *
     */

    // Pillanatnyi látogatók száma
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('-' . APP['intervals']['visit'] * 1.5 . ' seconds')]]],
      ['$group' => ['_id' => '$v']],
      ['$count' => 'count'],
    ]);
    $current_visitors = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;

    // Napi oldalletöltések száma, nincs group
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('today 00:00')]]],
      ['$count' => 'count'],
    ]);
    $today_pageviews = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;

    // Napi látogatások száma
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('today 00:00')]]],
      ['$group' => ['_id' => '$s']],
      ['$count' => 'count'],
    ]);
    $today_sessions = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;

    // Heti látogatások száma
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('last monday 00:00', strtotime('Sunday'))]]],
      ['$group' => ['_id' => '$s']],
      ['$count' => 'count'],
    ]);
    $weekly_sessions = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;


    // 24 órás látogatások adatsora
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('-24 hours')]]],
      ['$group' => ['_id' => '$s', 'tt' => ['$first' => '$tt']]],
      ['$sort' => ['tt' => 1]]
    ], [
      /*'cache' => [
        'name' => 'webstat_sessions_24h',
        'expiration' => 5*60
      ]*/
    ]);
    $sessions_24h = [];
    foreach ($aggregated as $item) {
      if (!isset($sessions_24h[date('H', $item->tt)])) {
        $sessions_24h[date('H', $item->tt)] = 0;
      }
      $sessions_24h[date('H', $item->tt)] += 1;
    }

    // 24 órás oldalletöltések adatsora, nincs aggregálás
    $results = $this->Mongo->find_array('webstat',
      ['tt' => ['$gt' => strtotime('-24 hours')]],
      [
        '$sort' => ['tt' => 1],
        /*'cache' => [
          'name' => 'webstat_pageviews_24h',
          'expiration' => 5*60
        ]*/
      ]
    );
    $pageviews_24h = [];
    for ($i=-23; $i<=0; $i++) {
      $pageviews_24h[date('H', strtotime($i . ' hours'))] = 0;
    }
    foreach ($results as $item) {
      $pageviews_24h[date('H', $item['tt'])] += 1;
    }

    // 30 napos egyedi látogatások adatsora
    $aggregated = $this->Mongo->aggregate('webstat', [
      ['$match' => ['tt' => ['$gt' => strtotime('-30 days')]]],
      ['$group' => ['_id' => '$s', 'tt' => ['$first' => '$tt']]],
      ['$sort' => ['tt' => 1]]
    ], [
      /*'cache' => [
        'name' => 'webstat_sessions_30d',
        'expiration' => 30*60
      ]*/
    ]);

    $sessions_30d = [];
    for ($i=-24; $i<=0; $i++) {
      $sessions_30d[date('y.m.d.', strtotime($i . ' days'))] = 0;
    }
    foreach ($aggregated as $item) {
      if (!isset($sessions_30d[date('y.m.d.', $item->tt)])) {
        $sessions_30d[date('y.m.d.', $item->tt)] = 0;
      }
      $sessions_30d[date('y.m.d.', $item->tt)] += 1;
    }
    ksort($sessions_30d);


    // Hivatkozók
    $referrers = $this->Mongo->aggregate('webstat', [
      ['$match' => ['$and' => [
        ['tt' => ['$gt' => strtotime('-30 days')]],
        ['r' => ['$not' => new MongoDB\BSON\Regex('kozterkep.hu')]],
        ['r' => ['$ne' => '']]
      ]]],
      ['$group' => ['_id' => '$r', 'count' => ['$sum' => 1]]],
      ['$sort' => ['count' => -1]],
      ['$limit' => 20],
    ]);

    $this->set([
      'current_visitors' => $current_visitors,
      'today_sessions' => $today_sessions,
      'today_pageviews' => $today_pageviews,
      'weekly_sessions' => $weekly_sessions,
      'sessions_24h' => $sessions_24h,
      'pageviews_24h' => $pageviews_24h,
      'sessions_30d' => $sessions_30d,
      'referrers' => $referrers,
      '_title' => 'Áttekintés',
    ]);
  }


  /**
   * Oldalak megtekintései
   */
  public function pages() {
    $query = $this->params->query;

    $page = '';

    if (@$query['kulcsszo'] != '') {

      if (@$query['eleje'] == 1) {
        $page_filter = ['$regex' => '^' . $query['kulcsszo'], '$options' => 'm'];
      } else {
        $page_filter = ['$regex' => $query['kulcsszo'], '$options' => 'i'];
      }

      $results = [];
      if (strlen($query['kulcsszo']) > 2) {
        // Hasonló path-ok keresése
        $results = $this->Mongo->aggregate('webstat', [
          ['$match' => [
            'p' => $page_filter,
            'tt' => ['$gt' => strtotime('-1 year')],
          ]],
          ['$group' => ['_id' => '$p']],
          ['$sort' => ['p' => 1]],
        ]);
      }

      $this->set([
        'results' => $results,
      ]);
    }


    $stats = $referrers = false;
    $filters = [];

    if (@$query['vp'] != '' && @$query['vi'] > 0) {

      $models = APP['models'];
      if (!isset($models[$query['vp']])) {
        $this->redirect('/webstat/oldalak', [texts('hibas_url'), 'warning']);
      }

      $filters = [
        'vp' => $query['vp'],
        'vi' => (int)$query['vi']
      ];

      $page = $models[$query['vp']][1] . $query['vi'];

      // Oldal URL értelmesítése
      if ($query['vp'] == 'artpieces') {
        $item = $this->MC->t('artpieces', $query['vi']);
        if ($item) {

          if ($item['status_id'] != 5) {
            // Nem publikus műlap statját nem nézegetjük
            $this->redirect('/webstat');
          }

          $page .= '/' . $this->Text->slug($item['title']);
        }
      } elseif ($query['vp'] == 'artists') {
        // .. @todo
      } elseif ($query['vp'] == 'places') {
        // .. @todo
      } elseif ($query['vp'] == 'profiles') {
        // .. @todo
      } elseif ($query['vp'] == 'folders') {
        $item = $this->MC->t('folders', $query['vi']);
        if ($item) {
          if ($item['public'] != 1) {
            // Nem publikus műlap statját nem nézegetjük
            $this->redirect('/webstat');
          }
          $page .= '/' . $this->Text->slug($item['name']);
        }
      }

    } elseif (@$query['p'] != '') {
      $filters = [
        'p' => $query['p'],
      ];

      $page = $query['p'];
    }

    // Össz megtekintés
    // Össz oldalletöltés

    if (count($filters) > 0) {

      // Megtekintések
      $aggregated = $this->Mongo->aggregate('webstat', [
        ['$match' => array_merge($filters, ['tt' => ['$gt' => strtotime('-90 days')]])],
        ['$group' => ['_id' => '$s', 'tt' => ['$first' => '$tt']]],
        ['$sort' => ['tt' => 1]]
      ]);
      $stats['sessions'] = [];
      foreach ($aggregated as $item) {
        if (!isset($stats['sessions'][date('y.m.d.', $item->tt)])) {
          $stats['sessions'][date('y.m.d.', $item->tt)] = 0;
        }
        $stats['sessions'][date('y.m.d.', $item->tt)] += 1;
      }
      ksort($stats['sessions']);

      // Hivatkozók
      $referrers = $this->Mongo->aggregate('webstat', [
        ['$match' => array_merge(
          $filters,
          ['$and' => [
            ['tt' => ['$gt' => strtotime('-90 days')]],
            ['r' => ['$not' => new MongoDB\BSON\Regex('kozterkep.hu')]],
            ['r' => ['$ne' => '']]
          ]]
        )],
        ['$group' => ['_id' => '$r', 'count' => ['$sum' => 1]]],
        ['$sort' => ['count' => -1]],
        ['$limit' => 20],
      ]);
    }

    $this->set([
      'page' => $page,
      'stats' => $stats,
      'referrers' => $referrers,
      '_title' => 'Oldalak',
    ]);
  }


  public function serverstatus() {
    $joblogs_filter = ['$and' => []];

    if (@$this->params->query['joblog_osztaly'] != '') {
      $joblogs_filter['$and'][] = ['class' => [
        '$regex' => $this->params->query['joblog_osztaly'],
        '$options' => 'i'
      ]];
    }
    if (@$this->params->query['joblog_metodus'] != '') {
      $joblogs_filter['$and'][] = ['method' => [
        '$regex' => $this->params->query['joblog_metodus'],
        '$options' => 'i'
      ]];
    }
    if (@$this->params->query['joblog_hiba'] == 1) {
      $joblogs_filter['$and'][] = ['ran' => 0];
    }

    $latest_errors = $this->Mongo->find('joblogs', ['ran' => 0], [
      'sort' => ['created' => -1],
      'limit' => 5,
    ]);

    $this->set([
      'load' => $this->_get_load($this->_get_cores(), 0), // 5 perces [1,5,15]
      'memory' => $this->_get_memory(),
      'connections' => $this->_get_http_connections(),
      'joblogs' => $this->Mongo->find('joblogs', $joblogs_filter, [
        'sort' => ['created' => -1],
        'limit' => 25,
      ]),
      'job_count' => $this->Mongo->count('jobs'),
      'latest_errors' => $latest_errors,
      '_title' => 'Szerverállapot',
    ]);
  }


  /**
   *
   * Processzormagok száma
   *
   * https://wp-mix.com/php-get-server-information/
   *
   * @return int
   */
  private function _get_cores() {
    $cmd = "uname";
    $OS = strtolower(trim(shell_exec($cmd)));

    switch ($OS) {
      case('linux'):
        $cmd = "cat /proc/cpuinfo | grep processor | wc -l";
        break;
      case('freebsd'):
        $cmd = "sysctl -a | grep 'hw.ncpu' | cut -d ':' -f2";
        break;
      default:
        unset($cmd);
    }

    if ($cmd != '') {
      $cpuCoreNo = intval(trim(shell_exec($cmd)));
    }

    return empty($cpuCoreNo) ? 1 : $cpuCoreNo;
  }

  /**
   *
   * Proci terhelés százalékban, magok alapján
   *
   * https://wp-mix.com/php-get-server-information/
   *
   * @param int $coreCount
   * @param int $interval
   * @return array
   */
  private function _get_load($coreCount = 2, $interval = 1) {
    $rs = sys_getloadavg();
    $interval = $interval >= 0 && 3 <= $interval ? $interval : 0;
    $load = $rs[$interval];
    return [round(($load * 100) / $coreCount, 2), $rs[$interval]];
  }


  /**
   *
   * HTTP kapcsolatok száma
   *
   * https://wp-mix.com/php-get-server-information/
   *
   * @return int
   */
  private function _get_http_connections() {
    if (function_exists('exec')) {

      $www_total_count = $www_unique_count = 0;
      $unique = [];

      @exec('netstat -an | egrep \':80|:443\' | awk \'{print $5}\' | grep -v \':::\*\' |  grep -v \'0.0.0.0\'', $results);

      foreach ($results as $result) {
        $array = explode(':', $result);
        $www_total_count++;

        if (preg_match('/^::/', $result)) {
          $ipaddr = $array[3];
        } else {
          $ipaddr = $array[0];
        }

        if (!in_array($ipaddr, $unique)) {
          $unique[] = $ipaddr;
          $www_unique_count++;
        }
      }

      unset ($results);

      return count($unique);

    }
  }


  /**
   *
   * Memóriahasználat
   *
   * https://wp-mix.com/php-get-server-information/
   *
   * @return array
   */
  private function _get_memory() {
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2] / $mem[1] * 100;
    return [round($mem[2]/(1024*1024), 2), round($memory_usage, 2)];
  }

}