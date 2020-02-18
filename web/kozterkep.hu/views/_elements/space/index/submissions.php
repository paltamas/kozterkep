<?php
echo '<h5 class="subtitle"><span class="fa fa-glasses-alt mr-2"></span>Publikálásra beküldöttek</h5>';

if (count($submissions) > 0) {

  echo '<div class="row">';
  foreach ($submissions as $artpiece) {
    echo '<div class="col-3 col-sm-3 py-0 px-1 mb-2 text-center">';

    // Szavazatok összegzése, állapotok
    $score = 0;
    $i_voted = $paused = false;
    if (isset($votes[$artpiece['id']])) {
      foreach ($votes[$artpiece['id']] as $vote) {
        if ($vote['type_id'] == 1) {
          $score += $vote['score'];
          if ($vote['user_id'] == $_user['id']) {
            $i_voted = true;
          }
        }
        if ($vote['type_id'] == 2) {
          $paused = true;
        }
      }
    }

    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
        'tooltip' => true,
        'photo_class' => $i_voted ? 'fade-image' : false,
        'extra_class' => '',
        //'editor_info' => true,
        //'link_options' => ['target' => '_blank']
      ],
    ]);


    // Publikálással kapcsolatos szavazási részletek
    echo '<div class="mt-1 small font-weight-semibold">';
    if ($artpiece['publish_at'] > 0) {
      echo '<span class="fas fa-hourglass-half text-success" title="Tagunk elérte a heti limitjét, ezért ' . strip_tags(_time($artpiece['publish_at'])) . '-kor automatikusan publikáljuk a műlapot, ha minden feltétel adott." data-toggle="tooltip"></span>';
    } else {
      if ($artpiece['open_question'] == 1) {
        echo '<span class="fas fa-question-circle mx-1 text-dark" title="Nyitott kérdés van a lapon" data-toggle="tooltip"></span>';
      }
      if ($paused) {
        echo '<span class="fas fa-pause-circle mx-1 text-dark" title="A publikálás szüneteltetve nyitott kérdés miatt." data-toggle="tooltip"></span>';
      }
      //echo '<span class="text-muted" title="Még ' . ($vote_types['publish'][3] - $score) . ' pont kell a publikáláshoz." data-toggle="tooltip">' . $score . '/' . $vote_types['publish'][3] . '</span>';
      if ($i_voted) {
        echo '<span class="fa fa-user-check mx-1 fa-sm text-muted" title="Szavaztál, köszönjük!" data-toggle="tooltip"></span>';
      }
    }
    echo '</div>';


    echo '</div>';
  }
  echo '</div>';

} else {
  echo '<div class="text-muted">Jelenleg nincs beküldés...</div>';
}