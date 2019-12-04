<div class="row">

  <div class="col-12 mb-4 pt-4 pb-0 px-3 bg-gray-kt rounded">
  <?php
  echo $app->element('layout/partials/simple_search_form', ['options' => [
    'placeholder' => 'Kulcsszó',
    'custom_inputs' => []
  ]]);
  ?>
  </div>

  <?php
  if ($keyword != '') {

    echo '<div class="col-12 p-3 mb-4 bg-light rounded">';
    echo '<h4 class="subtitle mb-4">Találati lista <span class="text-muted">(' . count($results) . ')</span></h4>';

    if (count($results) > 0) {
      $i = 0;

      foreach ($results as $result) {
        $i++;

        echo '<h4>' . $app->Html->link($result->name, '/adattar/lexikon_szocikk/' . $result->person_id . '?kiemeles=' . $keyword) . '</h4>';

        echo '<h6 class="text-muted">' . $result->subtitle . '</h6>';

        $text = str_replace('<h2 class="title"', '<h2 class="d-none"', $result->text);
        $text = str_replace('<h3 class="subtitle"', '<h3 class="d-none"', $text);
        if (strpos(mb_strtolower($text), mb_strtolower($keyword)) !== false) {
          echo ltrim($app->Text->format($text, [
            'strip_tags' => true,
            'highlight' => $_params->query['kulcsszo'],
            'highlight_excerpt' => 150,
          ]), ',');
        }

        echo $i < count($results) ? '<hr />' : '';

      }
    }

    echo '</div>';

  }
  ?>

  <div class="col-md-6 mb-3">
    <p>Az <strong><a href="http://enciklopediakiado.hu" target="_blank">Enciklopédia Kiadó</a></strong> által kiadott 3 kötetes <strong>Kortárs Magyar Művészeti Lexikon</strong> alapvető kutatási adatbázis számunkra. Az online térben itt-ott elérhető valamilyen digitális formában, de szerettük volna könnyen kereshető formában közreadni. A mi változatunk az eredetileg 2001-ben lezárt és kiadott lexikon adatbázisának kivonata: a művészeket bemutató <?=_n($person_count)?> szócikket tartalmazza.</p>

    <p>Megjelenés éve: 1999&ndash;2001<br />Főszerkesztő: Fitz Péter<br />Felelős szerkesztő és kiadó: F. Almási Éva</p>

    <p>A köteteket a kiadó még forgalmazza, <a href="http://enciklopediakiado.hu/?p=638" target="_blank">további információkért kattints ide</a>.</p>

    <hr />

    <p>A Köztérkép 2019-es újraírásakor felkerestük a kiadó vezetőjét, <span class="text-nowrap">Almási Évát,</span> aki hozzájárult az adatbázis publikus megjelentetéséhez, amit ezúton is nagyon köszönünk!</p>

    <p>Az adatbázis a lexikon személyeket bemutató szócikkeinek digitális mása, adatait nem frissítjük, azokat csak közreadjuk, hogy ezzel segítsük tagjaink és a látogatók kutatásait.</p>

  </div>

  <div class="col-md-6">
    <div class="my-1">
      <img src="/img/etc/kmml.jpg" class="img-fluid" />
    </div>
  </div>
</div>
