<?php
// Instant keresőmező
echo $app->Form->input('map_instant_search', [
  'label' => 'Alkotás címekben a teljes Köztérképen',
  'placeholder' => 'Keresés',
  'class' => 'd-inline-block instant-search input-no-clear',
  'data-target' => '#map-search-results',
  'data-type' => 'simple',
  'data-for-map' => 1,
]);

echo '<div class="m-0 p-0" id="map-search-results"></div>';