<?php
/*echo $app->Html->link('', '#', [
  'class' => 'ariaSwitch link-gray',
  'icon' => 'low-vision',
  'divs' => 'd-inline-block',
  'title' => 'Gyengénlátó verzió ki/bekapcsolása'
]);
*/

echo $app->Html->tag('div',
  $app->Html->link('', '#', [
    'class' => 'textSizeReset link-gray px-1',
    'icon' => 'font',
    'title' => 'Betűméret visszaállítása alapértelmezettre'
  ]) .
  $app->Html->link('', '#', [
    'class' => 'textSize link-gray px-1',
    'data-size' => '1',
    'icon' => 'plus-square',
    'title' => 'Betűméret növelése'
  ]) .
  $app->Html->link('', '#', [
    'class' => 'textSize link-gray px-1',
    'data-size' => '-1',
    'icon' => 'minus-square',
    'title' => 'Betűméret csökkentése'
  ]),
  ['class' => 'd-inline-block ml-3']
);