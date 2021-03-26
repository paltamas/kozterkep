<?php
namespace Kozterkep;

/**
 *
 * Ez egy MySQLi komponens. Nem szuper PDO, ami 12
 * adatbázis típust támogat, amiből soha egyet se fogunk használni.
 * Nem. Ez egy hiperegyszerű cucc. Ha váltunk, majd új komponenst írunk
 * újabb néhány óra alatt ;)
 * Viva.
 *
 * Ja, csak join kellene, mi?!
 *
 * Class DatabaseComponent
 * @package Kozterkep
 */

class DatabaseComponent {

  private $connection;

  /**
   * DB constructor.
   * példány létrehozásakor alapértelmezetten kapcsolódunk.
   * @param string $dbname
   * @param bool $encoding - opcionális, egyedi karakterkódolás
   *
   */
  public function __construct($dbname = 'kt', $encoding = false) {
    $this->Log = new LogComponent();
    $this->Cache = new CacheComponent();

    $this->cache_prefix = 'query_';

    $this->connect($dbname, $encoding);
  }

  /**
   * Osztálypéldány megszűnésekor zárjuk a kapcsolatot,
   * hogy ne az idle timeout-ra bízzuk
   */
  public function __destruct() {
    //$this->close();
  }

  /**
   * Adatbázis kapcsolat megépítése
   * @param string $db_name
   * @param $encoding - opcionális, egyedi karakterkódolás
   */
  public function connect($db_name = 'kt', $encoding) {
    $type = C_MYSQL[$db_name]['type'];
    $host = C_MYSQL[$db_name]['host'];
    $name = C_MYSQL[$db_name]['name'];
    $user = C_MYSQL[$db_name]['user'];
    $pass = C_MYSQL[$db_name]['pass'];
    $encoding = $encoding ? $encoding : C_MYSQL[$db_name]['encoding'];

    // Kapcsolódás
    if(isset($GLOBALS['mysqli_connection_id'])) {
      $connection = $GLOBALS['mysqli_connection_id'];
    } else {
      $connection = new \mysqli($host, $user, $pass, $name);
      $GLOBALS['mysqli_connection_id'] = $connection;

      if (mysqli_connect_errno()) {
        $this->Log->write('DB: ' . mysqli_connect_error());
        exit();
      }
    }

    // Be kell álllítani
    mysqli_set_charset($connection, $encoding);

    // A közösbe dobott kapcsolat
    $this->connection = $connection;
  }

  /**
   * Adatbázis váltás
   * @param $dbname
   */
  public function dbchange($dbname) {
    if (!$this->connection) {
      $this->connect($dbname);
    } else {
      // Először zárunk...
      $this->close();
      // ...aztán újra konnektálunk
      $this->connect($dbname);
    }
  }

  /**
   *
   * Beadott zárja a kapcsolatot
   * @return bool
   *
   */
  public function close () {
    if (@$this->connection) {
      mysqli_close($this->connection);
    }
    return true;
  }


  private function execute_statement($query, $type = '') {
    if (!$this->connection) {
      $this->connect();
    }

    // Típus meghatározása
    if ($type == '') {
      switch (true) {

        case strpos($query, 'DELETE') === 0:
        case strpos($query, 'UPDATE') === 0:
          $type = 'affected';
          break;

        case strpos($query, 'INSERT') === 0:
          $type = 'insertid';
          break;

        case strpos($query, 'SELECT') === 0
          && strpos($query, 'COUNT(*)') === false:
          $type = 'fetch';
          break;

        case strpos($query, 'COUNT(*)') !== false:
          $type = 'count';
          break;

        default:
          $type = 'bool';
          break;
      }
    }

    // A lekérdezés előkészítése
    $stmt = mysqli_prepare($this->connection, $query);

    // Futtatás, ha lehet, és ha OK, akkor megyünk bele
    if ($stmt && mysqli_stmt_execute($stmt)) {

      // Kiszedem a választ
      $result = mysqli_stmt_get_result($stmt);

      if ($type == 'fetch') {
        if (isset($result->num_rows) && $result->num_rows > 0) {
          $response = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
          $response = [];
        }

      } elseif ($type == 'count') {

        $count_res = $result ? mysqli_fetch_all($result) : [];
        if (isset($count_res[0][0])) {
          $response = $count_res[0][0];
        } else {
          $response = 0;
        }

      } elseif ($type == 'affected') {
        $response = mysqli_affected_rows($this->connection);

      } elseif ($type == 'insertid') {
        $response = mysqli_insert_id($this->connection);

      } else {
        // Tuti true, mert már benne vagyunk
        // a sikeres execute ágban
        $response = true;

      }

      // Memfelszab.
      if (is_resource($result)) {
        mysqli_free_result($result);
      }

      // Visszanyomás
      return $response;
    }

    if (CORE['DEBUG_LEVEL'] > 0) {
      debug('DB hiba: ' . mysqli_error($this->connection) . '<br>' . $query);
      exit;
    }

    $this->Log->write("Lekerdezesi hiba: '" . $query . "' || MySQL: " . mysqli_error($this->connection));
    return false;
  }


  /**
   *
   * Beadott query-t futtat le
   *
   * @param $query
   * @return array|bool|int
   */
  public function query ($query) {
    return $this->execute_statement($query);
  }


  public function count($table, $conditions = [], $options = []) {
    $conditions = $this->conditions($conditions);
    $query = "SELECT COUNT(*) FROM " . $table . $conditions;
    $results = $this->execute_statement($query, 'count');
    return $results > 0 ? $results : 0;
  }


  /**
   *
   * Komplex find
   *  - conditions array-t ez konvertálja
   *  - tud több típust, amik jelenleg:
   *    * list (array)
   *    * first
   *    * all - sima tömb
   *
   * @param bool $table
   * @param array $options
   * @param array $cache
   * @return array|bool|int|mixed
   */
  public function find($table = false, $options = [], $cache = []) {
    $options = (array)$options + [
      'type' => 'all',
      'conditions' => false,
      'join' => false,
      'having' => false,
      'limit' => false,
      'page' => false,
      'order' => false,
      'group' => false,
      'fields' => '*',
      'debug' => false
    ];

    if ($options['type'] == 'first') {
      $options['limit'] = 1;
    }

    // Ha cache-ből is jöhet, onnan olvasunk
    if (@$cache['name'] != '') {
      $cache['name'] = $this->cache_prefix . $cache['name'];
      $result = $this->Cache->get($cache['name']);
      if ($result) {
        return $result;
      }
    }

    $fields = is_array($options['fields']) ? implode(", ", $options['fields']) : $options['fields'];
    $where = $this->conditions($options['conditions']);

    $having = $options['having'] ? " HAVING " . $options['having'] : '';
    $group = $options['group'] ? " GROUP BY " . $options['group'] : '';
    $order = $options['order'] ? " ORDER BY " . $options['order'] : '';
    $limit = $options['limit'] ? " LIMIT " . $options['limit'] : '';
    if ($options['limit'] && $options['page']) {
      $limit = " LIMIT " . $options['limit'] * ($options['page']-1) . ', ' . $options['limit'];
    }

    $query = "SELECT " . $fields . " 
            FROM " . $table . " 
            " . $where . " 
            " . $having . " 
            " . $group . " 
            " . $order . " 
            " . $limit;

    if ($options['debug']) {
      debug($query);
    }

    switch ($options['type']) {
      case 'all':
        $result = $this->query($query);
        if (!is_array($result)) {
          $result = [];
        }
        break;

      case 'first':
        $result_ = $this->query($query);
        $result = isset($result_[0]) ? $result_[0] : false;
        break;

      case 'list':
        $result = [];
        $result_ = $this->query($query);
        if (is_array($result_) && count($result_) > 0) {
          $result = [];
          $key_field = isset($options['key']) ? $options['key'] : 'id';
          foreach ($result_ as $item) {
            $result[$item[$key_field]] = $item;
          }
        }
        break;

      case 'fieldlist':
        $result = [];
        $result_ = $this->query($query);
        if (is_array($result_) && @count($result_) > 0) {
          $result = [];
          $key_field = isset($options['key']) ? $options['key'] : 'id';
          foreach ($result_ as $item) {
            $result[] = $item[$key_field];
          }
        }
        break;
    }

    if (@count(@$options['connect']) > 0 && $options['type'] == 'first' && $result) {
      foreach ($options['connect'] as $table => $table_options) {
        $field = $table_options[0];
        $value = $result[$table_options[1]];
        $result[$table] = $this->first($table, [$field => $value]);
      }
    }

    // Ha cache-ből is jöhet, visszaírjuk
    if (@$cache['name'] != '') {
      $this->Cache->set(
        $this->cache_prefix . $cache['name'],
        $result,
        @$cache['expiration'] ? $cache['expiration'] : 'queries'
      );
    }

    return $result;
  }


  /**
   *
   * Az első, ami
   *
   * @param $table
   * @param array $conditions_array
   * @param array $options
   * @return array|bool|int|mixed
   */
  public function first($table, $conditions_array = [], $options = []) {
    if ($conditions_array == '') {
      // Ilyen nincs.
      return false;
    }
    $options = [
      'conditions' => $conditions_array,
      'type' => 'first'
    ] + (array)@$options; // ha esetleg order-t akarunk még
    return $this->find($table, $options);
  }


  /**
   *
   * Szabad szavas lekérdező - überlustáknak.
   * Ezt a szuperséget a Cake-től tanultam, de ott bele van keverve a
   * CamelCase meg a Pluralize, amit itt fölösnek tartottam.
   * Ilyenre is jó pl: find_by_email('users', 'paltamas@gmail.com')
   * vagy ilyenre: list_by_id('users', ['email <>' => ''])
   *
   * @param $name
   * @param array $arguments: table, (by)field, value||conditions, options
   * @return bool|void
   */
  public function __call($name, $arguments = []) {
    if ((strpos($name, 'find_by_') === false && strpos($name, 'list_by_') === false)
      || !isset($arguments[0])) {
      return false;
    }
    $table = $arguments[0];

    if (strpos($name, 'find_by_') !== false) {
      // Keresés a megadott mező alapján
      $field = str_replace('find_by_', '', $name);
      $value = $arguments[1];
      // Alapból first, de egyébként add meg, ha all vagy list
      $other_options = (array)@$arguments[2] + ['type' => 'first'];
      $options = ['conditions' => [
          $field => $value
        ]] + $other_options;
    } elseif (strpos($name, 'list_by_') !== false) {
      // Lista, amiben a megadott mező a kulcs
      $key_field = str_replace('list_by_', '', $name);
      $conditions = (array)@$arguments[1];
      $other_options = [
        'type' => 'list',
        'key' => $key_field
      ] + (array)@$arguments[2];
      $options = ['conditions' => $conditions] + $other_options;
    }

    return $this->find($table, $options);
  }


  /**
   *
   * Ha jön id, akkor update, egyébként insert
   *
   * @param $table
   * @param array $data
   * @return array|bool|int|null|string
   */
  public function upsert($table, array $data) {
    if (isset($data['id'])) {
      $id = $data['id'];
      unset($data['id']);
      return $this->update($table, $data, $id);
    } else {
      return $this->insert($table, $data);
    }
    return false;
  }


  /**
   *
   * Klasszik update
   *
   * @param $table
   * @param array $update_array
   * @param array $condition_array
   * @return array|bool|int|null|string
   */
  public function update($table, $update_array = [], $condition_array = []) {
    if (!is_array($update_array) || @count($update_array) == 0
      || (@count($condition_array) == 0 && !is_numeric($condition_array))) {
      return false;
    }

    $updates = '';
    $i = 0;
    foreach ($update_array as $field => $value) {
      $i++;
      $comma = $i < count($update_array) ? ', ' : '';
      // A léptetést akarjuk úgy, ahogy van
      $value_ = is_string($value) && $value == $field . '+1'
        ? $value : $this->_($value);
      $updates .= '`' . $field . '`' . " = " . $value_ . $comma;
    }

    $conditions = $this->conditions($condition_array);
    $query = "UPDATE " . $table . " SET " . $updates . $conditions;

    $results = $this->execute_statement($query);
    return $results;
  }



  public function insert($table, $insert_array = []) {
    if (!is_array($insert_array) || @count($insert_array) == 0) {
      return false;
    }

    $fields = $values = '';
    $i = 0;
    foreach ($insert_array as $field => $value) {
      $i++;
      $comma = $i < count($insert_array) ? ', ' : '';
      $fields .= '`' . $field . '`' . $comma;
      $values .= $this->_($value) . $comma;
    }

    $query = "INSERT INTO " . $table . " (" . $fields . ") VALUES (" . $values . ")";

    return $this->execute_statement($query);
  }


  /**
   * Többes insert
   *
   * @param $table
   * @param array $inserts
   * @return array|bool|int|null|string
   */
  public function insert_multi($table, $inserts = []) {
    if (!is_array($inserts) || @count($inserts) == 0) {
      return false;
    }

    $fields = $values_all = '';

    $i = 0;
    foreach ($inserts as $insert_array) {
      $i++;
      $values = '(';
      $k = 0;
      foreach ($insert_array as $field => $value) {
        $k++;
        $comma = $k < count($insert_array) ? ', ' : '';
        if ($i == 1) {
          $fields .= '`' . $field . '`' . $comma;
        }
        $values .= $this->_($value) . $comma;
      }
      $values .= '),';

      $values_all .= $values;
    }

    $query = "INSERT INTO " . $table . " (" . $fields . ") VALUES " . rtrim($values_all, ',');

    return $this->execute_statement($query);
  }



  /**
   *
   * Klasszik delete
   *
   * @param $table
   * @param array $condition_array
   * @return array|bool|int|null|string
   */
  public function delete ($table, $condition_array = []) {
    if ($condition_array == '') {
      return false;
    }
    $conditions = $this->conditions($condition_array);
    $query = "DELETE FROM " . $table . $conditions;
    return $this->execute_statement($query);
  }


  /**
   *
   * Lusta escape és value alakító (update insert esetére)
   *
   * @param $value
   * @return string
   */
  private function _($value) {
    if (is_array($value)) {
      $value = _json_encode($value);
    }

    $cv = mysqli_real_escape_string($this->connection, $value);

    if (!is_integer($cv)) {
      $cv = "'" . $cv . "'";
    }

    return $cv;
  }

  /**
   *
   * Condition építő
   * Kaphat:
   *  - sima stringet
   *  - ID-t
   *  - tömböt (LIKE, >=, <=, =)
   *
   * @param $array
   * @return string
   */
  private function conditions($array) {

    if ($array === false) {
      return '';
    }

    if (is_numeric($array) || $array == "0") { // a kedvencem
      // Lusta ID-t dobtam be
      return " WHERE id = " . (int)$array;
    }

    if (!is_array($array) && $array != '') {
      // Sima raw condition jött
      return " WHERE " . $array;
    }

    $conditions = [];

    if (is_array($array)) {
      foreach ($array as $field => $value) {
        if (!in_array($field, ['OR']) && is_array($value)) {
          // Tömb
          $operator = ' IN ';
          $conditions[] = $field . $operator . "(" . implode(',', $value) . ")";
        } elseif ($field == 'OR') {
          // OR ág
          $or_conditions = [];
          foreach ($value as $f => $v) {
            $or_conditions[] = trim(str_replace('WHERE', '', $this->conditions([$f => $v])));
          }
          $conditions[] = '(' . implode(' OR ', $or_conditions) . ')';
        } else {
          //
          $operator = ' = ';
          if (strpos($field, ' LIKE') !== false) {
            $operator = ' LIKE ';
            $field = trim(str_replace('LIKE', '', $field));
          } elseif (strpos($field, '<>') !== false) {
            $operator = ' <> ';
            $field = trim(str_replace('<>', '', $field));
          } elseif (strpos($field, '<=') !== false) {
            $operator = ' <= ';
            $field = trim(str_replace('<=', '', $field));
          } elseif (strpos($field, '<') !== false) {
            $operator = ' < ';
            $field = trim(str_replace('<', '', $field));
          } elseif (strpos($field, '>=') !== false) {
            $operator = ' >= ';
            $field = trim(str_replace('>=', '', $field));
          } elseif (strpos($field, '>') !== false) {
            $operator = ' > ';
            $field = trim(str_replace('>', '', $field));
          }

          $value_ = $this->_($value);
          $conditions[] = $field . $operator . $value_;
        }
      }
    }
    if (count($conditions) > 0) {
      return " WHERE " . implode(' AND ', $conditions);
    } else {
      return '';
    }
  }


  /**
   *
   * Cache törlés
   *
   * @param string $cache_name
   * @return bool
   */
  public function delete_cache($cache_name = '') {
    return $this->Cache->delete($this->cache_prefix . $cache_name);
  }


}