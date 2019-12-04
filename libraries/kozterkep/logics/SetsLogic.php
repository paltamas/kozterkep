<?php
namespace Kozterkep;

class SetsLogic {

  public function __construct($DB, $Mongo) {
    $this->DB = $DB;
    $this->Mongo = $Mongo;
    $this->MC = new MemcacheComponent();
  }


  /**
   *
   * Helység név
   *
   * @param $place
   * @param array $options
   * @return string
   */
  public function get_artpieces($set, $options = []) {
    $options = (array)$options + [
      'only_ids' => true
    ];

    $artpieces = [];

    if (@count(@$set['artpieces']) > 0) {
      foreach ($set['artpieces'] as $a) {
        $artpiece = $this->MC->t('artpieces', $a['artpiece_id']);
        if (@$artpiece['status_id'] == 5) {
          $artpieces[] = $options['only_ids'] ? $artpiece['id'] : $artpiece;
        }
      }
    }

    return $artpieces;
  }

}