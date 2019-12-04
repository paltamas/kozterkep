<?php
//echo '<h6 class="subtitle">Alkotók</h6>';

if (@count(@$artists['artists']) == 0 && @count(@$artists['contributors']) == 0) {
  echo '<div class="text-muted small">Az alkotót eddig nem sikerült kideríteni. ' . $app->Html->link('Többet tudsz?', $_editable) . '</div>';
}

if (@count(@$artists['artists']) > 0 || @count(@$artists['contributors']) > 0) {
  echo '<div class="row">';

  // Közreműködőket külön kaptuk, ez plusszolja a dimenziót
  foreach ($artists as $type => $artists_by_type) {
    foreach ($artists_by_type as $artist) {
      echo '<div class="col-md-12">';

      if ($type == 'artists') {
        echo '<strong>' . $app->Artists->name($artist['id']) . '</strong>';
      } else {
        echo $app->Artists->name($artist['id']);
      }

      // Közreműködők infója, vagy szerep nem szobrász
      if ($artist['profession_id'] > 1 || $type == 'contributors') {
        echo ' <span class="text-muted">(';
        // szobrász trivia, nem írjuk ki
        echo $artist['profession_id'] > 1 ? '<span data-toggle="tooltip" title="Kifejezetten a jelen alkotás létrehozásában betöltött szerep. Tehát nem feltétlenül egyezik meg a személy többnyire űzött hivatásával, ill. szakmájával.">' . mb_strtolower(sDB['artist_professions'][$artist['profession_id']][0]) . '</span>' : '';
        echo $artist['profession_id'] > 1 && $type == 'contributors' ? ', ' : '';
        echo $type == 'contributors' ? '<span data-toggle="tooltip" title="Nem vett részt az alkotói folyamatban, de fontos közreműködői szerepet vállalt a kivitelezésben.">közreműködő</span>' : '';
        echo ')</span>';
      }
      echo '</div>';
    }
  }

  echo '</div>';
}