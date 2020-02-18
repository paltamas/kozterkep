<?php
$tabs = [
  /*'Megtekintés' => [
    'link' => '/' . $artpiece['id'] . '/' . $app->Text->slug($artpiece['title']),
    'icon' => 'file',
    //'target' => '_blank'
  ],*/
  'SzerkKomm' => [
    'hash' => 'szerk-szerkkomm',
    'icon' => 'comment-edit',
  ],
  'Bemérés' => [
    'hash' => 'szerk-terkep',
    'icon' => 'map-marker',
  ],
  'Fotók' => [
    'hash' => 'szerk-fotok',
    'icon' => 'images',
  ],
  'Adatok' => [
    'hash' => 'szerk-adatok',
    'icon' => 'list-alt',
  ],
  'Sztorik' => [
    'hash' => 'szerk-sztorik',
    'icon' => 'paragraph',
  ],
  'Kapcsok' => [
    'hash' => 'szerk-kapcsok',
    'icon' => 'paperclip',
  ],
];

if ($app->Users->owner_or_head($artpiece, $_user)) {
  $tabs = $tabs + ['Műveletek' => [
    'hash' => 'szerk-muveletek',
    'icon' => 'database',
  ]];
}

echo $app->Html->tabs($tabs, [
  'type' => 'pills',
  'align' => 'center',
  'selected' => @$_params->query['tab'] > 0 ? $_params->query['tab'] : 1,
  'class' => '',
  'preload' => true,
]);

if ($_user['id'] == $artpiece['user_id']) {
  // Hogy ezen a tabon maradjunk mentés után, ha miénk a lap
  echo $app->Form->input('_redirect_tab', [
    'type' => 'hidden',
    'id' => '_redirect_tab',
    'value' => 'comment-edit'
  ]);
}

echo $app->Form->input('artpiece_id', [
  'class' => 'd-none',
  'id' => 'artpiece_id',
  'value' => $artpiece['id']
]);

echo $app->Form->input('user_id', [
  'type' => 'text',
  'id' => 'user_id',
  'value' => $_user['id'],
  'class' => 'd-none',
]);

echo $app->Form->input('artpiece_status_id', [
  'class' => 'd-none',
  'id' => 'artpiece_status_id',
  'value' => $artpiece['status_id']
]);

echo $app->Form->input('country_id', [
  'class' => 'd-none',
  'id' => 'country_id',
  'value' => $artpiece['country_id']
]);

?>

<hr/>


<?php
echo $app->Html->link('', '#', [
  'class' => 'btn btn-outline-secondary show-info-column d-none z-1000',
  'style' => 'position: absolute; right: 5px;',
  'icon' => 'arrow-left',
  'title' => 'Információs hasáb megjelenítése',
]);
?>

<div class="row">

  <div class="col-sm-8 col-md-9 order-sm-1 order-2" id="edit-form-column">
    <div class="tab-content d-none">
      <div class="tab-pane show active" id="szerk-szerkkomm" role="tabpanel">
        <?= $app->element('artpieces/edit/editcom') ?>
      </div>
      <div class="tab-pane" id="szerk-terkep" role="tabpanel">
        <?= $app->element('artpieces/edit/place') ?>
      </div>
      <div class="tab-pane" id="szerk-fotok" role="tabpanel">
        <?= $app->element('artpieces/edit/photos') ?>
      </div>
      <div class="tab-pane" id="szerk-adatok" role="tabpanel">
        <?= $app->element('artpieces/edit/data') ?>
      </div>
      <div class="tab-pane" id="szerk-sztorik" role="tabpanel">
        <?= $app->element('artpieces/edit/stories') ?>
      </div>
      <div class="tab-pane" id="szerk-kapcsok" role="tabpanel">
        <?= $app->element('artpieces/edit/connections') ?>
      </div>
      <?php if ($app->Users->owner_or_head($artpiece, $_user)) { ?>
      <div class="tab-pane" id="szerk-muveletek" role="tabpanel">
        <?= $app->element('artpieces/edit/operations') ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="col-sm-4 col-md-3 order-sm-2 order-1 mb-3" id="edit-info-column">
    <div class="sticky-top z-1 pt-2">

      <?php
      echo $app->Html->link('', '#', [
        'class' => 'btn btn-link hide-info-column float-right d-none d-sm-block',
        'icon' => 'arrow-right',
        'title' => 'Információs hasáb elrejtése',
      ]);
      ?>

      <?php
      echo $app->Html->link('', '#', [
        'data-target' => '#artpiece-infobox',
        'data-toggle' => 'collapse',
        'class' => 'float-right infobox-toggle fa-lg d-sm-none mt-2',
        'icon' => $app->ts('artpiece_edit_infobox') == 1 ? 'plus-square fas' : 'minus-square fas',
        'ia-bind' => 'users.tiny_settings',
        'ia-vars-artpiece_edit_infobox' => $app->ts('artpiece_edit_infobox') == 1 ? 0 : 1,
        'ia-toggleclass' => 'fa-plus-square fa-minus-square',
        'ia-target' => '.infobox-toggle .fas',
      ]);
      ?>

      <h4 class="subtitle">Infók</h4>
      <div class="collapse show-sm <?=$app->ts('artpiece_edit_infobox') == 1 ? '' : 'show'?>" id="artpiece-infobox">
        <div id="artpiece-check-info" class=""></div>
        <?= $app->element('artpieces/edit/info_box') ?>
      </div>
      <?php /*$artpiece['status_id'] == 5 ? '<div class="publication-votes small mt-4"></div>' : '' */?>
    </div>
  </div>
</div>


