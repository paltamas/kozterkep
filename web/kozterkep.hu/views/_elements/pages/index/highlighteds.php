<div class="text-muted float-right font-italic pt-1">aktuális vagy érdekes vagy</div>
<h5 class="subtitle mb-3 text-dark">Kiemelés</h5>
<div class="row mb-4 justify-content-center">
  <?php

  // Csoportcím, ha nem ugyanaz a szöveg is, mert akkor oda megy a cím is
  if ($super_highlighteds[0]['group'] == 1
    && $super_highlighteds[0]['text'] != $super_highlighteds[1]['text']) {
    echo '<div class="col-12 text-center text-muted font-italic"><h5 class="mb-2">[' . $super_highlighteds[0]['title'] . ']</h5></div>';
  }


  foreach ($super_highlighteds as $highlighted) {
    $artpiece = $app->MC->t('artpieces', $highlighted['artpiece_id']);
    echo '<div class="col-12 col-sm-6 col-md-12 col-lg-6 mb-3 mb-md-0 px-2">';

    echo '<div class="text-center text-md-left">';
    echo $app->Image->photo($highlighted, [
      'size' => 4,
      'class' => 'img-fluid img-thumbnail rounded',
      'link' => $app->Html->link_url('', ['artpiece' => $artpiece])
    ]);
    echo '</div>';

    echo '<div class="my-2 px-1 text-center text-md-left">';
    echo $app->Html->link($artpiece['title'], '', [
      'artpiece' => $artpiece,
      'class' => 'font-weight-semibold text-dark',
      'icon_right' => 'arrow-right',
    ]);
    echo '</div>';

    if ($highlighted['group'] == 0) {
      // Szimpla cím + szöveg
      echo '<p class="font-italic text-dark px-1"><strong>' . $highlighted['title'] . '</strong> <span class="text-muted">&bull;</span> ' . $highlighted['text'] . '</p>';
    } elseif ($highlighted['group'] == 1
      && $super_highlighteds[0]['text'] != $super_highlighteds[1]['text']) {
      // Csoport, de különböző szöveggel
      echo '<p class="font-italic text-dark px-1">' . $highlighted['text'] . '</p>';
    }


    echo '</div>'; // col --
  }

  if ($highlighted['group'] == 1
      && $super_highlighteds[0]['text'] == $super_highlighteds[1]['text']) {
    // Csoport, azonos szöveggel
    echo '<div class="col-12 mb-3 mb-md-0 px-2">';
    echo '<p class="font-italic text-dark px-1"><strong>' . $super_highlighteds[0]['title'] . '</strong> <span class="text-muted">&bull;</span> ' . $super_highlighteds[0]['text'] . '</p>';
    echo '</div>';
  }

  ?>

</div>