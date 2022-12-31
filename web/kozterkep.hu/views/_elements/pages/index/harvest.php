<div class="text-muted float-right font-italic pt-1">szubjektív válogatás <span class="d-none d-md-inline-block">friss műlapjainkból</span></div>
<h4 class="subtitle text-dark mb-1">Szüret</h4>
<?php
echo '<div class="row">';

// Legfrissebb 2 aláhúzás
echo '<div class="col-md-6 mb-5 mb-md-0">';
echo $app->element('pages/index/underlined_item', ['artpiece' => $underlineds[0]]);
echo '</div>';
echo '<div class="col-md-6">';
echo $app->element('pages/index/underlined_item', ['artpiece' => $underlineds[1]]);
echo '</div>';

echo '<div class="col-12">';
echo '<hr class="my-4">';
echo '</div>';

echo '<div class="col-md-6 col-lg-5 mb-5 mb-md-0">';
// További friss aláhúzások
unset($underlineds[0], $underlineds[1]);
$i = 0;
foreach ($underlineds as $underlined) {
  $i++;
  echo $app->element('pages/index/underlined_item', [
    'artpiece' => $underlined,
    'compact' => true,
  ]);
  echo count($underlineds) > $i ? '<hr class="my-3">' : '';
}
echo '</div>'; // col--

echo '<div class="col-md-6 col-lg-7">';
// További friss szüretek
echo '<div class="row">';
echo $app->element('artpieces/list/list', [
  'artpieces' => $harvesteds,
  'options' => [
    'top_count' =>  6,
    'top_class' =>  'col-6 col-md-4 p-0 d-flex',
    'top_details' => true,
    //'top_background' => '',
    'class' => 'col-4 col-sm-3 col-md-2 p-md-1',
    'max_items' => 30,
  ]
]);
echo '</div>'; // row --
echo '</div>'; // col --

echo '</div>'; // row--
?>