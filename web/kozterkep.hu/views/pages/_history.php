<?php
if (count($edits) == 0) {
  echo '<p class="text-muted">A szerkesztési történet tárolása óta még nem történt változás ezen az oldalon.</p>';
} else {

  echo '<div class="alert alert-info">A "Módosítás részleteit" alatt pirossal a törléseket, zölddel az új szövegrészeket emeltük ki.</div>';

  foreach ($edits as $edit) {
    echo '<div class="row px-1 py-4 border-bottom fade-icons">';

    echo '<div class="col-lg-4 col-md-6 col-12">';
    echo $app->Users->name($edit['user_id']);
    echo ' @ ' . _time($edit['created']);
    echo '</div>';

    echo '<div class="col-lg-8 col-md-6 col-12">';

    echo $app->Html->link('Módosítás részletei...', '#edit-' . $edit['id'], [
      'data-toggle' => 'collapse',
    ]);

    echo '</div>'; // col-10

    echo '<div class="col-12 collapse diff pb-4" id="edit-' . $edit['id'] . '">';

    echo $app->Text->html_diff(
      html_entity_decode($edit['previous']['content']),
      html_entity_decode($edit['actual']['content']),
      ['strip_tags' => true]
    );
    echo '</div>'; // collapse

    echo '</div>';
  }
}
?>
<?=$app->element('pages/tabs', ['selected' => 2])?>

