<div class="row">

  <div class="col-lg-6 col-md-6 mb-4">
    <h4 class="subtitle">Hozzászólások</h4>
    <p>Jelezz bármit, ami törzsadat, vagy rövidebb szöveges, linkes adalék. A rögzítés után az alkotótár kezelője ellenőrzi és kiemeli, ha értékes adalék.</p>
    <?php
    echo $app->element('comments/thread', [
      'model_name' => 'artist',
      'model_id' => $artist['id'],
      'files' => false,
      'search' => false,
      'link_class' => 'd-block',
    ]);
    ?>
  </div>

  <div class="col-lg-6 col-md-6 mb-4">

    <h4 class="subtitle mb-3">Alkotó fotó feltöltése</h4>

    <?php
    echo $app->Form->create(null, [
      'class' => 'ajaxForm',
      'ia-form-method' => 'post',
      'ia-form-action' => 'api/artists/photos',
      'ia-form-redirect' => 'alkotok/megtekintes/' . $artist['id'] . '#fotolista',
      'id' => 'fileUploadForm'
    ]);
    echo '<p>Olyan fotód van, amin szerepel az alkotó, de nem tölthető műlaphoz?</p>';

    echo $app->Form->input('artist_id', [
      'type' => 'hidden',
      'value' => $artist['id']
    ]);

    echo $app->Form->input('photo_files', [
      'type' => 'file',
      'multiple' => false, // itt csak egyet lehet!
      'ia-previewfile' => true,
      'ia-filetype' => 'images',
      'ia-fileupload' => true,
      'required' => true,
    ]);

    echo $app->Form->input('license_type_id', [
      'options' => sDB['license_types'],
      'label' => 'Kép felhasználhatósága',
      'value' => $_user['license_type_id'],
      'help' => 'Módosítsd az alapértelmezett felhasználási jogot arra, ahogyan kaptad a képet, ha nem saját fotód.'
    ]);

    echo $app->Form->input('text', [
      'label' => 'Leírás',
      'type' => 'textarea',
    ]);

    echo $app->Form->input('source', [
      'label' => 'Forrás',
      'type' => 'textarea',
      'class' => 'textarea-short',
    ]);

    echo $app->Form->end('Feltöltés indítása', ['id' => 'Photo-submit']);
    ?>

  </div>

</div>