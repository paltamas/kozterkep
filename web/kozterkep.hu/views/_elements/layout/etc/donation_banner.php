<?php
if ((!$_user || @$_user['changes_accepted'] == 1)
  && $this->Session->get('visited_pages') > 5 && $_params->here != '/oldalak/tamogass-minket') {
  echo '<div class="container d-none" id="donationBanner" data-month="' . date('Ym') . '">';
  echo '<div class="row">';
  echo '<div class="col-12 my-md-3 p-2 bg-gray-kt border-bottom border-md rounded-md">';

  echo $app->Html->link('Bezár', '#', [
    'icon' => 'times',
    'class' => 'float-right btn btn-sm btn-outline-secondary ml-3 mt-md-3 hideDonationBanner'
  ]);

  echo '<a href="/oldalak/tamogass-minket" class="text-dark text-decoration-none p-2 d-block">';
  echo '<strong class="mr-2"><span class="fa fa-hands-helping mr-2 text-primary fa-lg"></span>Értékesnek tartod a Köztérkép munkáját? Támogasd működésünket!</strong>';
  echo 'Ha te is fontosnak tartod, hogy weboldalunk hosszú távon létezzen, segíts az üzemeltetésben és a fejlesztésben adományoddal! Támogatásod lehetővé teszi, hogy folytathassuk gyűjtő- és bemutató munkánkat. Kattints ide a részletekért!';
  echo '</a>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
}