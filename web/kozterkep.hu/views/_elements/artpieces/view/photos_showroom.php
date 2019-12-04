<?php
if (count($artpiece_photos) > 0) {

  echo '<div class="row">';

  echo '<div class="col-12 mb-3 text-center">';
  echo $app->Html->link('Műlap', '#mulap', [
    'icon' => 'arrow-left',
    'class' => 'btn btn-outline-primary mx-2 tab-button',
  ]);

  echo $app->Html->link('Nagy képek', '', [
    'ia-showroom' => 'photo',
    'ia-showroom-hash' => 'fotolista',
    'ia-showroom-container' => '#fotolista',
    'icon' => 'expand-alt',
    'class' => 'btn btn-outline-primary mx-2'
  ]);

  if ($artpiece['status_id'] == 5) {
    echo '<div class="mt-3 mt-md-0 float-md-right">';
    echo $app->Html->link('Új fotók feltöltése', '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-fotok', [
      'icon' => 'upload',
      'class' => 'btn btn-outline-primary'
    ]);
    echo '</div>';
  }

  echo '</div>';

  echo '</div>'; // row


  /**
   * Összeszedjük a fotókat, és ha van élménykép vagy adalék, akkor két hasábba pakolunk,
   * ha nincs, akkor egybe, cím nélkül.
   */

  $photolist_html = '';
  $archivelist_html = '';
  $joylist_html = '';
  $otherlist_html = '';

  // Alap fotók
  foreach ($artpiece_photos as $artpiece_photo) {
    if (!isset($photos[$artpiece_photo['id']])) {
      // migrálás alatt
      continue;
    }
    $photo = $photos[$artpiece_photo['id']] + $artpiece_photo;
    if ($photo['joy'] == 0 && $photo['other'] == 0 && $photo['other_place'] == 0) {
      if ($photo['archive'] == 0) {
        $photolist_html .= '<div class="col-6 col-sm-4 col-md-3 mb-4">';
        $photolist_html .= $app->element('artpieces/view/photos_showroom_item', ['photo' => $photo], false);
        $photolist_html .= '</div>'; // col
      } else {
        $archivelist_html .= '<div class="col-6 col-sm-4 col-md-3 mb-4">';
        $archivelist_html .= $app->element('artpieces/view/photos_showroom_item', ['photo' => $photo], false);
        $archivelist_html .= '</div>'; // col
      }
    } else {
      if ($photo['joy'] == 1) {
        $joylist_html .= '<div class="col-6 mb-4 small">';
        $joylist_html .= $app->element('artpieces/view/photos_showroom_item', [
          'photo' => $photo,
          'options' => ['truncate' => 30]
        ], false);
        $joylist_html .= '</div>'; // col
      } elseif ($photo['other'] == 1 || $photo['other_place'] == 1) {
        $otherlist_html .= '<div class="col-6 mb-4 small">';
        $otherlist_html .= $app->element('artpieces/view/photos_showroom_item', [
          'photo' => $photo,
          'options' => ['truncate' => 30]
        ], false);
        $otherlist_html .= '</div>'; // col
      }
    }
  }


  if ($joylist_html == '' && $otherlist_html == '') {
    // NINCS élménykép, se adalék, se máshelyről
    echo '<div class="row">';
    echo $photolist_html;
    if ($archivelist_html != '') {
      if ($photolist_html != '') {
        echo '<div class="col-12">';
        echo '<hr class="my-4" />';
        echo '<h4 class="subtitle mb-3">Archív fotók</h4>';
        echo '</div>';
      }
      echo $archivelist_html;
    }
    echo '</div>';
  } else {

    /**
     * Van élménykép vagy adalék vagy máshelyről:
     *  - két hasáb
     *  - címmel
     */

    echo '<div class="row">';
    echo '<div class="col-md-8 col-lg-9 mb-5">';
    if ($photolist_html != '') {
      echo '<h4 class="subtitle mb-3">Normál és archív fotók</h4>';
    } else {
      echo '<h4 class="subtitle mb-3">Archív fotók</h4>';
    }
    echo '<div class="row">';
    echo $photolist_html;
    if ($archivelist_html != '') {
      if ($photolist_html != '') {
        echo '<div class="col-12">';
        echo '<hr class="my-4" />';
        echo '<h4 class="subtitle mb-3">Archív fotók</h4>';
        echo '</div>';
      }
      echo $archivelist_html;
    }
    echo '</div>'; // row
    echo '</div>'; // col - bal

    echo '<div class="col-md-4 col-lg-3">';

    if ($otherlist_html) {
      echo '<h4 class="subtitle mb-3">Adalék vagy máshol</h4>';
      echo '<div class="row">';
      echo $otherlist_html;
      echo '</div>'; // row
    }

    if ($joylist_html) {
      echo '<h4 class="subtitle mb-3">Élményképek</h4>';
      echo '<div class="row">';
      echo $joylist_html;
      echo '</div>'; // row
    }

    echo '</div>'; // col - jobb
    echo '</div>'; // row - nagy
  }

  echo '<div class="text-muted">';
  echo '<strong>Összesen ' . count($artpiece_photos) . ' fotó</strong>';
  echo '</div>';

} else {

  echo $app->element('layout/partials/empty');

}