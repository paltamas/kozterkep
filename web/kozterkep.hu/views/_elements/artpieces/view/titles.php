<?php
//echo '<h6 class="subtitle">Címek</h6>';
/*echo '<div class="mb-0">';
echo '<span class="text-muted">Alkotás címe:</span>';
echo '<strong class="ml-2">' . $artpiece['title'] . '</strong>';
echo '</div>';*/

if ($artpiece['title_alternatives'] != '') {
  echo '<div class="mb-0">';
  echo '<span class="text-muted">Alternatív, helyi címek:</span>';
  echo '<strong class="ml-2">' . $artpiece['title_alternatives'] . '</strong>';
  echo '</div>';
}

if ($artpiece['title_en'] != '') {
  echo '<div class="mb-0">';
  echo '<span class="text-muted">Angol cím:</span>';
  echo '<strong class="ml-2">' . $artpiece['title_en'] . '</strong>';
  echo '</div>';
}

if ($artpiece['title_alternatives'] != '' || $artpiece['title_en'] != '') {
  echo '<hr class="my-3" />';
}