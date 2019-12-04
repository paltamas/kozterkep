<?php
if ($user['passed_away'] == 1) {
  echo '<div class="bg-dark p-3 rounded mb-4 text-light"><span class="display-4 mr-3 text-yellow">Emléklap</span>' . $user['name'] . ' már nincs köztünk, de ezzel az oldallal is őrizzük emlékét' , $user['artpiece_count'] > 0 ? ', tisztelegve a közösségért és a köztéri alkotások megőrzéséért végzett munkája előtt' : '' , '.</div>';
}