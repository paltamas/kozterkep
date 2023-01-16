<?php
namespace Kozterkep;

class MemcacheComponent {

  private $mc;

  public function __construct() {
    $this->mc = new \Memcached(C_MEMCACHE['prefix'] . 'Mem'); // PHP beépített memcache class
    $list = $this->mc->getServerList();
    if (!is_array($list) || count($list) == 0) {
      $this->mc->addServer(C_MEMCACHE['host'], C_MEMCACHE['port']);
    }
  }

  public function __destruct() {
    $this->mc->quit();
  }



  /**
   *
   * Cachejob-on belül tárolt táblák kiolvasása
   *
   * @param $name
   * @param $id
   * @return bool|mixed
   */
  public function t($name, $id) {
    return $this->get('tables.' . $name . '.' . $id);
  }



  public function get($key, $trash_after_read = false) {
    $value = $this->mc->get(C_MEMCACHE['prefix'] . $key);

    if ($trash_after_read) {
      $this->delete(C_MEMCACHE['prefix'] . $key);
    }

    if ($value) {
      return $value;
    } else {
      return false;
    }
  }


  public function set($key, $value, $expire = 300) {
    if ($this->mc->set(C_MEMCACHE['prefix'] . $key, $value, $expire)) {
      return true;
    } else {
      return false;
    }
  }


  public function delete($key) {
    $this->mc->delete(C_MEMCACHE['prefix'] . $key);
    return true;
  }

}
