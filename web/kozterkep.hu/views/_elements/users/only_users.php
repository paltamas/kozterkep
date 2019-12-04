<?php
$options = (array)@$options + [
  'alert' => true,
];

if ($options['alert']) {
  if (is_array($options['alert'])) {
    $text = $options['alert'][0];
    $type = @$options['alert'][1] != '' ? $options['alert'][1] : 'secondary';
  } else {
    $text = '<span class="far fa-info-circle mr-1"></span>Ez csak bejelentkezés után érhető el.';
    $type = 'secondary';
  }

  echo '<div class="alert alert-' . $type . '">' . $text . '</div>';
}
?>

<p class="text-muted"><strong><?=$app->Html->link('Jelentkezz be', '/tagsag/belepes')?></strong>, vagy <strong><?=$app->Html->link('regisztrálj', '/tagsag/regisztracio?idejojjunkmajd=' . urlencode($_params->here))?></strong>, hogy te is elérhesd ezt a funkciót.</p>
<?php //$app->Html->link('Információk a tagságról', '/tagsag/info', ['icon' => 'info-circle'])?>