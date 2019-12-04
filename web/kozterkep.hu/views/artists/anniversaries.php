<div class="row">

  <div class="col-md-12 mb-4">
    <div class="kt-info-box">
      A két listában az alkotó adattár jelenlegi kitöltöttsége alapján mutatjuk a születésnapokat és a halálozási évfordulókat <?=_time($day, 'Y.m.d.')?> dátumra. Tekintettel arra, hogy adattárunk folyamatosan bővül, ha hiányolsz valamit jelezd az alkotó adatlapon kommentben.
    </div>
  </div>


  <div class="col-lg-7 mb-4 text-center">

    <?php
    echo $app->Form->create('', ['class' => 'form-inline']);

    echo $app->Form->label('Válassz dátumot');

    echo $app->Form->input('honap', [
      'options' => sDB['month_names'],
      'value' => @$_params->query['honap'] > 0
        ? $_params->query['honap'] : (int)date('m', $day),
      'divs' => 'mx-3 mb-0',
    ]);

    echo $app->Form->input('nap', [
      'maxlength' => 2,
      'placeholder' => 'Nap...',
      'value' => @$_params->query['nap'] > 0
        ? (int)$_params->query['nap'] : (int)date('d', $day),
      'class' => 'narrow',
      'divs' => 'mr-3 mb-0',
    ]);

    echo $app->Form->submit('Mehet');
    ?>

  </div>
  <div class="col-lg-5 mb-4 text-center">
    <?php
    echo $app->Html->link('Előző', $app->Html->parse_url($_params->url, ['updatevars' => ['leptetes' => -1]]), [
      'class' => 'btn btn-outline-secondary mr-3',
      'icon' => 'angle-left'
    ]);
    echo $app->Html->link('Ma', '/alkotok/evfordulok', [
      'class' => 'btn btn-outline-secondary mr-3',
    ]);
    echo $app->Html->link('Következő', $app->Html->parse_url($_params->url, ['updatevars' => ['leptetes' => 1]]), [
      'class' => 'btn btn-outline-secondary',
      'icon_right' => 'angle-right'
    ]);
    ?>
  </div>



  <div class="col-md-6 mb-3">

    <h4 class="subtitle">Születési évfordulók</h4>

    <?php
    if (count($births) > 0) {
      echo '<table class="table table-striped mt-3">';

      echo '<tbody>';

      foreach ($births as $artist) {
        $year_count = date('Y') - _time($artist['born_date'], 'Y', true);
        echo '<tr>';
        echo '<td>' . $app->Artists->name($artist, [
          'class' => 'font-weight-bold',
          'profession' => true,
          'tooltip' => true,
        ]) . '</td>';
        echo '<td class="text-center">' . _time($artist['born_date'], 'Y') . '</td>';
        echo '<td class="text-center">' . $year_count . '</td>';
        echo '</tr>';
      }

      echo '</tbody>';

      echo '</table>';
    } else {
      echo $app->element('layout/partials/empty');
    }
    ?>
  </div>


  <div class="col-md-6 mb-3">

    <h4 class="subtitle">Halálozási évfordulók</h4>

    <?php
    if (count($deaths) > 0) {
      echo '<table class="table table-striped mt-3">';

      echo '<tbody>';

      foreach ($deaths as $artist) {
        $year_count = date('Y') - _time($artist['death_date'], 'Y', true);
        echo '<tr>';
        echo '<td>' . $app->Artists->name($artist, [
          'class' => 'font-weight-bold',
          'profession' => true,
          'tooltip' => true,
        ]) . '</td>';
        echo '<td class="text-center">' . _time($artist['death_date'], 'Y') . '</td>';
        echo '<td class="text-center">' . $year_count . '</td>';
        echo '</tr>';
      }

      echo '</tbody>';

      echo '</table>';
    } else {
      echo $app->element('layout/partials/empty');
    }
    ?>

  </div>

</div>