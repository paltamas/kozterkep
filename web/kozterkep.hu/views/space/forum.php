<div class="row">

  <div class="col-md-12 pt-3 mb-4 text-center text-md-left">
    <?=$app->element('space/forum/topic_list', [
      'options' => []
    ])?>
  </div>

  <div class="col-md-12 pt-3 mb-4">
    <?=$app->element('space/forum/comment_search_form', [
      'options' => ['show_types' => true]
    ])?>
  </div>

  <div class="col-md-7 mb-2">
    <?=$app->element('space/forum/latest_comments', ['options' => [
      'highlight' => @$_params->query['kifejezes'] != ''
        ? $_params->query['kifejezes'] : '',
    ]])?>
  </div>

  <div class="col-md-5 mb-2">
    <h4 class="subtitle mb-3">Legfrissebb párbeszédek</h4>
    <div class="ajaxdiv-photos" ia-ajaxdiv="/kozter/parbeszedek"></div>
  </div>

</div>