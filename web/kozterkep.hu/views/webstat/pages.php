<?php
echo $app->element('layout/partials/simple_search_form', ['options' => [
  'placeholder' => 'Oldal webcímben',
  'start_filter' => true,
]]);

if (@$_params->query['kulcsszo'] != '') {
  if (count($results) > 0) {

    echo '<p>Az alábbi hasonló oldalakat találtuk a statisztikában. Kattints az adott oldalra a részletes oldal-statisztika megtekintéséhez.</p>';

    echo '<ul>';
    foreach ($results as $item) {

      // Kizárt kereséseket nem mutatjuk
      if (_contains($item->_id, ['/beszelgetesek', '/tagsag'])) {
        continue;
      }

      echo '<li>' . $app->Html->link($item->_id, '/webstat/oldalak?p=' . $item->_id) . '</li>';
    }
    echo '</ul>';

  } else {
    echo '<p class="text-muted">';
    echo strlen($_params->query['kulcsszo']) < 3 ? 'Legalább 3 karaktert adj meg.' : texts('nincs_talalat');
    echo '</p>';
  }
}

?>

<?php if ($stats) {?>

  <h4 class="subtitle">Oldal: <?=$app->Html->link($page, $page)?></h4>

  <div class="row">
    <div class="col-md-12 mb-4">
      <h4 class="subtitle">90 nap</h4>

      <?php
      $p = explode('/', $page);
      if (is_numeric($p[1])) {
        echo '<p class="text-muted"><strong>Műlapok esetében</strong>, a publikálás előtti látogatásokat is mérjük, mert azok a látogatások is a webstatisztika részét képezik.</p>';
      }
      ?>

      <p class="text-muted">Egyedi látogatók számának alakulása az elmúlt 90 napban<?=(strtotime(date('Y-m-d')) - strtotime(APP['kt2_start'])) < 90*24*60*60 ? ' (a mérés ' . _date(APP['kt2_start']) . ' napon indult)' : ''?>.</p>

      <canvas class="chart"
        ia-chart-type="line"
        ia-chart-labels="<?=htmlentities(json_encode(array_keys($stats['sessions'])))?>"
        ia-chart-data="<?=htmlentities(json_encode(array_values($stats['sessions'])))?>"
      ></canvas>
    </div>

    <div class="col-md-12">
      <h4 class="subtitle">Top hivatkozó URL-ek az elmúlt 90 napban</h4>
      <p class="text-muted">Ezekről a webcímekről érkeztek látogatók erre az aloldalra.</p>
      <?php
      if (count($referrers) > 0) {
        echo $app->Html->table('create', [
          '#',
          'Hivatkozó URL',
          'Oldalletöltés',
        ]);
        $i = 0;
        foreach ($referrers as $referrer) {
          $i++;
          $ref_url = $referrer->_id != '' ? $app->Html->link($app->Text->truncate($referrer->_id, 60, 'substr'), $referrer->_id, [
            'target' => '_blank',
            'title' => $referrer->_id,
          ]) : 'Nincs / Direkt';
          echo '<tr>';
          echo '<td>' . $i . '</td>';
          echo '<td>' . $ref_url . '</td>';
          echo '<td>' . _n($referrer->count) . '</td>';
          echo '</tr>';
        }
        echo $app->Html->table('end');
      }
      ?>
    </div>
  </div>

<?php } ?>

<div class="text-muted mt-4"><span class="far fa-info-circle mr-2"></span>A részletes mérés <?=_date(APP['kt2_start'])?> napon indult.</div>
