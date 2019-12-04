<?php

class PlacesJob extends Kozterkep\JobBase {

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

    if (count($ids) == 0 && @self::$_argv['id'] == 'full') {
      // teljes recalc - pl konzolról futtatott job esetén; csak óvatosan
      $ids = $this->DB->find('places', [
        'type' => 'fieldlist',
        'fields' => ['id'],
      ]);
    }

    if (count($ids) > 0) {
      foreach ($ids as $id) {
        $public_artpiece_count = $this->DB->count('artpieces', [
          'place_id' => $id,
          'status_id' => 5,
        ]);

        $additional_count = $this->Mongo->count('artpiece_edits', [
          [
            ['place_id' => $id],
            ['status_id' => ['$ne' => 5]]
          ]
        ]);

        if ($additional_count == 0) {
          $additional_count = $this->DB->count('artpieces', [
            'place_id' => $id,
            'status_id <' => 5,
          ]);
        }

        $last_artpiece = $this->DB->first('artpieces', [
          'place_id' => $id,
          'status_id' => 5
        ], ['order' => 'published DESC']);

        $this->DB->update('places', [
          'last_artpiece_id' => @$last_artpiece ? $last_artpiece['id'] : 0,
          'artpiece_count' => $public_artpiece_count,
          'all_artpiece_count' => $public_artpiece_count + $additional_count
        ], $id);

        $this->Cache->delete('cached-view-places-view-' . $id);
      }
    }

    return true;
  }



  /**
   * Üres alkotók törlése, hogy ne terheljék
   * és zavarják össze az adatbázist
   */
  public function delete_empty() {

    $deletables = $this->DB->find('places', [
      'conditions' => [
        'artpiece_count' => 0,
        'checked' => 0,
      ],
      'fields' => ['id']
    ]);

    if (count($deletables) > 0) {
      foreach ($deletables as $deletable) {
        // Szerkesztések
        $edits = $this->Mongo->count('artpiece_edits', [
          'status_id' => ['$in' => [1,2,3,5]],
          'place_id' => (int)$deletable['id']
        ]);

        $artpieces = $this->DB->count('artpieces', [
          'conditions' => [
            'place_id' => $deletable['id'],
          ],
        ]);

        if ($edits == 0 && $artpieces == 0) {
          // Nincs, törlés
          $this->DB->delete('places', $deletable['id']);
        }
      }
    }

    return true;
  }

}