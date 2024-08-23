<div class="tab-content">

  <div class="tab-pane show active" id="szerkesztes" role="tabpanel" aria-labelledby="szerkesztes-tab">

    <div class="row">
      <div class="col-md-8 mb-3">
        <?php
        echo $app->Form->create($place,[
          'method' => 'post',
          'id' => 'edit-place',
          'class' => '',
          'ia-form-change-alert' => 1,
        ]);


        echo $app->Form->input('name', [
          'label' => 'Település megnevezése',
          'required' => true,
        ]);

        echo $app->Form->input('original_name', [
          'label' => 'Eredeti nyelvű megnevezés',
        ]);

        echo $app->Form->input('alternative_names', [
          'label' => 'Egyéb alternatív, régi megnevezések',
        ]);

        echo $app->Form->input('country_id', [
          'options' => [0 => 'Nincs megadva']
            + $app->Arrays->id_list(sDB['countries'], 1, ['sort' => 'ASC']),
          'label' => 'Ország',
        ]);

        echo $app->Form->input('county_id', [
          'options' => [0 => 'Nincs, nem magyar']
            + $app->Arrays->id_list(sDB['counties'], 0, ['sort' => 'ASC']),
          'label' => 'Vármegye, ha magyar',
        ]);

        echo $app->Form->input('checked', [
          'type' => 'checkbox',
          'label' => 'Ellenőrzött',
          'value' => 1,
        ]);

        echo $app->Form->input('admin_memo', [
          'type' => 'textarea',
          'label' => 'Publikus admin megjegyzések',
        ]);

        echo $app->Form->input('inner_memo', [
          'type' => 'textarea',
          'label' => 'Belső admin megjegyzések',
        ]);

        echo $app->Form->end('Mentés', ['name' => 'save_settings']);




        echo $app->Html->link('Beolvasztás kezelése', '#', [
          'class' => 'd-block mt-4 mb-2',
          'icon' => 'code-merge',
          'data-toggle' => 'collapse',
          'data-target' => '#target-place-container',
        ]);

        echo '<div class="bg-gray-kt rounded border p-3 mb-4 collapse" id="target-place-container">';

        echo $app->Form->create($place, ['method' => 'post']);

        echo '<p>Beolvasztás előtt ellenőrizd a cél települést, hogy helyes-e a vármegye és ország beállítása, mert ezt rámentjük az összes műlapra is, ami a beolvasztandó településhez kapcsolódik.</p>';
        echo '<p>A beolvasztás során módosítjuk az összes szerkesztést is, amiben a beolvasztandó település szerepel.</p>';

        echo $app->Form->input('target_place_name', [
          'label' => 'Beolvasztási cél hely név',
          'class' => 'noEnterInput',
          'id' => 'target_place_name',
          'ia-auto' => 'places',
          'ia-auto-query' => 'name',
          'ia-auto-key' => 'id',
          'ia-auto-target' => '#target_place_id',
          'autocomplete' => 'off',
        ]);

        echo $app->Form->input('target_place_id', [
          'label' => 'Beolvasztási cél hely AZ',
          'id' => 'target_place_id',
          'help' => 'Legalább a hely azonosító legyen kitöltve.'
        ]);

        echo $app->Form->end('Beolvasztás', ['name' => 'merge']);

        echo '</div>';
        ?>
      </div>


      <div class="col-md-4">

        <?php
        echo '<h4 class="subtitle mt-0">Adatlap infók</h4>';
        echo $app->Html->dl('create', ['class' => 'row mb-0']);
        echo $app->Html->dl(['Műlapok', $place['artpiece_count'] > 0 ? $place['artpiece_count'] : '-']);
        echo $app->Html->dl(['Ellenőrizve', $place['checked'] == 1 ? _time($place['checked_time']) : '-']);
        echo $app->Html->dl(['Frissítve', _time($place['modified'])]);
        echo $app->Html->dl(['Létrehozás', _time($place['created'])]);
        echo $app->Html->dl('end');


        if ($place['creator_artpiece_id'] > 0) {
          $artpiece = $app->MC->t('artpieces', $place['creator_artpiece_id']);
          if ($artpiece) {
            echo '<hr />';
            echo '<h4 class="subtitle">Létrejött itt</h4>';
            echo $app->Image->photo($artpiece, [
              'size' => 5,
              'class' => 'img-thumbnail',
              'artpiece_tooltip' => $artpiece['id'],
              'link' => $app->Html->link_url('', ['artpiece' => $artpiece]),
              'link_options' => ['target' => '_blank']
            ]);
          }
        }

        if ($place['artpiece_count'] > 0) {
          echo '<hr />';
          echo $app->Html->link('Műlapok listája', '/kereses?hely_az=' . $place['id'], [
            'icon_right' => 'external-link',
            'target' => '_blank',
          ]);
        }

        if (count($similars) > 0) {
          echo '<hr />';
          echo '<h4 class="subtitle">Hasonlók</h4>';
          foreach ($similars as $similar) {
            echo '<p>' . $app->Places->name($similar, ['class' => 'font-weight-bold'])
              . ' <span class="text-muted">(AZ: ' . $similar['id'] . ', '
              . $app->Html->icon('map-marker mr-1') . $similar['artpiece_count'] . ')</span></p>';
          }
        }

        ?>

      </div>
    </div>

  </div>

  <div class="tab-pane" id="szerkkomm" role="tabpanel" aria-labelledby="szerkkomm-tab">
    <?=$app->element('places/edit/editcom')?>
  </div>

</div>


