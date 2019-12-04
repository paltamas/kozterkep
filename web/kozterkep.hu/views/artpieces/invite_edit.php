<div class="mb-4">
  <?php
  echo $app->Html->link('Visszalépés a szerkesztéshez', '/mulapok/szerkesztes/' . $artpiece['id'] . '#szerk-muveletek', [
    'class' => 'btn btn-outline-secondary',
    'icon' => 'arrow-left',
  ]);
  ?>
</div>

<div class="row">
  <div class="col-sm-6 col-md-4 mb-4">
    <h4 class="subtitle">Új tag meghívása</h4>
    <p>Mielőtt meghívsz valakit, egyeztess vele, hogy vállalja-e a segítséget! A meghívott egy értesítést kap a meghívásról.</p>

    <?php
    echo $app->Form->create(null, [
      'class' => 'noEnterForm',
      'method' => 'post',
    ]);

    echo $app->Form->input('user_id', [
      'placeholder' => 'Címzett neve',
      'autocomplete' => 'off',
      'class' => 'focus userSelect',
      //'help' => 'Kezdd el gépelni a tag nevét és válassz a felajánlott lehetőségek közül.',
      'ia-autouser' => 'users',
      'ia-autouser-min' => 3,
      'ia-autouser-query' => 'name',
      'ia-autouser-key' => 'id',
      'ia-autouser-image' => 'profile_photo_filename',
      'value' => '',
      'required' => true
    ]);

    echo $app->Form->submit('Meghívás elküldése');

    echo $app->Form->end();
    ?>


  </div>
  <div class="col-md-1"></div>
  <div class="col-sm-6 col-md-4 mb-4">
    <?php
    if (count($invited_users) > 0) {

      echo '<h4 class="subtitle">Meghívott tagok</h4>';
      foreach ($invited_users as $invited_user) {
        echo '<div class="row mt-3">';
        echo '<div class="col-10 col-md-6">';
        echo $app->Users->name($invited_user, [
          'class' => 'font-weight-bold',
          'image' => true,
        ]);
        echo '</div>';

        echo '<div class="col-2 col-md-6">';
        echo $app->Html->link('Meghívás törlése', '/mulapok/szerkesztes_meghivas/' . $artpiece['id'] . '?torles=' . $invited_user, [
          'icon' => 'trash',
          'hide_text' => true,
          'class' => 'mr-3 text-muted',
          'ia-confirm' => 'Biztosan törlöd a meghívottat? A meghívás törlése előtt rögzített szerkesztések a műlapon maradnak.',
        ]);
        echo '</div>';

        echo '</div>';
      }
    }
    ?>

  </div>
</div>