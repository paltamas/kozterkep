<?=$app->Form->create($post, [
  'method' => 'post',
  'ia-form-change-alert' => 1,
])?>
<div class="row">
  <div class="col-md-8 mb-4">
    <?php
    echo $app->element('posts/edit/texts');
    echo '<hr class="my-5" />';
    echo $app->element('posts/edit/connected_artpieces');
    ?>
  </div>
  <div class="col-md-4 mb-4">
    <?php

    echo $app->element('posts/edit/cover_image');

    echo $app->element('posts/edit/admin_functions');

    echo $app->element('posts/edit/buttons');

    if ($_user['id'] == $post['user_id']) {
      echo '<hr class="my-3" />';
      echo $app->element('posts/edit/connected_things');
    }
    ?>
  </div>

  <div class="col-12 mt-5">
    <hr class="my-5" />
    <?=$app->Html->link('Bejegyzés törlése', $_params->here . '?torles', [
      'class' => 'btn btn-outline-danger',
      'ia-confirm' => 'Biztosan törlöd a bejegyzést? A törlés nem visszaállítható.',
    ])?>
  </div>
</div>
<?=$app->Form->end()?>