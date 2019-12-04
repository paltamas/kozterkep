<div class="row">

  <div class="col-md-7 mb-2">
    <?php

    echo $app->element('comments/add', [
      'options' => [
        'default_text' => @$forum_topic['id'] > 0 ? '' : '<div class="alert alert-info p-2 small">Ha nem hozzászólásra válaszolsz, a "Beszéljük meg" fórumba kerül a kommented.</div>',
        'form_action' => 'comments.prepend_comment:.space-comments',
        'form_redirect' => $_params->here,
        'model_name' => 'forum_topic',
        'model_id' => @$forum_topic['id'] > 0 ? $forum_topic['id'] : 4, // beszéljük meg!
        'files' => true,
        'base_model_name' => 'forum_topic',
        'base_model_id' => @$forum_topic['id'] > 0 ? $forum_topic['id'] : 4,
        'hide_link' => true,
      ]
    ]);

    echo '<div class="">';
    foreach ($comments as $comment) {
      echo $app->element('comments/item', [
        'comment' => $comment,
        'options' => [
          'thread_links' => false
        ]
      ]);
    }
    echo '</div>';
    ?>
  </div>
</div>