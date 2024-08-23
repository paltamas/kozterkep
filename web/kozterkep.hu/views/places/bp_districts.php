<div class="row">
  <div class="col-md-7">


    <?php
    echo $app->Html->tabs([
      'Műlap szerint' => [
        'link' => $_params->path . '',
        'icon' => 'sort-amount-up',
      ],
      'Sorrendben' => [
        'link' => $_params->path . '?szam',
        'icon' => 'sort-numeric-down',
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
        <th>Kerület</th>
        <th>Műlap</th>
        <th>Ráta</th>
        <th>Lakos</th>
      </tr>
      </thead>
      <tbody>
      <?php
      $i = $sum = 0;
      foreach ($districts as $district) {
        $sum += $district['artpiece_count'];
        $i++;

        $persons = max(1, (sDB['districts'][$district['district_id']][1]*1000));
        $rate = ($district['artpiece_count'] / $persons);

        $margitsziget_te_szegeny = $district['district_id'] == 24 ? '*' : '';

        echo '<tr>';
        echo '<td>' . $i . '.</td>';
        echo '<td class="font-weight-bold">' . $app->Places->district($district['district_id']) . $margitsziget_te_szegeny . '</td>';
        echo '<td>' . _n($district['artpiece_count']) . '</td>';
        if ($persons > 1) {
          echo '<td>' . str_replace('0,0', '<span class="text-muted small">0,0</span>', _n($rate, 4)) . '</td>';
        } else {
          echo '<td>-</td>';
        }
        echo '<td>' . _n(sDB['districts'][$district['district_id']][1]) . 'e</td>';
        echo '</tr>';
      }
      ?>
      </tbody>
    </table>
  </div>

  <div class="col-md-5">
    <div class="kt-info-box">
      <p class="font-weight-bold">Ha Budapest adatlapját keresed, <?=$app->Html->link('kattints ide', '/helyek/megtekintes/110/budapest')?>.</p>
      <p>A kerületlista legérdekesebb része a ráta. Ezen keresztül látszik, mennyi mű jut egy lakosra. De az is jól átlátható, hol van még munkánk. Minél alacsonyabb a ráta, annál inkább lehetséges, hogy az adott kerületben több lapul még a köztereken és a közösségi helyeken. Persze ez csalóka, hisz a kevésbé felkapottabb városrészek, külső kerületek műellátottsága lényegesen alacsonyabb, mint a belváros, ahol reprezentálni <em>kell</em>.</p>
      <p><strong>Ráta</strong>: egy lakosra jutó műlapok száma (műlapok száma / lakosság száma).</p>
      <p><strong>Lakos</strong>: kerület lakossága, ezer főben megadva.<br />Forrás: https://hu.wikipedia.org/wiki/Budapest_ker%C3%BCletei <span class="text-muted">(frissítve: 2019.02.23.)</span>.</p>
      <p>Ha tetszik ez a ráta-dolog, nézd meg a <?=$app->Html->link('hazai vármegyéket', '/helyek/megyek')?> is!</p>

      <hr class="highlighter" />

      <p>* Valami fura? Igen, a Margit-szigetet "Margitsziget" városrész néven 2013. július 20-án elvették a 13. kerülettől és a főváros igazgatása alá került. Vagyis nem a 13. kerület része, de nem is igazi kerület. Mi ezért külön tartjuk nyilván.</p>
    </div>
  </div>
</div>