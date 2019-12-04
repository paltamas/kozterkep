<?php
$options = (array)@$options + [
  'class' => 'col-4 col-sm-3 col-md-2 px-1 text-center',
  'show_votes' => false,
];
if (count($artpieces) > 0) {
  echo '<div class="row">';
  foreach ($artpieces as $artpiece) {
    echo '<div class="' . $options['class'] . '">';

    if ($options['show_votes']) {
      // Szavazatok összegzése, állapotok
      $yes = $no = 0;
      $i_voted = false;
      if (isset($votes[$artpiece['id']])) {
        foreach ($votes[$artpiece['id']] as $vote) {
          $yes += $vote['score'] == 1 ? 1 : 0;
          $no += $vote['score'] == 2 ? 1 : 0;
          if ($vote['user_id'] == $_user['id']) {
            $i_voted = true;
          }
        }
      }
    }

    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
      'options' => [
        'simple' => true,
        'tooltip' => true,
        'extra_class' => 'mb-2',
        'photo_class' => @$i_voted ? 'fade-image' : '',
        'link_options' => ['target' => '_blank']
      ],
    ]);

    if ($options['show_votes']) {
      // Példással kapcsolatos szavazási részletek
      echo '<div class="mt-0 mb-3 small text-muted font-weight-semibold">';
      echo $yes . ' : ' . $no;
      echo '</div>';
    }

    echo '</div>';
  }
  echo '</div>';
} else {
  echo $app->element('layout/partials/empty', ['class' => 'py-2']);
}