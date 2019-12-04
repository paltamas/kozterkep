<div class="tab-content">

  <div class="tab-pane show active" id="adatlap" role="tabpanel" aria-labelledby="helylap-tab">

    <div class="row">

      <div class="col-md-5 col-lg-4 mb-5">

        <?=$app->element('artists/view/main_photos', [
          'photos' => $photos,
          'count' => 1,
          'photo_size' => 3,
        ])?>

        <?php if (count($latest_artpieces) > 5) { ?>
          <h4 class="subtitle">Legnépszerűbbek</h4>
          <div class="row mb-4">
            <?=$app->element('artists/view/top_artpieces')?>
          </div>
        <?php } ?>

        <h4 class="subtitle">Legfrissebb műlapok</h4>
        <div class="row mb-4">
          <?=$app->element('artists/view/latest_artpieces', ['options' => [
            'query' => [
              'alkoto_az' => $artist['id'],
              'alkoto' => $artist['name'],
            ]
          ]])?>
        </div>

        <?=$app->element('artists/view/main_photos', [
          'photos' => $sign_photos,
          'count' => 4,
          'photo_size' => 6,
        ])?>
      </div>


      <div class="col-md-7 col-lg-8">

        <div class="row kt-info-box p-2 pt-3 pt-md-2 mb-4">
          <?php if ($artist['artpiece_count'] > 4) { ?>
            <div class="col-md-5">
              <?php
              echo $app->element('layout/partials/simple_search_form', ['options' => [
                'action' => '/kereses',
                'placeholder' => 'Keress alkotásra...',
                'class' => 'mt-md-2 mb-0',
                'custom_inputs' => [
                  ['r', [
                    'value' => 1,
                    'type' => 'hidden',
                  ]],
                  ['alkoto_az', [
                    'value' => $artist['id'],
                    'type' => 'hidden',
                  ]],
                  ['alkoto', [
                    'value' => $artist['name'],
                    'type' => 'hidden',
                  ]],
                ]
              ]]);
              ?>
            </div>
          <?php } ?>
          <div class="col-md-7 py-2">
            <?php

            echo $app->Artists->name($artist, ['link' => false]) . ' <strong>' . _n($artist['artpiece_count']) . ' műlapon</strong> szerepel. ';

            $http_qery = [
              'oldalcim' => $_title,
              'visszalepes' => $_params->here,
              'alkoto_az' => $artist['id'],
              'alkoto' => $artist['name'],
            ];

            echo $app->Html->link('Mutasd mind', '/kereses/lista?' . http_build_query($http_qery), [
              'icon_right' => 'arrow-right',
              'class' => 'text-nowrap',
            ]);
            ?>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-8 mb-3 mb-md-0">
            <?=$app->element('artists/view/data')?>
          </div>
          <div class="col-lg-4">
            <?=$app->element('artists/view/basic_info')?>
          </div>
        </div>

        <?php
        if (count($artpieces_by_time) > 2) {
          echo '<h4 class="subtitle mt-4">Idővonal</h4>';
          echo $app->element('artpieces/list/timeline', ['artpieces' => $artpieces_by_time, 'options' => [
            'class' => 'mt-4',
            'count' => 5
          ]]);
        }
        ?>

        <?php
        if ($artist['artpiece_count'] > 0) {
          echo $app->element('maps/simple_filtered', ['options' => [
            'count' => $artist['artpiece_count'],
            'map_artpieces' => $map_artpieces,
            'height' => 350,
            'zoom' => 15,
            'center' => [
              'lat' => @$top_artpieces[0]['lat'],
              'lon' => @$top_artpieces[0]['lon'],
            ],
            'filter_query' => [
              'alkoto_az' => $artist['id'],
              'alkoto' => $artist['name'],
            ],
            'title' => '<h4 class="subtitle mt-0">Alkotások térképen</h4>',
            'div_class' => 'mt-4'
          ]]);
        }
        ?>

        <?php
        echo '<div class="row">';

        $col_width = count($artist_descriptions) > 0 && count($posts) > 0 ? 6 : 12;

        if (count($artist_descriptions) > 0) {
          echo '<div class="col-md-' . $col_width . ' mb-3">';
          echo '<h4 class="subtitle mt-4">További adalékok</h4>';
          foreach ($artist_descriptions as $artist_description) {
            echo $app->element('artists/view/description_item', [
              'description' => $artist_description,
              'options' => []
            ]);
          }
          echo '</div>';
        }

        if (count($posts) > 0) {
          echo '<div class="col-md-' . $col_width . ' mb-3">';
          echo '<h4 class="subtitle mt-4">Kapcsolódó bejegyzések</h4>';
          echo $app->element('posts/list');
          echo '</div>';
        }

        if ($_user) {
          echo '<div class="col-12 mt-3 kt-info-box">';
          echo '<p><strong>Új információd van?</strong> Írj hozzászólást a SzerkKomm fülön! Ha olyan jellegű a szöveg, adalékká változtatjuk.</p>';
          echo '<p><strong>Van egy jó portréd az alkotóról?</strong> Ugyancsak a SzerkKomm fülön töltheted fel azokat a fotókat, amelyeket műlapra nem lehet pakolni, mert nem kapcsolódnak az adott alkotáshoz.</p>';
          echo '</div>';
        }

        echo '</div>';
        ?>

      </div>

    </div>

  </div>

  <div class="tab-pane" id="fotolista" role="tabpanel" aria-labelledby="fotolista-tab">
    <?=$app->element('artists/view/photos_showroom', ['photos' => $photos])?>
  </div>

  <div class="tab-pane" id="szerkkomm" role="tabpanel" aria-labelledby="szerkkomm-tab">
    <div class="ajaxdiv-photos" ia-ajaxdiv="/alkotok/szerkkomm/<?=$artist['id']?>"></div>
  </div>

</div>
