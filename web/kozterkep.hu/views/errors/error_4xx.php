<?=$app->Log->write('404: ' . $_params->referer . ' ==> ' .$_params->here . ' // ' . $_params->user_agent, '404')?>
<div class="row">
  <div class="col-md-8">

    <p class="lead font-weight-bold">404-es hiba, vagyis a keresett "<?= $_params->here ?>"
      webcím nem található.</p>

    <p>Amennyiben úgy gondolod, hogy itt lennie kellene valaminek, úgy kérjük <a
        href="/oldalak/kapcsolat?webcim=<?=urlencode($_params->here)?>" class="font-weight-bold">jelezd nekünk ezen az űrlapon!</a><br /><strong>2019. április 22-én megújultunk</strong>, így a kezdeti átállás alatt kifejezetten értékes segítség, ha jelzed, hogy ezzel a webcímmel szerinted gond van.</p>

  </div>

  <div class="col-md-4">

    <?php
    echo $app->Html->image('gyermeknevelesi-hiba-oslo-vigeland-park.jpg', [
      'class' => 'img-fluid rounded'
    ]);
    echo '<div class="mt-1 small text-muted text-center">A dühös fiú (Sinnataggen, Gustav Vigeland), Oslo (Frogner Park)</small>';
    ?>

  </div>
</div>