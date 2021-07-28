<?php

class ArtistsJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }

  public function recalc() {
    $options = self::$_options;

    $ids = [];

    if (isset($options['id'])) {
      if (is_int($options['id'])) {
        $ids[] = $options['id'];
      } else {
        $ids = array_merge($ids, $options['id']);
      }
    }

    if (isset($options['json']) && count($options['json']) > 0) {
      foreach ($options['json'] as $json) {
        $array = _json_decode($json);
        foreach ($array as $item) {
          if (@$item['id'] > 0) {
            $ids[] = $item['id'];
          }
        }
      }
    }

    if (count($ids) == 0 && @self::$_argv['id'] == 'full') {
      // teljes recalc - pl konzolról futtatott job esetén; csak óvatosan
      // php /var/www/kozterkep/shell/run.php artists recalc full
      $ids = $this->DB->find('artists', [
        'type' => 'fieldlist',
        'fields' => ['id'],
      ]);
    }

    if (count($ids) > 0) {
      foreach ($ids as $id) {
        $public_artpiece_count = $this->DB->count('artpieces', [
          'artists LIKE' => '%"id":' . $id . ',"%',
          'status_id' => 5,
        ]);

        $additional_count = $this->Mongo->count('artpiece_edits', [
          [
            ['artists' => ['$regex' => $id, '$options' => 'i']],
            ['status_id' => ['$ne' => 5]]
          ]
        ]);

        if ($additional_count == 0) {
          $additional_count = $this->DB->count('artpieces', [
            'artists LIKE' => '%"id":' . $id . ',"%',
            'status_id <' => 5,
          ]);
        }

        $last_artpiece = $this->DB->first('artpieces', [
          'artists LIKE' => '%"id":' . $id . ',"%',
          'status_id' => 5
        ], ['order' => 'published DESC']);

        $this->DB->update('artists', [
          'last_artpiece_id' => @$last_artpiece ? $last_artpiece['id'] : 0,
          'artpiece_count' => $public_artpiece_count,
          'all_artpiece_count' => $public_artpiece_count + $additional_count
        ], $id);

        $this->Cache->delete('cached-view-artists-view-' . $id);
      }
    }

    return true;
  }



  /**
   * Üres alkotók törlése, hogy ne terheljék
   * és zavarják össze az adatbázist
   */
  public function delete_empty() {

    $deletables = $this->DB->find('artists', [
      'conditions' => [
        'artpiece_count' => 0,
        'checked' => 0,
      ],
      'fields' => ['id']
    ]);

    if (count($deletables) > 0) {
      // Szerkesztések
      $edits = $this->Mongo->find_array('artpiece_edits', [
        'status_id' => ['$in' => [1,2,3,5]],
      ]);

      // Van-e passzoló várakozó szerkesztés
      foreach ($deletables as $deletable) {
        $found = false;
        foreach ($edits as $edit) {
          if (!isset($edit['artists'])) {
            continue;
          }
          foreach ($edit['artists'] as $artist) {
            if ($artist['id'] == $deletable['id']) {
              $found = true;
              break;
            }
          }
          if ($found) {
            break;
          }
        }

        $artpieces = $this->DB->count('artpieces', [
          'conditions' => [
            'artists LIKE' => '%"id":' . $deletable['id']. ',"%',
          ],
        ]);

        if (!$found && $artpieces == 0) {
          // Nincs, törlés
          $this->DB->delete('artists', $deletable['id']);
        }
      }
    }

    return true;
  }

}