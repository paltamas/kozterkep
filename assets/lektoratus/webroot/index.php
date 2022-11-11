<?php
set_time_limit(300000000);
ini_set('memory_limit', '10000M');

include '../includes/config.php';
_session_start();

$link = _db();

/*if (@$_SESSION['user'] == 'paltamas') {
  $diff = 731958;
  echo date('Y-m-d', strtotime('1900-01-00 + ' . $diff . ' days - 1900 years'));
  exit;
}*/

if (@$_SESSION['user'] == 'paltamas' && isset($_GET['build'])) {
  //die('build kiiktatva');
  include '../includes/build.php';
}


$max_results = 1000;

$fields = [
  'cim' => ['Cím', 'title'],
  'dosszie_az' => ['Dosszié', 'dossier_id'],
  'tipus' => ['Típus', 'type'],
  'kivitel' => ['Kivitel', 'work_type'],
  'alkotok' => ['Alkotó', 'artists', 1],
  'telepules' => ['Település', 'city', 1],
  'intezmeny_neve' => ['Intézmény neve', 'placement_inst_name'],
  'intezmeny_cime' => ['Intézmény címe', 'placement_inst_address'],
  'megrendelo_neve' => ['Megrendelő neve', 'customer_inst_name'],
  'megrendelo_cime' => ['Megrendelő címe', 'customer_inst_address'],
  'megjegyzes' => ['Megjegyzés', 'memos'],
];


if (@$_GET['alkoto_az'] > 0) {
  $artist = mysqli_fetch_array(mysqli_query($link, "select * from artists where id = " . (int)$_GET['alkoto_az']));
  if (@$artist['name'] != '') {
    $_GET['alkotok'] = $artist['name'];
    $_GET['alkotok-pontosan'] = 1;
  }
}

if (@$_GET['telepules_az'] > 0) {
  $city = mysqli_fetch_array(mysqli_query($link, "select * from cities where id = " . (int)$_GET['telepules_az']));
  if (@$city['name'] != '') {
    $_GET['telepules'] = $city['name'];
    $_GET['telepules-pontosan'] = 1;
  }
}

if (@$_GET['intezmeny_az'] > 0) {
  $institution = mysqli_fetch_array(mysqli_query($link, "select * from institutions where id = " . (int)$_GET['intezmeny_az']));
  if (@$institution['name'] != '') {
    $_GET['intezmeny_neve'] = $institution['name'];
  }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="Pál Tamás, Pál Zoltán">
  <title>Lektorátusi adatbázis</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link href="/fa/css/all.css" rel="stylesheet">
</head>
<body>
<?php
$user = auth();
if ($user) {

  echo '<div class="container-fluid">';

  // Fejléc
  echo '<div class="row bg-light p-3 mb-3">';
  echo '<div class="col">';
  echo '<h5><a href="/" class="text-dark">Lektorátus Adatbázis</a> <span class="text-muted small">rekonstrukció</span></h5>';
  echo '</div>';
  echo '<div class="col text-right">';
  echo 'üdv, <strong>' . $user . '</strong> &bull; <a href="/?kilepes">kilépés</a>';
  echo '</div>';
  echo '</div>';
  // Fejléc --


  if (!isset($_GET['alkotas_az'])) {

    // LISTÁZÓ NÉZET


    // Űrlap
    echo '<form method="get">';

    echo '<div class="row">';

    echo '<div class="col-md-2 col-6 form-group">';
    echo '<label for="kulcsszo">Keresés minden szöveges mezőben</label>';
    echo '<input type="text" class="form-control" name="kulcsszo" id="kulcsszo" value="' . @$_GET['kulcsszo'] . '">';
    echo '</div>';

    echo '<div class="col-md-2 col-6 form-group">';
    echo '<label for="kulcsszo">Felállítás év intervalluma</label>';
    echo '<div><input type="text" class="form-control" style="width: 70px; display: inline;" name="ev_min" id="ev_min" value="' . @$_GET['ev_min'] . '"> &mdash; ';
    echo '<input type="text" class="form-control" style="width: 70px; display: inline;" name="ev_max" id="ev_max" value="' . @$_GET['ev_max'] . '"></div>';
    echo '</div>';

    foreach ($fields as $key => $field) {
      $type = in_array($key, ['ev_min', 'ev_max']) ? 'date' : 'text';
      echo '<div class="col-md-2 col-6 form-group mb-0">';
      echo '<label for="cim">' . $field[0] . '</label>';
      echo '<input type="' . $type . '" class="form-control" name="' . $key . '" id="' . $key . '" value="' . @$_GET[$key] . '">';
      if (@$field[2] == 1) {
        echo '<div class="custom-control custom-checkbox mt-1">';
        echo '<input type="checkbox" class="custom-control-input" value="1" id="' . $key . '-pontosan" name="' . $key . '-pontosan"';
        echo @$_GET[$key . '-pontosan'] == 1 ? ' checked' : '';
        echo '>';
        echo '<label class="custom-control-label" for="' . $key . '-pontosan">Teljes egyezés kell</label>';
        echo '</div>';
      }
      echo '</div>';
    }

    echo '</div>';


    echo '<div class="my-2"><input type="submit" value="Keresés" class="btn btn-primary"></div>';
    echo '</form>';


    // Űrlap --


    // Adatlapok

    if (@$artist) {
      echo '<div class="my-4 border rounded bg-light p-3">';
      echo '<h3>' . $artist['name'];
      echo $artist['profession'] != '' ? '<span class="text-muted">, ' . $artist['profession'] . '</span>' : '';
      echo '</h3>';
      echo '<div class="">';
      echo $artist['zip'] > 0 ? $artist['zip'] : '';
      echo ' ' . $artist['city'];
      echo $artist['city'] != '' && $artist['address'] != '' ? ', ' : '';
      echo $artist['address'];
      echo '</div>';
      echo '</div>';
    }


    // Találati feltétel építés
    $conditions = '';

    // szabad szavas
    if (@$_GET['kulcsszo'] != '') {
      $k = trim($_GET['kulcsszo']);
      $conditions .= "(
        title like '%" . $k . "%' OR 
        type like '%" . $k . "%' OR 
        work_type like '%" . $k . "%' OR 
        artists like '%" . $k . "%' OR 
        placement_inst_name like '%" . $k . "%' OR 
        customer_inst_name like '%" . $k . "%' OR 
        city like '%" . $k . "%' OR 
        memos like '%" . $k . "%'
      ) AND";
    }

    if (@$_GET['ev_min'] > 0) {
      $conditions .= " date_unveil >= '" . (int)$_GET['ev_min'] . "-01-01' AND ";
    }
    if (@$_GET['ev_max'] > 0) {
      $conditions .= " date_unveil <= '" . (int)$_GET['ev_max'] . "-12-31' AND ";
    }

    foreach ($fields as $key => $field) {
      if (@$_GET[$key] != '') {
        if (@$_GET[$key . '-pontosan'] == 1) {
          // Teljes egyezés kell
          if ($key == 'alkotok') {
            // jsonos mezők
            $conditions .= $field[1] . " LIKE '%\"" . $_GET[$key] . "\"%' AND ";
          } else {
            // sima mezők
            $conditions .= $field[1] . " LIKE '" . $_GET[$key] . "' AND ";
          }
        } else {
          $conditions .= $field[1] . " LIKE '%" . $_GET[$key] . "%' AND ";
        }
      }
    }

    if (@$_GET['alkoto_az'] > 0) {
      $k = (int)$_GET['alkoto_az'];
      $conditions .= "artists like '%\"" . $k . "\"%' AND";
    }

    if (@$_GET['intezmeny_az'] > 0) {
      $k = (int)$_GET['intezmeny_az'];
      $conditions .= "placement_inst_id = " . $k . " AND";
    }

    if (@$_GET['telepules_az'] > 0) {
      $k = (int)$_GET['telepules_az'];
      $conditions .= "city_id = " . $k . " AND";
    }

    $conditions = rtrim(trim($conditions), ' AND');

    $conditions = $conditions != '' ? ' where ' . $conditions : '';

    $all = mysqli_query($link, "select id from artpieces " . $conditions);

    if ($_SESSION['user'] == 'paltamas') {
      echo '<div class="small text-muted p-2 my-3 border rounded"><strong>Lekérdezés:</strong> select * from artpieces ' . $conditions . '</div>';
    }

    if ($all->num_rows > $max_results) {
      echo '<p class="float-right"><span class="fad fa-exclamation-triangle mr-1 text-danger"></span>Egyszerre maximum ' . $max_results . ' találatot mutatunk.</p>';
    }

    echo '<p>Összesen ' . $all->num_rows . ' találat.';
    echo $conditions != '' ? ' <a href="/">Szűrőfeltételek törlése</a>' : '';
    echo '</p>';

    // Találati lista kiolvasása
    $results = mysqli_query($link, "select * from artpieces
      " . $conditions . " 
      order by id asc limit " . $max_results);


    // Találati lista táblázata
    echo '<div class="">';

    echo '<table class="table table-sm table-striped table-hover">';

    echo '<thead>';
    echo '<tr>';
    echo '<th>Cím</th>';
    echo '<th>Elhelyezés</th>';
    echo '<th>Dosszié</th>';
    echo '<th>Típus</th>';
    echo '<th>Kivitel</th>';
    echo '<th>Alkotó(k)</th>';
    echo '<th>Település</th>';
    echo '<th>Cím</th>';
    echo '<th>Intézmény</th>';
    echo '<th data-toggle="tooltip" title="Kapcsolódó megjegyzések száma">Megj.</th>';
    echo '</tr>';
    echo '</thead>';

    if ($results->num_rows > 0) {
      while ($row = mysqli_fetch_array($results)) {
        $artists = '';
        $artists_array = json_decode($row['artists'], true);
        if (is_array($artists_array) && count($artists_array) > 0) {
          $artists_array_ = [];
          foreach ($artists_array as $artist) {
            $artists_array_[] = '<a href="/?alkoto_az=' . $artist[0] . '">' . $artist[1] . '</a>';
          }
          $artists = implode('<br>', $artists_array_);
        }
        $memos_array = json_decode($row['memos'], true);
        $memo_count = is_array($memos_array) && count($memos_array) > 0 ? count($memos_array) : '-';
        $inst = '';
        if ($row['placement_inst_name'] != '') {
          $inst = '<a href="?intezmeny_az=' . $row['placement_inst_id'] . '">' . $row['placement_inst_name'] . '</a>';
        }
        echo '<tr>';
        echo '<td class="font-weight-bold small"><a href="/?alkotas_az=' . $row['id'] . '">' . $row['title'] . '</a></td>';
        echo '<td class="small">' , $row['date_unveil'] > '0000-00-00' ? date('Y', strtotime($row['date_unveil'])) : '-' , '</td>';
        echo '<td class="small">' . $row['dossier_id'] . '</td>';
        echo '<td class="small">' . $row['type'] . '</td>';
        echo '<td class="small">' . $row['work_type'] . '</td>';
        echo '<td class="small">' . $artists . '</td>';
        echo '<td class="small"><a href="/?telepules_az=' . $row['city_id'] . '">' . $row['city'] . '</a></td>';
        echo '<td class="small">' . $row['placement_inst_address'] . '</td>';
        echo '<td class="small">' . $inst . '</td>';
        echo '<td class="small">' . $memo_count . '</td>';
        echo '</tr>';
      }
    }

    echo '</table>';

    echo '</div>';
    // Találati lista --

  } else {


    // ADATLAP

    $artpiece = mysqli_fetch_array(mysqli_query($link, "select * from artpieces where id = " . (int)$_GET['alkotas_az']));

    if (@$artpiece['id'] > 0) {

      $artists = '';
      $artists_array = json_decode($artpiece['artists'], true);
      if (is_array($artists_array) && count($artists_array) > 0) {
        $artists_array_ = [];
        foreach ($artists_array as $artist) {
          $artists_array_[] = '<a href="/?alkoto_az=' . $artist[0] . '">' . $artist[1] . '</a>';
        }
        $artists = implode('<br>', $artists_array_);
      }

      echo '<div class="row d-flex justify-content-center">';

      echo '<div class="col-lg-4 col-md-5 col-12">';

      echo '<h2 class="text-center mb-4">' . $artpiece['title'] . '</h2>';

      echo '<table class="table">';

      echo '<tr>';
      echo '<td>Cím</td>';
      echo '<th>' . $artpiece['title'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Megbízás</td>';
      echo '<th>';
      echo $artpiece['date_contract'] > '0000-00-00' ? date('Y.m.d.', strtotime($artpiece['date_contract'])) : '-';
      echo '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Elhelyezés</td>';
      echo '<th>';
      echo $artpiece['date_unveil'] > '0000-00-00' ? date('Y.m.d.', strtotime($artpiece['date_unveil'])) : '-';
      echo '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Dosszié</td>';
      echo '<th>' . $artpiece['dossier_id'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Ár</td>';
      echo '<th>' , $artpiece['amount'] > 0 ? number_format((float)$artpiece['amount'], 0, '.', ' ') . ' Ft' : '' , '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Típus</td>';
      echo '<th>' . $artpiece['type'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Kivitel</td>';
      echo '<th>' . $artpiece['work_type'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Méretarány</td>';
      echo '<th>' . $artpiece['work_ratio'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Méret</td>';
      echo '<th>';
      if ($artpiece['width'] > 0) {
        echo $artpiece['width'];
      }
      if ($artpiece['height'] > 0) {
        echo ' × ' . $artpiece['height'];
      }
      if ($artpiece['depth'] > 0) {
        echo ' × ' . $artpiece['depth'];
      }
      echo $artpiece['unit'] != '' ? ' ' . $artpiece['unit'] : '';
      echo '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Posztamens</td>';
      $postament_size = '';
      if ($artpiece['p_width'] > 0 && $artpiece['p_height'] > 0) {
        $postament_size .= $artpiece['p_width'] . ' × ' . $artpiece['p_height'];
      }
      if ($artpiece['p_depth'] > 0) {
        $postament_size .= ' × ' . $artpiece['p_depth'];
      }
      $postament_size .= $artpiece['p_unit'] != '' ? ' ' . $artpiece['p_unit'] : '';
      echo '<th>' . $artpiece['postament'] . $postament_size . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Alkotók</td>';
      echo '<th>' . $artists . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Település</td>';
      echo '<th><a href="/?telepules_az=' . $artpiece['city_id'] . '">' . $artpiece['city'] . '</a></th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Elhelyezés intézménye</td>';
      echo '<th><a href="/?intezmeny_az=' . $artpiece['placement_inst_id'] . '">' . $artpiece['placement_inst_name'] . '</a></th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Elhelyezés címe</td>';
      echo '<th>' . $artpiece['placement_inst_address'] . '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Megrendelő intézmény</td>';
      echo '<th><a href="/?intezmeny_az=' . $artpiece['customer_inst_id'] . '">' . $artpiece['customer_inst_name'] . '</a></th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>Megrendelő címe</td>';
      echo '<th>' . $artpiece['customer_inst_address'] . '</th>';
      echo '</tr>';

      echo '</table>';

      $memos_array = json_decode($artpiece['memos'], true);
      if (is_array($memos_array) && count($memos_array) > 0) {
        foreach ($memos_array as $memo) {
          echo '<div class="p-2 bg-light border my-4 rounded font-italic">' . $memo[1] . '</div>';
        }
      }

      if ($artpiece['database_date'] > '0000-00-00') {
        echo '<p class="text-muted mt-5"><span class="fa fa-edit mr-1"></span>Adatbázis szerkesztés vagy létrehozás: ' . date('Y.m.d.', strtotime($artpiece['database_date'])) . '</p>';
      }

      echo '</div>';
      echo '</div>';


    } else {

      echo '<div class="alert alert-danger">Hibás azonosító.</div>';

    }


  }


  // Lábléc
  echo '<div class="row mt-5">';
  echo '<div class="col-12 my-5 p-4 bg-light text-center small">';
  echo '<p>Az adatok a Képzőművészeti Lektorátus "Domino" adatbázisának 2010. szeptemberi adatmentését tükrözik.</p><p>Az adatbázist feldolgozta: Pál Zoltán, a kereső felületet készítette: Pál Tamás @ Köztérkép (paltamas@gmail.com) &nbsp;&nbsp;&bull;&nbsp;&nbsp; v1.1 &ndash; 2019.10.18.</p>';
  echo '</div>';
  echo '</div>';
  // Lábléc --


  echo '</div>';

} else { ?>
  <div class="container p-5">
    <div class="row d-flex justify-content-center">
      <div class="col-3 border p-3 bg-light">
        <h4>Bejelentkezés</h4>
        <form method="post">
          <div class="my-3">
            <input type="text" name="user" placeholder="felhasználó" class="form-control form-control-lg">
          </div>
          <div class="my-3">
            <input type="password" name="pass" placeholder="jelszó" class="form-control form-control-lg">
          </div>
          <div class="my-3">
            <input type="submit" value="Belépés" class="btn btn-primary">
          </div>
        </form>
      </div>
    </div>
  </div>

<?php } ?>
<script src="/js/vendor/jquery-3.4.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="/js/app.js"></script>
</body>
</html>