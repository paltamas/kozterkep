<?php
namespace Kozterkep;

class LocationHelper {

  public function __construct() {
  }


  /**
   *
   * Két geolokáció távolsága
   *
   * @param $lat1
   * @param $lon1
   * @param $lat2
   * @param $lon2
   * @param string $unit
   * @return float|int
   */
  public function distance ($lat1, $lon1, $lat2, $lon2, $unit = 'm') {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
      return 0;
    } else {
      $radlat1 = M_PI * $lat1 / 180;
      $radlat2 = M_PI * $lat2 / 180;
      $theta = $lon1 - $lon2;
      $radtheta = M_PI * $theta / 180;
      $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
      if ($dist > 1) {
        $dist = 1;
      }
      $dist = acos($dist);
      $dist = $dist * 180 / M_PI;
      $dist = $dist * 60 * 1.1515;

      // km-re
      $dist = $dist * 1.609344;

      if ($unit == 'm') {
        $dist = round($dist * 1000);
      }

      return $dist;
    }
  }

}