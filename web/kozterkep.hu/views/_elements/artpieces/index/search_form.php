<div class="bg-gray-kt pt-4 pb-3 px-lg-5 px-3 rounded">
  <?=$app->Html->link('Részletes keresés', '/kereses?r=1', [
    'class' => 'float-right font-weight-bold',
    'icon_right' => 'arrow-right',
  ])?>
  <h4 class="mb-3"><?=$app->Html->icon('search mr-1')?><span class="d-none d-md-inline-block">Keresés műlapjaink között</span></h4>
  <?=$app->element('search/index/form_simple', [
    'options' => [
      'form' => [
        'action' => '/kereses',
      ]
    ]
  ])?>
</div>