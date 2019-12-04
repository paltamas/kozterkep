<div class="row">
  <div class="col-md-6">

    <p><?=$app->Html->icon('info-circle mr-1')?>Jelenleg nem vagy bejelentkezve, így csak a leiratkozási funkciót éred el.</p>

    <hr />

    <?php
    echo $app->Form->create(null, [
      'method' => 'post',
    ]);
    echo $app->Form->input('hash', [
      'type' => 'hidden',
      'value' => $_params->query['kulcs'],
    ]);
    echo $app->Form->input('unsubscribe', [
      'type' => 'checkbox',
      'label' => 'Szeretnék leiratkozni minden email értesítőről és Minerva hírlevélről',
      'value' => 1,
    ]);
    echo $app->Form->end('Leiratkozás mentése');
    ?>
  </div>
  <div class="col-md-4">
    <div class="kt-info-box">
      <h4>Nem mindenről iratkoznál le?</h4>
      <p><?=$app->Html->link('Jelentkezz be', '/tagsag/beallitasok#ertesitesek')?>, ha pontosabban szeretnéd meghatározni, hogy milyen emaileket kapj tőlünk.</p>
    </div>
  </div>
</div>
