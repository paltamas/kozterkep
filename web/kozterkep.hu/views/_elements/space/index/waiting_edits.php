<?php
echo '<h5 class="subtitle">Várakozó szerkesztések</h5>';

if (count($waiting_edits) > 0) {

  echo '<div class="row">';
  foreach ($waiting_edits as $edit) {
    $artpiece = $app->MC->t('artpieces', $edit->artpiece_id);
    echo '<div class="col-3 col-sm-3 py-0 px-1 mb-2 text-center">';


    if (isset($votes)) {
      // Szavazatok összegzése, állapotok
      $score = 0;
      $i_voted = false;
      if (isset($votes[(string)$edit->_id])) {
        foreach ($votes[(string)$edit->_id] as $vote) {
          if ($vote['type_id'] == 6) {
            $score += $vote['score'];
            if ($vote['user_id'] == $_user['id']) {
              $i_voted = true;
            }
          }
        }
      }
    }

    echo $app->element('artpieces/edit/edit_item', [
      'edit' => $edit,
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
        'modal' => true,
        'photo_class' => @$i_voted ? 'fade-image' : '',
      ],
    ]);

    if (isset($votes)) {
      // Elfogadással kapcsolatos szavazási részletek
      echo '<div class="mt-1 small font-weight-semibold">';
      echo '<span class="text-muted" title="Még ' . ($vote_types['edit_accept'][3] - $score) . ' pont kell az elfogadáshoz." data-toggle="tooltip">' . $score . '/' . $vote_types['edit_accept'][3] . '</span>';
      if ($i_voted) {
        echo '<span class="fa fa-user-check mx-1 fa-sm text-muted" title="Szavaztál, köszönjük!" data-toggle="tooltip"></span>';
      }
      echo '</div>';
    }

    echo '</div>';
  }
  echo '</div>';

} else {
  echo '<div class="text-muted">Jelenleg nincs ilyen elem...</div>';
}

echo $app->Form->help('A legalább ' . sDB['limits']['edits']['wait_days'] . ' napos szerkesztések, és minden olyan, aminek a gazdája nem szeretné kezelni a műlapjait, vagy min. ' . sDB['limits']['edits']['inactive_after_months'] . ' hónapja nem járt erre.', ['class' => 'mt-0 mb-2', 'icon' => 'info-circle mr-1']);