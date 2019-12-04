<div class="row">

  <div class="col-12 col-md-6 mb-4 pt-4 pb-0 px-3 bg-gray-kt rounded">
    <?php
    echo $app->element('layout/partials/simple_search_form', ['options' => [
      'placeholder' => 'Keresés ' . $book_count . ' könyv között',
      'custom_inputs' => []
    ]]);
    ?>
  </div>

  <div class="col-12 col-md-6 mb-4">
    <div class="kt-info-box">
      <strong>A Könyv<span class="text-primary">tér</span> a mi kis kézikönyvtárunk</strong>, ahol tagjaink tölthetik fel saját könyveiket. Ha úgy véled, egy könyvben szerepelhet, amit keresel, kérdezd a példányok tulajdonosait!
    </div>
  </div>

</div>

<?php
if ($keyword) {

  echo '<h4 class="subtitle my-4">Találati lista <span class="text-muted">(' . count($results) . ')</span></h4>';

  if (count($results) > 0) {
    $i = 0;

    echo '<div class="table-responsive">';
    echo '<table class="table table-hover table-striped table-sm">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Cím</th>';
    echo '<th>Szerzők</th>';
    echo '<th>Kiadás</th>';
    echo '<th>Megvan</th>';
    echo '</tr>';
    echo '</thead>';
    foreach ($results as $result) {
      if ($result['cover_file_id'] > 0) {
        $image = $app->Html->image($result['cover_file_id'], [
          'link' => [
            '/adattar/konyv/' . $result['id']
          ],
          'class' => 'border float-left mr-2 mt-1',
          'height' => 50
        ]);
      }


      echo '<tr>';

      echo '<td class="font-weight-bold">' . $image . $app->Html->link($result['title'], '/adattar/konyv/' . $result['id']) . '</td>';

      echo '<td>' . $result['writers'] . '</td>';

      echo '<td>' . $result['publisher'] . ' (';
      echo $result['publishing_place'] != '' ? $result['publishing_place'] : '';
      echo $result['publishing_place'] != '' && $result['published'] != '' ? ', ' : '';
      echo $result['published'] != '' ? $result['published'] : '';
      echo ')</td>';

      echo '<td>' . $result['owner_count'] . ' tagnak</td>';

      echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
  }


} else {

  echo '<h4 class="subtitle my-4">Népszerű könyvek</h4>';

  echo '<div class="row">';

  foreach ($top_books as $top_book) {

    echo '<div class="col-md-6 col-lg-4 mb-5">';

    echo '<h5>' . $app->Html->link($top_book['title'], '/adattar/konyv/' . $top_book['id']) . '</h5>';

    echo $app->Html->image($top_book['cover_file_id'], [
      'link' => [
        '/adattar/konyv/' . $top_book['id']
      ],
      'class' => 'shadow float-left rounded mr-2',
      'height' => 125
    ]);

    echo '<div class="mt-3 mb-3 font-weight-semibold">' . $top_book['writers'];
    if (!_contains($top_book['writers'], [',', '('])) {
      echo $app->Html->link('', '/adattar/konyvter?kulcsszo=' . urlencode($top_book['writers']), [
        'icon' => 'search',
        'class' => 'ml-2',
        'title' => 'Szerző könyveinek keresése'
      ]);
    }
    echo '</div>';

    echo '<div class="mb-1 text-muted">' . $top_book['publisher'] . ' (';
    echo $top_book['publishing_place'] != '' ? $top_book['publishing_place'] : '';
    echo $top_book['publishing_place'] != '' && $top_book['published'] != '' ? ', ' : '';
    echo $top_book['published'] != '' ? $top_book['published'] : '';
    echo ')</div>';

    echo '<div class="text-muted small">' . $top_book['owner_count'] . ' tagnak van meg</div>';

    echo '</div>';
  }

  echo '</div>';

}

if ($_user) {
  echo $app->element('layout/partials/construction', ['text' => 'Új könyv hozzáadás, saját példány és borító rögzítése']);
}

?>
