<?=$app->element('layout/partials/construction', ['text' => [
  'Egyelőre csak megtekintésre pakoltam ide a KT1-ben mentett raklapok tartalmát.',
  'Ezen az oldalon szerkesztheted majd könyvjelzőket, és törölheted az elemeket.',
  'Az egyes oldalakon majd egy könyvjelző ikon jelenik meg, azzal pakolhatod be a könyvjelző mappákba az egyes linkeket vagy műlapokat.',
]])?>

<?php
if (count($bookmarks) > 0) {
  foreach ($bookmarks as $bookmark) {

    echo '<h3 class="mt-5 text-center"><a href="#bookmarks-' . $bookmark['id'] . '" data-toggle="collapse">' . $bookmark['name'] . '</s></h3>';

    if (count($bookmark['items']) > 0) {

      echo '<div class="row collapse" id="bookmarks-' . $bookmark['id'] . '">';
      foreach ($bookmark['items'] as $artpiece_id) {
        echo '<div class="col-6 col-md-3 col-lg-2 mb-3">';
        $artpiece = $app->MC->t('artpieces', $artpiece_id);
        echo $app->element('artpieces/list/item', [
          'artpiece' => $artpiece,
          'options' => [
            'photo_size' => 5,
            'tooltip' => true,
            'details' => false,
            'details_simple' => true,
          ]
        ]);
        echo '</div>';
      }

      echo '</div>';

      echo '<hr class="my-5" />';

    } else {
      echo '<p class="text-muted">Nincs még elem benne</p>';
    }

  }
} else {
  echo $app->element('layout/partials/empty', ['text' => '']);
}

?>