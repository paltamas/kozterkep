<div class="row">

  <div class="col-md-8 col-lg-9">

    <?php
    if (count($files) == 0) {
      echo '<p class="text-muted">Ebben a mappában még nincs fájl.</p>';
    } else {

      if (count($files) > 2) {
        echo $app->Html->link('Információk és hozzászólások', '#mappa-info', [
          'icon' => 'arrow-down',
          'class' => 'd-block d-md-none btn btn-outline-secondary mb-2 text-center',
          'ia-scrollto' => true
        ]);
      }

      echo '<div class="row">';

      $i = 0;

      foreach ($files as $file) {
        $i++;

        echo '<div class="col-md-4 mb-4 folder-file-' . $file['id'] . '">';
        echo '<div class="card">';
        echo '<div class="card-header bg-gray-kt draghandler">' . $file['original_name'] . '</div>';
        echo '<div class="card-body">';

        echo $app->element('files/preview', [
          'file' => $file,
          'link' => '#',
          'link_options' => [
            'ia-showroom' => 'folder',
            'ia-showroom-file' => $file['id'],
            'ia-showroom-file-path' => $app->File->get_file_path($file),
            'ia-showroom-file-type' => $file['type'],
          ]
        ]);

        echo $app->element('files/showroom_source');

        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // col

      }
      echo '</div>'; // row
    }
    ?>


  </div>

  <div class="col-md-4 col-lg-3" id="mappa-info">
    <h4 class="subtitle">Mappa információk</h4>

    <?=$app->element('folders/basic_info')?>

    <hr />

    <?php
    if (count($posts) > 0) {
      echo '<div class="mb-4">';
      echo '<h4 class="subtitle">Kapcsolódó bejegyzések</h4>';
      echo $app->element('posts/list', [
        'options' => ['intro' => ['image' => false]]
      ]);
      echo '</div>';
    }
    ?>


    <h4 class="subtitle">Hozzászólások</h4>
    <?php
    echo $app->element('comments/thread', [
      'model_name' => 'folder',
      'model_id' => $folder['id'],
      'files' => false,
    ]);
    ?>

  </div>

</div>
