<div class="row">

  <div class="col-md-12 pt-3 mb-4 text-center text-md-left">
    <?=$app->element('space/forum/topic_list', [
      'options' => []
    ])?>
  </div>

  <div class="col-md-12 pt-3 mb-4">
    <?=$app->element('space/forum/comment_search_form', [
      'options' => ['show_types' => false]
    ])?>
  </div>

  <div class="col-md-7 mb-2">
    <?=$app->element('space/forum/latest_comments', ['options' => [
      'highlight' => @$_params->query['kifejezes'] != ''
        ? $_params->query['kifejezes'] : '',
    ]])?>
  </div>

  <div class="col-md-5 mb-2">
    <?php
    if ($_user) {
      echo '<div class="kt-info-box mb-4">';
      echo '<p class=""><strong>Szeretnél értesítést</strong> az új hozzászólásokról ebben a fórumtémában?</p>';
      echo $app->Form->input('fn', [
        'type' => 'select_button',
        'options' => [
          0 => 'Nem kérek',
          1 => 'Kérek értesítést',
        ],
        'value' => $app->ts('fn_' . $forum_topic['id']) == 1 ? 1 : 0,
        'input' => [
          'ia-bind' => 'users.tiny_settings',
          'ia-vars-toggle_button' => 'fn_' . $forum_topic['id'],
        ],
        'divs' => 'mb-0'
      ]);

      echo '</div>';
    }
    ?>


    <h4 class="subtitle mb-3">Legfrissebb párbeszédek ebben a fórum témában</h4>
    <div class="ajaxdiv-photos" ia-ajaxdiv="/kozter/parbeszedek/<?=$forum_topic['id']?>"></div>
  </div>

</div>