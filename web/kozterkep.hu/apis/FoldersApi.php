<?php
class FoldersApi extends \Kozterkep\Api {

  public function __construct() {
    parent::__construct();
  }

  public function get() {

  }



  public function post() {

  }



  public function put() {
    $folder_updates = [];

    // Fájl feltöltés
    if (isset($this->data['_files'])) {
      // Folder jog ellenőrzése
      $folder = $this->DB->first('folders', $this->data['folder_id']);
      if (!$folder || $folder['user_id'] != static::$user['id']) {
        $this->send(['errors' => [texts('mentes_hiba')]]);
      }

      // Fájlok benyomása
      $this->File->upload_posted(
        'files',
        $this->data,
        [
          //'permissions' => [static::$user['id']],
          'user_id' => static::$user['id'],
          'folder_id' => $folder['id'],
          'license_type_id' => static::$user['license_type_id']
        ],
        [
          'onesize' => true,
          'watermark' => true
        ],
        ['rankstart' => $folder['file_count']]
      );
    }


    // Fájl törlés
    if (isset($this->data['delete_file'])) {
      $file = $this->DB->first('files', [
        'id' => $this->data['delete_file'],
        'folder_id' => (int)$this->data['folder_id'],
        'user_id' => static::$user['id']
      ]);

      // Ha borító volt
      if (@$file['cover'] == 1) {
        $folder_updates['file_id'] = 0;
      }

      if (!$this->File->delete($this->data['delete_file'], static::$user['id'])) {
        $this->send(['errors' => [texts('torles_hiba')]]);
      }

      $this->DB->delete('files', [
        'id' => $this->data['delete_file'],
        'user_id' => static::$user['id']
      ]);
    }


    // Borító beállítás
    if (isset($this->data['cover_file'])) {
      $file = $this->DB->first('files', [
        'id' => $this->data['cover_file'],
        'folder_id' => (int)$this->data['folder_id'],
        'user_id' => static::$user['id']
      ]);
      if (!$file) {
        $this->send(['errors' => [texts('mentes_hiba')]]);
      }

      if (@$file['cover'] != 1) {
        // nem ő volt => rátesszük
        $this->DB->update('files', ['cover' => 0], ['folder_id' => $file['folder_id']]);
        $this->DB->update('files', ['cover' => 1], $file['id']);
        $folder_updates['file_id'] = $file['id'];
      } else {
        // ő volt => levesszük
        $this->DB->update('files', ['cover' => 0], ['folder_id' => $file['folder_id']]);
        $folder_updates['file_id'] = '';
      }
    }

    // Ezeket mindenképpen frissítjük
    $folder_updates['file_count'] = $this->DB->count('files', ['folder_id' => (int)$this->data['folder_id']]);
    $folder_updates['updated'] = time();

    // Újraszámoljuk
    if ($this->DB->update('folders', $folder_updates, [
      'id' => $this->data['folder_id'],
      'user_id' => static::$user['id']
    ])) {

      $this->Cache->delete('cached-view-folders-index');
      $this->Cache->delete('cached-view-folders-view-' . $this->data['folder_id']);

      $this->send(['success' => true]);
    }

    $this->send(['errors' => [texts('mentes_hiba')]]);
  }

}