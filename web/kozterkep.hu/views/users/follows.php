<div class="tab-content">
  <div class="tab-pane show active" id="friss" role="tabpanel" aria-labelledby="friss-tab">
    <?=$app->element('users/follows/home')?>
  </div>

  <?php
  $tabs = [
    'mulapok' => 'artpieces',
    'alkotok' => 'artists',
    'helyek' => 'places',
    'tagok' => 'users',
    'mappak' => 'folders',
  ];
  foreach ($tabs as $tab => $model_id) {
    echo '<div class="tab-pane" id="' . $tab . '" role="tabpanel" aria-labelledby="' . $tab . '-tab">';

    $followeds = @$me['follow_' . $model_id];
    $model = sDB['model_parameters'][$model_id];

    echo '<h3 class="subtitle">Követett ' . $model[1] . ' listája</h3>';

    if (!isset($me['follow_' . $model_id])) {
      continue;
    }

    if (is_array($followeds) && count($followeds) > 0) {

      krsort($followeds);

      echo '<div class="table-responsive mt-3">';
      echo '<table class="table table-striped">';
      foreach ($followeds as $followed_id) {

        $cols = [];

        if ($followed_id == 0) {
          continue;
        }

        echo '<tr>';

        switch ($model_id) {

          case 'artpieces':
            $artpiece = $app->MC->t('artpieces', $followed_id);
            if (!$artpiece) {
              continue;
            }
            $cols = [
              $app->Image->photo($artpiece, [
                'size' => 7
              ]),
              $app->Html->link($artpiece['title'], '', [
                'artpiece' => $artpiece,
                'class' => 'font-weight-bold',
                'ia-tooltip' => 'mulap',
                'ia-tooltip-id' => $artpiece['id'],
              ]),
              $app->Places->name($artpiece['place_id']),
              $app->Users->name($artpiece['user_id']),
            ];
            break;

          case 'artists':
            $artist = $app->MC->t('artists', $followed_id);
            if (!$artist) {
              continue;
            }
            $cols = [
              $app->Artists->name($artist, [
                'year' => true,
                'tooltip' => true,
                'profession' => true,
                'class' => 'font-weight-bold',
              ]),
            ];
            break;

          case 'places':
            $place = $app->MC->t('places', $followed_id);
            if (!$place) {
              continue;
            }
            $cols = [
              $app->Places->name($place, [
                'tooltip' => true,
                'class' => 'font-weight-bold',
              ]),
              $place['county_id'] > 0 ? sDB['counties'][$place['county_id']][0] : '',
              sDB['countries'][$place['country_id']][1],
            ];
            break;

          case 'users':
            $user = $app->MC->t('users', $followed_id);
            if (!$user) {
              continue;
            }
            $cols = [
              $app->Users->name($user, [
                'image' => true,
                'tooltip' => true,
                'class' => 'font-weight-bold',
              ]),
            ];
            break;


          case 'folders':
            $folder = $app->MC->t('folders', $followed_id);
            if (!$folder || $folder['public'] == 0) {
              continue;
            }
            $cols = [
              $app->Html->link($folder['name'], '', [
                'folder' => $folder,
                'class' => 'font-weight-bold',
              ]),
              $app->Users->name($folder['user_id']  ),
            ];
            break;


        }

        foreach ($cols as $col) {
          echo '<td>';
          echo $col;
          echo '</td>';
        }

        echo '</tr>';
      }
      echo '</table>';
      echo '</div>';

    } else {
      echo '<p class="">Nincs még általad követett ' . $model[0] . '</p>';
    }

    echo '</div>';
  }
  ?>

</div>