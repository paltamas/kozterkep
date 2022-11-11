<?php
if (!isset($_GET['step'])) {
  die('hianyzik a &step= valtozo');
}

if ($_GET['step'] == 1) {
  $results = mysqli_query($link, "truncate artpieces");
  $results = mysqli_query($link, "update artworks set synced = 0 where id > 0");
}

$results = mysqli_query($link, "select * from artworks where synced = 0 order by id asc limit 5000");

$all_artists_ = mysqli_query($link, "select id,name from artists");
$all_artists = [];
while ($row = mysqli_fetch_array($all_artists_)) {
  $all_artists[$row['id']] = $row['name'];
}

$all_cities_ = mysqli_query($link, "select id,name from cities");
$all_cities = [];
while ($row = mysqli_fetch_array($all_cities_)) {
  $all_cities[$row['name']] = $row['id'];
}

$all_institutions_ = mysqli_query($link, "select id,name,zip,city,address from institutions");
$all_institutions = [];
while ($row = mysqli_fetch_array($all_institutions_)) {
  $address = '';
  $address .= $row['zip'] > 0 ? $row['zip'] . ' ' : '';
  $address .= $row['city'] != '' ? $row['city'] : '';
  $address .= $row['address'] != '' ? ', ' . $row['address'] : '';

  $all_institutions[$row['id']] = [
    'name' => $row['name'],
    'city' => $row['city'],
    'address' => $address,
  ];
}

while ($row = mysqli_fetch_array($results)) {

  // Alkotók kiolvasása és JSON-né alakítása
  $artists = [];
  $as = mysqli_query($link, "select artist_id from artist_artworks where artwork_id = " . $row['id']);
  if ($as->num_rows > 0) {
    while ($a = mysqli_fetch_array($as)) {
      $artists[] = [
        (string)$a['artist_id'],
        $all_artists[$a['artist_id']]
      ];
    }
  }
  $artists = count($artists) == 0 ? '' : json_encode($artists, JSON_UNESCAPED_UNICODE);


  // Megjegyzések különböző mezőkből és táblákból
  $memos = [];
  for ($i=1;$i<=3;$i++) {
    $ms = mysqli_query($link, "select aw" . $i . "memo_id as memoid from artwork_aw" . $i. "memos where artwork_id = " . $row['id']);
    if ($ms->num_rows > 0) {
      while ($m = mysqli_fetch_array($ms)) {
        $memo = mysqli_fetch_array(mysqli_query($link, "select memo from aw" . $i. "memos where id = " . $m['memoid']));
        if ($memo != NULL) {
          $memos[] = ['aw' . $i. 'memos', str_replace("'", "&#39;", $memo['memo'])];
        }
      }
    }
  }
  $memos = count($memos) == 0 ? '' : json_encode($memos, JSON_UNESCAPED_UNICODE);


  // Intézmények (ahova került, és aki rendelte)
  // és az elhelyezésből következő település
  $placement_inst_name = $placement_inst_address = '';
  $customer_inst_name = $customer_inst_address = '';
  $city_name = '';
  $customer_inst_id = $placement_inst_id = $city_id = 0;
  $ais = mysqli_query($link, "select institution_id, artwork_institutions_x1 from artwork_institutions 
    where artwork_id = " . $row['id'] . " and artwork_institutions_x1 in (135,139)");
  if ($ais->num_rows > 0) {
    while ($ai = mysqli_fetch_array($ais)) {
      $institution = $all_institutions[$ai['institution_id']];

      if ($ai['artwork_institutions_x1'] == 135) {
        // Ahova kerül
        $placement_inst_id = $ai['institution_id'];
        $placement_inst_name = $institution['name'];
        $placement_inst_address = $institution['address'];
        $city_name = $institution['city'];
        $city_id = @$all_cities[$institution['city']] > 0 ? $all_cities[$institution['city']] : 0;
      } else {
        // Megrendelő
        $customer_inst_id = $ai['institution_id'];
        $customer_inst_name = $institution['name'];
        $customer_inst_address = $institution['address'];
      }
    }
  }


  // Beszúrás
  $query = "insert into artpieces 
    (id, dossier_id, title, 
    date_contract, date_unveil, 
    database_date, amount, 
    type, postament,
    work_type, work_ratio, artists, 
    placement_inst_name, placement_inst_address, placement_inst_id,
    customer_inst_name, customer_inst_address, customer_inst_id,
    city, city_id, memos, width, height, depth, unit, 
    p_width, p_height, p_depth, p_unit) 
    VALUES 
    (" . $row['id'] . ", '" . $row['photo_size'] . "', '" . str_replace("'", "&#39;", $row['title']) . "', 
    '" . date_convert($row['artwork_x9']) . "', '" . date_convert($row['artwork_x10']) . "', 
    '" . date_convert($row['artwork_x1']) . "', '" . $row['artwork_x8'] . "', 
    '" . str_replace("'", "&#39;", $row['type']) . "', '" . str_replace("'", "&#39;", $row['postament']) . "', 
    '" . str_replace("'", "&#39;", $row['work_type']) . "', '" . str_replace("'", "&#39;", $row['work_ratio']) . "', '" . $artists . "',
    '" . str_replace("'", "&#39;", $placement_inst_name) . "', '" . str_replace("'", "&#39;", $placement_inst_address) . "', " . (int)$placement_inst_id . ",
    '" . str_replace("'", "&#39;", $customer_inst_name) . "', '" . str_replace("'", "&#39;", $customer_inst_address) . "', " . (int)$customer_inst_id . ",
    '" . str_replace("'", "&#39;", $city_name) . "', " . (int)$city_id . ", '" . $memos . "', 
    " . $row['artwork_x4'] . ", " . $row['artwork_x5'] . ", " . $row['artwork_x6'] . ", '" . $row['unit'] . "',
    " . $row['artwork_x13'] . ", " . $row['artwork_x14'] . ", " . $row['artwork_x15'] . ", '" . $row['unit2'] . "')
  ";
  $s = mysqli_query($link, $query);

  if (!$s) {
    debug('beszúrási hiba');
    echo mysqli_error($link);
    debug($query);
    debug($row); exit;
  }

  mysqli_query($link, "update artworks set synced = 1 where id = " . $row['id']);
}