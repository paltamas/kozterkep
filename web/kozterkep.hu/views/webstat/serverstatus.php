<p>Az alábbi értékek gyors áttekintést adnak a Köztérkép fő szerverének
  állapotáról. A térképrétegekhez, a feltöltött képek megjelenítéséhez és a
  levélküldéshez külső felhőszolgáltatókat veszünk igénybe, amelyek állapotáról
  nincs megjeleníthető
  statisztikánk.</p>

<hr />

<?php

// Load
switch (true) {
  case $load[0] >= 75:
    $load_color = 'danger';
    $load_text = 'Kritikus';
    break;
  case $load[0] >= 50:
    $load_color = 'warning';
    $load_text = 'Magas';
    break;
  case $load[0] >= 25:
    $load_color = 'info';
    $load_text = 'Közepes';
    break;
  default:
    $load_color = 'success';
    $load_text = 'Mérsékelt';
    break;
}

// Memory
switch (true) {
  case $memory[1] >= 75:
    $memory_color = 'danger';
    $memory_text = 'Kritikus';
    break;
  case $memory[1] >= 50:
    $memory_color = 'warning';
    $memory_text = 'Magas';
    break;
  case $memory[1] >= 25:
    $memory_color = 'info';
    $memory_text = 'Közepes';
    break;
  default:
    $memory_color = 'success';
    $memory_text = 'Mérsékelt';
    break;
}

echo '<h4 class="subtitle mb-3">Legfontosabb mutatók</h4>';

echo '<div class="row">';

echo '<div class="col-sm-5 col-lg-3 font-weight-bold mb-1">';
echo '<span class="far fa-microchip fa-fw mr-2"></span>Processzor terhelés';
echo '</div>';
echo '<div class="col-sm-7 col-lg-9 mb-3">';
echo '<span class="badge badge-' . $load_color . ' p-2">' . $load_text . '</span> '
  . '<span class="text-muted">' . $load[0] . '%-os terhelés, ' . $load[1] . ' művelet (1p átlag)</span>';
echo '</div>';

echo '<div class="col-sm-5 col-lg-3 font-weight-bold mb-1">';
echo '<span class="far fa-memory fa-fw mr-2"></span>Memória használat';
echo '</div>';
echo '<div class="col-sm-7 col-lg-9 mb-3">';
echo '<span class="badge badge-' . $memory_color . ' p-2">' . $memory_text . '</span> '
  . '<span class="text-muted">' . $memory[1] . '%, vagyis ' . $memory[0] . 'GB</span>';
echo '</div>';

echo '<div class="col-sm-5 col-lg-3 font-weight-bold mb-1">';
echo '<span class="far fa-plug fa-fw mr-2"></span>Kapcsolatok száma';
echo '</div>';
echo '<div class="col-sm-7 col-lg-9 mb-3">';
echo $connections . ' db <span class="text-muted">HTTP kapcsolat, ami nem egyenlő a látogatók számával</span>';
echo '</div>';

echo '<div class="col-sm-5 col-lg-3 font-weight-bold mb-1">';
echo '<span class="far fa-tasks fa-fw mr-2"></span>Várakozó teendők';
echo '</div>';
echo '<div class="col-sm-7 col-lg-9 mb-3">';
echo $job_count . ' db <span class="text-muted">ha több tucat, akkor pl. lassabb a képfeldolgozás</span>';
echo '</div>';

echo '</div>'; // row
?>


<p class="my-5 font-weight-bold"><span class="far fa-code-merge fa-fw mr-2"></span>Érdekel a Köztérkép technológiai háttere, közreműködnél a
  fejlesztésben? <?= $app->Html->link('Ismerkedj meg a részletekkel!', '/oldalak/fejlesztoknek') ?></p>

<hr />


<?php
echo '<h4 class="subtitle mb-3">Automatikák futási naplója</h4>';

echo '<p>Itt láthatóak az automatikusan futott feladatok, és azok futásának sikeressége. Egy részük ütemezett, más részük bizonyos felhasználói eseményekhez kötött. A "bővebben" linkre kattintva az adott job fájlját nyithatod meg a GitHub-on.</p>';

if (count($latest_errors) > 0) {
  $last_error = $latest_errors[0]->created;
  $distance = (time() - strtotime($last_error)) / (24*60*60);
  $since = $distance < 1 ? floor($distance * 24) . ' órával' : floor($distance) . ' nappal';
  echo '<p>Utolsó hibás futás: ' . $since . ' ezelőtt.</p>';
}

// Form
echo $app->Form->create($_params->query, [
  'method' => 'get',
  'class' => 'row mb-3'
]);

echo '<div class="col-md-4 col-sm-12">';
echo $app->Form->input('joblog_osztaly', [
  'placeholder' => 'Osztály megnevezés',
  'autocomplete' => 'off',
  'help' => @$_params->query['joblog_osztaly'] != ''
    || @$_params->query['joblog_metodus'] != ''
    || @$_params->query['joblog_hiba'] == 1
    ? $app->Html->link('Szűrés törlése', $_params->path, ['icon' => 'times', 'class' => '']) : ''
]);
echo '</div>';

echo '<div class="col-md-4 col-sm-12">';
echo $app->Form->input('joblog_metodus', [
  'placeholder' => 'Metódus megnevezés',
  'autocomplete' => 'off',
]);
echo '</div>';

echo '<div class="col-md-2 col-sm-12 pt-1">';
echo $app->Form->input('joblog_hiba', [
  'type' => 'checkbox',
  'label' => 'Hibás futás',
  'value' => 1,
]);
echo '</div>';

echo '<div class="col-md-2 col-sm-12">';
echo $app->Form->submit('Mehet', [
  'class' => 'btn-secondary',
]);
echo '</div>';

echo $app->Form->end();
// Form --

if (count($joblogs) > 0) {
  echo '<div class="table-responsive">';
  echo '<table class="table table-striped">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>Osztály</th>';
  echo '<th>Metódus</th>';
  echo '<th>Idő</th>';
  echo '<th>Mikor</th>';
  echo '<th>Kimenet</th>';
  echo '<th>Infó</th>';
  echo '</tr>';
  echo '</thead>';
  foreach ($joblogs as $joblog) {
    if (!is_string($joblog->class)) {
      continue;
    }
    echo '<tr class="' , $joblog->ran != 1 ? 'text-danger' : '' , '">';
    echo '<td class="font-weight-bold">' . $app->Html->link('', '/webstat/szerverallapot?joblog_osztaly=' . $joblog->class, [
      'icon' => 'search',
      'class' => 'mr-2',
      'title' => $joblog->class . ' automatika futásainak listázása',
    ]) . $joblog->class . '</td>';

    echo '<td class="font-weight-bold">' . $app->Html->link('', '/webstat/szerverallapot?joblog_osztaly=' . $joblog->class . '&joblog_metodus=' . $joblog->method, [
      'icon' => 'search',
      'class' => 'mr-2',
      'title' => $joblog->class . ' / ' . $joblog->method . ' automatika futásainak listázása',
    ]) . $joblog->method . '</td>';

    $run_time = $joblog->run_time > 1000 ? round($joblog->run_time/1000,2) . 's' : $joblog->run_time . 'ms';
    echo '<td class="text-muted">' . $run_time . '</td>';
    echo '<td class="text-muted">' . _time(strtotime($joblog->created), 'm.d. H:i:s') . '</td>';

    echo '<td>' , $joblog->ran == 1 ? '<span class="badge badge-success p-2">Rendben</span>' : 'Futási hiba' , '</td>';
    echo '<td>' . $app->Html->link('Bővebben', 'https://github.com/paltamas/kozterkep/blob/master/shell/jobs/' . $joblog->class . '.php', [
      'icon_right' => 'link-external',
      'title' => 'Job megnyitása a GitHubon',
      'target' => '_blank',
    ]) . '</td>';

    echo '</tr>';
  }
  echo '</table>';
  echo '</div>';

  echo '<p class="text-muted">A lista max. 25 elemet tartalmaz. A futási naplóbejegyzéseket 1 hétig tároljuk.</p>';

} else {
  echo '<p class="text-muted">Jelenleg nincs ilyen bejegyzés a naplóban.</p>';
}

if (count($latest_errors) > 0) {
  echo '<div class="alert alert-warning my-4"><strong>Utolsó hibás futások:</strong> ';
  foreach ($latest_errors as $joblog) {
    echo '<span class="badge badge-secondary mr-3 mb-2">';
    echo $joblog->class . '::' . $joblog->method;
    echo ' (' . _time(strtotime($joblog->created), 'm.d. H:i:s') . ')';
    echo '</span>';
  }
  echo '</div>';
}


?>
<hr class="highlighter my-5" />
<h4><span class="fal fa-robot mr-2"></span>egyébként pedig</h4>
<code>
  while (!$vegeztem) {<br />
  &nbsp;&nbsp;while ($lelkesedes == true) {<br />
  &nbsp;&nbsp;&nbsp;&nbsp;$lelkesedes = kodolok(['kave', 'csoki', 'zold_tea']);<br />
  &nbsp;&nbsp;}<br />
  &nbsp;&nbsp;$vegeztem = tesztelek('maximalista');<br />
  &nbsp;&nbsp;sleep(8);<br />
  }
</code>
