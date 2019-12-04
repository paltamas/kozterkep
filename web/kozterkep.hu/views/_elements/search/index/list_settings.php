<?php
echo '<div class="row my-3">';

echo '<div class="col-12 col-sm-4 my-2 d-lg-none text-center pt-1">';
echo '<strong class="text-muted">' . _n($total_count) . ' találat</strong>';
echo '</div>';

echo '<div class="col-md-8 d-none d-lg-block my-2">';
$pagination['div'] = 'my-0';
$pagination['centered'] = false;
echo $app->Html->pagination(count($artpieces), $pagination);

echo '</div>';

echo '<div class="col-4 col-sm-3 col-md-3 col-lg-1 text-center text-md-right my-2 form-inline">';

echo $app->Form->input('elem', [
  'options' => [
    24 => 24,
    36 => 36,
    50 => 50,
    100 => 100,
    200 => 200,
    500 => 500,
  ],
  'value' => @$_params->query['elem'] > 0 ? $_params->query['elem'] : 36,
  'class' => 'form-control-sm',
  'ia-urlchange-input' => 'elem',
]);
echo '</div>';

echo '<div class="col-8 col-sm-5 col-md-5 col-lg-3 text-center text-md-right my-2 form-inline">';
echo $app->Form->input('sorrend', [
  'options' => [
    'publikalas-csokkeno' => 'Publikálás, csökkenő',
    'publikalas-novekvo' => 'Publikálás, növekvő',
    'evszam_utolso-csokkeno' => 'Utolsó évszám, csökkenő',
    'evszam_utolso-novekvo' => 'Utolsó évszám, növekvő',
    'evszam_elso-csokkeno' => 'Első évszám, csökkenő',
    'evszam_elso-novekvo' => 'Első évszám, növekvő',
    'nezettseg-csokkeno' => 'Nézettség, csökkenő',
    'nezettseg-novekvo' => 'Nézettség, növekvő',
    'napi_nezettseg-csokkeno' => 'Napi nézettség, csökkenő',
    'napi_nezettseg-novekvo' => 'Napi nézettség, növekvő',
  ],
  'value' => @$_params->query['sorrend'] != '' ? $_params->query['sorrend'] : 'publikalas-csokkeno',
  'class' => 'form-control-sm',
  'ia-urlchange-input' => 'sorrend',
]);
echo '</div>';

echo '</div>';