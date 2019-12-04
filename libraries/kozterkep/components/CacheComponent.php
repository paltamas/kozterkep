<?php
namespace Kozterkep;

/**
 * FileCache
 *
 * http://github.com/inouet/file-cache/
 *
 * A simple PHP class for caching data in the filesystem.
 *
 * License
 *   This software is released under the MIT License, see LICENSE.txt.
 *
 * @package FileCache
 * @author  Taiji Inoue <inudog@gmail.com>
 * 
 * Néhány dolgot átírtam benne:
 *  - dir nem paraméter, fix
 * 
 */


class CacheComponent {

  /**
   * The root cache directory.
   * @var string
   */
  private $cache_dir;

  /**
   * Creates a FileCache object
   */
  public function __construct() {
    $this->cache_dir = CORE['PATHS']['CACHE'];
    $this->cache_types = C_CACHE_TYPES;
  }

  /**
   * Fetches an entry from the cache.
   *
   * @param string $id
   */
  public function get($id) {
    $file_name = $this->getFileName($id);

    if (!is_file($file_name) || !is_readable($file_name)) {
      return false;
    }

    $lines = file($file_name);
    $lifetime = array_shift($lines);
    $lifetime = (int) trim($lifetime);

    if ($lifetime !== 0 && $lifetime < time()) {
      @unlink($file_name);
      return false;
    }
    $serialized = join('', $lines);
    $data = unserialize($serialized);
    return $data;
  }

  /**
   * Deletes a cache entry.
   *
   * @param string $id
   *
   * @return bool
   */
  public function delete($id) {
    $file_name = $this->getFileName($id);
    return is_file($file_name) ? unlink($file_name) : true;
  }

  /**
   * Puts data into the cache.
   *
   * @param string $id
   * @param mixed  $data
   * @param string $type
   *
   * @return bool
   */

  public function set($id, $data, $type = 'view_short') {
    $dir = $this->getDirectory($id);
    if (!is_dir($dir)) {
      if (!mkdir($dir, 0755, true)) {
        return false;
      }
    }
    $file_name = $this->getFileName($id);
    // Itt behoztam a típust, hogy konfigban piszkálhassuk az időket,
    // de jöhet szám is, és akkor annyi.
    $keep_live = $type > 0 ? $type : $this->cache_types[$type];
    $lifetime = time() + $keep_live;
    $serialized = serialize($data);
    $result = file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);
    if ($result === false) {
      return false;
    }
    return true;
  }


  /**
   *
   * Tömböt tartalmazó cache megadott kulcsainak értékét frissíti,
   * de tud bővíteni is tömböt új kulcs-érték párokkal, ha még
   * nem szerepelnek benne.
   *
   * @param $id
   * @param $updates
   * @return bool
   */
  public function update($id, $updates) {
    if ($cached = $this->get($id)) {
      if (is_array($cached)) {
        $cached = array_merge($cached, $updates);
        return $this->set($id, $cached);
      }
    }
    return false;
  }

  //------------------------------------------------
  // PRIVATE METHODS
  //------------------------------------------------

  /**
   * Fetches a directory to store the cache data
   *
   * @param string $id
   *
   * @return string
   */
  protected function getDirectory($id) {
    $hash = sha1($id, false);
    $dirs = array(
      $this->getCacheDirectory(),
      substr($hash, 0, 2),
      substr($hash, 2, 2)
    );
    return join(DIRECTORY_SEPARATOR, $dirs);
  }

  /**
   * Fetches a base directory to store the cache data
   *
   * @return string
   */
  protected function getCacheDirectory() {
    return $this->cache_dir;
  }

  /**
   * Fetches a file path of the cache data
   *
   * @param string $id
   *
   * @return string
   */
  protected function getFileName($id) {
    $directory = $this->getDirectory($id);
    $hash = sha1($id, false);
    $file = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
    return $file;
  }

}