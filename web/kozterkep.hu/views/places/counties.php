<div class="row">
  <div class="col-md-7 mb-3">


    <?php
    echo $app->Html->tabs([
      'Műlap szerint' => [
        'link' => $_params->path . '',
        'icon' => 'sort-amount-up',
      ],
      'ABC rendben' => [
        'link' => $_params->path . '?abc',
        'icon' => 'sort-alpha-down',
      ],
      'Ráta szerint' => [
        'link' => $_params->path . '?rata',
        'icon' => 'percent',
      ],
    ], [
      'class' => 'mb-3',
      'type' => 'pills',
      'selected' => $selected_tab,
    ])
    ?>


    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Vármegye</th>
          <th>Műlap</th>
          <th>Ráta</th>
          <th>Lakos</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $i = $sum = 0;
      foreach ($counties as $county) {
        $sum += $county['artpiece_count'];
        $i++;

        $rate = ($county['artpiece_count'] / (sDB['counties'][$county['county_id']][1]*1000));

        // Bp nem a megyére linkeljen
        $county_link = $county['county_id'] == 1 ?
          $app->Places->name(110) : $app->Places->county($county['county_id']);

        echo '<tr>';
        echo '<td>' . $i . '.</td>';
        echo '<td class="font-weight-bold">' . $county_link . '</td>';
        echo '<td>' . _n($county['artpiece_count']) . '</td>';
        echo '<td>' . str_replace('0,00', '<span class="text-muted small">0,00</span>', _n($rate,4)) . '</td>';
        echo '<td>' . _n(sDB['counties'][$county['county_id']][1]) . 'e</td>';
        echo '</tr>';
      }
      ?>
      </tbody>
    </table>
  </div>

  <div class="col-md-5">
    <div class="kt-info-box">
      <p>A hazai vármegyék közt nem a legmegfelelőbben, de Budapestet is megjelenítjük. Így jobban látszik a vízfej. De biztosan vízfej a fővárosunk?</p>
      <p>A vármegyelista legérdekesebb része a ráta. Ezen keresztül látszik, mennyi mű jut egy lakosra. De az is jól átlátható, hol van még munkánk. Minél alacsonyabb a ráta, annál inkább lehetséges, hogy az adott megyében több lapul még a köztereken és a közösségi helyeken. Persze ez csalóka, hisz a szegényebb régiók műellátottsága lényegesen alacsonyabb lehet.</p>
      <p><strong>Ráta</strong>: egy lakosra jutó műlapok száma (műlapok száma / lakosság száma).</p>
      <p><strong>Lakos</strong>: vármegye lakossága, ezer főben megadva.<br />Forrás: https://hu.wikipedia.org/wiki/Magyarorsz%C3%A1g_megy%C3%A9i <span class="text-muted">(frissítve: 2019.02.23.)</span>.</p>
      <p>Ha tetszik ez a ráta-dolog, nézd meg a <?=$app->Html->link('budapesti kerületeket', '/helyek/budapesti-keruletek')?> is!</p>
    </div>
  </div>
</div>