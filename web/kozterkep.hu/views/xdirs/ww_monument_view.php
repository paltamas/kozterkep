<div class="row">
  <div class="col-md-9 mb-4">
    <div class="row">
      <div class="col-lg-5 mb-5">
        <?php
        if ($monument['cover_photo_path'] != '') {
          echo $app->Html->image('/he/' . $monument['cover_photo_path'] . '_1.jpg', [
            'link' => [
              '/he/' . $monument['cover_photo_path'] . '_1.jpg',
              ['target' => '_blank']
            ],
            'class' => ' img-thumbnail img-fluid',
          ]);
        } else {
          echo '<p class="text-muted">Nincs kép az al-adatbázisban az emlékműről.</p>';
        }
        ?>
      </div>
      <div class="col-lg-7 mb-5">
        <?php
        echo '<h5 class="subtitle">Adatok</h5>';
        echo $app->Html->dl('create');

        if ($monument['country_id'] > 0) {
          echo $app->Html->dl([
            'Ország',
            $parameters[$monument['country_id']]['description']
          ]);
        }

        if (!in_array($monument['county_id'], [0,1152])) { // BP ne
          echo $app->Html->dl([
            'Megye',
            $parameters[$monument['county_id']]['description']
          ]);
        }

        if ($monument['place_name'] != '') {
          echo $app->Html->dl([
            'Helység',
            $app->Html->link($monument['place_name'], '/adattar/hosi-emlek?hely=' . $monument['place_name'])
          ]);
        }

        if (!in_array($monument['district_id'], [0,6])) { // a 6. az üres string :D
          echo $app->Html->dl([
            'Kerület',
            $parameters[$monument['district_id']]['description']
          ]);
        }


        if ($monument['unveil_text'] != '') {
          echo $app->Html->dl([
            'Avatás',
            $monument['unveil_text']
          ]);
        }

        if (count(_json_decode($monument['artists'])) > 0) {
          echo $app->Html->dl([
            'Alkotó',
            $app->Arrays->json_list($monument['artists'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['second_artists'])) > 0) {
          echo $app->Html->dl([
            'További alkotó',
            $app->Arrays->json_list($monument['second_artists'], $parameters, 'description')
          ]);
        }


        if (count(_json_decode($monument['connected_buildings'])) > 0) {
          echo $app->Html->dl([
            'Kapcs. épület',
            $app->Arrays->json_list($monument['connected_buildings'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['connected_monuments'])) > 0) {
          echo $app->Html->dl([
            'Kapcs. emlékművek',
            $app->Arrays->json_list($monument['connected_monuments'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['connected_events'])) > 0) {
          echo $app->Html->dl([
            'Kapcs. események',
            $app->Arrays->json_list($monument['connected_events'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['hm_him'])) > 0) {
          echo $app->Html->dl([
            'HM/HIM',
            $monument['hm_him']
          ]);
        }

        if (count(_json_decode($monument['topics'])) > 0) {
          echo $app->Html->dl([
            'Téma',
            $app->Arrays->json_list($monument['topics'], $parameters, 'description')
          ]);
        }

        if ($monument['type_id'] > 0) {
          echo $app->Html->dl([
            'Típus',
            $parameters[$monument['type_id']]['description']
          ]);
        }

        if ($monument['nationalities'] > 0) {
          echo $app->Html->dl([
            'Jelképek',
            $parameters[$monument['nationalities']]['description']
          ]);
        }

        if ($monument['nationalities'] > 0) {
          echo $app->Html->dl([
            'Nemzetiségek',
            $parameters[$monument['nationalities']]['description']
          ]);
        }

        if (count(_json_decode($monument['unveilers'])) > 0) {
          echo $app->Html->dl([
            'Avatta',
            $app->Arrays->json_list($monument['unveilers'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['maintainers'])) > 0) {
          echo $app->Html->dl([
            'Fenntartók',
            $app->Arrays->json_list($monument['maintainers'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['founders'])) > 0) {
          echo $app->Html->dl([
            'Alapítók',
            $app->Arrays->json_list($monument['founders'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['corps'])) > 0) {
          echo $app->Html->dl([
            'Katonai egység',
            $app->Arrays->json_list($monument['corps'], $parameters, 'description')
          ]);
        }

        if (count(_json_decode($monument['states'])) > 0) {
          echo $app->Html->dl([
            'Egyéb',
            $app->Arrays->json_list($monument['states'], $parameters, 'description')
          ]);
        }


        echo $app->Html->dl('end');


        if ($monument['dead_count'] > 0) {
          echo '<div class="mt-3">Ez az emlékmű <strong>' . $monument['dead_count'] . '</strong> hősi halottnak állít emléket.</div>';
        }

        if ($monument['enrolled_count'] > 0) {
          echo '<div class="mt-3">Besorozottak száma: <strong>' . $monument['enrolled_count'] . '</strong></div>';
        }

        if (count(_json_decode($monument['sources'])) > 0) {
          echo '<div class="mt-3">Adat és kép források: ';
          echo $app->Arrays->json_list($monument['sources'], $parameters, 'description');
          echo '</div>';
        }

        if ($monument['artpiece_id'] > 0) {
          echo '<div class="mt-3">';
          echo $app->Html->link('Kapcsolódó műlap', '/' . $monument['artpiece_id'], [
            'icon' => 'map-marker',
            'class' => 'font-weight-bold',
          ]) . ' <span class="text-muted"> - az alkotás a Köztérkép adatbázisában is szerepel.</span>';
          echo '</div>';
        }
        ?>
      </div>
      <div class="col-md-12">
        <?php

        if (count($photos) > 1) {

          echo '<h5 class="subtitle">További fotók</h5>';

          foreach ($photos as $photo) {
            $photo_path = '/he/' . $monument['id']%10 . '/' . $photo['slug'];

            echo $app->Html->image($photo_path . '_4.jpg', [
              'link' => [
                $photo_path . '_1.jpg',
                ['target' => '_blank']
              ],
              'class' => ' img-fluid img-thumbnail mr-2 mb-2',
            ]);
          }
        }
        ?>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-4">
    <?php
    echo '<div class="kt-info-box mb-3"><span class="fas text-danger fa-exclamation-triangle mr-2"></span>A hozzászólások zárolásra kerültek, a <strong>Hősi Emlék al-adatbázist már nem frissítjük</strong>. További információk a ' . $app->Html->link('Hősi Emlék kezdőlapján', '/adattar/hosi-emlek') . '.</div>';
    if (count($comments) > 0) {
      echo '<h5 class="subtitle">Hozzászólások</h5>';
      if ($_user) {
        foreach ($comments as $comment) {
          echo '<div class="rounded p-2 border mb-3">';
          echo '<div class="small text-muted float-right">' . _time($comment['created']) . '</div>';
          echo $app->Users->name($comment['user_id'], [
            'image' => true,
            'class' => 'font-weight-bold',
          ]);
          echo $app->Text->format($comment['text']);
          echo '</div>';
        }
      }
    }
    ?>
  </div>
</div>
