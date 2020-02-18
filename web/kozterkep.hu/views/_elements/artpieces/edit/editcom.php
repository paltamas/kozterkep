<div class="row">

  <div class="col-md-6 col-lg-5 mb-4">

    <div class="ajaxdiv-usermemo" ia-ajaxdiv="/mulapok/tagmemo/<?=$artpiece['id']?>"></div>

    <h4 class="subtitle mb-3">Szerkesztések</h4>

    <?php
    if ($_user['id'] == $artpiece['user_id'] && $artpiece['status_id'] != 5) {
      echo '<p>' . $app->Html->link('Megosztás előtti szerkesztéseim törlése', '/mulapok/szerkeszteseim_torlese/' . $artpiece['id'], [
        'icon' => 'trash',
        'ia-confirm' => 'Biztos töröljük?'
      ]) . '</p>';
    }

    if ($artpiece['status_id'] == 1) {
      echo '<div class="mb-3 text-muted">A megosztás előtti szerkesztéseidet ellenőrzésre küldéskor vagy műlap publikáláskor automatikusan töröljük.</div>';
    }

    $own = 0;

    if (count($edits) == 0) {
      echo '<p class="text-muted">Nincs megjeleníthető szerkesztés.</p>';
    } else {

      $items = [];

      foreach ($edits as $item) {
        if (@$item['invisible'] == 1
          && !in_array($_user['id'], [$item['user_id'], $item['receiver_user_id']])) {
          continue;
        }

        if (@$item['own_edit'] == 1 && $artpiece['status_id'] == 5) {
          $own++;
          continue;
        }

        // Várakozókat előre
        $type = $item['status_id'] == 2 ? 'waiting' : 'others';

        $items[$type] = !isset($items[$type]) ? '' : $items[$type];

        if (@$item['invisible'] == 1) {
          $div_class = 'bg-light';
        } else {
          $div_class = $item['status_id'] == 2 ? 'bg-yellow-light' : '';
        }

        $items[$type] .= $app->element('artpieces/edit/edit_item', [
          'edit' => $item,
          'artpiece' => $artpiece,
          'options' => [
            'div_class' => $div_class
          ],
        ]);
      }

      echo @$items['waiting'];
      echo @$items['others'];
    }

    if ($own > 0) {
      echo '<p class="mt-2 text-muted">';
      echo (@count($items['waiting']) + @count($items['others'])) > 0 ? 'Továbbá ' : 'Eddig még nem volt más tag általi műlap szerkesztés, csak ';
      echo $own . ' saját műlap módosítás.';
      echo '</p>';
    }
    ?>
  </div>

  <div class="col-md-6 col-lg-7 mb-4">

    <div class="ajaxdiv-adminmemo" ia-ajaxdiv="/mulapok/adminmemo/<?=$artpiece['id']?>"></div>

    <h4 class="subtitle">Hozzászólások</h4>
    <?php

    if ($_user['id'] == $artpiece['user_id'] && $artpiece['status_id'] != 5) {
      echo '<p>' . $app->Html->link('Megosztás előtti hozzászólások törlése', '/mulapok/hozzaszolasok_torlese/' . $artpiece['id'], [
          'icon' => 'trash',
          'ia-confirm' => 'Biztos vagy ebben? A törlés végérvényes, vagyis ha később szükséged lenne valamire, ami ezekben a kommentekben volt, abban nem tudunk segíteni.'
        ]) . '</p>';
    }

    echo $app->element('comments/thread', [
      'model_name' => 'artpiece',
      'model_id' => $artpiece['id'],
      'custom_field' => 'forum_topic_id',
      'custom_value' => 0,
      'files' => false,
      'search' => false,
      'link_class' => 'd-block',
    ]);
    ?>
  </div>

</div>
