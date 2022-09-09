
<div class="row">
  <?php if ($_user['headitor'] == 1) { ?>
  <div class="col-12 mb-2">
    <?=$app->element('space/index/waiting_edits')?>
    <hr class="my-3" />
  </div>
  <?php } ?>

  <div class="col-md-7 mb-4 order-2 order-md-1">
    <h4 class="subtitle">Műlap szerkesztések időrendben</h4>
    <?php

    echo '<div class="my-2">';
    echo $app->element('space/edits/search_form');
    echo '</div>';

    if (count($edits) > 0) {

      //echo '<div class="text-muted mb-2">' . _n($edits_total) . ' találat</div>';

      foreach ($edits as $edit) {
        echo $app->element('artpieces/edit/edit_item', [
          'edit' => $edit,
          'options' => [
            'artpiece_details' => true
          ],
        ]);
      }

      echo $app->Html->pagination(count($edits), $pagination);
    }
    ?>
  </div>
  <div class="col-md-5 mb-4 order-1 order-md-2">
    <h4 class="subtitle">Várakozó szerkesztések nálam</h4>
    <?php
    if (count($edits_for_me) > 0) {
      foreach ($edits_for_me as $edit) {
        echo $app->element('artpieces/edit/edit_item', [
          'edit' => $edit,
          'options' => [
            'artpiece_details' => true
          ],
        ]);
      }
    } else {
      echo '<p class="text-muted">' . texts('nincs_elem') . '</p>';
    }
    ?>

    <hr class="my-4" />

    <h4 class="subtitle">Enyémek másnál</h4>
    <?php
    if (count($edits_by_me) > 0) {
      foreach ($edits_by_me as $edit) {
        echo $app->element('artpieces/edit/edit_item', [
          'edit' => $edit,
          'options' => [
            'artpiece_details' => true
          ],
        ]);
      }
    } else {
      echo '<p class="text-muted">' . texts('nincs_elem') . '</p>';
    }
    ?>


  </div>

</div>