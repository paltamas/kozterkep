<?php
namespace Kozterkep;

class ArtistsLogic {

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();

    $this->Arrays = new ArraysHelper();
    $this->Html = new HtmlHelper($app_config);
  }


  public function name($artist, $options = []) {
    $options = (array)$options + [
      'link' => true,
      'year' => false,
      'tooltip' => false,
      'profession' => false,
      'english_comma' => false,
      'class' => '',
    ];

    if (!isset($artist['id'])) {
      $artist = $this->MC->t('artists', $artist);
      if (!$artist) {
        // Nagyon új...
        $artist = $this->DB->first('artists', $artist);
      }
    }

    $s = '';

    if ($artist) {
      // Előnév, ha nem angol forma van
      if ($artist['english_form'] == 0 && $artist['before_name'] > 0) {
        $s .= sDB['before_names'][$artist['before_name']] . ' ';
      }

      if ($artist['first_name'] == '' && $artist['last_name'] == '') {
        $s .= $artist['name'];
      } else {
        if ($artist['english_form'] == 1) {
          if ($options['english_comma']) {
            $s .= $artist['last_name'] . ', ' . $artist['first_name'];
          } else {
            $s .= $artist['first_name'] . ' ' . $artist['last_name'];
          }
          if ($artist['before_name'] > 0) {
            $s .= ', ' . sDB['before_names'][$artist['before_name']];
          }
        } else {
          $s .= $artist['last_name'] . ' ' . $artist['first_name'];
        }
      }

      if ($options['year'] && $artist['born_year'] != '') {
        $s .= $artist['born_year'];
      }

      if ($options['link']) {
        $link_options = [
          'artist' => ['id' => $artist['id'], 'name' => $s],
          'class' => $options['class']
        ];

        if ($options['tooltip']) {
          $link_options = $link_options + [
            'ia-tooltip' => 'alkoto',
            'ia-tooltip-id' => $artist['id'],
          ];
        }

        $s = $this->Html->link($s, '', $link_options);

        if ($options['profession'] && @$artist['profession_id'] > 0) {
          $s .= ' <span class="text-muted">(' . sDB['artist_professions'][$artist['profession_id']][0] . ')</span>';
        }
      } else {
        $s = '<span class="' . $options['class'] . '">' . $s . '</span>';
      }
    }

    return $s;
  }


  /**
   *
   * Szerep lista
   *
   * @param array $options
   * @return array
   */
  public function professions($options = []) {
    $options = (array)$options + [
      'only_roles' => false,
      'only_professions' => false,
      'sort' => true,
    ];

    $professions = sDB['artist_professions'];

    // Csak alkotásben betöltött szerepek
    if ($options['only_roles']) {
      $array = [];
      foreach ($professions as $key => $profession) {
        if ($profession[1] == 1) {
          $array[$key] = $profession[0];
        }
      }
      $professions = $array;
    } elseif ($options['only_professions']) {
      // Csak mesterség
      $array = [];
      foreach ($professions as $key => $profession) {
        if ($profession[2] == 1) {
          $array[$key] = $profession[0];
        }
      }
      $professions = $array;
    }

    if ($options['sort']) {
      asort($professions);
    }

    return $professions;
  }



  public function merge($user, $from, $to) {
    if ($user['admin'] == 1 || $user['headitor'] == 1
      || USERS['places'] != $user['id']) {

      $old_artist = $this->DB->first('artists', $from);
      $new_artist = $this->DB->first('artists', $to);

      if ($old_artist && $new_artist) {

        // Alkotóhoz tett, szignós, vagy portrés képek
        $this->DB->update('photos', [
          'artist_id' => $to,
        ], ['artist_id' => $from]);
        $this->DB->update('photos', [
          'sign_artist_id' => $to,
        ], ['sign_artist_id' => $from]);
        $this->DB->update('photos', [
          'portrait_artist_id' => $to,
        ], ['portrait_artist_id' => $from]);

        // Műlapok ezzel az alkotóval
        $artpieces = $this->DB->find('artpieces', [
          'conditions' => [
            'artists LIKE' => '%"id":' . $old_artist['id']. ',"%',
          ],
          'fields' => ['id', 'artists']
        ]);

        if (count($artpieces) > 0) {

          // Érintettek, ahol az editeket is végig kell nyálazni
          $related_artpieces = [];

          foreach ($artpieces as $artpiece) {

            $related_artpieces[] = $artpiece['id'];

            // Cserélünk
            $artists_array = _json_decode($artpiece['artists']);

            $artists = [];
            foreach ($artists_array as $artist) {

              $artist_id = $artist['id'];
              if ($artist['id'] == $old_artist['id']) {
                $artist_id = $new_artist['id'];
              }

              $artists[] = [
                'id' => (int)$artist_id,
                'rank' => (int)$artist['rank'],
                //'question' => (int)$artist['question'],
                'contributor' => (int)$artist['contributor'],
                'profession_id' => (int)$artist['profession_id'],
              ];
            }
            $artists = $this->Arrays->sort_by_key($artists, 'rank');

            $artists_json = json_encode($artists);

            $this->DB->update('artpieces', [
              'artists' => $artists_json,
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


          $edits = $this->Mongo->find_array('artpiece_edits', [
            'artpiece_id' => ['$in' => $related_artpieces]
          ]);

          if (count($edits) > 0) {
            foreach ($edits as $edit) {
              $this->replace_edit_artist($edit, $old_artist['id'], $new_artist['id']);
            }
          }
        }

        // Várakozókban is lehet
        $waiting_edits = $this->Mongo->find_array('artpiece_edits', [
          'status_id' => 2
        ]);

        $change_count = 0;

        if (count($waiting_edits) > 0) {
          foreach ($waiting_edits as $edit) {
            $changed = $this->replace_edit_artist($edit, $old_artist['id'], $new_artist['id']);
            if ($changed) {
              $change_count++;
            }
          }
        }

        $this->DB->delete('artists', $old_artist['id']);
        $this->Cache->delete('cached-view-artists-view-' . $old_artist['id']);
        $this->Cache->delete('cached-view-artists-view-' . $new_artist['id']);
        $this->Mongo->insert('jobs', [
          'class' => 'artists',
          'action' => 'recalc',
          'options' => ['id' => $new_artist['id']],
          'created' => date('Y-m-d H:i:s'),
        ]);

        return [
          'artpieces' => count($artpieces),
          'waiting_edits' => $change_count,
        ];
      }
    }

    return false;
  }


  /**
   *
   * Kapott edit tömbben kicseréli az artist_id-t
   *
   * @param $edit - a szerkesztés
   * @param $old_id - erről cserélünk
   * @param $new_id - erre cserélünk
   * @param bool $update - update-eljük is-e, vagy csak adjuk vissza az edit tömböt
   * @return \MongoDB\Driver\WriteResult
   */
  public function replace_edit_artist($edit, $old_id, $new_id, $update = true) {
    $changed = false;
    if (isset($edit['artists'])) {
      $artists = [];
      foreach ($edit['artists'] as $artist) {

        $artist_id = $artist['id'];
        if ($artist['id'] == $old_id) {
          $artist_id = $new_id;
          $changed = true;
        }

        $artists[] = [
          'id' => (int)$artist_id,
          'rank' => (int)$artist['rank'],
          //'question' => (int)$artist['question'],
          'contributor' => (int)$artist['contributor'],
          'profession_id' => (int)$artist['profession_id'],
        ];
      }

      $edit['artists'] = $artists;

      if ($changed && $update) {
        return $this->Mongo->update('artpiece_edits', ['artists' => $edit['artists']], ['_id' => $edit['id']]);
      } elseif ($changed) {
        return $edit;
      }
    }

    return $changed;
  }


  /**
   *
   * Alkotó keresésben használt logika, a kulcssó => név keresésre
   *
   * @param $query
   * @param $conditions
   * @return array
   */
  public function build_keyword_condition($query, $conditions) {
    // Kulcsszó javítások
    // pont után szóköz
    $query['kulcsszo'] = preg_replace('/(?<!\.)\.(?!(\s|$|\,|\w\.))/', '. ', $query['kulcsszo']);

    $keyword_ = $query['kulcsszo'];

    // Elütések, bővíthető majd még
    if (strpos($keyword_, 'i') !== false) {
      $keyword_ = str_replace('i', 'y', $keyword_);
    } elseif (strpos($keyword_, 'y') !== false) {
      $keyword_ = str_replace('y', 'i', $keyword_);
    } elseif (strpos($keyword_, 'cs') !== false) {
      $keyword_ = str_replace('cs', 'ch', $keyword_);
    }

    if (is_numeric($query['kulcsszo'])) {
      $conditions += ['id' => $query['kulcsszo']];
    } elseif (@$query['eleje'] == 1) {
      // Kezdeti egyezés
      $conditions += ['OR' => [
        'name LIKE' => $query['kulcsszo'] . '%',
        'artist_name LIKE' => $query['kulcsszo'] . '%',
        'alternative_names LIKE' => $query['kulcsszo'] . '%',
        // mivel a name LIKE a tömb kulcsa, ezért szóközt kell tenni, hogy ne írja felül az előzőt...
        'name LIKE ' => $keyword_ . '%',
        'artist_name LIKE ' => $keyword_ . '%',
        'alternative_names LIKE ' => $keyword_ . '%',
      ]];
    } elseif (strpos(trim($query['kulcsszo']), ' ') !== false) {
      if (@$query['monogram'] == 1) {
        // Szóközös, szignót akarunk
        $p = explode(' ', trim($query['kulcsszo']));
        $conditions += [
          'last_name LIKE' => $p[0] . '%',
          'first_name LIKE' => $p[1] . '%'
        ];
      } else {
        // Sima szóközös névalak
        $conditions += [
          'name LIKE' => '%' . trim($query['kulcsszo']) . '%'
        ];
      }
    } else {
      // tetszőleges egyezés
      $conditions += ['OR' => [
        'name LIKE' => '%' . $query['kulcsszo'] . '%',
        'name LIKE ' => $query['kulcsszo'] . '%',
        'artist_name LIKE' => '%' . $query['kulcsszo'] . '%',
        'alternative_names LIKE' => '%' . $query['kulcsszo'] . '%',
        'name LIKE  ' => '%' . $keyword_ . '%',
        'artist_name LIKE ' => '%' . $keyword_ . '%',
        'alternative_names LIKE ' => '%' . $keyword_ . '%',
      ]];
    }

    //debug($conditions);

    return $conditions;
  }
}