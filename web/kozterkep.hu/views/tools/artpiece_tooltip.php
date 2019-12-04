<?php
if ($artpiece) {
  echo $app->element('artpieces/list/item', [
    'options' => [
      'background' => '',
      'links' => false,
      'extra_class' => 'p-2',
      'superb' => true,
      'condition' => true,
    ],
  ]);
}