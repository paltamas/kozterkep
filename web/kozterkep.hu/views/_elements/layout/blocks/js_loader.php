<?php
/*
 * Ezt most megőrzésre áttettem ide.
 * <script src="//code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.js" crossorigin="anonymous">*/

/**
 * * az a logika itt, hogy ha APP['minify'] == true, akkor begyűjtünk minden
 * src fájlt, ebből megépítjük, hogy milyen "classokat" kell inicializálni, és
 * a végén behívjuk a build.js-t. Ha az URL-ben ?minify szerepel, akkor
 * generálunk egy új buildet.
 *
 * Ha az APP['minify'] == false, akkor nincs minifájolás, csak a scriptek, külön.
 */

// Ebbe gyűjtjük a path-okat a minify-nak
$file_paths = [];
$file_paths_vendors = [];

// Vendor JS betöltés
// abc-ben megy, ezért kellett a jquery és a popper-fájlt alulvonással kezdeni
$vendor_folder = CORE['PATHS']['WEB'] . '/' . APP['path'] . '/webroot/js/vendor/';
$files = $app->File->scan_dir($vendor_folder);
foreach ($files as $file) {
  if (strpos($file, 'map') !== false) {
    continue;
  }
  echo !APP['minify'] ? PHP_EOL . '<script src="/js/vendor/' . $file . '"></script>' : '';
  $file_paths_vendors[] = 'vendor/' . $file;
}


/**
 * SAJÁT SCRIPTEK
 */

echo !APP['minify'] ? PHP_EOL . '<script src="/js/src/basefunctions.js"></script>' : '';
$file_paths[] = 'src/basefunctions.js';


// sDB JS-be!
echo !APP['minify'] ? PHP_EOL . '<script src="/js/src/sdb.php"></script>' : '';
$file_paths[] = 'src/sdb.php';

// SAJÁT JS modulok betöltése, ami van, mind
$app_folder = CORE['PATHS']['WEB'] . '/' . APP['path'] . '/webroot/js/src/';


// Itt építjük az initet is, amivel JS-ben
// inicializáljuk a modulokat alant
$inits = PHP_EOL;
$files = $app->File->scan_dir($app_folder);

foreach ($files as $file) {

  // Kiírjuk
  echo !APP['minify'] ? PHP_EOL . '<script src="/js/src/' . $file . '?' . uniqid() . '"></script>' : '';
  $file_paths[] = 'src/' . $file;

  // Mudul inicializáláshoz
  // csak js-eket
  if (strpos($file, '.php') !== false) {
    continue;
  }
  // alkönyvtárban van
  if (strpos($file, '/') !== false) {
    $p = explode('/', $file);
    $file = $p[1];
  }
  // modul initek összeszedése
  $module_name = str_replace('.js', '', ucfirst($file));
  $inits .= '    ' . $module_name . '.init();' . PHP_EOL;
}

$init_scripts = '  window.onload = function(){' . $inits . '  }';
$file_paths[] = $init_scripts;

?>

<script>
  // Köztérkép változók, hello.
  var $app = {
    // alap utvonal
    'path': '/',
    // csrf token; hogy te az vagy, akire gondolunk
    'token': '<?=$app->csrf_token()?>',
    // aktualis kiirt uzenet
    'flash': <?=@$_params->query['flash'] != ''
      ? '[' . str_replace("&#39;", "'", urldecode($_params->query['flash'])) . ']'
      : $app->flash()?>,
    // itt futunk, szaladunk
    'domain': '<?=$_params->domain?>',
    // itt vagy most
    'here': '<?=$_params->here?>',
    // itt vagy most, de varok nelkul
    'here_path': '<?=$_params->path?>',
    // KT framework action
    'action': '<?=$_params->action?>',
    // URL hash (az)
    'hash': window.location.hash,
    // belepett user-e vagy. ha atirod true-ra, meg nem leptetunk be am ;]
    'auth': <?=$_user ? 'true' : 'false'?>,
    // user hash, uhum.
    'user_hash': '<?=$_user ? md5($_user['id']) : 'x'?>',
    // notification && message pause
    'user_pause': <?=$_user ? $_user['pause'] : 0?>,
    // notification pause
    'user_notification_pause': <?=$_user ? $_user['notification_pause'] : 0?>,
    // session
    'page_session': '<?=uid()?>',
    // amazon path
    's3_photos': '<?=C_WS_S3['url']?>photos/',
    // frissitesi intervallumok
    'latest_interval': <?=APP['intervals']['latest']?>,
    'conversation_thread_interval': <?=APP['intervals']['conversation_thread']?>,
    'comment_thread_interval': <?=APP['intervals']['comment_thread']?>,
    'time_interval': <?=APP['intervals']['times']?>,
    'visit_interval': <?=APP['intervals']['visit']?>,
    // interval, timeout ID-k
    'ic': ['latest', 'conversation_thread', 'comment_thread', 'time_updates', 'visits'],
    // teljes szelessegu nezetben vagyunk-e
    'fluid_view': <?=$app->ts('fluid_view') == 1 ? 1 : 0?>,
    // fajl upload tarolo
    'files_to_upload': [],
    // visit page és id
    'model': '<?=@$_model != '' ? $_model : ''?>',
    'model_id': '<?=@$_model_id != '' ? $_model_id : ''?>',
    // ide dobunk
    'redirect_hash': '',
    'redirect_tab': '',
  };<?=!APP['minify'] ? PHP_EOL . $init_scripts : PHP_EOL?>
</script>
<?php
// Minify futtatása, ha prod környezetben vagyunk,
// és az URL-ben beadjuk, hogy ?minify
if (APP['minify'] && isset($_params->query['minify'])) {
  $app->Html->minify_js($file_paths, 'build');
  $app->Html->minify_js($file_paths_vendors, 'vendors');
}


// Minify-olt script beolvasása, csak prod környezetben
$dev_id = CORE['ENV'] == 'dev' ? date('ymd') : '';
echo APP['minify'] ? PHP_EOL . '<script src="/js/app/vendors.min.js?' . CORE['VER'] . $dev_id . '"></script>' : '';
echo APP['minify'] ? PHP_EOL . '<script src="/js/app/build.min.js?' . CORE['VER'] . $dev_id . '"></script>' : '';

?>

</body>
</html>