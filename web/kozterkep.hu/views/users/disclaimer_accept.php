<div class="row">
  <div class="col-md-2"></div>
  <div class="col-md-7 my-3 my-md-5">

    <h2>Kedves <?=$_user['name']?>,</h2>

    <?=$app->element('layout/etc/reborn_welcome')?>

    <hr />

    <?php
    echo $app->Form->create($_params->data, [
      'method' => 'post',
      'class' => 'my-4',
    ]);

    echo '<div class="mb-2 lead">';
    echo $app->Html->link('Jogi nyilatkozat', '/oldalak/jogi-nyilatkozat', [
      'target' => '_blank',
      'icon' => 'book'
    ]);
    echo '</div>';
    echo '<div class="mb-2 lead">';
    echo $app->Html->link('Adatkezelési szabályzat', '/oldalak/adatkezelesi-szabalyzat', [
      'target' => '_blank',
      'icon' => 'book'
    ]);
    echo '</div>';
    echo '<div class="mb-2 lead">';
    echo $app->Html->link('Működési elvek', '/oldalak/mukodesi-elvek', [
      'target' => '_blank',
      'icon' => 'book'
    ]);
    echo '</div>';

    echo $app->Form->end('Elfogadom a Köztérkép új szabályzatait', [
      'class' => 'btn-primary btn-lg',
      'divs' => 'my-3 text-left'
    ]);
    ?>

    <p>A weboldal használatához el kell fogadnod az új szabályokat. Amennyiben később szeretnéd elfogadni új szabályainkat, <?=$app->Html->link('ide kattintva kijelentkezhetsz', '/tagsag/kilepes', [
      'icon_right' => 'sign-out'
    ])?> és így böngészheted tovább a lapot.</p>
    <?php if ($_user['artpiece_count'] < 5) { ?>
    <p>Amennyiben nem szeretnéd elfogadni a szabályokat, és törölnéd hozzáférésedet, <a href="/tagsag/profil-torlese">kattints ide</a> a további instrukciókért.</p>
    <?php } ?>
  </div>
  <div class="col-md-3"></div>
</div>