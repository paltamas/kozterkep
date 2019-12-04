<?php
if (!$last_hug) {
  echo '<div class="near-info text-center">';
  echo 'Nagyon közel vagy az alkotáshoz, így virtuálisan is ' . $app->Html->icon('hand-point-up') . ' megérintheted!';
  echo '<hr />';
  echo '</div>';
}

if (!$_user) {
  echo '<div class="font-weight-bold mb-1">Az érintéshez be kell jelentkezned.</div>';
  echo $app->Html->link('Belépés', '/tagsag/belepes', [
    'class' => 'btn btn-primary'
  ]);
  echo '<div class="mt-4 font-weight-bold mb-1">Nincs még Köztérkép-hozzáférésed?</div>';
  echo $app->Html->link('Regisztrálj itt', '/tagsag/bejelentkezesi-segitseg', [
    'class' => 'btn btn-outline-primary'
  ]);

} else {

  if ($last_hug) {

    $next_time = $last_hug['created'] + (sDB['limits']['games']['hug_days'] * 24*60*60);

    echo $app->Html->alert('Az alkotást <strong>' . _time($last_hug['created'], ['ago' => true]) . '</strong> érintetted utoljára. <strong>' . _time($next_time) . '</strong> után érintheted újra.', 'info');

  } else {

    echo '<div class="text-center my-4">';
    echo $app->Html->link('Most megérintem!', '#', [
      'icon' => 'hand-point-up',
      'class' => 'btn btn-primary btn-lg add-hug-button',
      'ia-bind' => 'games.add_hug',
      'ia-pass' => $artpiece['id'],
    ]);
    echo '</div>';
  }
}