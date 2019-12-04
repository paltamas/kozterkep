<div class="tab-content">

  <div class="tab-pane show active" id="fajlok" role="tabpanel" aria-labelledby="fajlok-tab">

    <?php

    echo '<div class="">';
    echo $app->Html->link('Új fájl feltöltése', '#fileUploadForm', [
      'icon' => 'upload',
      'class' => 'btn btn-secondary',
      'data-toggle' => 'collapse',
    ]);
    echo '</div>';

    echo $app->Form->create(null, [
      'class' => 'ajaxForm collapse border rounded p-4 my-4',
      'ia-form-method' => 'put',
      'ia-form-action' => 'api/folders',
      'ia-form-redirect' => 'mappak/szerkesztes/' . $folder['id'],
      'id' => 'fileUploadForm'
    ]);

    echo $app->Form->input('folder_id', [
      'type' => 'hidden',
      'value' => $folder['id']
    ]);

    echo $app->Form->input('file', [
      'label' => 'Fájlok kiválasztása',
      'type' => 'file',
      'multiple' => true,
      'ia-previewfile' => true,
      'ia-fileupload' => true,
      'required' => true,
    ]);

    echo '<p class="text-muted">A fájlok az alapértelmezett felhasználási jogoddal kerülnek beállításra. Ezt, valamint a fájlok nevét és leírását és más részleteket a feltöltés után kezelheted. A képeket feltöltés után automatikusan ellenőrizzük: ha kell, elforgatjuk, átméretezzük és vízjellel látjuk el.</p>';


    echo $app->Form->end('Feltöltés indítása');


    //echo '<h4 class="title my-4">Fájlok a mappában</h4>';

    if (count($files) == 0) {
      echo '<p class="text-muted">Ebben a mappában még nincs fájl.</p>';
    } else {

      echo $app->Form->create(null, [
        'method' => 'post',
        'id' => 'folderFilesForm',
        'ia-form-change' => 'enable_submit'
      ]);

      echo '<span class="float-right mt-2 hide-on-touch">';
      echo $app->Html->link('Váltás sorrendező nézetre', '#', [
        'icon' => 'images',
        'ia-bind' => 'folders.switch_to_ranking'
      ]);
      echo '</span>';

      echo $app->Form->submit('Változtatások mentése', [
        'class' => 'disabled',
        'divs' => 'my-4 text-left',
        'name' => 'save_files'
      ]);

      echo '<div class="row" id="drag-and-drop" ia-dragorder=".rank-input" ia-draghandler="draghandler">';

      $i = 0;

      foreach ($files as $file) {
        $i++;

        echo '<div class="col-lg-3 col-md-4 mb-4 folder-file-' . $file['id'] . '">';
        echo '<div class="card">';
        echo '<div class="card-header bg-gray-kt draghandler">' . $file['original_name'] . '</div>';
        echo '<div class="card-body">';

        echo $app->element('files/preview', [
          'file' => $file,
          'link' => 'self',
          'link_options' => [
            'target' => '_blank',
            'title' => 'Teljes méretű fájl megnyitása'
          ]
        ]);

        echo '<div class="mt-2">';

        echo '<div class="icons">';

        echo $app->Html->link('', '#file-form-' . $file['id'], [
          'icon' => 'pencil',
          'class' => 'btn btn-sm btn-outline-secondary mr-2',
          'data-toggle' => 'collapse'
        ]);

        if (strpos($file['type'], 'image') !== false) {
          echo $app->Html->link('', '#', [
            'icon' => @$file['cover'] == 1 ? 'badge-check ' : 'badge',
            'class' => 'btn btn-sm btn-outline-secondary mr-2 setCover',
            'title' => 'Borítókezelés',
            'data-toggle' => 'tooltip',
            'ia-bind' => 'folders.cover_file',
            'ia-pass' => $file['id'],
            'ia-vars-folder_id' => $folder['id'],
            'ia-toggleclass' => 'fa-badge fa-badge-check',
            'ia-target' => '#{id} .far',
          ]);
        }

        echo $app->Html->link('', '#', [
          'icon' => 'trash',
          'class' => 'float-right ml-2 btn btn-sm btn-outline-danger',
          'title' => 'Fájl törlése',
          'data-toggle' => 'tooltip',
          'ia-confirm' => 'Biztos vagy abban, hogy visszavonhatatlanul törlöd ezt az fájlt?',
          'ia-bind' => 'folders.delete_file',
          'ia-pass' => $file['id'],
          'ia-vars-folder_id' => $folder['id'],
        ]);

        echo '</div>';


        echo '<div class="collapse mt-3 pt-2 border-top" id="file-form-' . $file['id'] . '">';
        echo $app->Form->input('files[' . $file['id'] . '][id]', [
          'type' => 'hidden',
          'class' => 'form-control-sm',
          'value' => $file['id'],
        ]);
        echo $app->Form->input('files[' . $file['id'] . '][rank]', [
          'type' => 'text',
          'class' => 'rank-input d-none',
          'value' => @$file['rank'] > 0 ? $file['rank'] : $i,
        ]);
        echo $app->Form->input('files[' . $file['id'] . '][original_name]', [
          'label' => 'Fájlnév',
          'class' => 'form-control-sm',
          'value' => $file['original_name']
        ]);
        echo $app->Form->input('files[' . $file['id'] . '][text]', [
          'type' => 'textarea',
          'label' => 'Leírás',
          'class' => 'form-control-sm',
          'value' => @$file['text']
        ]);
        echo $app->Form->input('files[' . $file['id'] . '][source]', [
          'type' => 'textarea',
          'label' => 'Forrás, ha nem saját',
          'class' => 'form-control-sm',
          'value' => @$file['source']
        ]);
        echo $app->Form->input('files[' . $file['id'] . '][license_type_id]', [
          'options' => $app->Users->licenses_selectable($file),
          'value' => @$file['license_type_id'],
          'label' => 'Felhasználási jog',
          'help' => 'Ha más forrásból szerezted be az fájlt, oszd meg azonos jogokkal!'
        ]);

        echo '</div>'; // collapse form

        echo '</div>'; // form

        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // col

      }
      echo '</div>'; // row

      if (count($files) > 8) {
        echo $app->Form->submit('Változtatások mentése', [
          'class' => 'disabled',
          'divs' => 'text-center mb-4',
          'name' => 'save_files'
        ]);
      }

      echo $app->Form->end();

      //debug($file);

      echo '<p class="mb-4"><span class="only-on-touch d-none">A sorrendet csak egérrel rendelkező eszközön tudod megváltoztatni. </span><span class="hide-on-touch">A sorrend megváltoztatásához fogd meg a fejlécet és húzd az egérrel a fájlt a kívánt helyre. </span>A képen látható <span class="far fa-sync-alt fa-border"></span> ikon azt jelenti, hogy még dolgozunk a feltöltött fájlon. ' . $app->Html->link('Frissítsd az oldalt', 'referer') . ', hogy eltűnjön a jelölés.</p>';
    }

    ?>


  </div>

  <div class="tab-pane" id="beallitasok" role="tabpanel" aria-labelledby="beallitasok-tab">
    <?php
    echo $app->Form->create($folder,
      [
        'method' => 'post',
        'id' => 'edit-folder',
        'class' => ''
      ]
    );

    echo $app->Form->input('name', [
      'label' => 'Mappa megnevezése',
      'required' => true,
    ]);

    echo $app->Form->input('description', [
      'type' => 'textarea',
      'label' => 'Mappa leírása',
    ]);

    echo $app->Form->input('public', [
      'type' => 'checkbox',
      'label' => 'Publikus',
      'value' => 1,
      'help' => 'A publikus mappát mindenki láthatja. Ha egy mappa nem publikus, akkor zárt, csak te éred el. A zárt mappában se tárolj személyes adatokat vagy más, nem nyilvános információt.'
    ]);


    echo $app->Form->end('Mentés', ['name' => 'save_settings']);
    ?>
  </div>

</div>
