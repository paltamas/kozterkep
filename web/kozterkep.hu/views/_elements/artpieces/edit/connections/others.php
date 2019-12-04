<?php
echo $app->Form->input('links', [
  'type' => 'textarea',
  'label' => 'Kapcsolódó weboldalak',
  'help' => 'Hasznos linkek a kutatásaiddal, vagy a mű témájával kapcsolatban. A leírásod forrását a leírás forrás mezőjébe másold, ne ide!',
  'divs' => 'mt-4'
]);
?>