<?php
echo '<div class="row">';
echo '<div class="col-12 col-md-6 mb-3">';
echo $app->element('space/index/submissions');
echo '</div>';
echo '<div class="col-12 col-md-6 mb-3">';
echo $app->element('space/index/waiting_edits');
echo '</div>';
echo '</div>';
echo '<hr class="my-2" />';