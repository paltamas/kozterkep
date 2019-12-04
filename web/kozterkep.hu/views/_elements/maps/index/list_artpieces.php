<div class="row pl-0 pr-0 py-4">
  <div class="col-md-12">

    <a href="#" class="float-right btn btn-secondary btn-sm hide-artpieces-pane"><span class="far fa-times"></span></a>

    <h4 class="subtitle"><?=$_title?></h4>

    <div class="kt-info-box my-3">

      <span class="fal fa-filter mr-2"></span>Ez a térkép nézet egy <?=$app->Html->link('szűrt listát', $back_path . '?' . http_build_query($_params->query))?> mutat a Köztérkép műlapjaiból. Ha az összes alkotást böngésznéd térképen, <?=$app->Html->link('kattints ide', '/terkep')?>.
    </div>

    <div class="artpiece-list row"></div>

  </div>
</div>