<h4 class="subtitle mb-3">Műlap története</h4>

<div class="row">
  <div class="col-md-7 order-2 order-md-1">
    <?php
    if (count($events) > 0) {
      foreach ($events as $event) {
        echo $app->element('events/item', ['event' => $event, 'options' => [
          'hide_connecteds' => true,
        ]]);
      }
    } else {
      echo $app->element('layout/partials/empty', ['text' => 'Nincs még publikus esemény.']);
    }
    ?>
  </div>
  <div class="col-md-5 order-1 order-md-2 mb-3 mb-mb-0">
    <div class="kt-info-box">Ebben a listában időrendi csökkenő sorrendben nyomon követheted a műlap változásait, bővüléseit és minden lényeges eseményét. Ez a publikus lista minden látogatónk számára elérhető.</div>
  </div>
</div>