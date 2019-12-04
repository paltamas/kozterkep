<?php
//echo '<h6 class="subtitle">Helyszín</h6>';

echo '<div class="">';
if ($artpiece['country_id'] != 101) {
  echo $app->Places->country($artpiece['country_id']);
}
if ($artpiece['country_id'] == 101 && $artpiece['county_id'] > 0 && $artpiece['county_id'] != 1) {
  echo $app->Places->county($artpiece['county_id']);
}
echo '</div>';

echo '<div class="">';
echo '<strong>' . $app->Places->name($artpiece['place_id']) . '</strong>';

if ($artpiece['address'] != '' || $artpiece['district_id'] > 0) {
  echo '<br />';
  echo $artpiece['district_id'] > 0 ? $app->Places->district($artpiece['district_id']) : '';
  echo $artpiece['address'] != '' && $artpiece['district_id'] > 0 ? ', ' : '';
  echo $artpiece['address'] != '' ? $artpiece['address'] : '';
}

echo '</div>';

if ($artpiece['place_description'] != '') {
  echo '<div class="text-muted small">';
  echo $app->Text->read_more($artpiece['place_description'], 75);
  echo '</div>';
}