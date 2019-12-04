<?php
if ($super_highlighteds[0]['time'] > strtotime('-4 days')) {
?>
  <div class="row my-3 my-md-4">
    <div class="col-md-4 mb-3 mb-md-0 order-2 order-md-1">
      <?=$app->element('pages/index/highlighteds')?>
      <?=$app->element('pages/index/admin_posts')?>
    </div>
    <div class="col-md-8 pl-lg-5 order-1 order-md-2 mb-4 mb-md-0">
      <?=$app->element('pages/index/harvest')?>
    </div>
  </div>
<?php } else { ?>
  <div class="row my-3 my-md-4">
    <div class="col-12 mb-4 mb-md-0">
      <?=$app->element('pages/index/harvest', [
        'options' => [
          'top_count' => 6,
          'top_class' => 'col-6 col-md-2 p-0 d-flex',
          'max_items' => 30,
        ]
      ])?>
    </div>
  </div>
<?php } ?>


<?=$app->element('pages/index/short_intro')?>

<?=$app->element('pages/index/instant_search')?>

<hr class="highlighter text-center my-3">


<div class="row py-md-5">

  <div class="col-md-6 mb-3">
    <?=$app->element('pages/index/map')?>
  </div>
  <div class="col-md-6 mb-3">
    <?=$app->element('pages/index/blog_friends')?>
    <?=$app->element('pages/index/member_posts')?>
  </div>
</div>



<hr class="highlighter text-center my-3">


<div class="row py-md-4">

  <div class="col-md-8 pr-md-5 mb-3">
    <?=$app->element('pages/index/latest_artpieces')?>
  </div>

  <div class="col-md-4 mb-3">
    <?=$app->element('community/index/highlighted_user')?>
    <?=$app->element('pages/index/top_users')?>
  </div>
</div>


