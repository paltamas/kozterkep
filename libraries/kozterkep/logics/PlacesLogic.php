<?php
namespace Kozterkep;

class PlacesLogic {

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();

    $this->Html = new HtmlHelper($app_config);
  }


  /**
   *
   * Helység név
   *
   * @param $place
   * @param array $options
   * @return string
   */
  public function name($place, $options = []) {
    $options = (array)$options + [
      'link' => true,
      'original_name' => true,
      'class' => '',
      'tooltip' => false,
    ];

    if (!is_array($place)) {
      // id-t kaptunk
      $place = $this->MC->t('places', $place);
    }

    $s = '';

    if ($place) {
      if (@$place['name'] != '') {
        $s .= $place['name'];
        if ($options['original_name']) {
          $s .= @$place['original_name'] != '' && $place['name'] != $place['original_name']
            ? ' (' . $place['original_name'] . ')' : '';
        }
      }

      if ($options['link']) {
        $link_options = [
          'place' => $place,
          'class' => $options['class'],
        ];
        if ($options['tooltip']) {
          $link_options += [
            'ia-tooltip' => 'hely',
            'ia-tooltip-id' => $place['id'],
          ];
        }
        $s = $this->Html->link($s, '', $link_options);
      } else {
        $s = '<span class="' . $options['class'] . '">' . $s . '</span>';
      }
    }

    return $s;
  }


  /**
   *
   * Ország
   *
   * @param $country_id
   * @param array $options
   * @return string
   */
  public function country($country_id, $options = []) {
    $options = (array)$options + [
      'link' => true
    ];

    $s = '';

    $list = sDB['countries'];
    if (!isset($list[$country_id][0])) {
      return $s;
    }

    if (@sDB['countries'][$country_id][1] != '') {
      $s = sDB['countries'][$country_id][1];
    }

    if ($options['link']) {
      $s = $this->Html->link($s, '', [
        'country' => sDB['countries'][$country_id] + ['id' => $country_id]
      ]);
    }

    return $s;
  }


  /**
   *
   * Megye
   *
   * @param $county_id
   * @param array $options
   * @return string
   */
  public function county($county_id, $options = []) {
    $options = (array)$options + [
      'link' => true
    ];

    $s = '';

    $list = sDB['counties'];
    if (!isset($list[$county_id])) {
      return $s;
    }

    if (@sDB['counties'][$county_id][0] != '') {
      $s = sDB['counties'][$county_id][0];
    }

    if ($options['link']) {
      $s = $this->Html->link($s, '', [
        'county' => sDB['counties'][$county_id] + ['id' => $county_id]
      ]);
    }

    return $s;
  }


  /**
   *
   * BP kerület
   *
   * @param $district_id
   * @param array $options
   * @return string
   */
  public function district($district_id, $options = []) {
    $options = (array)$options + [
      'link' => true
    ];

    $s = '';

    $list = sDB['districts'];
    if (!isset($list[$district_id])) {
      return $s;
    }

    if (@sDB['districts'][$district_id][0] != '') {
      $s = sDB['districts'][$district_id][0];
    }

    if ($options['link']) {
      $s = $this->Html->link($s, '', [
        'district' => sDB['districts'][$district_id] + ['id' => $district_id]
      ]);
    }

    return $s;
  }


  /**
   *
   * Nominatim tömbből kitalálja, mi ez a hely
   * összeveti a saját adatbázissal is település
   * szinten és létre is hoz, ha kell...
   * Bátrak vagyunk?
   *
   * @param $address_array
   * @param $create
   * @param $creator_user_id
   * @return array
   */
  public function parse_address($address_array, $create = false, $creator_user_id = 0) {
    $address = [
      'place_id' => 0,
      'country_id' => 0,
      'county_id' => 0,
      'district_id' => 0,
      'address' => '',
    ];

    // Irszám hogy jön...
    if (!isset($address_array['postcode']) && isset($address_array['postal_code'])) {
      $address_array['postcode'] = $address_array['postal_code'];
    }

    // Ország
    foreach (sDB['countries'] as $id => $country) {
      if ($country[2] == @$address_array['country_code']) {
        $address['country_id'] = $id;
        break;
      }
    }

    if (@$address_array['country_code'] == 'hu') {
      // Megye
      foreach (sDB['counties'] as $id => $county) {
        if (strpos($county[0], trim(str_replace('region', '', @$address_array['county']))) !== false) {
          $address['county_id'] = $id;
          break;
        }
      }

      // Kerület vagy Margitsziget...
      if (@$address_array['postcode'] > 0 && @$address_array['postcode'] < 2000) {
        if (@$address_array['suburb'] == 'Margaret Island') {
          $address['district_id'] = 24;
        } else {
          $address['district_id'] = (int)substr($address_array['postcode'],1,2);
        }
      }
    }

    $nominatim = '';
    if (@$address_array['village'] != '') {
      $nominatim = $address_array['village'];
    } elseif (@$address_array['town'] != '') {
      $nominatim = $address_array['town'];
    } elseif (@$address_array['city'] != '') {
      $nominatim = $address_array['city'];
    }

    // Település kitalálása
    $place = $this->DB->first('places', [
      'OR' => [
        'name' => $nominatim,
        'nominatim' => $nominatim,
      ],
      'country_id' => $address['country_id'],
    ]);

    // Nincs, létre kell hozni
    if (!$place) {
      $place_id = $this->DB->insert('places', [
        'name' => $nominatim,
        'nominatim' => $nominatim,
        'country_code' => strtoupper(@$address['country_code']),
        'country_id' => $address['country_id'],
        'county_id' => @$address['county_id'],
        'created' => time(),
        'modified' => time(),
        'creator_user_id' => $creator_user_id,
        'user_id' => CORE['USERS']['places'],
        'artpiece_count' => 1, // ez, ami miatt szerkesztjük
      ]);
      $place = ['id' => $place_id];
    }

    $address['place_id'] = $place['id'];

    // Bp esetén 1-es megye
    if ($address['place_id'] == 110) {
      $address['county_id'] = 1;
    }

    // Utcaházszám
    if (@$address_array['road'] != '') {
      $address['address'] = $address_array['road'];
    }
    if (@$address_array['house_number'] != '') {
      $address['address'] .= ' ' . $address_array['house_number'];
      if ($address['country_id'] == 101) {
        $address['address'] .= '.';
      }
    }

    return $address;
  }


  /**
   *
   * Hely szinteket generál, amit morzsamenüben használunk
   *
   * @param $artpiece
   * @return array
   */
  public function get_breadcrumbs_menu($place) {
    $items = ['Helyek' => '/helyek/attekintes'];

    // Ország
    $list = sDB['countries'];
    if (isset($list[$place['country_id']][0])) {
      $items[$list[$place['country_id']][1]] = $this->Html->link_url('', [
        'country' => sDB['countries'][$place['country_id']] + ['id' => $place['country_id']]
      ]);
    }

    // Megye, és nem BP
    if ($place['county_id'] > 1) {
      $list = sDB['counties'];
      if (isset($list[$place['county_id']][0])) {
        $items[$list[$place['county_id']][0]] = $this->Html->link_url('', [
          'county' => sDB['counties'][$place['county_id']] + ['id' => $place['county_id']]
        ]);
      }
    }

    return $items;
  }


  public function merge($user, $from, $to) {
    if ($user['admin'] == 1 || $user['headitor'] == 1
      || USERS['places'] != $user['id']) {

      $old_place = $this->DB->first('places', $from);
      $new_place = $this->DB->first('places', $to);

      if ($old_place && $new_place) {

        // Műlapok ezzel a település névvel
        $artpieces = $this->DB->find('artpieces', [
          'conditions' => ['place_id' => $old_place['id']],
          'fields' => ['id']
        ]);

        if (count($artpieces) > 0) {
          foreach ($artpieces as $artpiece) {
            $this->DB->update('artpieces', [
              'place_id' => $new_place['id'],
              'country_id' => $new_place['country_id'],
              'county_id' => $new_place['county_id'],
            ], $artpiece['id']);

            // Sajnos itt az artpieces->generate metódust kellene tolni, de nem lehet, mert
            // körkörösség van.
            $this->Cache->delete('cached-view-artpieces-view-' . $artpiece['id']);
            $this->Mongo->insert('jobs', [
              'class' => 'artpieces',
              'action' => 'generate',
              'options' => ['id' => $artpiece['id']],
              'created' => date('Y-m-d H:i:s'),
            ]);
          }
        }

        $edits = $this->Mongo->find_array('artpiece_edits', [
          'place_id' => (int)$old_place['id']
        ]);

        if (count($edits) > 0) {
          foreach ($edits as $edit) {
            $this->Mongo->update('artpiece_edits',
              ['place_id' => (int)$new_place['id']],
              ['_id' => $edit['id']]
            );
            // Sajnos itt az artpieces->generate metódust kellene tolni, de nem lehet, mert
            // körkörösség van.
            $this->Cache->delete('cached-view-artpieces-view-' . $edit['artpiece_id']);
            $this->Mongo->insert('jobs', [
              'class' => 'artpieces',
              'action' => 'generate',
              'options' => ['id' => $edit['artpiece_id']],
              'created' => date('Y-m-d H:i:s'),
            ]);
          }
        }

        $this->DB->delete('places', $old_place['id']);
        $this->Cache->delete('cached-view-places-view-' . $old_place['id']);
        $this->Cache->delete('cached-view-places-view-' . $new_place['id']);
        $this->Mongo->insert('jobs', [
          'class' => 'places',
          'action' => 'recalc',
          'options' => ['id' => $new_place['id']],
          'created' => date('Y-m-d H:i:s'),
        ]);

        return [
          'artpieces' => count($artpieces),
          'edits' => count($edits),
        ];
      }
    }

    return false;
  }

}

