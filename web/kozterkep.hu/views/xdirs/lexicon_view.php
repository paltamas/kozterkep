<div class="row">

  <div class="col-md-8 order-2 order-md-1 mb-4">

    <?php
    if (isset($_params->query['kiemeles']) && $_params->query['kiemeles'] != '') {
      echo '<div class="mb-4">';
      echo $app->Html->link('Vissza a kereséshez', $_params->referer, [
        'class' => 'btn btn-outline-secondary',
        'icon' => 'arrow-left',
      ]);
      echo '</div>';
    }
    ?>

    <div class="text-muted float-right">#<?=$person['person_id']?></div>
    <h5 class="text-muted mb-4"><?=$person['subtitle']?></h5>

    <?php
    if ($artist) {
      echo '<div class="kt-info-box my-3">';
      echo $app->Html->link('<strong>' . $artist['name'] . '</strong> alkotói adatlapja', '', [
        'artist' => $artist,
        'icon_right' => 'arrow-right'
      ]);
      echo '</div>';
    }
    ?>

    <div>
      <?php
      $text = str_replace('<h2 class="title"', '<h2 class="d-none"', $person['text']);
      $text = str_replace('<h3 class="subtitle"', '<h3 class="d-none"', $text);
      if (isset($_params->query['kiemeles']) && $_params->query['kiemeles'] != '') {
        echo $app->Text->format($text, [
          'strip_tags' => false,
          'highlight' => $_params->query['kiemeles']
        ]);
      } else {
        echo $text;
      }
      ?>
    </div>

  </div>

  <div class="col-md-4 order-1 order-md-2 mb-4">
    <div class="kt-info-box">
      <p><span class="fas text-danger fa-exclamation-triangle mr-2"></span>Az adatok az <a href="http://enciklopediakiado.hu/" target="_blank">Enciklopédia Kiadó</a> által kiadott <span class="font-italic">Kortárs Magyar Művészeti Lexikon I-III.</span> köteteinek digitális változatából származnak. A kötetek tartalmát <strong>nem frissítjük</strong>, csak közreadjuk.</p>
      <?=$app->Html->link('Információk a lexikonról', '/adattar/lexikon', [
        'icon' => 'info-circle'
      ])?>
    </div>
  </div>

</div>
