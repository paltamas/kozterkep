<p class="small text-muted"><strong>Ellenőrzésre küldött</strong> műlapok és műlap szerkesztések hozzászólásai. A kezdőlapon ez csak főszerkesztőknek látható.</p>
<?php
echo '<div class="space-comments thread-refresh mb-2 pb-3 pb-md-0 border-bottom border-md-0" '
  . ' ia-custom-field="spacewall" ia-custom-value="editcomments" id="editcomments">';
$i = 0;
foreach ($editcomments as $comment) {
  $i++;
  echo $app->element('comments/item', ['comment' => $comment, 'options' => [
    'reply' => false,
    'row_class' => $i > 10 ? 'd-none d-md-block' : '', // 10 komment után mobilon nem mutatjuk
  ]]);
}

echo '</div>';