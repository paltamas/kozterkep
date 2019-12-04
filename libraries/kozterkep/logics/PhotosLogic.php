<?php

namespace Kozterkep;

class PhotosLogic {

  private $app_config;

  public function __construct($app_config, $DB) {
    $this->Cache = new CacheComponent();
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->Cache = new CacheComponent();

    $this->Array = new ArraysHelper();
    $this->File = new FileHelper($this->DB, $this->Mongo);

    $this->Artpieces = new ArtpiecesLogic($app_config, $this->DB);
    $this->Users = new UsersLogic($app_config, $this->DB);
  }

  /**
   *
   * Egyik műlapról másikra pakolás
   *
   * @param $photo
   * @param $artpiece_to_remove
   * @param $artpiece_to_add
   * @param bool $update
   * @param bool $artpieces
   * @return array|bool|mixed
   */
  public function artpiece_switch($photo, $artpiece_to_remove, $artpiece_to_add, $update = true, $artpieces = false) {
    if (is_numeric($photo)) {
      $photo = $this->DB->first('photos', $photo, ['fields' => ['id', 'artpiece_id', 'artpieces']]);
      if (!$photo) {
        return false;
      }
    }

    if ($artpieces === false) {
      $artpieces = _json_decode($photo['artpieces']);
    }

    $artpieces = $this->artpiece_remove($photo, $artpiece_to_remove, false, $artpieces);
    $artpieces = $this->artpiece_add($photo, $artpiece_to_add, false, $artpieces);

    // Kell-e menteni
    if ($update) {
      // Mentéskor csak értékes tömböt mentünk
      $json = _json_encode(array_values($artpieces), false, false);
      return $this->DB->update('photos', [
        'artpiece_id' => $artpiece_to_add,
        'artpieces' => $json,
        'modified' => time()
      ], $photo['id']);
    } else {
      return $artpieces;
    }
  }


  /**
   *
   * Műlap bepakolása fotóhoz
   *
   * @param $photo
   * @param $artpiece_id
   * @param bool $update
   * @param bool $artpieces
   * @return array|bool|mixed
   */
  public function artpiece_add($photo, $artpiece_id, $update = true, $artpieces = false) {
    if (is_numeric($photo)) {
      $photo = $this->DB->first('photos', $photo, ['fields' => ['id', 'artpieces']]);
      if (!$photo) {
        return false;
      }
    }

    // Ha nem kaptunk kezdő tömböt, akkor a fotóét használjuk
    if ($artpieces === false) {
      $artpieces = _json_decode($photo['artpieces']);
    }

    // Ha még nincs benne, akkor tesszük csak be
    $key = array_search($artpiece_id, $artpieces);
    if (!$key) {
      $artpieces[] = (string)$artpiece_id;
    }

    // Hmm, hátha (összevissza pakolgatás, akármi)
    $artpieces = array_unique($artpieces);

    // Kell-e menteni
    if ($update) {
      // Mentéskor csak értékes tömböt mentünk
      $json = _json_encode(array_values($artpieces), false, false);
      return $this->DB->update('photos', [
        'artpieces' => $json,
        'modified' => time()
      ], $photo['id']);
    } else {
      return $artpieces;
    }
  }


  /**
   *
   * Műlap kiszedése fotónál
   *
   * @param $photo
   * @param $artpiece_id
   * @param bool $update
   * @param bool $artpieces
   * @return array|bool|mixed
   */
  public function artpiece_remove($photo, $artpiece_id, $update = true, $artpieces = false) {
    if (is_numeric($photo)) {
      $photo = $this->DB->first('photos', $photo, ['fields' => ['id', 'artpieces']]);
      if (!$photo) {
        return false;
      }
    }

    // Ha nem kaptunk kezdő tömböt, akkor a fotóét használjuk
    if ($artpieces === false) {
      $artpieces = _json_decode($photo['artpieces']);
    }

    // Kiezsedjük a tömbből, ha benne van
    $key = array_search($artpiece_id, $artpieces);
    if ($key > -1) {
      unset($artpieces[$key]);
    }

    // Hmm, hátha (összevissza pakolgatás, akármi)
    $artpieces = array_unique($artpieces);

    // Rá kell-e menteni a fotóra, vagy csak adjuk vissza és majd ott...
    if ($update) {
      // Mentéskor csak értékes tömböt mentünk
      $json = _json_encode(array_values($artpieces), false, false);
      return $this->DB->update('photos', ['artpieces' => $json, 'modified' => time()], $photo['id']);
    } else {
      return $artpieces;
    }
  }


  /**
   *
   * Fotó teljes törlése, fájlokkal együtt, mindenhonnan
   * kell hozzá a user tömb, mert nincs bizodalom, hogy ellenőrzés
   * után hívtuk ;)
   *
   * @todo: logolni kellene sztem
   *
   * @param $photo
   * @param $user
   * @return bool
   */
  public function delete($photo, $user) {
    if (is_numeric($photo)) {
      $photo = $this->DB->first('photos', $photo, ['fields' => ['id', 'slug', 'original_slug', 'user_id', 'artpieces', 'artist_id', 'sign_artist_id', 'portrait_artist_id']]);
      if (!$photo) {
        return false;
      }
    }

    if (!$this->Users->owner_or_head($photo, $user)) {
      return false;
    }

    @unlink(CORE['PATHS']['DATA'] . '/s3gate/originals/' . $photo['original_slug'] . '.jpg');
    $this->File->s3_delete('photos/' . $photo['original_slug'] . '.jpg');
    for ($i = 1; $i <= 8; $i++) {
      @unlink(CORE['PATHS']['DATA'] . '/s3gate/photos/' . $photo['slug'] . '_' . $i . '.jpg');
      $this->File->s3_delete('photos/' . $photo['slug'] . '_' . $i . '.jpg');
    }

    // Fotó törlése DB-ből (slug alapján, mert ha másolatok is voltak, akkor így tudjuk megfogni)
    $this->DB->delete('photos', ['slug' => $photo['slug']]);

    // Minden érintett műlap photos regenerate
    $artpieces = _json_decode($photo['artpieces']);
    foreach ($artpieces as $a_id) {
      $this->Artpieces->update_photos_field($a_id);
    }

    // Ha alkotó van a képen valahogy, akkor ott is cache-törlés
    foreach (['artist_id', 'sign_artist_id', 'portrait_artist_id'] as $field) {
      if (@$photo[$field] > 0) {
        $this->Cache->delete('cached-view-artists-view-' . $photo[$field]);
      }
    }

    return true;
  }

}

