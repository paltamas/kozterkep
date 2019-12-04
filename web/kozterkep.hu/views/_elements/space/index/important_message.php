<?php if (1 == 2) { ?>
<div class="alert alert-success p-3">
  <strong>Köztérkép szerkesztői találkozó</strong>
  <br />2019.06.15. 14:00, B-Terv Cafe
  <br /><?=$app->Html->link('1073 Budapest, Kertész utca 46.', 'https://www.google.com/maps/place/Budapest,+Kert%C3%A9sz+u.+46,+1073/@47.5022034,19.0628715,17z/data=!3m1!4b1!4m5!3m4!1s0x4741dc6f2bdf1467:0x72d421cda435244!8m2!3d47.5021998!4d19.0650603', [
    'target' => '_blank',
    'icon' => 'map',
  ])?>
  <div class="mt-2 font-weight-bold"><?=$app->Html->link('Részletek és jelentkezés', 'https://docs.google.com/forms/d/e/1FAIpQLSeDw1EhqV7tB7CAjFD-DM-VgEgh6oXrWJgwS7rShlN3hseLaw/viewform', [
    'target' => '_blank',
    'icon_right' => 'external-link',
  ])?></div>
</div>
<?php } ?>