<?php
$model_name = !isset($model_name) ? 'all' : $model_name;
$model_id = !isset($model_id) ? '0' : $model_id;

if (@$search) {
  echo '<div class="row justify-content-end">';
  echo '<div class="col-md-7 col-lg-7 col-6">';
  echo $app->Form->input('unisearch', [
    'ia-unisearch-container' => '.comment-thread',
    'ia-unisearch-item-container' => '.row',
    'ia-unisearch-items' => '.row .comment-text-box',
    'placeholder' => 'Keresés...',
    'prepend_icon' => 'search'
  ]);
  echo '</div>';
  echo '</div>';
}

echo $app->element('comments/add', [
  'options' => [
    'model_name' => $model_name,
    'model_id' => $model_id,
    'custom_field' => @$custom_field,
    'custom_value' => @$custom_value,
    'base_model_name' => $model_name,
    'base_model_id' => $model_id,
    'files' => @$files ? $files : false,
    'link_class' => isset($link_class) ? $link_class : 'd-none',
  ]
]);



/**
 *
 * FIGYELEM
 *
 * Ide ne tegyél thread-refresh-t, mert ezt refresheli
 * a build_thread a /comments/get-en keresztül
 */

?>

<div class="comment-thread"
     ia-model-name="<?=$model_name?>"
     ia-model-id="<?=$model_id?>"
     ia-custom-field="<?=@$custom_field?>"
     ia-custom-value="<?=@$custom_value?>"
     ia-limit="<?=@$limit > 0 ? $limit : 200?>"
  <?=@$custom_params?>></div>
