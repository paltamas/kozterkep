<?php
$class = isset($class) ? $class : 'text-center py-5';
?>
<div class="<?=$class?> text-muted">
  <span class="fal fa-ellipsis-h-alt <?=$class == 'small' || @$text != '' ? '' : 'fa-2x'?> mr-2"></span><?=@$text?>
</div>