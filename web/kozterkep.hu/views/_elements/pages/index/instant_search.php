<div class="row my-3 my-md-4">
  <div class="col-md-6 col-lg-7 p-4 bg-gray-kt rounded">
    <?php

    echo '<div class="float-right pr-3">';
    echo $app->Html->link('Részletes keresés', '/kereses?r=1', [
      'icon' => 'far fa-search',
      'class' => 'font-weight-bold'
    ]);
    echo '</div>';

    echo '<h5 class="mt-0 mb-2">' . $app->Html->icon('search mr-1') . 'Gyorskeresés</h5>';

    echo $app->Form->input('instant_search', [
      'placeholder' => 'Műlap keresés...',
      'class' => 'd-inline-block form-control-lg instant-search input-no-clear',
      'data-target' => '#instant-search',
      'divs' => 'mb-1',
    ]);


    ?>
  </div>

  <div class="col-md-6 col-lg-5">
    <?=$app->element('pages/index/short_intro')?>
  </div>

  <div class="col-12">
    <div id="instant-search" class="my-4"></div>
  </div>
</div>