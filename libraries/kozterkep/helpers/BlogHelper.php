<?php
namespace Kozterkep;

class BlogHelper {

  public function __construct($app_config, $DB) {
    $this->DB = $DB;
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();

    $this->Html = new HtmlHelper($app_config);
    $this->Text = new TextHelper($app_config);
    $this->Users = new UsersLogic($app_config, $this->DB);
  }


  /**
   *
   * Téma lista slug kulccsal
   *
   * @param bool $full
   * @return array
   */
  public function category_list($full = false) {
    $categories_ = sDB['post_categories'];
    $categories = [];
    foreach ($categories_ as $key => $category) {
      if (!$full) {
        $categories[$category[2]] = $category[0];
      } else {
        $categories[$category[2]] = [
          'id' => $key,
          'slug' => $category[2],
          'name' => $category[0],
          'admin' => $category[1],
        ];
      }
    }
    return $categories;
  }


  /**
   *
   * Poszt item kb. Cím, infó, kép, intro
   *
   * @param $post
   * @param array $options
   * @return string
   */
  public function intro($post, $options = []) {
    $options = (array)$options + [
      'info' => true,
      'intro' => 300,
      'title_size' => 5,
      'category' => false,
      'image' => true,
      'blog_name' => true,
      'image_position' => 'left',
      'image_size' => 0,
    ];

    if ($options['image_position'] != 'hide') {

      $image_class = '';
      if (in_array($options['image_position'], ['left', 'right'])) {
        if ($options['image_size'] == 0) {
          $options['image_size'] = 100;
        }
        $image_class = ' float-' . $options['image_position'] . ' mt-1 mb-1';
        if ($options['image_position'] == 'left') {
          $image_class .= ' mr-2';
        } else {
          $image_class .= ' ml-2';
        }
      }

      $image = $this->image($post, [
        'class' => $image_class,
        'size' => $options['image_size'],
      ]);

      if ($image != '') {
        $image = $this->Html->link($image, '', ['post' => $post]);
      }
    } else {
      $image = '';
    }

    $title = '';

    if ($post['status_id'] != 5) {
      $title .= '<div class="float-right"><span class="badge badge-warning">Szerkesztés alatt</span></div>';
    }

    $title .= '<h' . $options['title_size'] . ' class="font-weight-bold mb-1">' . $this->Html->link($post['title'], '', [
      'post' => $post
    ]) . '</h' . $options['title_size'] . '>';


    if ($options['info']) {
      $info = '<div class="text-muted mb-2">';
      if ($options['blog_name']) {
        $info .= $this->blog_name($post['user_id']) . ' @ ';
      }
      $info .= $post['published'] == 0 ? '-' : _time($post['published']);

      if ($options['category']) {
        $postcategory = sDB['post_categories'][$post['postcategory_id']];
        $info .= $this->Html->link($postcategory[0], '/blogok/tema/' . $postcategory[2], [
          'class' => 'ml-2 text-muted',
        ]);
      }
      $info .= '</div>';
    } else {
      $info = '';
    }

    if ($options['intro'] != false) {
      if ($post['intro'] == '') {
        // Szövegből csinálunk intrót
        $text = $this->Text->truncate($post['text'], $options['intro'], [
          'nl2br' => false,
          'format' => false,
          'strip_tags' => true,
        ]);
      } else {
        $text = $post['intro'];
      }
    } else {
      $text = '';
    }

    if (!$options['image']) {
      $s = $title . $info . $text;
    } else {

      if ($options['image_position'] == '') {
        $s = $title . $info;
        $s .= $image != '' ? '<div class="my-3">' . $image . '</div>' : '';
        $s .= $text;
      } else {
        $s = $image . $title . $info . $text;
      }
    }

    return $s;
  }



  /**
   *
   * Poszt képe
   *
   * @param $post
   * @param array $options
   * @return string
   */
  public function image($post, $options = []) {
    $options = $options + [
      'class' => '', // kép class
      'size' => 0, // méret pixelben
      'info_container' => false, // keret, és alatta link
      'image_size' => 4, // csak a photos-ra vonatkozó méret
      'file_size' => 0, // csak a fájlokra vonatkozó méret
    ];


    $path = false;
    $s = '';

    //debug($post);

    if (@$post['photo_id'] > 0) {
      $path = C_WS_S3['url'] . 'photos/' . $post['photo_slug'] . '_' . $options['image_size'] . '.jpg';
    } elseif (@$post['file_id'] > 0) {
      if ($options['file_size'] > 0) {
        $options['size'] = $options['file_size'];
      }
      $path = C_WS_S3['url'] . 'files/' . $post['file_slug'] . '.jpg';
    }

    $size = '';
    if ($options['size'] > 0) {
      $size = ' width="' . $options['size'] . '" style="max-width:45vw;" ';
    }

    if ($path) {
      $s = '<img src="' . $path . '" '
        . 'class="img img-fluid img-thumbnail ' . $options['class'] . '" ' . $size . '>';
    }


    if ($s != '' && $options['info_container']) {
      $container = '<div class="float-sm-left mr-3 mb-3 mt-2">';

      $container .= $s;

      if (@$post['file_folder_id'] > 0) {
        $folder = $this->MC->t('folders', $post['file_folder_id']);
        $container .= '<div class="mt-1 text-muted small text-center">';
        if (@$folder['public'] == 1) {
          $container .= $this->Html->link($folder['name'], '', [
            'folder' => $folder,
            'url_end' => '#vetito=' . $post['file_id'],
            'target' => '_blank',
            'icon' => 'folder',
          ]) . ' mappa';
        } else {
          // Nem publikus a folder, új lapon nyitjuk a képet
          $container .= $this->Html->link('Kép nagyobb méretben', '/mappak/fajl_mutato/' . $post['file_id'], [
            'target' => '_blank',
            'icon_right' => 'external-link',
          ]);
        }
        $container .= '</div>';
      } elseif (@$post['photo_artpiece_id'] > 0) {
        $photo_artpiece = $this->MC->t('artpieces', $post['photo_artpiece_id']);
        $container .= '<div class="mt-1 text-muted small text-center">';
        if (in_array(@$photo_artpiece['status_id'], [2,5])) {
          $container .= $this->Html->link($photo_artpiece['title'], '', [
            'artpiece' => $photo_artpiece,
            'url_end' => '#vetito=' . $post['photo_id'],
            'target' => '_blank',
            'icon' => 'map-marker',
          ]);
        } else {
          // Nem publikus a műlap, új lapon nyitjuk a képet
          $container .= $this->Html->link('Kép nagyobb méretben', C_WS_S3['url'] . 'photos/' . $post['photo_slug'] . '_1.jpg', [
            'target' => '_blank',
            'icon_right' => 'external-link',
          ]);
        }
        $container .= '</div>';
      }

      $container .= '</div>';
      $s = $container;
    }

    return $s;
  }


  /**
   *
   * Blog megnevezése
   *
   * @param $user
   * @param array $options
   * @return string
   */
  public function blog_name($user, $options = []) {
    $options = $options + [
      'link' => true,
      'only_name' => false,
      'image' => true,
    ];
    $user = is_numeric($user) ? $this->MC->t('users', $user) : $user;
    $s = '';
    if ($user) {
      $s = $options['image'] ? $this->Users->profile_image($user, 5) : '';
      $s .= $user['blog_title'] != '' ? $user['blog_title'] : $user['name'];

      if ($options['link']) {
        $s = $this->Html->link($s, '/blogok/tag/' . $user['link']);
      }
    }
    return $s;
  }


  public function text($post) {
    $postcategory = sDB['post_categories'][$post['postcategory_id']];

    // Admin bejegyzéseknél nincs strip_tags,
    // ha bekapcsoltuk a HTML-formázottságot
    if ($postcategory[1] == 1 && $post['html_formatted'] == 1) {
      $text = $this->Text->format($post['text'], [
        'strip_tags' => false,
        'nl2br' => false,
      ]);
    } else {
      $text = $this->Text->format($post['text'], [
        'allowed_tags' => '<strong><aimage><ffile>'
      ]);
    }

    // Műlap képek
    preg_match_all('/<aimage>(.*)<\/aimage>/', $text, $matches);
    if (count($matches[1]) > 0) {
      foreach ($matches[1] as $match) {
        if ($match > 0) {
          $photo = $this->DB->first('photos', $match, ['fields' => ['slug', 'artpiece_id']]);
          $text = str_replace('<aimage>' . $match . '</aimage>', $this->image([
            'photo_id' => $match,
            'photo_slug' => $photo['slug'],
            'photo_artpiece_id' => $photo['artpiece_id'],
          ], [
            'info_container' => true,
          ]), $text);
        }
      }
    }

    // Fájlok
    preg_match_all('/<ffile>(.*)<\/ffile>/', $text, $matches);
    if (count($matches[1]) > 0) {
      foreach ($matches[1] as $match) {
        if ($match > 0) {
          $file = $this->DB->first('files', $match, ['fields' => ['name', 'folder_id']]);
          $text = str_replace('<ffile>' . $match . '</ffile>', $this->image([
            'file_id' => $match,
            'file_slug' => $file['name'],
            'file_artpiece_id' => $file['folder_id'],
          ], [
            'info_container' => true,
          ]), $text);
        }
      }
    }

    return $text;
  }


  /**
   * Töröljük a cach-eket, ahol a blogposzt szerepel
   * @return bool
   */
  public function delete_caches($post, $user) {
    $this->Cache->delete('cached-view-posts-view-' . $post['id']);
    $this->Cache->delete('cached-view-posts-index');
    $this->Cache->delete('cached-view-posts-category');
    $this->Cache->delete('cached-view-community-index');
    $this->Cache->delete('cached-view-community-profile-' . $user['link']);

    if ($post['highlighted'] == 1) {
      $this->Cache->delete('cached-view-pages-index');
    }

    if ($post['artpiece_id'] > 0) {
      $this->Cache->delete('cached-view-artpieces-view-' . $post['artpiece_id']);
    }

    if (!in_array($post['connected_artpieces'], ['[]', ''])) {
      foreach (_json_decode($post['connected_artpieces']) as $aid) {
        $this->Cache->delete('cached-view-artpieces-view-' . $aid);
      }
    }

    if ($post['place_id'] > 0) {
      $this->Cache->delete('cached-view-places-view-' . $post['place_id']);
    }

    if ($post['artist_id'] > 0) {
      $this->Cache->delete('cached-view-artists-view-' . $post['artist_id']);
    }

    if ($post['set_id'] != '') {
      $this->Cache->delete('cached-view-sets-view-' . $post['set_id']);
    }
    return true;
  }
}
