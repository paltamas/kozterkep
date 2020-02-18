<div class="row">
  <div class="col-md-7 mb-4">

    <div class="lead">
      <p class="font-weight-bold">Mi a Köztérkép? A közterek és közösségi terek művészi alkotásainak <span style="color: #ff48e7;">s</span><span style="color: #aa48e7;">z</span><span style="color: #ffaa11;">u</span><span style="color: #b2352f;">b</span><span style="color: #1122ff;">j</span><span style="color: #43ff7a;">e</span><span style="color: #bb1155;">k</span><span style="color: #4455aa;">t</span><span style="color: #885f3f;">í</span><span style="color: #ccc33f;">v</span> bemutatására vállalkozó független és önkéntes munkára épülő webes közösség és adatbázis.</p>

      <p>Munkánkat önszerveződően végezzük, nonprofit formában, saját működési elveink alapján. Nincsenek támogatóink és nem függünk hivatalos szervezetektől. Törekszünk az objektivitásra, de az véleményünk szerint csak a számtalan szubjektív nézőpont összességeként valósulhat meg.</p>

      <p>Nem vagyunk intézményi adatbázis, komoly anyagi vagy szakmai háttérrel. Minden, amit itt találsz, a sokféle lelkesedés által hajtott tagjaink munkájának gyümölcse.</p>
    </div>

  </div>


  <div class="col-md-5 mb-4">
    <div class="kt-info-box">
      <h4>Közreműködnél?</h4>
      <div class="lead mb-4">Ha új alkotást töltenél fel, vagy fotódat adnád hozzá egy fent lévőhöz, vagy bármilyen észrevételed lenne: regisztrálj és jelezd az adott műlapon! Munkánk a közreműködésre épül.</div>

      <h4>Ajánlott olvasmányok</h4>

      <?php
      echo '<div class="mb-2 lead">';
      echo $app->Html->link('Működési elvek', '/oldalak/mukodesi-elvek', [
        'icon' => 'book'
      ]);
      echo '</div>';

      echo '<div class="mb-2 lead">';
      echo $app->Html->link('Jogi nyilatkozat', '/oldalak/jogi-nyilatkozat', [
        'icon' => 'book'
      ]);
      echo '</div>';

      echo '<div class="mb-2 lead">';
      echo $app->Html->link('Adatkezelési szabályzat', '/oldalak/adatkezelesi-szabalyzat', [
        'icon' => 'book'
      ]);
      echo '</div>';

      ?>
    </div>
  </div>

  <div class="col-12">
    <?=$app->element('pages/index/short_intro', ['hide_link' => true])?>
  </div>

</div>