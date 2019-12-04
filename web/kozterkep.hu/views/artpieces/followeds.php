<?php
if (count($followeds) > 0) {

  echo '<div class="row" id="lista">';

  foreach ($followeds as $followed) {

    $artpiece = $app->MC->t('artpieces', $followed);

    if (!$artpiece) {
      continue;
    }

    echo '<div class="followed col-6 col-md-4 col-lg-3 d-sm-flex px-0 px-sm-2 mb-3">';
    echo $app->element('artpieces/list/item', [
      'artpiece' => $artpiece,
    ]);
    echo '</div>';

  }

  echo '</div>';

} else {
  echo '<div class="text-muted my-3"><p>Jelenleg nem követsz egy műlapot sem.</p><p>Használd a műlapokon megjelenő <span class="btn btn-outline-secondary btn-sm"><span class="fal fa-star"></span> Követés</span> gombot, és erre kattintva mentsd a műlapot, ha figyelemmel tartanád.</p></div>';
}