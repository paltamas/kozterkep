<?php
echo $app->Html->tabs([
  'Komm' => [
    'hash' => 'hozzaszolasok',
    'icon' => 'comments fa-lg',
  ],
  'SzerkKomm' => [
    'hash' => 'szerkhozzaszolasok',
    'icon' => 'comment-edit fa-lg',
  ],
  'Párbeszéd' => [
    'hash' => 'parbeszedek',
    'icon' => 'stream fa-lg',
    'options' => [
      'ia-ajaxdiv-load' => '/kozter/parbeszedek',
      'ia-ajaxdiv-target' => '.ajaxdiv-threads',
    ]
  ],
  'Fotók' => [
    'hash' => 'fotok',
    'icon' => 'images fa-lg',
    'options' => [
      'ia-ajaxdiv-load' => '/kozter/friss_fotok',
      'ia-ajaxdiv-target' => '.ajaxdiv-photos',
    ]
  ],
  'Történet' => [
    'hash' => 'esemenyek',
    'icon' => 'history fa-lg',
    'options' => [
      'ia-ajaxdiv-load' => '/kozter/friss_esemenyek',
      'ia-ajaxdiv-target' => '.ajaxdiv-events',
    ]
  ],
], [
  'only_icons' => true,
  'type' => 'pills',
  'align' => 'center',
  'selected' => 1,
  'class' => 'mb-2'
]);
?>

<div class="tab-content">
  <div class="tab-pane show active" id="hozzaszolasok" role="tabpanel" aria-labelledby="hozzaszolasok-tab">
    <?=$app->element('space/index/comments')?>
  </div>

  <div class="tab-pane show" id="szerkhozzaszolasok" role="tabpanel" aria-labelledby="szerkhozzaszolasok-tab">
    <?=$app->element('space/index/editcomments')?>
  </div>

  <div class="tab-pane" id="parbeszedek" role="tabpanel" aria-labelledby="parbeszedek-tab">
    <div class="ajaxdiv-threads" ia-ajaxdiv="/kozter/parbeszedek/?mennyi=10"></div>
  </div>

  <div class="tab-pane" id="fotok" role="tabpanel" aria-labelledby="fotok-tab">
    <div class="ajaxdiv-photos" ia-ajaxdiv="/kozter/friss_fotok"></div>
  </div>

  <div class="tab-pane" id="esemenyek" role="tabpanel" aria-labelledby="esemenyek-tab">
    <div class="ajaxdiv-events" ia-ajaxdiv="/kozter/friss_esemenyek"></div>
  </div>
</div>