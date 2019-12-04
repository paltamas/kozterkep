<div class="row">
  <div class="col-md-8 mb-4">
    <div class="row">
      <div class="col-lg-6 mb-5">
        <?php
        echo $app->Html->image($book['cover_file_id'], [
          'link' => [
            'self',
            ['target' => '_blank']
          ],
          'class' => 'border img-fluid',
        ]);
        ?>
      </div>
      <div class="col-lg-6 mb-5">
        <?php
        echo '<h5 class="subtitle">A könyvről</h5>';
        echo $app->Html->dl('create');
        echo $app->Html->dl(['Szerző(k)', $book['writers']]);
        echo $app->Html->dl(['Kiadó', $book['publisher']]);
        echo $app->Html->dl(['Kiadás helye', $book['publishing_place']]);
        echo $app->Html->dl(['Kiadás éve', $book['published']]);
        echo $app->Html->dl(['Oldalszám', $book['page_number']]);
        echo $app->Html->dl('end');

        echo '<h5 class="subtitle mt-4">Feltöltés</h5>';
        echo $app->Html->dl('create');
        echo $app->Html->dl(['Létrehozta', $app->Users->name($book['user_id'], ['image' => true])]);
        echo $app->Html->dl(['Ideje', _time($book['created'])]);
        echo $app->Html->dl('end');
        ?>
      </div>
      <div class="col-md-12">
        <h5 class="subtitle">Példányok</h5>
        <?php
        if (count($owners) > 0) {
          foreach ($owners as $user_id => $data) {
            echo '<div class="rounded p-2 border mb-3">';
            echo '<div class="small text-muted float-right">' . _time($data['created']) . '</div>';

            echo $app->Users->name($user_id, [
              'image' => true,
              'class' => 'font-weight-bold',
            ]);

            echo '<div class="mt-2 mb-3">Kiadás: <strong>';
            echo $data['publishing_place'] != '' ? $data['publishing_place'] : '';
            echo $data['publishing_place'] != '' && $data['published'] ? ', ' : '';
            echo $data['published'] != '' ?  $data['published'] : '';
            echo '</strong>';
            echo $data['page_number'] > 0 ? ' (' . $data['page_number'] . ' oldal)' : '';
            echo '</div>';

            echo $data['description'] != ''
              ? $app->Text->format($data['description']) : '';

            echo '</div>';
          }
        }
        ?>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <?php
    if ($_user) {
      echo '<div class="kt-info-box">A könyv példányok rögzítése egyelőre szünetel, mert az ehhez szükséges funkció még nem készült el.</div>';
    }
    ?>
    <?php
    /*
    echo '<h5 class="subtitle">Hozzászólások</h5>';
    echo $app->element('comments/thread', [
      'model_name' => 'book',
      'model_id' => $book['id'],
      'files' => false,
    ]);
    */
    ?>
  </div>
</div>
