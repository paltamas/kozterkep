<div class="row m-0">
  <?php
  echo $app->Form->create($_user, [
    'method' => 'post',
    'class' => 'col-md-5 mb-4',
    'id' => 'Form-Users-Settings-Ui-Settings',
    'ia-form-change-alert' => 1,
  ]);

  echo $app->Form->input('settings_desktop_everything', [
    'type' => 'checkbox',
    'label' => 'Mobilon is teljes méretű weboldal',
    'value' => 1,
    'checked' => $app->ts('settings_desktop_everything') == 1 ? true : false,
    'help' => 'Ha ezt jelölöd, akkor a kisebb kijelzőjű eszközökön belépve is a teljes méretű weboldalt mutatjuk, tehát nincs mobil-reszponzív működés.',
  ]);

  echo $app->Form->input('splitted_menu', [
    'type' => 'checkbox',
    'label' => 'Osztott fejléc főmenü',
    'value' => 1,
    'checked' => $app->ts('splitted_menu') == 1 ? true : false,
    'help' => 'Az osztott menü esetén a menüpont és a nyíl külön működik. A menüpontra kattintv az adott aloldalcsalád főlapjára jutsz, a nyíl pedig legördíti az aloldalakat. Ez csak normál kijelzők esetén érvényes, kisebb kijelzőkön marad a legördülőmenü.',
  ]);

  echo $app->Form->input('fluid_view', [
    'type' => 'checkbox',
    'label' => 'Teljes szélességű nézet normál képernyőn',
    'value' => 1,
    'checked' => $app->ts('fluid_view') == 1 ? true : false,
    'help' => 'Jelöld, ha szeretnéd, hogy nagy képernyődet teljes szélességben kitöltse a lap. Ezt egyébként a fejlécben látható "monitor" ikonra kattintva is állíthatod. Csak normál kijelzőre hat, mobilra nem.',
  ]);

  echo $app->Form->end('Mentés', [
    'name' => 'felulet-mukodese',
    'class' => 'btn-primary'
  ]);
  ?>

  <div class="col-md-7">
    <h4 class="title">Néhány hasznos apróság</h4>
    <p>A felület működésére tudsz itt hatni. Ezeket a beállításokat a felhasználódhoz mentjük, tehát csak akkor "hatnak", ha bejelentkezel.</p>
  </div>
</div>