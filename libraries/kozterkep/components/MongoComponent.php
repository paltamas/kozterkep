<?php
namespace Kozterkep;

/*
 * Saját könnyítés, hogy ne kelljen collectiont és DB-t hívni.
 * Erre megyünk: http://php.net/manual/en/book.mongodb.php
 * 
 * Érdekes gondolatok deprec Mongo libhez: 
 * https://github.com/masylum/php_mongo/blob/master/php_mongo.php
 * 
 * @todo: log, exceptions
 *
 * text index készítése:
 * db.__collection__.ensureIndex({"$**": "text" }
 *
 * 
 */

class MongoComponent {

  private $db;
  private $dbname;
  private $mongo_connection;

  public function __construct() {
    // őt használjuk
    $this->dbname = C_MONGODB['dbname'];
    $this->mongo_connection = 'mongodb://' . C_MONGODB['host'] . ':' . C_MONGODB['port'];
    $this->connect();
    $this->Cache = new CacheComponent();
  }


  /**
   * Kapcsolódás
   */
  public function connect() {
    $this->db = new \MongoDB\Driver\Manager($this->mongo_connection);
  }


  /*
   * dokumentumok keresése
   */
  public function find($collection, $filter = [], $options = []) {

    //$this->connect();

    $array = false;
    if (@$options['cache']['name'] != '') {
      $array = $this->Cache->get($options['cache']['name']);
    }
    if (!$array) {
      if (isset($options['sort']) && !isset($options['collation'])) {
        $options['collation'] = ['locale' => 'hu'];
      }
      $query = new \MongoDB\Driver\Query($this->_handle_filter($filter), $options);
      $cursor = $this->db->executeQuery($this->dbname . '.' . $collection, $query);
      if ($cursor != null) {
        $array = $cursor->toArray();
      }

      if (@$options['cache']['name'] != '') {
        $this->Cache->set(
          $options['cache']['name'],
          $array,
          @$options['cache']['expiration'] ? $options['cache']['expiration'] : 'queries'
        );
      }
      return $array;
    }
    return false;
  }



  /*
   * 1 dokumentum _id alapján
   */
  public function first($collection, $filter = '', $options = []) {
    if (!is_array($filter)) {
      $filter = ['_id' => $filter];
    }

    $result = $this->find_array(
      $collection, $filter, $options
    );

    if (count($result) == 1) {
      $row = json_decode(json_encode($result[0]), true);
      return $row;
    }
  }



  /*
   * Sima tömböt ad
   */
  public function find_array($collection, $filter = [], $options = []) {
    $array = false;
    if (@$options['cache']['name'] != '') {
      $array = $this->Cache->get($options['cache']['name']);
      $cache = $options['cache'];
      unset($options['cache']);
    }

    if (!$array) {
      $array = [];
      if (isset($options['sort']) && !isset($options['collation'])) {
        $options['collation'] = ['locale' => 'hu'];
      }
      $result = $this->find($collection, $filter, $options);

      if (isset($options['idlist']) && $options['idlist']) {
        foreach ($result as $item) {
          $array[(string)$item->_id] = $this->arraize($item);
        }
      } else {
        foreach ($result as $item) {
          $array[] = $this->arraize($item);
        }
      }

      if (@$cache['name'] != '' && $cache['name'] != false) {
        $this->Cache->set(
          $cache['name'],
          $array,
          @$cache['expiration'] ? $cache['expiration'] : 'queries'
        );
      }
    }

    return $array;
  }



  /*
   * Asszociatív tömböt ad megadott kulcs / érték mezőkre
   */
  public function find_assoc($collection, $key_field, $filter = [], $options = []) {
    $result = $this->find($collection, $filter, $options);
    $array = array();
    foreach ($result as $item) {
      $item = (array)$item;
      if ($key_field == '_id') {
        $key = (string)$item[$key_field];
      } else {
        $key = $item[$key_field];
      }
      $array[$key] = $item;
    }
    return $array;
  }



  /*
   * dokumentumok száma
   */
  public function count($collection, $filter = [], $options = []) {
    if ($collection) {
      if (count((array)$options) == 0) {
        $options = ['projection' => ['_id' => 1]];
      }
      $cursor = $this->find($collection, $filter, $options);
      return $cursor != null && count($cursor) > 0 ? count($cursor) : 0;
    }
    return false;
  }



  /*
   * grouppolás
   */
  public function group($collection, $field, $filter = [], $options = []) {
    $options = (array)$options + [
      'summarize' => false,
      'arraize' => true,
    ];

    if ($collection) {
      $group = ['_id' => '$' . $field];
      if ($options['summarize']) {
        $group['count'] = ['$sum' => 1];
      }

      $pipeline = [
        ['$group' => $group]
      ];

      if (count($filter) > 0) {
        $pipeline[] = ['$match' => $filter];
      }

      $cursor = $this->aggregate($collection, $pipeline);

      if ($options['arraize']) {
        $array = [];
        if (count($cursor) > 0) {
          foreach ($cursor as $item) {
            $array[] = !$options['summarize'] ? $item->_id : [
              $item->_id, $item->count
            ];
          }
        }
        return $array;
      }

      return $cursor != null && count($cursor) > 0 ? $cursor : [];
    }
    return false;
  }


  /*
   * aggregálás, cache logikával
   *
   * BAKKER: A SORT A PIPELINE TÖMBBEN ELŐBB KELL, HOGY LEGYEN,
   * MINT A LIMIT, különben "látszólagos" sorrendezést csinál. Látszólagos, mert
   * majd nem jó, de mégis hibás. BAKKER.
   *
   */
  public function aggregate($collection, $pipeline = [], $options = []) {
    if ($collection) {
      $array = false;
      if (@$options['cache']['name'] != '') {
        $array = $this->Cache->get($options['cache']['name']);
        $cache = $options['cache'];
        unset($options['cache']);
      }

      if (!$array) {
        $command = new \MongoDB\Driver\Command([
          'aggregate' => $collection,
          'pipeline' => $pipeline,
          'cursor' => new \stdClass,
        ]);
        $cursor = $this->db->executeCommand($this->dbname, $command);
        $array = $cursor->toArray();

        if (@$cache['name'] != '') {
          $this->Cache->set(
            $cache['name'],
            $array,
            @$cache['expiration'] ? $cache['expiration'] : 'queries'
          );
        }
      }

      return $array;
    }
    return false;
  }



  /*
   * distinct
   * limit, sort és minden nélkül
   * mire jó? @todo
   */
  public function distinct($collection, $key, $filter = []) {
    if ($collection) {
      $array = false;
      if (!$array) {
        $command = new \MongoDB\Driver\Command([
          'distinct' => $collection,
          'key' => $key,
          'query' => $this->_handle_filter($filter),
        ]);
        $cursor = $this->db->executeCommand($this->dbname, $command);
        $array = $cursor->toArray();
      }

      return $array;
    }
    return false;
  }



  /*
   * insert, akár több sorra is OK
   */
  public function insert($collection, $data = [], $multi = false, $arraize = false) {
    $data = $this->_handle_data($data, $arraize);

    $bulk = new \MongoDB\Driver\BulkWrite;

    if ($multi) {
      $inserted_ids = [];
      foreach ($data as $doc) {
        $inserted_ids[] = (string)$bulk->insert($doc);
      }
    } else {
      $inserted_ids = (string)$bulk->insert($data);
    }

    $this->db->executeBulkWrite($this->dbname . '.' . $collection, $bulk);

    if ($inserted_ids) {
      return $inserted_ids;
    }
  }



  /*
   * update N sorra
   */
  public function update($collection, $data = [], $filter = [], $upsert = false, $multi = true, $arraize = false) {
    $bulk = new \MongoDB\Driver\BulkWrite;

    if (isset($data['id'])) {
      unset($data['id']);
    }

    $bulk->update(
      $this->_handle_filter($filter), ['$set' => $this->_handle_data($data, $arraize)], ['multi' => $multi, 'upsert' => $upsert]
    );

    $result = $this->db->executeBulkWrite($this->dbname . '.' . $collection, $bulk);


    return $result;
  }



  /*
   *  upsert, aha.
   */
  public function upsert($collection, $data = [], $filter = []) {
    if (isset($data['_id']) || isset($data['id']) || count($filter) > 0 ) {
      if (count($filter) == 0) {
        $filter['_id'] = isset($data['_id']) ? $data['_id'] : $data['id'];
        unset($data['_id']);
        unset($data['id']);
      }
      return $this->update($collection, $data, $filter, true, false);
    } else {
      // Ha nincs filter és a datában sem jön elsődleges kulcs,
      // akkor ez igazából insert
      return $this->insert($collection, $data);
    }
  }



  /*
   * delete N sorra
   */
  public function delete($collection, $filter = [], $delete_all = true) {
    $bulk = new \MongoDB\Driver\BulkWrite;

    $bulk->delete(
      $this->_handle_filter($filter), ['limit' => $delete_all ? 0 : 1]
    );

    $result = $this->db->executeBulkWrite($this->dbname . '.' . $collection, $bulk);

    return $result;
  }



  /*
   * Az ID-t átalakítja, ha van a filterben (és reális)
   */
  private function _handle_filter($filter) {
    if (isset($filter['_id']) && is_string($filter['_id']) && strlen($filter['_id']) > 5) {
      $filter['_id'] = new \MongoDB\BSON\ObjectId($filter['_id']);
    } elseif (isset($filter['$and']) && count($filter['$and']) == 0) {
      // Üres and tömb jött be; bár nem tudom, ez OK-e, hogy ilyen hülyebiztos
      $filter = [];
    }

    // Végigpörgetjük a belső or / and tömböket is, hátha ilyen a feltétel
    // @todo: valami array_map kellene, mert ez förtelmes
    // (persze az array_map belsejében ugyanez megy... :))
    for ($i = 0; $i<=5; $i++) {
      if (isset($filter['$and'][$i]['$or'])) {
        foreach ($filter['$and'][$i]['$or'] as $key => $value) {
          if (isset($value['_id'])) {
            $filter['$and'][$i]['$or'][$key]['_id'] = new \MongoDB\BSON\ObjectId($value['_id']);
          }
        }
      } elseif (isset($filter[$i]['$or'])) {
        foreach ($filter[$i]['$or'] as $key => $value) {
          if (isset($value['_id'])) {
            $filter[$i]['$or'][$key]['_id'] = new \MongoDB\BSON\ObjectId($value['_id']);
          }
        }
      }
    }


    return $filter;
  }



  /*
   * Mindenféle előkezelés - bővítés alatt!
   * A numerikus mezőket (int)-té alakítom
   * Tud array-t is, ha kell
   */
  private function _handle_data($array, $arraize = false) {
    if ($arraize) {
      array_walk_recursive($array, function (&$value, &$key) {
        if (is_int($value)) {
          $value = (int)$value;
        } elseif (is_float($value)) {
          $value = (float)$value;
        } else {
          $v_array = json_decode($value);
        }
        if (isset($v_array) && is_array($v_array)) {
          $value = $v_array;
        }
      });
    } else {
      array_walk_recursive($array, function (&$value, &$key) {
        if (is_int($value)) {
          $value = (int)$value;
        } elseif (is_float($value)) {
          $value = (float)$value;
        }
      });
    }
    return $array;
  }


  /**
   *
   * Mongo objektumot tömbbé alakítunk
   * itt is, de máshol is meghívhatjuk, ha épp objektumként is jöhet
   * az adott elem
   *
   * @param $item
   * @return array|mixed
   */
  public function arraize ($item) {
    if (!is_array($item)) {
      $item = (array)$item;
      if (isset($item['_id'])) {
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
      }
      $item = json_decode(json_encode($item), true);
    }

    return $item;
  }

}
