<?php
namespace Kozterkep;

class HtmlHelper {

  public function __construct($app_config = false) {
    if ($app_config) {
      $this->app_config = $app_config;
      $this->Request = new RequestComponent($app_config);
      $this->Session = new SessionComponent($app_config);
      $this->Text = new TextHelper();
    } else {
      $this->app_config = false;
    }
  }

  /**
   *
   * Kép helper
   *
   * @param $path
   * @param array $attributes
   * @return string
   */
  public function image($path, $options = []) {
    $options = (array)@$options + [
      'class' => '',
      'link' => false,
      'crop' => false,
      'style' => '',
    ];

    if (is_numeric($path)) {
      $path = $path > 0 ? '/mappak/fajl_mutato/' . $path : '/img/placeholder.png';
    } elseif (strpos($path, '/') === false) {
      $path = '/img/' . $path;
    }
    $src = $path;

    /* ez torzít, mert nem négyzetesek a képek
     * if (strpos($options['class'], 'float') !== false) {
      $options['style'] .= ' max-width: 33%;';
    }*/

    $attributes = [];

    // Függőleges croppolás, a legszebb
    if ($options['crop']) {
      $crop_height = $options['crop'];
      $extra_style = 'display: block; width: 100%;';
      $attributes['style'] = $options['style'] != '' ? $options['style'] . ' ' . $extra_style : $extra_style;
    }

    $attributes = _unset($options, ['crop', 'link']) + [
      'src' => $src,
    ];
    $attributes = $this->parse_attributes($attributes);

    $img = '<img' . $attributes . '>';

    if ($options['crop']) {
      $img = '<div style="height: ' . $crop_height . 'px; overflow: hidden;">' . $img . '</div>';
    }

    if ($options['link']) {
      if ($options['link'][0] == 'self') {
        $options['link'][0] = $src;
      }
      return $this->link($img, $options['link'][0], @$options['link'][1]);
    }
    return $img;
  }



  /**
   * Beadott fa- nélküli class-ból ikont ad vissza spanba pakolva
   * @param $icon_class
   * @param array $options
   * @return string
   */
  public function icon($icon_class, $options = []) {
    $options = (array)@$options + [
      'class' => '',
    ];

    if (strpos($icon_class, ' fas') !== false) {
      $fa_style = 'fas';
    } elseif (strpos($icon_class, ' far') !== false) {
      $fa_style = 'far';
    } elseif (strpos($icon_class, ' fal') !== false) {
      $fa_style = 'fal';
    } elseif (strpos($icon_class, ' fab') !== false) {
      $fa_style = 'fab';
    } else {
      $fa_style = 'far';
    }
    return '<span class="' . $fa_style . ' fa-' . $icon_class . ' ' . $options['class'] . '"></span>';
  }
  
  
  /**
   * 
   * Link helper
   * 
   * @param $text
   * @param $url
   * @param $attributes
   * @return
   */
  public function link($text, $url = '', $attributes = []) {
    $id = 'link-' . uniqid();

    $attributes = (array)$attributes + [
      'id' => $id,
      'hide_text' => false,
      'icon' => '',
      'icon_right' => '',
      'no_span' => false,
    ];

    $wrapper_start = $wrapper_end = $icon = '';

    // Ikon
    if ($attributes['icon'] != '' || $attributes['icon_right'] != '') {
      $icon_class = $attributes['icon'] != '' ? $attributes['icon'] : $attributes['icon_right'];
      $icon = $this->icon($icon_class);
    }

    if (@$attributes['divs'] != '') {
      $wrapper_start = '<div class="' . $attributes['divs'] . '">';
      $wrapper_end = '</div>';
      unset($attributes['divs']);
    }

    if (@$attributes['title'] != '' && !in_array(@$attributes['data-toggle'], ['tooltip', 'popover'])) {
      $attributes['data-toggle'] = 'tooltip';
      $attributes['title'] = htmlentities($attributes['title']);
    }

    // Szöveg rejtődjön? És itt adjuk meg, hogy bal vagy jobb margó, ikon pozitól függően
    if ($attributes['icon'] != '' && strpos($attributes['icon'], 'mr-0') === false) {
      $text_margin = 'ml-1';
    } elseif ($attributes['icon_right'] != '' && strpos($attributes['icon_right'], 'ml-0') === false) {
      $text_margin = 'mr-1';
    } else {
      $text_margin = '';
    }

    if ($attributes['no_span'] == false) {
      if ($attributes['hide_text']) {
        $text_class = is_bool($attributes['hide_text']) ? 'd-none d-sm-inline' : $attributes['hide_text'];
        $text = '<span class="' . $text_class . ' link-text ' . $text_margin . '">' . $text . '</span>';
        unset($attributes['hide_text']);
      } elseif ($text != '') {
        $text = '<span class="link-text ' . $text_margin . '">' . $text . '</span>';
      }
    }

    if (isset($attributes['ia-tooltip'])
      && strpos(@$attributes['class'], 'd-inline-block') === false) {
      if (!isset($attributes['class'])) {
        $attributes['class'] = 'd-inline-flex align-items-center';
      } else {
        $attributes['class'] .= ' d-inline-flex align-items-center';
      }
    }

    // Szöveg + ikon összepakolása, pozitól függően
    $text = $attributes['icon'] != '' ? $icon . $text : $text . $icon;
    $attributes = _unset($attributes, ['icon', 'icon_right', 'no_span', 'hide_text']);

    $url = $this->link_url($url, $attributes);

    $attributes = $this->parse_attributes($attributes);
    $attributes = str_replace('{id}', $id, $attributes);
    return $wrapper_start . '<a href="' . $url . '"' . $attributes . '>' . $text . '</a>' . $wrapper_end;
  }


  /**
   *
   * Link URL előállítás
   *
   * @param $url
   * @param array $options
   * @return mixed
   */
  public function link_url($url, $options = []) {
    $options = (array)$options + [
      'url_end' => '',
    ];
    if ($this->app_config && $url == 'referer') {
      $url = $this->Request->here();
    }

    // Műlap
    if (is_array(@$options['artpiece'])) {
      $item = $options['artpiece'];
      $url = '/' . $item['id'];
      $url .= isset($item['title']) ? '/' . $this->Text->slug($item['title']) : '';
      unset($options['artpiece']);
      $url .= $options['url_end'];
    }

    // Profil
    if (is_array(@$options['user'])) {
      $item = $options['user'];
      $url = '/kozosseg/profil/' . $item['link'];
      unset($options['user']);
      $url .= $options['url_end'];
    }

    // Egyéb linkelések
    $models = [
      'artist' => ['alkotok', 'name'],
      'place' => ['helyek', 'name'],
      'country' => ['orszagok', '1'],
      'county' => ['megyek', '0'],
      'district' => ['budapesti-keruletek', '0'],
      'folder' => ['mappak', 'name'],
      'set' => ['gyujtemenyek', 'name'],
      'post' => ['blogok', 'title'],
      'user' => ['kozosseg', 'link'],
    ];

    foreach ($models as $model => $params) {
      if (is_array(@$options[$model])) {
        $models_ = sDB['model_parameters'];
        $model_params = isset($models_[$model . 's']) ? $models_[$model . 's'] : false;

        $item = $options[$model];
        $slug = isset($item[$params[1]]) ? '/' . $this->Text->slug($item[$params[1]]) : '';

        if ($model_params) {
          $url = $model_params[2] . $item['id'] . $slug . $options['url_end'];
        } else {
          $url = '/' . $params[0] . '/megtekintes/' . $item['id'] . $slug . $options['url_end'];
        }

        unset($options[$model]);
      }
    }

    return $this->parse_url($url);
  }



  public function tag(string $name, $content, $attributes = []) {
    $attributes = $this->parse_attributes($attributes);
    $s = '<' . $name . '' . $attributes . '>' . $content;
    if (!in_array($name, ['img'])) {
      $s .= '</' . $name . '>';
    }
    return  $s;
  }


  /**
   *
   * BS dropdown
   *
   * @param $text
   * @param array $link_attributes
   * @param array $menu_items
   * @param array $menu_attributes
   * @return string
   */
  public function dropdown(string $text, $link_attributes = [], $menu_items = [], $menu_attributes = []) {
    $id = isset($link_attributes['id']) ? $link_attributes['id'] : 'dropdown' . uniqid();
    $class = isset($link_attributes['class']) ? $link_attributes['class'] . ' dropdown-toggle' : 'dropdown-toggle';
    unset($link_attributes['class']);
    unset($link_attributes['id']);

    if (@$link_attributes['no_caret']) {
      $class = trim(str_replace('dropdown-toggle', '', $class));
      unset($link_attributes['no_caret']);
    }

    // Link
    $link_attributes = (array)$link_attributes + [
      'class' => $class,
      'data-toggle' => 'dropdown',
      'data-target' => $id . 'Menu',
      'role' => 'button',
      'aria-haspopup' => 'true',
      'aria-expanded' => 'false'
    ];

    $s = $this->link($text, '#', $link_attributes);

    // Menü
    $menu_attributes['class'] = @$menu_attributes['class'] . ' dropdown-menu';
    $menu_attributes['id'] = $id . 'Menu';
    $menu_attributes['aria-labelledby'] = $menu_attributes['id'];
    $menu_attributes['role'] = 'menu';
    $parsed_menu_attributes = $this->parse_attributes($menu_attributes);
    $s .= '<div ' . $parsed_menu_attributes . '>';

    if (!is_array($menu_items)) {
      // Stringet kaptunk, valami HTML okosság lesz ebből
      $s .= $menu_items;
    } else {
      // Sima menü lista
      foreach ($menu_items as $item) {
        if ($item == '') {
          $s .= '<div class="dropdown-divider"></div>';
          continue;
        } elseif (is_array($item)) {
          @$item[2]['class'] .= ' dropdown-item';
          $s .= $this->link(
            $item[0],
            $item[1],
            @$item[2] // ha van bármilyen attr
          );
        }
      }
    }
    $s .= '</div>';

    return $s;
  }


  /**
   *
   * Egyszerű lista
   *
   * @param $elements
   * @param $attributes
   * @return string
   */
  public function list($elements = [], $attributes = []) {
    $s = '';
    if (count($elements) > 0) {
      $parsed_attributes = $this->parse_attributes($attributes);
      $s .= '<ul' . $parsed_attributes . '><li>';
      $s .= implode('</li><li>', $elements);
      $s .= '</li></ul>';
    }
    return $s;
  }


  /**
   *
   * Description list(a) ;]
   *
   * dl, dt, dd volt kezdetben, de az olyan furán viselkedett, hogy
   * szégyenszemre átírtam az egészet div-re...
   *
   * @param $what start | end | array
   * @return string
   */
  public function dl($what, $options = []) {
    $s = '';
    switch (true) {
      case ($what == 'create'):
        $attributes = (array)$options + [
          'class' => 'row'
        ];
        $attributes = $this->parse_attributes($attributes);
        $s .= '<div' . $attributes . '>';
        break;

      case ($what == 'end'):
        $s .= '</div>';
        break;

      case (is_array($what)):
        $s .= '<div class="col-4 font-weight-normal text-nowrap">' . $what[0] . '</div>';
        $s .= '<div class="col-8 mb-2 mb-sm-1 font-weight-bold">' . $what[1] . '</div>';
        break;
    }
    return $s;
  }


  public function tabs($tabs = [], $options = []) {
    $options = (array)$options + [
      'type' => 'tabs',
      'align' => 'center',
      'only_icons' => false, // beadtunk cimkéket, de ne mutassuk őket, csak az ikonokat
      'hide_labels' => true, // mobilon
      'text_align' => 'center',
      'direction' => 'horizontal',
      'mobile_title' => false,
      'preload' => false,
    ];

    if (!$options['hide_labels']) {
      if ($options['direction'] == 'horizontal') {
        // Egyelőre ezt kiiktattam, hogy egymás alatti szöveges tabok legyenek sm alatt
        //$ul_flex_class = ' flex-column flex-sm-row ';
        $ul_flex_class = ' flex-row ';
      } else {
        $ul_flex_class = ' flex-row ';
      }
      $li_flex_class = ' flex-sm-fill ';
    } else {
      if ($options['direction'] == 'vertical') {
        $ul_flex_class = ' flex-row flex-md-column ';
      }
    }

    $align_class = 'text-' . $options['text_align'];

    $hidden_ul = $options['preload'] ? 'd-none ' : '';

    $s = '<ul class="' . $hidden_ul . ' nav nav-' . $options['type'] . ' '
      . @$options['class'] . @$ul_flex_class . ' ' . $align_class . '" role="tablist">';

    if ($options['mobile_title']) {
      $s .= '<span class="d-inline d-block d-md-none mr-2 pt-1">' . $options['mobile_title'] . ':</span>';
    }

    $i = 0;
    foreach (@$tabs as $title => $tab_options) {
      if (isset($tab_options['options'])) {
        $tab_options['options'] = _unset($tab_options['options'], ['class', 'id', 'data-toggle', 'role', 'icon', 'target']);
        $extra_options = $tab_options['options'];
      } else {
        $extra_options = [];
      }

      $i++;
      if (!is_array($tab_options)) {
        $tab_options_ = [];
        $tab_options_['hash'] = $tab_options;
        $tab_options = $tab_options_;
      }
      if (@$options['selected'] == @$tab_options['hash']
        || is_int(@$options['selected']) && $i == @$options['selected']) {
        $class = ' active';
        $aria_selected = 'true';
      } else {
        $class = '';
        $aria_selected = 'false';
      }

      $link = @$tab_options['hash'] != '' ? '#' . $tab_options['hash'] : $tab_options['link'];

      $tab_option_array = [
        // Itt beadjuk, hogy nincs jobb margó, de md-től van
        'icon' => @$tab_options['icon'] . ' mr-0 mr-md-1',
        'class' => 'nav-link ' . @$li_flex_class . $class,
        'target' => @$tab_options['target'] ? $tab_options['target'] : '_self',
      ] + $extra_options;

      if (@$tab_options['hash'] != '') {
        $tab_option_array = array_merge($tab_option_array, [
          'id' => @$tab_options['hash'] . '-tab',
          'data-toggle' => trim($options['type'], 's'),
          'role' => 'tab',
          'aria-controls' => @$tab_options['hash'],
          'aria-selected' => $aria_selected
        ]);
      }

      if ($options['only_icons']) {
        $title = '';
      } elseif ($options['hide_labels']) {
        $title = '<span class="d-none d-md-inline">' . $title . '</span>';
      }

      $margin_bottom = $options['type'] == 'pills' ? 'mb-2 mb-md-0' : '';
      $s .= '<li class="nav-item ' . $margin_bottom . ' mr-sm-2">';
      $s .= $this->link($title, $link, $tab_option_array);
      $s .= '</li>';
    }

    $s .= '</ul>';

    if (@$options['align'] == 'right') {
      $s = '<div class="float-right">' . $s . '</div><div class="clearfix"></div>';
    } elseif (@$options['align'] == 'center') {
      $s = '<div class="d-flex justify-content-center">' . $s . '</div><div class="clearfix"></div>';
    }

    return $s;
  }
  
  
  /**
   * 
   * Html elemek attribútumainak stringgé alakítása
   * 
   * @param string $attributes
   * @param $defaults - default attribútumok, alapértelmezett értékkel
   * @param $appendable_defaults - ezek azok az alapértelmezettek, amikhez appendolni lehet, felülírni nem
   * @param $exclude - ezeket nem parszoljuk bele
   * @return string
   */
  public function parse_attributes($attributes = [], $defaults = [], $appendable_defaults = [], $exclude = []) {
    if (count($exclude) > 0) {
      $attributes = _unset($attributes, $exclude);
    }

    if (count($defaults) > 0) {

      // Ezek mindenképp kellenek
      $appendable_defaults = (array)$appendable_defaults + ['class'];

      if (count($appendable_defaults) > 0) {
        foreach ($appendable_defaults as $attr) {
          if (isset($attributes[$attr]) && isset($defaults[$attr])) {
            $attributes[$attr] = $defaults[$attr] . ' ' . $attributes[$attr];
          }
        }
      }
      $attributes = (array)$attributes + $defaults;
    }
    
    if (count($attributes) == 0 || !$attributes || !is_array($attributes)) {
      return ' ';
    }

    foreach ($attributes as $attribute => $value) {
      if (!$attribute || is_array($value)) {
        unset($attributes[$attribute]);
        continue;
      }
      $attributes[$attribute] = ' ' . $attribute . '="' . $value . '"';
    }
    
    return is_array($attributes) ? join('', $attributes) : $attributes;
  }


  /**
   *
   * URL parsolás, egyelőre szegényes funkciókkal
   *
   * @param $url
   * @return mixed
   */
  public function parse_url(string $url, $options = []) {
    $parsed_url = parse_url($url);

    if ($this->app_config) {
      $user = $this->Session->get('user');

      // User behelyettesítés
      if (@$user == true) {
        foreach ([
                   '{user.id}' => $user['id'],
                   '{user.link}' => $user['link']
                 ] as $from => $to) {
          $url = str_replace($from, $to, $url);
        }
      }
    }

    // Egyéb dolgok

    // Kikukázzuk a delvars tömbben kapott változókat
    if (@count(@$options['delvars']) > 0 || @count(@$options['updatevars']) > 0) {
      parse_str(parse_url(urldecode($url), PHP_URL_QUERY), $query_vars);

      // Kiszedjük, ami nem kell
      if (@count(@$options['delvars']) > 0) {
        foreach ($options['delvars'] as $var) {
          unset($query_vars[$var]);
        }
      }

      // Betesszük, vagy felülírjuk, ami kell
      if (@count(@$options['updatevars']) > 0) {
        foreach ($options['updatevars'] as $var => $new_value) {
          $query_vars[$var] = $new_value;
        }
      }

      $url = $parsed_url['path'] . '?' . http_build_query($query_vars);
    }

    return $url;
  }


  /**
   *
   * Egyszerű pagináció
   *
   * @param $item_count - aktuális oldalon megjelenő elemek száma (utolsó oldalon kevesebb lehet, mint a limit)
   * @param array $options
   * @return string
   */
  public function pagination($item_count, $options = []) {
    $query = $this->Request->query();

    $options = (array)$options + [
      'div' => 'my-5',
      'class' => 'btn btn-outline-secondary mx-2 mb-3 mb-sm-0 d-inline-block',
      'page' => @$query['oldal'] > -1 ? $query['oldal'] : 0,
      'limit' => 50,
      'centered' => true,
      'page_selector' => false, // hogy ez menjen, kell a total_count
      'total_count' => false,
    ];

    $link_base = rtrim(
      str_replace('?&', '?',
        str_replace('oldal=' . $options['page'], '', $this->Request->here())
      ), '&'
    );

    $glue = strpos($link_base, '?') !== false ? '&' : '?';

    $mx_auto = $options['centered'] ? 'mx-auto text-center' : '';

    $s = '<div class="' . $options['div'] . ' row d-flex">';
    $s .= '<div class="' . $mx_auto . ' form-inline">';

    if ($options['total_count'] || $options['page_selector']) {
      $s .= '<div class="text-center mx-4">';
    }

    if ($options['total_count']) {
      $s .= '<div class="pt-1 d-inline-block font-weight-bold text-muted mr-3">' . _n($options['total_count']) . ' találat</div>';
    }

    if ($options['page_selector'] && $options['total_count']) {
      $page_count = ceil($options['total_count']/$options['limit']);
      $s .= '<select name="oldal" class="form-control d-inline-block mr-3 mb-3 mb-md-0" style="max-width: 130px;" ia-urlchange-input="oldal">';
      for ($i=1;$i<=$page_count;$i++) {
        $selected = $options['page'] == $i ? ' selected' : '';
        $s .= '<option value="' . $i . '"' . $selected . '>' . $i . '. oldal</option>';
      };
      $s .= '</select>';
    }

    if ($options['total_count'] || $options['page_selector']) {
      $s .= '</div>';
    }


    if ($options['total_count'] || $options['page_selector']) {
      $s .= '<div class="text-center mx-4">';
    }

    if ($options['page'] > 1) {
      $s .= $this->link('Első', $link_base . $glue . 'oldal=1', [
        'icon' => 'chevron-double-left',
        'class' => $options['class']
      ]);
    }

    $prev_class = $options['page'] < 2 ? ' disabled' : '';
    $s .= $this->link('Előző', $link_base . $glue . 'oldal=' . max(1, $options['page']-1), [
      'icon' => 'chevron-left',
      'class' => $options['class'] . $prev_class
    ]);

    $next_class = $item_count < $options['limit'] ? ' disabled' : '';
    $s .= $this->link('Következő', $link_base . $glue . 'oldal=' . ($options['page']+1), [
      'icon_right' => 'chevron-right',
      'class' => $options['class'] . $next_class
    ]);

    if ($options['total_count'] || $options['page_selector']) {
      $s .= '</div>';
    }

    $s .= '</div>';
    $s .= '</div>';

    return $s;
  }


  /**
   *
   * Info bubi
   *
   * @param $text
   * @param array $options
   * @return string
   */
  public function info($text, $options = []) {
    $options = (array)$options + [
      'icon' => 'info-circle fa-fw',
      'class' => '',
    ];

    $s = '<span class="far fa-' . $options['icon'] . ' text-muted  '
      . $options['class'] . '" '
      . 'data-toggle="tooltip" title="' . $text . '"></span>';

    return $s;
  }



  /**
   *
   * Email cím alapján visszanyomja a szolgáltató linkjét egy gombbal
   * Szuperextrakényelem.
   *
   * @param $email_address
   * @return string
   */
  public function email_account_link($email_address) {
    switch (true) {
      case _contains($email_address, '@gmail.com'):
        $provider = [
          'name' => 'Gmail',
          'url' => 'https://mail.google.com'
        ];
        break;
      case _contains($email_address, [
        '@outlook.com',
        '@hotmail.com',
      ]):
        $provider = [
          'name' => 'Outlook',
          'url' => 'https://outlook.live.com'
        ];
        break;
      case _contains($email_address, '@yahoo.com'):
        $provider = [
          'name' => 'Yahoo! Mail',
          'url' => 'https://mail.yahoo.com'
        ];
        break;
      case _contains($email_address, '@freemail.hu'):
        $provider = [
          'name' => 'Freemail',
          'url' => 'https://accounts.freemail.hu'
        ];
        break;
      case _contains($email_address, '@citromail.hu'):
        $provider = [
          'name' => 'Citromail',
          'url' => 'https://www.citromail.hu/'
        ];
        break;
      case _contains($email_address, [
        '@indamail.hu',
        '@eposta.hu',
        '@vipmail.hu',
        '@webmail.hu',
      ]):
        $provider = [
          'name' => 'Indamail',
          'url' => 'https://indamail.hu/'
        ];
        break;
    }

    if (@$provider['name'] != '') {
      return $this->link($provider['name'] . ' megnyitása', $provider['url'],
        [
          'icon' => 'envelope',
          'class' => 'btn btn-secondary btn-sm ml-3 my-0',
          'target' => '_blank'
        ]
      );
    }

    return '';
  }


  /**
   *
   * Sima alert doboz kiírás bárhova
   * (hisz a flash csak load után jön ki)
   *
   * @param $text
   * @param $type
   * @param $options
   * @return string
   */
  public function alert($text, $type, $options = []) {
    $options = (array)$options + [
      'class' => '',
      'dismissable' => false,
      'remove_after' => false,
      'id' => 'alert-' . uniqid()
    ];

    $options['class'] = 'alert alert-' . $type . ' ' . $options['class'];

    if ($options['dismissable']) {
      $options['class'] .= ' alert-dismissible fade show';
    }

    $s = '<div class="' . $options['class'] . '" id="' . $options['id'] . '" role="alert">';
    if (is_array($text)) {
      $s .= @$text['title'] != '' ? '<strong>' . $text['title'] . '</strong><br />' : '';
      $s .= @$text['bold'] != '' ? '<strong>' . $text['bold'] . '</strong> ' : '';
      $s .= @$text['text'] != '' ? $text['text'] : '';
    } else {
      $s .= $text;
    }

    if ($options['dismissable']) {
      $s .= '<a href="#" class="close" data-dismiss="alert" aria-label="Bezárás">'
        . '<span aria-hidden="true" class="far fa-times-circle"></span>'
        . '</a>';
    }

    $s .= '</div>';

    return $s;
  }



  /**
   *
   * JS minifájoló
   *
   * @param array $paths
   * @return bool
   */
  public function minify_js($paths = [], $target = 'build') {
    $app_folder = CORE['PATHS']['WEB'] . '/' . APP['path'] . '/webroot/js/';

    $minifier = new \MatthiasMullie\Minify\JS();
    if (is_array($paths)) {
      foreach ($paths as $path) {
        if (substr($path, -4) == '.php') {
          // PHP fájl útvonalat kaptunk
          $content = file_get_contents(CORE['BASE_URL'] . '/js/' . $path);
          if ($content) {
            $minifier->add($content);
          }
        } elseif (substr($path, -3) == '.js') {
          // JS fájl útvonalat kaptunk
          $minifier->add($app_folder . $path);
        } else {
          // STRINGET kaptunk, ami ugye script
          $minifier->add($path);
        }
      }
    } else {
      // egy útvonal, vagy konkrétan JS script
      $minifier->add($paths);
    }

    $minified = $minifier->minify($app_folder . 'app/' . $target . '.min.js');
    return $minified ? true : false;
  }


  /**
   *
   * CSS minifájoló
   *
   * @param array $paths
   * @return bool
   */
  public function minify_css($paths = []) {
    $app_folder = CORE['PATHS']['WEB'] . '/' . APP['path'] . '/webroot/css/';

    $minifier = new \MatthiasMullie\Minify\CSS();
    if (is_array($paths)) {
      foreach ($paths as $path) {
        if (substr($path, -4) == '.css') {
          // CSS fájl útvonalat kaptunk
          $minifier->add($app_folder . $path);
        } else {
          // STRINGET kaptunk, ami ugye style
          $minifier->add($path);
        }
      }
    } else {
      // egy útvonal, vagy konkrétan JS script
      $minifier->add($paths);
    }

    $minified = $minifier->minify($app_folder . 'app/build.min.css');
    return $minified ? true : false;
  }


  /**
   *
   * Table kezdő és záró logika
   * mert lusta vagyok.
   *
   * @param $method
   * @param string $class
   * @param array $columns
   * @return string
   */
  public function table($method, $columns = [], $class = 'table table-hover table-striped table-sm') {
    $s = '';

    if ($method == 'create') {
      $s .= '<div class="table-responsive">';
      $s .= '<table class="' . $class . '">';
      $s .= '<thead>';
      $s .= '<tr>';
      foreach ($columns as $column) {
        $s .= '<th>' . $column . '</th>';
      }
      $s .= '</tr>';
      $s .= '</thead>';
      $s .= '<tbody>';

    } elseif ($method == 'end') {
      $s .= '</tbody>';
      $s .= '</table>';
      $s .= '</div>';
    }

    return $s;
  }

}
