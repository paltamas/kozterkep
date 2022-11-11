<?php
if ($artpiece) {
  echo $app->element('artpieces/list/item', [
    'options' => [
      'photo_size' => isset($_params->query['meret'])
        && $_params->query['meret'] < 8 && $_params->query['meret'] > 0
          ? $_params->query['meret'] : 5,
      'details' => true,
      'background' => '',
      'links' => true,
      'extra_class' => 'text-center',
      'condition' => true,
      'tooltip' => true,
    ],
  ]);
}