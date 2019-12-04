<?php
echo '<h2 class="diapls-1">' . $newsletter['subject'] . '</h2>';
echo '<h4 class="text-muted">' . _time($newsletter['sent']) . '</h4>';
echo '<hr />';
echo str_replace([
  'Szia Köztérkép Gépház!',
  //''
], '', html_entity_decode($newsletter['body']));
