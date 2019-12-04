<div class="row my-3 my-md-4">
  <div class="col-md-2"></div>
  <div class="col-md-8 bg-gray-kt p-4 rounded">
    <h5 class="mt-0 mb-2"><?=$app->Html->icon('search mr-1')?>Gyorskeresés</h5>
    <?php
    echo $app->Form->input('instant_search', [
      'placeholder' => 'Műlap keresés...',
      'class' => 'd-inline-block form-control-lg instant-search input-no-clear',
      'data-target' => '#instant-search',
      'divs' => 'mb-1',
    ]);

    echo '<div class="text-right mt-1 pr-3">';
    echo $app->Html->link('Részletes keresés', '/kereses?r=1', [
      'icon' => 'far fa-search',
      'class' => 'font-weight-bold'
    ]);
    echo '</div>';
    ?>
  </div>
  <div class="col-md-2"></div>

  <div class="col-12">
    <div id="instant-search" class="my-4"></div>
  </div>
</div>