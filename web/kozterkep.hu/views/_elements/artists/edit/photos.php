<div class="row">
  <div class="col-md-7">
    <h5 class="subtitle">Alkotói adatlapra töltött fotók</h5>
    <?php
    if (count($photos) > 0) {

      echo $app->Form->create(null, [
        'method' => 'post'
      ]);

      if (count($photos) > 3) {
        echo '<div class="my-3">';
        echo $app->Form->submit('Módosítások mentése', ['name' => 'save_photos']);
        echo '</div >';
      }

      $i = 0;

      foreach ($photos as $photo) {
        $i++;
        echo '<div class="row mb-4 pb-4 border-bottom">';
        echo '<div class="col-sm-6 col-md-5 col-lg-4 mb-2">';
        echo $app->Image->photo($photo, [
          'link' => 'self',
          'link_options' => ['target' => '_blank'],
          'size' => 5,
          'class' => 'img-thumbnail img-fluid',
        ]);
        echo $app->element('photos/info', [
          'photo' => $photo,
          'artpiece' => false,
        ]);
        echo '</div>'; // col --


        echo '<div class="col-sm-6 col-md-7 col-lg-8 mb-2">';

        echo $app->Form->input('photolist[' . $i . '][id]', [
          'type' => 'hidden',
          'value' => $photo['id'],
        ]);

        echo $app->Form->input('photolist[' . $i . '][text]', [
          'label' => 'Leírás',
          'type' => 'textarea',
          'value' => $photo['text'],
        ]);

        echo $app->Form->input('photolist[' . $i . '][source]', [
          'label' => 'Forrás',
          'type' => 'textarea',
          'class' => 'textarea-short',
          'value' => $photo['source'],
        ]);

        echo '<div class="mt-3 text-right">';
        echo $app->Html->link('Fotó törlése', '/alkotok/szerkesztes/' . $artist['id'] . '?foto_torles=' . $photo['id'], [
          'icon' => 'trash fa-lg',
          'class' => 'text-muted mb-2 cursor-pointer text-nowrap',
          'ia-confirm' => 'Biztosan törlöd ezt a fotót? A törlés után a fotó nem visszaállítható.',
        ]);
        echo '</div>';

        echo '</div>'; // col --
        echo '</div>'; // row --
      }

      echo $app->Form->end('Módosítások mentése', ['name' => 'save_photos']);
    } else {
      echo $app->element('layout/partials/empty');
    }
    ?>
  </div>
  <div class="col-md-5">
    <h5 class="subtitle">Műlapokra töltött fotók</h5>
    <p>Ezeket az adott műlapokon tudod szerkeszteni.</p>
    <?php
    if (count($artpiece_photos) > 0) {

      echo '<div class="row">';

      foreach ($artpiece_photos as $photo) {

        $artpiece = $app->MC->t('artpieces', $photo['artpiece_id']);

        echo '<div class="col-6 text-center mb-3">';
        echo $app->Image->photo($photo, [
          'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
          'size' => 3,
          'class' => 'img-thumbnail img-fluid',
        ]);

        if ($artpiece['status_id'] != 5) {
          $status = sDB['artpiece_statuses'][$artpiece['status_id']];
          echo '<div class="small my-1"><span class="badge badge-' . $status[1] . '">' . $status[0] . '</span></div>';
        }

        echo $app->element('photos/info', [
          'photo' => $photo,
          'artpiece' => $artpiece
        ]);
        echo '</div>'; // col --
      }

      echo '</div>'; // row --
    } else {
      echo $app->element('layout/partials/empty');
    }
    ?>
  </div>
</div>
