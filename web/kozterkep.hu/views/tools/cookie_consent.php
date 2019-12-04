<h4>Sütik?</h4>

<p>A cookie-kban, vagyis böngésző sütikben olyan információkat tárolunk az eszközödön, amelyek lehetővé teszik a Köztérkép működését. Minden részletet megtalálsz az Adatkezelési szabályzatunk "<?=$app->Html->link('Cookie-szabályzat', '/oldalak/adatkezelesi-szabalyzat#hopp=cookie')?>" fejezetében.</p>

<p class="text-center">
  <?=$app->Html->link('Hozzájárulok ehhez', '#', [
    'class' => 'btn btn-primary accept-cookies'
  ])?>
</p>


<hr class="my-3" />

<h4>Milyen sütiket mentünk?</h4>

<?php
foreach ($own_cookies as $cookie_name => $cookie_description) {
  echo '<p><strong>' . $cookie_name . ':</strong> ' . $cookie_description . '</p>';
}
?>
<p class="mt-4"><span class="far fa-info-circle mr-1"></span>A sütik nem tartalmaznak <strong>semmilyen személyes adatot</strong>, és tartalmuk ismeretében nem vagy azonosítható illetéktelenek számára.</p>
