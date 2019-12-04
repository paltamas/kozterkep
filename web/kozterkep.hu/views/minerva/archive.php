<p class="kt-info-box">Archívumunk az újabban "Heti szüret" megnevezés alatt futó Minerva-hírleveleink közös részét listázza 2017. októberig visszamenőleg. A közös részt minden feliratkozó megkapja. A levelek emellett személyesen a feliratkozó számára beszúrt blokkokat is tartalmaznak a beállításaitól valamint a követéseitől függően. Ezeket a részeket nem archiváljuk.</p>
<?php
echo $app->Html->pagination(count($newsletters), $pagination);

echo '<div class="table-responsive">';
echo '<table class="table table-striped table-hover">';
echo '<thead>';
echo '<tr>';
echo '<th>Kiküldés</th>';
echo '<th>Tárgy</th>';
echo '<th>Címzettszám</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach ($newsletters as $newsletter) {
  echo '<tr>';
  echo '<td>' . _time($newsletter['sent']) . '</td>';
  echo '<td>' . $app->Html->link($newsletter['subject'], '/minerva/archiv_hirlevel/' . $newsletter['id'], [
    'ia-modal' => 'modal-md',
    'class' => 'font-weight-bold',
  ]) . '</td>';
  echo '<td>' . _n($newsletter['receiver_count']) . '</td>';
  echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';
echo $app->Html->pagination(count($newsletters), $pagination);