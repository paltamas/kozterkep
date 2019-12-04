<?php
$options = (array)$options + [
  'form_action' => 'comments.build_thread',
  'form_redirect' => false,
  'default_text' => '',
  'model_name' => 'forum_topic',
  'model_id' => 4,
  'custom_field' => '',
  'custom_value' => '',
  'files' => false,
  'base_model_name' => 'forum_topic',
  'base_model_id' => 4,
  'hide_link' => false,
  'link_class' => 'd-none',
];

$id = $options['model_name'] . '-' . $options['model_id'];

if ($options['custom_field'] != '' && $options['custom_value'] !== '') {
  $id .=  '-' . $options['custom_field'] . '-' . $options['custom_value'];
}

if (!$options['hide_link']) {
  echo $app->Html->link('Hozzászólás írása...', '#Form-Comment-' . $id, [
    'icon' => 'comment-plus',
    'data-toggle' => 'collapse',
    'class' => 'font-weight-bold py-2 commentLink ' . $options['link_class'],
    'ia-focus' => '#Comment'
  ]);
}


$form_options = [
  'class' => 'ajaxForm mt-0 mb-3 collapse commentForm',
  'ia-form-action' => 'api/comments',
  'ia-form-method' => 'post',
  'id' => 'Form-Comment-' . $id,
  'ia-form-trigger' => $options['form_action'] . ', layout.collapse_toggle:.commentForm, forms.resetField:#Comment'
];

if ($options['form_redirect']) {
  $form_options['ia-form-redirect'] = $options['form_redirect'];
}

echo $app->Form->create(null, $form_options);

echo $options['default_text'] != ''
  ? '<div class="default-text">' . $options['default_text'] . '</div>' : '';

echo $app->Form->input('model_name', [
  'type' => 'hidden',
  'value' => $options['model_name'],
  'data-default' => $options['base_model_name'],
]);

echo $app->Form->input('model_id', [
  'type' => 'hidden',
  'value' => $options['model_id'],
  'data-default' => $options['base_model_id'],
]);

if ($options['custom_field'] != '' && $options['custom_value'] != '') {
  echo $app->Form->input('custom_field', [
    'type' => 'hidden',
    'value' => $options['custom_field'],
  ]);

  echo $app->Form->input('custom_value', [
    'type' => 'hidden',
    'value' => $options['custom_value'],
  ]);

  $custom_params = ' ia-custom-field="' . $options['custom_field'] . '" ia-custom-value="' . $options['custom_value'] . '" ';
}

echo $app->Form->input('comment', [
  'placeholder' => 'Hozzászólásod...',
  'type' => 'textarea',
  'rows' => 4,
  'class' => 'controlEnter',
]);

if ($options['files'] != false) {
  echo $app->Form->input('file', [
    'type' => 'file',
    'divs' => 'form-group fileInput collapse',
    'multiple' => true,
    'ia-previewfile' => true,
    'ia-fileupload' => true
  ]);
}

echo $app->Form->submit('Mehet', ['class' => '']);

if ($options['files'] != false) {
  echo $app->Html->link('Fájl csatolás', '', [
    'icon' => 'paperclip',
    'data-toggle' => 'collapse',
    'data-target' => '.fileInput',
    'class' => 'float-right pt-1'
  ]);
}

echo $app->Form->end();