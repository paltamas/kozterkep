<div class="row">
  <div class="col-md-8 mb-4">
    <?php
    echo $app->Form->create($artist, [
      'method' => 'post',
      'id' => 'edit-artist',
      'class' => '',
      'ia-form-change-alert' => 1,
    ]);

    echo '<div class="row">';


    echo $app->Form->input('name', [
      'label' => 'Név',
      'required' => true,
      'divs' => 'col-md-12 mb-4'
    ]);

    echo $app->Form->input('first_name', [
      'label' => 'Keresztnév',
      'divs' => 'col-md-6 mb-4'
    ]);


    echo $app->Form->input('last_name', [
      'label' => 'Vezetéknév',
      'required' => true,
      'divs' => 'col-md-6 mb-4'
    ]);


    echo $app->Form->input('before_name', [
      'label' => 'Előnév',
      'options' => sDB['before_names'],
      'empty' => '-',
      'divs' => 'col-md-4 mb-4'
    ]);

    echo $app->Form->input('english_form', [
      'type' => 'checkbox',
      'label' => 'Angol névsorrend',
      'value' => 1,
      'divs' => 'col-md-4 mb-4 pt-4'
    ]);

    echo $app->Form->input('artpeople_id', [
      'label' => 'KMML AZ',
      'divs' => 'col-md-4 mb-4'
    ]);


    echo $app->Form->input('alternative_names', [
      'label' => 'Alternatív nevek',
      'divs' => 'col-md-12 mb-4'
    ]);

    echo $app->Form->input('website_url', [
      'type' => 'textarea',
      'label' => 'Weboldal(ak)',
      'divs' => 'col-md-12 mb-4'
    ]);

    echo $app->Form->input('corporation', [
      'type' => 'checkbox',
      'label' => 'Gazdasági társaság',
      'value' => 1,
      'divs' => 'col-md-6 mb-4 pt-3'
    ]);

    echo $app->Form->input('artistgroup', [
      'type' => 'checkbox',
      'label' => 'Alkotócsoport',
      'value' => 1,
      'divs' => 'col-md-6 mb-4 pt-3'
    ]);

    echo $app->Form->input('artist_name', [
      'label' => 'Művésznév',
      'divs' => 'col-md-6 mb-4'
    ]);

    echo $app->Form->input('profession_id', [
      'options' => $app->Artists->professions(['only_professions' => 1]),
      'label' => 'Foglalkozás',
      'divs' => 'col-md-6 mb-4'
    ]);


    echo '<div class="col-md-12 mb-4 form-inline">';
    echo $app->Form->label('Született', ['class' => 'mr-3']);
    echo $app->Form->input('born_date_year', [
      'maxlength' => 4,
      'placeholder' => 'Évszám',
      'value' => _cdate($artist['born_date'], 'y') > 0
        ? _cdate($artist['born_date'], 'y') : '',
      'class' => 'narrow mr-2',
      'divs' => 'mb-2 m-md-0 p-0 d-inline',
    ]);
    echo $app->Form->input('born_date_month', [
      'type' => 'select',
      'options' => sDB['month_names'],
      'empty' => [0 => 'Hónap...'],
      'value' => _cdate($artist['born_date'], 'm'),
      'class' => 'mr-2',
      'divs' => 'mb-2 m-md-0 p-0 d-inline',
    ]);
    echo $app->Form->input('born_date_day', [
      'maxlength' => 2,
      'placeholder' => 'Nap...',
      'value' => _cdate($artist['born_date'], 'd') > 0
        ? _cdate($artist['born_date'], 'd') : '',
      'class' => 'narrow mr-2',
      'divs' => 'mb-2 m-md-0 p-0 pl-2 d-inline',
    ]);
    echo $app->Form->input('born_place_name', [
      'placeholder' => 'Helység',
      'divs' => 'mb-2 m-md-0 p-0 pl-2 d-inline',
    ]);
    echo '</div>';

    echo '<div class="col-md-12 mb-4 form-inline">';
    echo $app->Form->label('Elhunyt', ['class' => 'mr-4']);
    echo $app->Form->input('death_date_year', [
      'maxlength' => 4,
      'placeholder' => 'Évszám',
      'value' => _cdate($artist['death_date'], 'y') > 0
        ? _cdate($artist['death_date'], 'y') : '',
      'class' => 'narrow mr-2',
      'divs' => 'mb-2 m-md-0 p-0 d-inline',
    ]);
    echo $app->Form->input('death_date_month', [
      'type' => 'select',
      'options' => sDB['month_names'],
      'empty' => [0 => 'Hónap...'],
      'value' => _cdate($artist['death_date'], 'm'),
      'class' => 'mr-2',
      'divs' => 'mb-2 m-md-0 p-0 d-inline',
    ]);
    echo $app->Form->input('death_date_day', [
      'maxlength' => 2,
      'placeholder' => 'Nap...',
      'value' => _cdate($artist['death_date'], 'd') > 0
        ? _cdate($artist['death_date'], 'd') : '',
      'class' => 'narrow mr-2',
      'divs' => 'mb-2 m-md-0 p-0 pl-2 d-inline',
    ]);
    echo $app->Form->input('death_place_name', [
      'placeholder' => 'Helység',
      'divs' => 'mb-2 m-md-0 p-0 pl-2 d-inline',
    ]);
    echo '</div>';


    echo '</div>'; // row --


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
      'data-target' => '#target-artist-container',
    ]);

    echo '<div class="bg-gray-kt rounded border p-3 mb-4 collapse" id="target-artist-container">';

    echo $app->Form->create($artist, ['method' => 'post']);

    echo '<p>Beolvasztás előtt ellenőrizd a cél alkotót.</p>';
    echo '<p>A beolvasztás során módosítjuk az összes szerkesztést is, amiben a beolvasztandó alkotó szerepel.</p>';

    echo $app->Form->input('target_artist_name', [
      'label' => 'Beolvasztási cél alkotó név',
      'class' => 'noEnterInput',
      'id' => 'target_artist_name',
      'ia-auto' => 'artists',
      'ia-auto-query' => 'name',
      'ia-auto-key' => 'id',
      'ia-auto-target' => '#target_artist_id',
      'autocomplete' => 'off',
    ]);

    echo $app->Form->input('target_artist_id', [
      'label' => 'Beolvasztási cél alkotó AZ',
      'id' => 'target_artist_id',
      'help' => 'Legalább az alkotó azonosító legyen kitöltve.'
    ]);

    echo $app->Form->end('Beolvasztás', ['name' => 'merge']);

    echo '</div>';
    ?>
  </div>


  <div class="col-md-4">

    <?php
    echo '<h4 class="subtitle mt-0">Adatlap infók</h4>';
    echo $app->Html->dl('create', ['class' => 'row mb-0']);
    echo $app->Html->dl(['Műlapok', $artist['artpiece_count'] > 0 ? $artist['artpiece_count'] : '-']);
    echo $app->Html->dl(['Ellenőrizve', $artist['checked'] == 1 ? _time($artist['checked_time']) : '-']);
    echo $app->Html->dl(['Frissítve', _time($artist['modified'])]);
    echo $app->Html->dl(['Létrehozás', _time($artist['created'])]);
    echo $app->Html->dl('end');


    if ($artist['creator_artpiece_id'] > 0) {
      $artpiece = $app->MC->t('artpieces', $artist['creator_artpiece_id']);
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

    if ($artist['artpeople_id'] > 0) {
      echo '<hr />';
      echo $app->Html->link('KMML szócikk megnyitása', '/adattar/lexikon_szocikk/' . $artist['artpeople_id'], [
        'icon_right' => 'external-link',
        'target' => '_blank',
      ]);
    }

    if ($artist['artpiece_count'] > 0) {
      echo '<hr />';
      echo $app->Html->link('Műlapok listája', '/kereses?alkoto_az=' . $artist['id'], [
        'icon_right' => 'external-link',
        'target' => '_blank',
      ]);
    }

    if (count($similars) > 0) {
      echo '<hr />';
      echo '<h4 class="subtitle">Hasonlók</h4>';
      foreach ($similars as $similar) {
        echo '<p>' . $app->Artists->name($similar, ['class' => 'font-weight-bold'])
          . ' <span class="text-muted">(AZ: ' . $similar['id'] . ', '
          . $app->Html->icon('map-marker mr-1') . $similar['artpiece_count'] . ')</span></p>';
      }
    }
    ?>

  </div>
</div>