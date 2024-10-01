<?php
namespace Kozterkep;

class ImageHelper {

  public function __construct($passed) {
    $this->Html = new HtmlHelper();
    $this->MC = new MemcacheComponent();
    $this->Mongo = $passed['Mongo'];
  }

  /**
   *
   * Műlap fotó slug alapján.
   * Bedobhatunk neki műlapot is.
   * Ha még nem s3-ra másolt, akkor a lokális
   * URL-t adja oda, egyébként s3-at.
   *
   * @param $slug
   * @param array $options
   * @return string
   */
  public function photo($photo, $options = []) {
    $options = (array)$options + [
      'size' => 4,
      'class' => '',
      'info' => false,
      'uploader' => false,
      'link' => false,
      'link_options' => [],
      'original_slug' => false,
      'artpiece' => false, // átadott műlap
      'artist' => false, // átadott alkotó
      'artpiece_tooltip' => false,
      'photo_tooltip' => false,
    ];

    // Ha igazából műlap jön
    if (is_array($photo) && (isset($photo['photo_id']) || isset($photo['photo_slug']))) {
      $photo = [
        'id' => @$photo['photo_id'],
        'slug' => @$photo['photo_slug'],
      ];
    }

    if (is_array($photo)) {
      $slug = $photo['slug'];
      $options['original_slug'] = @$photo['original_slug'];
    } else {
      $slug = $photo;
    }

    // Tooltipek
    if ($options['artpiece_tooltip']) {
      $tooltip_options = [
        'ia-tooltip' => 'mulap',
        'ia-tooltip-id' => $options['artpiece_tooltip'],
      ];

      if ($options['link']) {
        $options['link_options'] = $options['link_options'] + $tooltip_options;
      } else {
        $options = $options + $tooltip_options;
      }

      // mindenképp
      $options['photo_tooltip'] = false;
    }
    unset($options['artpiece_tooltip']);

    if ($options['photo_tooltip']) {
      $tooltip_options = [
        'ia-tooltip' => 'foto',
        'ia-tooltip-id' => $photo['id'],
      ];

      if ($options['link']) {
        $options['link_options'] = $options['link_options'] + $tooltip_options;
      } else {
        $options = $options + $tooltip_options;
      }
    }
    unset($options['photo_tooltip']);


    if (strpos($options['class'], 'img-fluid') === false) {
      $options['class'] .= ' img-fluid';
    }


    $filename = $slug . '_' . $options['size'] . '.jpg';
    $s3_file = C_WS_S3['url'] . C_WS_S3['folder_prefix'] . 'photos/' . $filename;

    // Ha még itt a szerveren a fájl, akkor innen szolgálunk ki
    if (@$photo['copied'] === 0) {
      $process_info = '<span class="image-badge bg-warning badge" title="" data-toggle="tooltip" data-original-title="Feldolgozás alatt. Néhány percen belül kezeljük az állományt!"><span class="far fa-sync-alt fa-fw"></span></span>';
      $path = '/eszkozok/atmeneti_foto_hely/' . $options['original_slug'];
    } /*elseif (is_file(CORE['PATHS']['WEB'] . '/' . APP['path'] . '/webroot/imgcache/' . $filename)) {
      $process_info = '';
      $path = '/imgcache/' . $filename;
    }*/ else {
      $process_info = '';
      $path = $s3_file;
      /*$this->Mongo->upsert('cacheimages', [
        'filename' => $filename,
        's3_url' => $s3_file,
        'request' => date('Y-m-d H:i:s'),
      ], [
        'filename' => $filename,
      ]);*/
    }

    $parsed_attributes = $this->Html->parse_attributes($options, [], [], ['link', 'link_target', 'original_slug', 'size', 'info', 'uploader']);

    if ($slug != '') {
      $img = $process_info . '<img src="' . $path . '" class="s3-image ' . $options['class'] . '" ' . $parsed_attributes . ' />';
    } else {
      // Nincs kép
      // szélesség képmérettől függ
      $width = sDB['photo_sizes'][$options['size']];
      $img = '<img src="/img/placeholder.png" class="' . $options['class'] . '" ' . $parsed_attributes . ' style="width: ' . $width . 'px;" />';
    }

    if ($options['link']) {
      if ($options['link'] == 'showroom') {
        $link = '#';
        $options['link_options'] = [
          'ia-showroom' => 'photo',
          'ia-showroom-file' => @$photo['id'],
          'ia-showroom-file-path' => str_replace('_' . $options['size'], '_1', $path),
          'ia-showroom-file-type' => 'image',
          //'ia-showroom-hash' => 'fotolista',
          'ia-showroom-container' => '#fotolista'
        ];

      } elseif ($options['link'] == 'original' && $options['original_slug']) {
        $link = C_WS_S3['url'] . 'originals/' . $options['original_slug'] . '.jpg';
      } elseif ($options['link'] == 'full') {
        $link = str_replace('_' . $options['size'], '_1' , $path);
      } elseif ($options['link'] == 'self') {
        $link = $path;
      } else {
        $link = $options['link'];
      }

      if (isset($tooltip_options)) {
        $options['link_options'] = $options['link_options'] + $tooltip_options;
      }

      $s = $this->Html->link($img, $link, $options['link_options']);

    } else {

      $s = $img;
    }

    if ($options['info']) {

      $s .= '<div class="text-center mt-1 small">';

      if ($options['artpiece'] || @$photo['artpiece_id'] > 0) {
        if (!$options['artpiece']) {
          $artpiece = $this->MC->t('artpieces', $photo['artpiece_id']);
        } else {
          $artpiece = $options['artpiece'];
        }

        if ($artpiece) {
          $s .= '<strong>' . $artpiece['title'] . '</strong>';
        }
      } elseif ($options['artist'] || @$photo['portrait_artist_id'] > 0) {

        if (!$options['artist']) {
          $artist = $this->MC->t('artists', $photo['portrait_artist_id']);
        } else {
          $artist = $options['artist'];
        }

        if ($artist) {
          $s .= '<strong>' . $artist['name'] . '</strong>';
        }
      }



      $user = $this->MC->t('users', $photo['user_id']);
      if ($user) {
        $s .= '<br />' . $user['name'] . ' fotója';
      }
      $s .= '<span class="ml-2 text-muted"><span class="fal fa-upload mr-2"></span>' . _time($photo['approved']) . '</span>';
      $s .= '</div>';

    } elseif ($options['uploader']) {
      $s .= '<div class="text-center mt-1 small">';
      $user = $this->MC->t('users', $photo['user_id']);
      if ($user) {
        $s .= $user['name'] . ' fotója';
      }
      $s .= '<div class="mt-1 text-muted"><span class="fal fa-upload mr-2"></span>' . _time($photo['approved']) . '</div>';
      $s .= '</div>';
    }


    return $s;
  }
}
