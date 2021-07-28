<?php
echo $app->Form->create($artpiece, [
  //'method' => 'post',
  'class' => 'w-100 artpiece-edit-form ajaxForm photos-list-form',
]);

echo '<hr />';

/*echo $app->Form->input('photos', [
  'class' => 'd-none',
  'value' => urlencode($artpiece['photos'])
]);*/

if (count($photos) > 0) {

  if (($app->Users->owner_or_right($artpiece, $_user)
    && ($artpiece_user['managing_on'] == 0 || $artpiece_user['id'] == $_user['id'] || $artpiece_user['passed_away'] == 1))
    || $_user['admin'] == 1 || $_user['headitor'] == 1) {
    // Csak a nemkezelők lapján módosíthatjuk a képsorrendet, ha szabad publikálók vagyunk
    echo '<div class="float-right">' . $app->Html->link('Képsorrend', '#', [
        'icon' => 'images',
        'class' => 'btn btn-secondary photos-sort-button',
      ]) . '</div>';
  }
  echo '<h4 class="subtitle">Fotók listája</h4>';
  echo '<p><strong>' . count($photos) . ' fotó</strong> &ndash; feltöltés szerint csökkenő sorrendben.</p>';

  echo '<p>Feltöltők:';

  $users_showed = [];
  foreach ($photos as $photo) {

    if (!in_array($photo['user_id'], $users_showed)) {
      $photo_user = $app->Users->name($photo['user_id'], [
        'tooltip' => false,
        'link' => false,
      ]);

      echo $app->Html->link($photo_user, '#', [
        'class' => 'mx-2 photo-user-filter',
        'ia-bind' => 'artpieces.photos_filter',
        'ia-pass' => $photo['user_id']
      ]);
      $users_showed[] = $photo['user_id'];
    }
  }

  echo $app->Html->link('Szűrés törlése', '#', [
    'icon' => 'times',
    'class' => 'photo-user-filter-delete d-none',
    'ia-bind' => 'artpieces.photos_filter',
    'ia-pass' => 0
  ]);

  echo '</p>';


  echo $app->Form->help('Az egyes mezők jelentése: '
    . $app->Html->list([
      '<strong>Leírás</strong>: a kép leírása. Mindenképp add meg, ha a kép archív, vagy más szempontból nem megszokott.',
      '<strong>Forrás</strong>: minden nem saját képnél adj meg forrást. A meglévő alkotások műlapjait saját fotókból kell elkészíteni, a mások képei a minimum saját kép elvárás felett jöhetnek szóba.',
      '<strong>Évszám</strong>: akkor add meg, ha több évvel korábbi a fotó, mint amikor feltöltöd. Korábbi állapotok esetén értékes információ az évszám.',
      '<strong>Borító</strong>: a műlap főképe, ami a listákban is látható.',
      '<strong>Archív fotó</strong>: minden olyan korábban készült fotó, amely már egy nem aktuális, jelentősen más állapotot rögzít, illetve több évtizeddel korábban készült. Az eleve archívként feltöltött képek jelölése nem módosítható.',
      '<strong>Szignó a képen</strong>: az alkotáson látható alkotói kézjegy (neve, nevének rövidítése, egyedi jele stb.) látható a képen. Ha a teljes név szimplán kiolvasható, az valószínűleg nem szignó, így a kép csak növeli a "zajt" a szignós listában. Fontos forrás lehet, használd tudatosan! Egy domborműves műlapon ne pipáld az összes képet, mert mindegyiken látszik... Válaszd ki, kinek a szignója. Ha nem ismert az alkotó, akkor válaszd az "Ismeretlen alkotó" lehetőséget.',
      '<strong>Avatást látunk</strong>: a mű "hivatalos" leleplezésén, avatásán készült fotókat jelöld ezzel.',
      '<strong>Alkotó a képen</strong>: ha sikerült lencsevégre kapnod a mű alkotóját a művel, ezt is jelöld. Ezeket a képeket a művész adatlapján külön kiemelten mutatjuk. Ha nem az elsődleges alkotóról szól a kép, akkor válaszd ki a megfelelőt a legördülő menüből. A lista csak a jóváhagyott alkotókat tartalmazza. Ne tölts ide olyan képet az alkotóról, aminek nincs köze a műhöz! Ezeket az alkotó adatlapjára töltheted.',
      '<strong>Nem a helyszínről</strong>: ha a műről, de nem a műlapon mutatott helyszínről készült fotót töltesz fel. Ilyen lehet pl. szállítás közben, a műteremben stb. Ne ide töltsd az áthelyezett művek más helyszínén készített fotóit. Áthelyezett művek esetében külön műlapot készítünk! Ezek a típusú képek nem jelennek meg a műlap kezdőlapján.',
      '<strong>Adalék</strong> (korábban "Más"): az alkotáshoz szervesen kapcsolódó, de nem az alkotást bemutató fénykép, vagy más szempontból nem szokványos adalék felvétel (pl.: újságcikk, festmény a szoborról, a szobor kismintája stb.). Az adalék típusú képek nem jelennek meg a műlap kezdőlapján.',
      '<strong>Élménykép</strong>: az alkotással kapcsolatos, azt is mutató, mostanában készült, élményszerű helyszíni fotóid, ami nem kifejezetten dokumentarista, de mindenképp hangulatos és közösségi. Az élményképek nem jelennek meg a műlap kezdőlapján. Élménykép példák: önarckép szoborral, galamb a szobor fején, kisgyerek mászott a szoborra, a biciklid és a szobor közös képe: bármi extra, de nem CSAK egy fotó a szoborról.',
      '<strong>Kép felhasználhatósága</strong>: archív nem saját fotó esetén speciális felhasználhatóságot rögzítünk, egyébként a saját alapértelmezett beállításod érvényes. Amennyiben itt egy képnél átállítod az engedélyt, úgy az speciális engedélyes képpé válik és ha a központi engedély-beállításodat változtatod, ez a kép-engedély változatlan marad.',
    ])
  );

  echo '<hr />';


  $i = 0;

  foreach ($photos as $photo) {

    $i++;

    // Ebben van a rank
    $artpiece_photo = $artpiece_photos_by_id[$photo['id']];

    $image_info = $app->Html->dl('create', ['class' => 'row small text-muted mt-1 mb-2']);
    $image_info .= $app->Html->dl(['Kiemelt', $artpiece_photo['rank'] <= $artpiece['top_photo_count'] ? 'igen' : 'nem']);
    $image_info .= $app->Html->dl(['Azonosító', $photo['id']]);
    $image_info .= $app->Html->dl(['Feltöltő', $app->Users->name($photo['user_id'])]);
    $image_info .= $app->Html->dl(['Frissítve', _time($photo['modified'])]);
    $image_info .= $app->Html->dl(['Feltöltve', _time($photo['created'])]);
    $image_info .= $app->Html->dl('end');


    if ($photo['user_id'] == $_user['id']
      || $_user['admin'] == 1 || $_user['headitor'] == 1) {
      $image_info .= '<div>';
      $image_info .= $app->Html->link('További képinfók', '#user-info-' . $photo['id'], [
        'data-toggle' => 'collapse',
        'class' => 'small text-muted not-form-change',
        'icon' => 'info-circle',
      ]);
      $image_info .= '<div class="collapse bg-light rounded p-2 mt-1 small" id="user-info-' . $photo['id'] . '">';
      $image_info .= '<div class="font-weight-bold text-muted mb-2">' . $app->Html->icon('lock mr-1') . 'Ezt csak te, a kép feltöltője látod</div>';
      if ($photo['filename'] != '') {
        $image_info .= '<div class="mb-1"><span class="text-muted">Fájlnév:</span> ' . $photo['filename'] . '</div>';
      }
      $image_info .= '<div class="mb-1"><span class="text-muted">Fotózva:</span> ';
      $image_info .= $photo['exif_taken'] > 0 ? _date($photo['exif_taken'], 'Y.m.d.') : 'nem volt EXIF';
      $image_info .= '</div>';

      if ($photo['missing_original'] == 0) {
        $url = C_WS_S3['url'] . 'originals/' . $photo['original_slug'] . '.jpg';
        $image_info .= '<div class="mb-1">' . $app->Html->link('Eredeti feltöltött kép', $url, [
          'target' => '_blank',
          'icon_right' => 'external-link',
        ]) . '</div>';
      }

      $image_info .= '</div>';
      $image_info .= '</div>';
    }


    echo $app->Form->input('photolist[' . $i . '][id]', [
      'type' => 'text',
      'value' => $photo['id'],
      'class' => 'd-none',
    ]);

    echo $app->Form->input('photolist[' . $i . '][slug]', [
      'type' => 'text',
      'value' => $photo['slug'],
      'class' => 'd-none',
    ]);

    echo $app->Form->input('photolist[' . $i . '][user_id]', [
      'type' => 'text',
      'value' => $photo['user_id'],
      'class' => 'd-none',
    ]);

    $its_cover = $artpiece['photo_slug'] == $photo['slug'] ? true : false;

    echo '<div class="row photo-row photo-row-' . $photo['id'] . ' pb-2' , $its_cover ? ' bg-gray-kt pt-4 pb-2 mb-4 rounded' : ' border-bottom' , '" data-user-id="' . $photo['user_id'] . '" id="fotosor-' . $photo['id'] . '">';

    // Kép és törzsadatai
    echo '<div class="col-md-12 col-lg-4 mb-2 mb-lg-0">';
    echo $app->Image->photo($photo, [
      'link' => 'full',
      'link_options' => ['target' => '_blank'],
      'size' => 3,
      'class' => 'img-thumbnail',
      'title' => 'Nagyméretű kép megnyitása új fülön',
      'data-toggle' => 'tooltip',
    ]);

    if ($app->Users->owner_or_right($artpiece, $_user) || $_user['id'] == $photo['user_id']) {

      echo $image_info;

      echo $app->Html->link('Részletek megjelenítése...', '#', [
        'class' => 'd-lg-none',
        'ia-bind' => 'true',
        'ia-toggleclass' => 'd-none',
        'ia-target' => '.photo-editcol-' . $i
      ]);
    }

    echo '</div>';


    if ($app->Users->owner_or_right($artpiece, $_user) || $_user['id'] == $photo['user_id']) {

      // Paraméterek
      echo '<div class="col-12 col-lg-3 mb-4 mb-lg-0 d-none d-lg-block photo-editcol-' . $i . '">';

      if ($app->Users->owner_or_right($artpiece, $_user)
        && ($artpiece_photo['rank'] <= $artpiece['top_photo_count'] || $its_cover)) {
        echo $app->Form->input('photolist[' . $i . '][cover]', [
          'label' => 'Borítókép',
          'type' => 'checkbox',
          'value' => 1,
          'checked' => $its_cover,
          'class' => 'photo-cover-checkbox',
          'ia-conn-unset' => '.photo-cover-checkbox',
          'ia-uncheckable' => 1,
          'divs' => 'mb-1',
          'help' => $artpiece_photo['rank'] > $artpiece['top_photo_count'] ? 'Kérjük, válassz kiemelt képet borítónak, vagy emeld ki ezt' : '',
        ]);
      } else {
        echo $app->Form->label('Csak kiemelt lehet borító', ['class' => 'text-muted']);
        echo $app->Form->help('A kép sorrendezésnél tudod megadni, mi kiemelt.');
      }


      if ($photo['archive_locked'] == 1) {
        echo '<p title="Archívként került feltöltésre, ezért nem változtatható" data-toggle="tooltip"><span class="far fa-check mr-1"></span>Archív fotó</p>';
        echo $app->Form->input('photolist[' . $i . '][archive]', [
          'type' => 'text',
          'value' => $photo['archive'],
          'class' => 'd-none',
        ]);
      } else {
        echo $app->Form->input('photolist[' . $i . '][archive]', [
          'label' => 'Archív fotó',
          'type' => 'checkbox',
          'value' => 1,
          'checked' => $photo['archive'] == 1 ? true : false,
          'divs' => 'mb-1',
        ]);
      }



      echo $app->Form->input('photolist[' . $i . '][sign]', [
        'label' => 'Szignó a képen',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['sign'] == 1 ? true : false,
        'ia-conn-select' => '.sign-artist-select-' . $i,
        'ia-conn-select-default' => @$artists[0]['id'] > 0 ? $artists[0]['id'] : '',
        'divs' => 'mb-1',
      ]);

      $extra_class = $photo['sign'] == 1 ? '' : ' d-none';
      echo $app->Form->input('photolist[' . $i . '][sign_artist_id]', [
        'label' => false,
        'divs' => false,
        'options' => $artists,
        'select_options' => [
          'value' => 'id',
          'text' => 'name',
        ],
        'class' => 'mb-4 sign-artist-select-' . $i . $extra_class,
        'empty' => [0 => 'Ismeretlen...'],
        'value' => $photo['sign_artist_id'],
      ]);




      echo $app->Form->input('photolist[' . $i . '][unveil]', [
        'label' => 'Avatást látunk',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['unveil'] == 1 ? true : false,
        'divs' => 'mb-1',
      ]);



      echo $app->Form->input('photolist[' . $i . '][artist]', [
        'label' => 'Alkotó a képen',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['artist'] == 1 ? true : false,
        'ia-conn-select' => '.photo-artist-select-' . $i,
        'ia-conn-select-default' => @$artists[0]['id'] > 0 ? $artists[0]['id'] : '',
        'divs' => 'mb-1',
      ]);

      $extra_class = $photo['artist'] == 1 ? '' : ' d-none';
      echo $app->Form->input('photolist[' . $i . '][artist_id]', [
        'label' => false,
        'divs' => false,
        'options' => $artists,
        'select_options' => [
          'value' => 'id',
          'text' => 'name',
        ],
        'class' => 'mb-4 photo-artist-select-' . $i . $extra_class,
        'empty' => [0 => 'Válassz alkotót...'],
        'value' => $photo['artist_id'],
      ]);



      echo $app->Form->input('photolist[' . $i . '][other_place]', [
        'label' => 'Nem a helyszínről',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['other_place'] == 1 ? true : false,
        'divs' => 'mb-1',
      ]);

      echo $app->Form->input('photolist[' . $i . '][other]', [
        'label' => 'Adalék',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['other'] == 1 ? true : false,
        'divs' => 'mb-1',
      ]);

      echo $app->Form->input('photolist[' . $i . '][joy]', [
        'label' => 'Élménykép',
        'type' => 'checkbox',
        'value' => 1,
        'checked' => $photo['joy'] == 1 ? true : false,
      ]);


      if ($app->Users->owner_or_head($photo, $_user)) {
        if ($photo['archive_locked'] == 1) {
          echo '<p>Kép felhasználhatósága:<br /><strong>' . sDB['license_types'][$photo['license_type_id']] . '</strong></p>';
        } else {
          echo $app->Form->input('photolist[' . $i . '][license_type_id]', [
            'options' => $app->Users->licenses_selectable($photo),
            'label' => 'Kép felhasználhatósága',
            'value' => $photo['license_type_id'],
          ]);
        }
      }


      echo '</div>';


      // Leírás, forrás
      echo '<div class="col-12 col-lg-5 mb-4 mb-lg-0 d-none d-lg-block photo-editcol-' . $i . '">';
      echo $app->Form->input('photolist[' . $i . '][text]', [
        'label' => 'Leírás',
        'type' => 'textarea',
        'value' => $photo['text'],
      ]);
      echo $app->Form->input('photolist[' . $i . '][source]', [
        'label' => 'Forrás',
        'type' => 'textarea_short',
        'value' => $photo['source'],
      ]);

      // Év, ha
      echo $app->Form->input('photolist[' . $i . '][year]', [
        'label' => 'Évszám, ha korábbi fotó',
        'type' => 'text',
        'ia-input' => 'number',
        // 1827-ben készítették az első értelmes fotót; számoljunk egy kis lemaradással, azér' ;)
        // https://hu.wikipedia.org/wiki/A_fotogr%C3%A1fia_t%C3%B6rt%C3%A9nete
        // Illetve most jelent meg Péntek Orsolya könyve: https://www.libri.hu/konyv/pentek_orsolya.a-magyar-foto.html
        // ez pedig 1840-től indul, vagyis ergo tehát.
        'ia-input-min' => '1840',
        'ia-input-max' => date('Y') - 1,
        'value' => $photo['year'],
      ]);


      if ($app->Users->owner_or_head($photo, $_user)) {

        echo '<div class="rounded bg-light p-2 pb-0 mt-2 small">';

        if ($photo['missing_original'] == 0 && $photo['copied'] > 0
          && ($app->Users->owner_or_right($artpiece, $_user) || $_user['id'] == $photo['user_id'])) {
          echo $app->Html->link('Forgatás', '/eszkozok/kepkezelo/?foto=' . $photo['id'], [
            'icon' => 'redo',
            'class' => 'text-muted mr-3 mb-2 cursor-pointer text-nowrap',
            'title' => 'Kép forgatása',
          ]);
        }

        echo $app->Html->link('Másol', '#', [
          'icon' => 'copy fa-lg',
          'class' => 'text-muted mr-3 mb-2 cursor-pointer text-nowrap',
          'ia-bind' => 'artpieces.photo_copy_question',
          'ia-pass' => $photo['id'],
          'ia-vars-delete' => 0,
          'ia-vars-artpiece_id' => $artpiece['id'],
          'ia-vars-cover' => $its_cover ? 1 : 0,
          'title' => 'Fotó másolása',
        ]);

        if ($app->Users->is_head($_user)) {
          echo $app->Html->link('Áthelyez', '#', [
            'icon' => 'arrow-alt-from-left fa-lg',
            'class' => 'text-muted mr-3 mb-2 cursor-pointer text-nowrap',
            'ia-bind' => 'artpieces.photo_copy_question',
            'ia-pass' => $photo['id'],
            'ia-vars-delete' => 1,
            'ia-vars-artpiece_id' => $artpiece['id'],
            'ia-vars-cover' => $its_cover ? 1 : 0,
            'ia-vars-artist' => $photo['artist_id'] > 0 || $photo['sign_artist_id'] > 0 ? 1 : 0,
            'title' => 'Fotó áthelyezése',
          ]);
        }

        echo $app->Html->link('Töröl', '#', [
          'icon' => 'trash fa-lg',
          'class' => 'text-muted mb-2 cursor-pointer text-nowrap',
          'ia-bind' => 'artpieces.photo_delete_question',
          'ia-pass' => $photo['id'],
          'ia-vars-artpieces' => urlencode(str_replace('"', '', $photo['artpieces'])),
          'ia-vars-artpiece_id' => $artpiece['id'],
          'ia-vars-cover' => $its_cover ? 1 : 0,
          'title' => 'Fotó törlése',
        ]);

        echo '</div>';

      }
      
      $photo_artpieces = _json_decode($photo['artpieces']);
      if (count($photo_artpieces) > 1) {
        echo '<div class="mt-3"><span class="far fa-copy text-muted mr-2"></span>Más műlapokon:';
        foreach ($photo_artpieces as $photo_artpiece) {
          if ($photo_artpiece != $artpiece['id']) {
            echo $app->Html->link($photo_artpiece, '#', [
              'artpiece' => ['id' => $photo_artpiece],
              'target' => '_blank',
              'class' => 'font-weight-bold ml-2'
            ]);
          }
        }
        echo '</div>';
      }
      

      echo '</div>';

    } else {

      echo '<div class="col-md-12 col-lg-8 pt-0 pt-lg-3">';
      echo $image_info;
      echo '</div>';

    }



    echo '</div>'; // row

  }

}

echo $app->Form->end();