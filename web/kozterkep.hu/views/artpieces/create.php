<div class="row helpText">

  <div class="col-md-8">
    <p class="lead font-weight-bold">Mielőtt létrehoznád az új műlapot, győződj meg róla, hogy
      belefér-e az alkotás a kereteinkbe, és van-e elég információd ahhoz, hogy
      a műlap megszülethessen.</p>
    <p>A műlap létrehozása során folyamatosan segítünk, de fontos ismerni a kereteket.</p>
  </div>

  <div class="col-md-4">
    <h4 class="subtitle">Hasznos linkek</h4>
    <p><?= $app->Html->link('Működési elvek', '/oldalak/mukodesi-elvek', [
        'class' => 'font-weight-bold',
        'icon' => 'pen-alt',
      ]) ?> <span class="text-muted">- Határaink megismerése.</span><br/>

      <?= $app->Html->link('Segédlet', '/oldalak/segedlet', [
        'class' => 'font-weight-bold',
        'icon' => 'info-square',
      ]) ?>
      <span class="text-muted">- Mit és hogyan.</span><br/>

      <?= $app->Html->link('Köztér fórum', '/kozter/forum', [
        'class' => 'font-weight-bold',
        'icon' => 'comments',
      ]) ?>
      <span class="text-muted">- Kérdezz, ha bizonytalan vagy!</span></p>
  </div>
</div>

<hr class="helpText" />

<div class="row">

  <?php
  echo $app->Form->create(null, [
    'method' => 'post',
    'class' => 'w-100 artpiece-edit-form noEnterForm',
  ]);
  ?>

  <div class="col-md-12 mb-4">
    <h4 class="subtitle">Hol található az alkotás?</h4>
    <?= $app->element('artpieces/create/map') ?>

    <script>
        /*function geocoderInit() {
            window.geocoder = new google.maps.Geocoder();
        }*/
    </script>
    <?php
    //echo '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . C_WS_GOOGLE['maps'] . '&v=' . C_WS_GOOGLE['js_version'] . '&callback=geocoderInit"></script>'
    ?>

  </div>
  <div class="col-md-12 mb-4">

    <h4 class="subtitle">Közeli alkotások</h4>

    <?php
    echo $app->Form->help('Amennyiben már fent van az alkotás a lapon, akkor töltsd hozzá a saját képeidet és információidat.', [
      'class' => 'mb-4'
    ]);
    ?>

    <div class="nearby-artpieces" ia-alist-img-width="100" ia-alist-radius="500" ia-alist-limit="30" ia-alist-showdist="true">

    </div>

    <hr />

    <div class="row">
      <div class="col-md-7 col-lg-6 mb-4">
        <?php
        echo $app->Form->input('id', ['type' => 'hidden', 'value' => 0]);
        echo $app->Form->input('country_code', ['type' => 'hidden']);
        echo $app->Form->input('address_json', ['type' => 'hidden']);
        echo $app->Form->input('lat', ['type' => 'hidden']);
        echo $app->Form->input('lon', ['type' => 'hidden']);

        echo $app->Form->input('artpiece_location_id', [
          'type' => 'select_button',
          'options' => [
            1 => 'Köztéren',
            2 => 'Közösségi térben',
          ],
          'label' => 'Hol található az alkotás?',
          'value' => 1,
        ]);

        echo $app->Form->input('not_public_type_id', [
          'type' => 'select',
          'options' => [0 => 'Válassz...'] + $app->Arrays->id_list(sDB['not_public_types']),
          'label' => 'Közösségi tér típusa',
          'divs' => [
            'class' => 'd-none',
            'ia-toggleelement-parent' => '#Artpiece-location-id',
            'ia-toggleelement-value' => 2,
          ]
        ]);
        ?>
      </div>

      <div class="col-md-5 col-lg-6">
        <div class="sticky-top">
          <h4 class="subtitle">Szerkesztési segítség</h4>
          <div id="artpiece-check-info" class="small"></div>
          <div>Ha egy fogalom nem egyértelmű, fusd át a <?=$app->Html->link('Működési elveket', '/oldalak/mukodesi-elvek', ['target' => '_blank'])?>. Egyes meghatározásaink sajátosak, nem egyeznek más köznapi szokványokkal.</div>
        </div>
      </div>
    </div>


  </div>


  <div class="col-md-12 mb-4">
    <?php
    echo $app->Form->end('Műlap létrehozása', [
      'class' => 'disabled'
    ]);
    ?>
  </div>

</div>
