<?php
if ($_user && @$_user['changes_accepted'] != 1) {
  echo '<div class="container">';
  echo '<div class="row mx-1 mx-sm-0">';
  echo '<div class="col-12 my-2 p-3 bg-light border rounded">';

  echo $app->Html->link('Rendben', '/tagsag/valtozasok-elfogadasa', [
    'icon' => 'check',
    'class' => 'float-right btn btn-outline-primary ml-3'
  ]);

  echo '<strong class="mr-2"><span class="fa fa-exclamation-triangle mr-2 text-danger"></span>Változtak az elveink, kérjük, ismerd meg az újdonságokat!</strong>';
  echo 'A változásokat <a href="/blogok/megtekintes/1025/valtozasok-a-mukodesunkben-es-a-foszerkesztoi-csapat-bovulese">ebben a bejegyzésben</a> soroltuk fel részletesen. A lap további használatával elfogadod, hogy rád is érvényesek ezek a változások.';
  echo '</div>';
  echo '</div>';
  echo '</div>';
}