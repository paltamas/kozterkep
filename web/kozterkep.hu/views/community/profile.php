<?=$app->element('community/profile/header_image')?>

<div class="row">
  <div class="col-md-4 text-center" style="<?=$user['header_photo_filename'] != '' ? 'margin-top: -65px;' : ''?>">
    <?=$app->element('community/profile/namecard')?>
    <?=$app->Users->contact_link($user, [
      'text' => 'Új beszélgetés indítása',
      'div' => 'mt-3',
      'link_options' => [
        'class' => 'btn btn-secondary',
        'icon' => 'comment-alt-plus',
      ]
    ])?>
    <?=$app->element('community/profile/statistics')?>
  </div>

  <div class="col-md-8 pt-3">

    <?=$app->element('community/profile/passed_away')?>
    <?=$app->element('community/profile/artpieces')?>

    <div class="row">
      <div class="col-md-6">
        <?=$app->element('community/profile/events')?>
      </div>
      <div class="col-md-6">
        <?=$app->element('community/profile/top_places')?>
        <?=$app->element('community/profile/top_artpieces')?>
        <?=$app->element('community/profile/folders')?>
        <?=$app->element('community/profile/sets')?>
        <?=$app->element('community/profile/posts')?>
      </div>
    </div>

  </div>

</div>