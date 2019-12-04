<?php
echo $app->Form->input('new_artist', [
  'label' => 'Személy hozzáadása',
  'class' => 'not-form-change',
  'ia-auto' => 'artists',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target-run' => 'artpieces.artist_add',
  'ia-auto-add' => 'artpieces.artist_create',
  'help' => 'Kezdd el gépelni a nevet és válassz a listából. Ha biztosan nem szerepel a keresett személy az adatbázisban, hozd létre, mint új alkotó.',
]);

echo '<div id="artist-list" ia-dragorder=".artist-rank" ia-draghandler="draghandler">';

if (count($artists) > 0) {

  foreach ($artists as $artist) {

    echo '<div class="row bg-light py-2 mb-2 artist-row artist-row-' . $artist['id'] . '">';

    echo $app->Form->input('artists[' . $artist['id'] . '][id]', [
      'type' => 'text',
      'class' => 'd-none',
      'value' => $artist['id'],
      'divs' => false,
    ]);

    echo $app->Form->input('artists[' . $artist['id'] . '][rank]', [
      'type' => 'text',
      'class' => 'artist-rank d-none',
      'value' => $artist['rank'],
      'divs' => false,
    ]);

    echo '<div class="col-lg-5 mb-2 mb-lg-0">';
    echo $app->Html->link($artist['name'], '#', [
      'class' => 'font-weight-bold',
      'artist' => $artist,
      'target' => '_blank',
    ]);
    echo '</div>';


    echo '<div class="col-lg-3 pl-3 px-lg-0 pb-2 pb-lg-0">';
    echo $app->Form->input('artists[' . $artist['id'] . '][contributor]', [
      'type' => 'select_button',
      'options' => [
        0 => 'Alkotó',
        1 => 'Közreműködő',
      ],
      'value' => $artist['contributor'],
      'divs' => false,
    ]);
    echo '</div>';


    echo '<div class="col-lg-2 col-6">';

    echo $app->Form->input('artists[' . $artist['id'] . '][profession_id]', [
      'options' => $app->Artists->professions(['only_roles' => true]),
      'value' => $artist['profession_id'],
      'class' => 'd-inline',
      'divs' => false,
      'title' => 'Alkotás létrehozásában betöltött szerep',
      'data-toggle' => 'tooltip',
    ]);

    echo '</div>';

    /*echo '<div class="col-lg-1 col-2 pt-1">';

    echo $app->Form->input('artists[' . $artist['id'] . '][question]', [
      'type' => 'checkbox',
      'label' => '<span class="far text-muted fa-question fa-lg" data-toggle="tooltip" title="Az alkotó bizonytalan"></span>',
      'value' => 1,
      'checked' => $artist['question'] == 1 ? true : false,
      'class' => 'd-inline',
      'title' => 'Az alkotó bizonytalan',
      'divs' => false,
    ]);

    echo '</div>';*/


    echo '<div class="col-lg-2 col-4 pt-1 text-right">';
    echo $app->Html->link('', '#', [
      'icon' => 'trash fa-lg',
      'class' => 'text-muted mr-2 cursor-pointer',
      'ia-confirm' => 'Biztosan törlöd ezt az alkotót?',
      'ia-bind' => 'artpieces.artist_delete',
      'ia-pass' => $artist['id'],
      'title' => 'Törlés',
    ]);
    echo '<span class="fas fa-grip-vertical fa-lg text-muted draghandler hide-on-touch px-1" data-toggle="tooltip" title="Sorrend módosítása áthúzással"></span>';
    echo '</div>';

    echo '</div>'; // row

  }

}

echo '<div class="mb-4 help-box">';
echo '<div class="">' . $app->Form->help('Az alkotás létrehozásában betöltött szerepnél ne az adott személy szakmáját add meg, hanem ennek az alkotásnak a létrehozásában betöltött szerepet. Tehát ha pl. egy építész tervezett egy művet, akkor a "Tervező"-t jelöld, ne az építészt.') . '</div>';
echo '<div class="hide-on-touch">' . $app->Form->help('A rögzített alkotók sorbarendezéséhez használd a <span class="fas fa-grip-vertical fa-sm text-muted"></span> ikont. Kattints rá, és húzd a megfelelő helyre az illetőt. A műlapon a közreműködőket mindenképp az alkotók után jelenítjük meg.') . '</div>';
echo '<div class="only-on-touch">' . $app->Form->help('A sorrend módosítása csak asztali számítógépen érhető el.') . '</div>';
echo '</div>';

echo '</div>';
