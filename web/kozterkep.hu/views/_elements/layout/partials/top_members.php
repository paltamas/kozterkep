<div class="mb-1"><strong>Mindenkori legaktívabb <?=$count?> tagunk</strong></div>
<?php
$i = 0;
foreach ($members as $member) {
  $i++;
  echo count($members) > $i ? '<span class="text-nowrap mr-1"> ' : '';
  echo $app->Users->name($member['id']);
  echo count($members) > $i ? ',</span> ' : '';
}