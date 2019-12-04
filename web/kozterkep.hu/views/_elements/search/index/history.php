<?php
if (count($search_history) > 0) {

  // Megfordítjuk, hogy a legfrissebbekkel kezdjük
  $search_history = array_reverse($search_history);

  $i = 0;
  $modal_body = '<div class="row">';

  echo '<div class="d-none d-md-inline-block">';

  foreach ($search_history as $search) {

    $i++;

    $s = $app->Search->history_item($search, [
      'parameters' => $artpiece_parameters
    ]);

    // Max 5-öt mutatunk, a többit a modalba gyűjtjük csak
    if ($i <= 10) {
      $link = '<span class="mr-1">';
      $link .= $app->Html->link($s, '/kereses?' . $search['query_string'], [
        'class' => 'badge badge-light font-weight-normal'
      ]);
      $link .= '</span>';

      echo $link;
    }

    if ($i == 25 && count($search_history) > 36) {
      $modal_body .= '</div>'; // row --

      $modal_body .= '<div class="text-center mb-4">';
      $modal_body .= $app->Html->link('Korábbi előzmények mutatása', '#elozmeny-lista', [
        'icon_right' => 'caret-down fas',
        'data-toggle' => 'collapse',
      ]);
      $modal_body .= '</div>';

      $modal_body .= '<div class="collapse" id="elozmeny-lista">'; // row --
      $modal_body .= '<div class="row">'; // row --
    }

    $modal_body .= '<div class="col-6 col-md-3 col-lg-2 mb-3 text-left">';

    $modal_body .= $app->Html->link($s, '/kereses?' . $search['query_string'], [
      'class' => 'font-weight-bold'
    ]);
    $modal_body .= '<br /><span class="text-muted small">' . _time($search['created']) . '</span>';

    $modal_body .= '</div>';
  }

  echo '</div>';

  $modal_body .= '</div>'; // row --

  if (count($search_history) > 36) {
    $modal_body .= '</div>'; // collapse --
  }

  $modal_body .= '<div class="row">';
  $modal_body .= '<div class="col-12 text-center mb-3">';
  $modal_body .= $app->Html->link('Előzmények törlése', '/kereses?elozmenyek_torlese', [
    'ia-confirm' => 'Biztosan végérvényesen törlöd a keresési előzményeidet?',
    'icon' => 'trash',
    'class' => 'btn btn-danger'
  ]);
  $modal_body .= '</div>';

  $modal_body .= '<div class="col-12 text-left text-muted mb-3"><span class="fal fa-info-circle mr-1"></span>Bejelentkezett tagoknak az utolsó 300, látogatóknak az utolsó 30 keresést tároljuk. Ha nem jelentkezel be, az előzmény csak ezen az eszközön és ebben a böngészőben látszik.</div>';

  $modal_body .= '</div>';

  // Kezelő link
  echo $app->Html->link('Előzmények', '#keresesi-elozmenyek', [
    'data-toggle' => 'modal',
    'icon' => 'history',
    'class' => 'd-inline-block ml-md-2 small text-nowrap'
  ]);

  // Modal
  echo $app->element('layout/partials/unimodal', ['options' => [
    'id' => 'keresesi-elozmenyek',
    'title' => 'Előzmények',
    'body' => $modal_body,
  ]]);
}