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
    ], [
      'class' => 'mb-3',
      'type' => 'pills',
      'selected' => isset($_params->query['abc']) ? 2 : 1,
    ])
    ?>


    <table class="table table-sm table-striped">
      <tbody>
      <?php
      /**
       * Haha, ha abc-t akarunk, akkor mivel
       * nem DB-ből jön a cucc, kell egy kis izélódás.
       * A slug azért kell, mert a ksort nem érzi az ékezetet
       */
      if (isset($_params->query['abc'])) {
        $countries_ = [];
        foreach ($countries as $country) {
          $countries_[$app->Text->slug(sDB['countries'][$country['country_id']][1])] = $country;
        }
        ksort($countries_);
        $countries = $countries_;
      }

      $i = $sum = 0;
      foreach ($countries as $country) {
        $sum += $country['artpiece_count'];
        $i++;
        echo '<tr>';
        echo '<td>' . $i . '.</td>';
        echo '<td class="font-weight-bold">' . $app->Places->country($country['country_id']) . '</td>';
        echo '<td>' . _n($country['artpiece_count']) . '</td>';
        echo '</tr>';
      }
      ?>
      </tbody>
    </table>
  </div>

  <div class="col-md-5">
    <div class="kt-info-box">
      <p>A Köztérképen kiemelten a hazai és a magyar vonatkozású külföldi alkotásokra fókuszálunk.</p>
      <p>Voltak évek, amikor nem is engedtünk be nem magyar vonatkozású külföldi művet, de most már mindenhonnan várjuk az alkotásokat.</p>
    </div>
  </div>
</div>