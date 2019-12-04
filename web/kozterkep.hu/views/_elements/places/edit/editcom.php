<div class="row">

  <div class="col-lg-6 col-md-6 mb-4">
    <h4 class="subtitle">Hozzászólások</h4>
    <?php
    echo $app->element('comments/thread', [
      'model_name' => 'place',
      'model_id' => $place['id'],
      'files' => true,
      'search' => false,
      'link_class' => 'd-block',
    ]);
    ?>
  </div>


  <div class="col-lg-6 col-md-6 mb-4">

    <div class="kt-info-box">
      <p>Hozzászólásban jelezd az esetleges törzsadat-problémákat, vagy amit fontosnak tartasz a településsel kapcsolatban.</p>
      <p>Ha hosszabb településbemutatást írnál, készíts blogbejegyzést és ahhoz kapcsold a települést.</p>
    </div>

  </div>

</div>