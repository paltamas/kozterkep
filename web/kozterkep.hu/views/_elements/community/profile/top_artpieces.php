<?php
if (count($top_artpieces) > 0) {
  echo '<h5 class="subtitle mb-3">Heti népszerű műlapok</h5>';
  echo $app->element('artpieces/list/toplist', [
    'artpieces' => $top_artpieces,
    'options' => [
      'field' => 'view_week',
      'simple' => true
    ]
  ]);
}