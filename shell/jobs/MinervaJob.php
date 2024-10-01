<?php

class MinervaJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }





  /**
   *
   * Heti szüret
   *
   * @return bool
   */
  public function weekly_harvest() {

    $from_time = strtotime('last monday 00:00', strtotime('Sunday'));
    //$from_time = strtotime('2024-09-16 00:00');

    $users = $this->DB->find('users', [
      'conditions' => CORE['ENV'] == 'dev'
        ? 'id = 1 '
        : 'harakiri = 0 AND blocked = 0 AND JSON_EXTRACT(newsletter_settings, "$.weekly_harvest") = 1',
      'fields' => ['id', 'name', 'link', 'email']
    ]);

    if (count($users) > 0) {

      $subject = 'Heti szüret';

      $artpiece_count = $this->DB->count('artpieces', [
        'status_id' => 5,
        'published >' => $from_time,
      ]);
      $photo_count = $this->DB->count('photos', [
        'approved >' => $from_time,
      ]);
      $new_user_count = $this->DB->count('users', [
        'created >' => $from_time,
      ]);
      $comment_count = $this->Mongo->count('comments', [
        'created' => ['$gt' => $from_time],
      ]);
      $artpiece_edit_count = $this->Mongo->count('artpiece_edits', [
        'own_edit' => ['$ne' => 1],
        'before_shared' => ['$ne' => 1],
        'approved' => ['$gt' => $from_time],
      ]);
      $description_count = $this->Mongo->count('artpiece_descriptions', [
        'before_shared' => ['$ne' => 1],
        'approved' => ['$gt' => $from_time],
      ]);
      $hug_count = $this->Mongo->count('artpiece_hugs', [
        'created' => ['$gt' => $from_time],
      ]);
      /*$aggregated = $this->Mongo->aggregate('webstat', [
        ['$match' => ['tt' => ['$gt' => $from_time]]],
        ['$count' => 'count'],
      ]);
      $pageviews = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;*/
      $aggregated = $this->Mongo->aggregate('webstat', [
        ['$match' => ['tt' => ['$gt' => $from_time]]],
        ['$group' => ['_id' => '$s']],
        ['$count' => 'count'],
      ]);
      $sessions = @$aggregated[0]->count > 0 ? $aggregated[0]->count : 1;

      $content = '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
      $content .= '<p style="margin-top: 30px; text-align: center;"><strong>EGY KIS STATISZTIKA</strong></p><p>Ezen a héten <strong>' . _n($artpiece_count) . ' műlapot</strong> publikáltunk és <strong>' . _n($photo_count) . ' fotót</strong> töltöttünk fel. Összesen <strong>' . _n($comment_count) . ' hozzászólás</strong> érkezett mindenhova, valamint <strong>' . _n($artpiece_edit_count + $description_count) . ' szerkesztés és új sztori</strong> született.</p>';
      $content .= '<p>Játékosaink <strong>' . _n($hug_count) . ' alkalommal érintettek</strong> meg köztéri alkotásokat. ';
      if ($new_user_count > 0) {
        $content .= 'Az elmúlt hét napban <strong>' . $new_user_count . ' új regisztrált</strong> taggal gyarapodtunk. ';
      }
      $content .= 'A Köztérképet ez idő alatt látogatóink átlagosan napi <strong>' . _n(floor($sessions/7)) . ' alkalommal</strong> nyitották meg.</p>';


      // Heti szüret bevezető
      // ...

      // Kiemelések
      $highlighteds = $this->DB->find('highlighteds', [
        'conditions' => [
          'time >' => $from_time,
        ],
        'order' => 'time ASC',
      ]);

      if (count($highlighteds) > 0) {
        $content .= '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
        $content .= '<p style="margin-top: 30px; text-align: center;"><strong>KIEMELÉSEK</strong> <span style="color: #868e96">&bull; aktuális vagy érdekes vagy</span></p>';
        $content .= $this->highlighted_table($highlighteds);

        //$content .= '<p style="font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;"><a href="' . CORE['BASE_URL'] . '/mulapok/kiemelesek" style="color: #c95b12; text-decoration: none;">Kiemelések listája &rarr;</a></p>';
      }


      // Heti szüretelt műlapok
      $artpieces = $this->DB->find('artpieces', [
        /*'conditions' => [
          'published >' => $from_time,
          'status_id' => 5,
          'harvested' => 1,
        ],*/
        'conditions' => 'published > ' . $from_time . ' AND status_id = 5 AND (harvested = 1 OR underlined = 1)',
        'order' => 'published ASC',
      ]);

      if (count($artpieces) > 0) {
        $content .= '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
        $content .= '<p style="margin-top: 30px; text-align: center;"><strong>HETI SZÜRET</strong> <span style="color: #868e96">&bull; szubjektív válogatás friss műlapjainkból</span></p>';
        $content .= $this->artpiece_table($artpieces, [
          'cols' => 2,
        ]);

        $content .= '<p style="font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;"><a href="' . CORE['BASE_URL'] . '/mulapok" style="color: #c95b12; text-decoration: none;">Műlapok listája &rarr;</a></p>';
      }


      // Események a napokban
      // ...


      // Heti kiemelt tagunk
      // ...


      // Új tagi és admin blogbejegyzések
      $posts = $this->DB->find('posts', [
        'conditions' => [
          'published >' => $from_time,
          'status_id' => 5,
          'highlighted' => 1,
        ],
        'order' => 'published ASC',
      ]);

      if (count($posts) > 0) {
        $content .= '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
        $content .= '<p style="margin-top: 30px; text-align: center;"><strong>FRISS POSZTOK</strong> <span style="color: #868e96">&bull; gépház hírek és kiemelt bejegyzések tagjainktól</span></p>';
        foreach ($posts as $post) {
          $u = $this->MC->t('users', $post['user_id']);
          $content .= '<p style="margin-top: 5px; margin-bottom: 5px;"><strong><a href="' . CORE['BASE_URL'] . '/blogok/megtekintes/' . $post['id'] . '" style="color: #c95b12; text-decoration: none;">' . $post['title'] . '</a></strong> (' . $u['name'] . ' @ ' . _time($post['published']) . ')</p>';
        }

        $content .= '<p style="font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;"><a href="' . CORE['BASE_URL'] . '/blogok/friss" style="color: #c95b12; text-decoration: none;">Minden blogbejegyzés &rarr;</a></p>';
      }


      // Tagi mérföldkövek
      // ...


      // Legnézettebb heti műlapok
      $artpieces = $this->DB->find('artpieces', [
        'conditions' => [
          'status_id' => 5,
        ],
        'order' => 'view_week DESC',
        'limit' => 20,
      ]);

      if (count($artpieces) > 0) {
        $content .= '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
        $content .= '<p style="margin-top: 30px; text-align: center;"><strong>HETI NÉPSZERŰ</strong> <span style="color: #868e96">&bull; legtöbb megtekintést kapott műlapok a héten</span></p>';
        $content .= $this->Email->artpiece_toplist($artpieces);
        $content .= '<p style="font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;"><a href="' . CORE['BASE_URL'] . '/mulapok/statisztikak" style="color: #c95b12; text-decoration: none;">Műlap statisztikák &rarr;</a></p>';
      }


      // Ennek mentése az archívumba
      $this->DB->insert('newsletters', [
        'weekly_harvest' => 1,
        'subject' => $subject,
        'body' => $content,
        'sent' => time(),
        'receiver_count' => count($users),
      ]);

      foreach ($users as $user) {

        if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
          continue;
        }

        /*
         * SAJÁT MÉG
         *
         * Követett dolgok:
         *  - tagok új lapjai
         *  - alkotók, helyek
         *  - követett műlapok, mappák bővülései
         *
         * Nyitott szerk. szám (?)
         */
        $user_content = '';

        // Legnézettebb heti saját műlapok
        $artpieces = $this->DB->find('artpieces', [
          'conditions' => [
            'status_id' => 5,
            'user_id' => $user['id']
          ],
          'order' => 'view_week DESC',
          'limit' => 10,
        ]);

        if (count($artpieces) > 0) {
          $user_content .= '<hr style="margin-top: 15px; margin-bottom: 15px;" />';
          $user_content .= '<p style="margin-top: 30px; text-align: center;"><strong>HETI NÉPSZERŰ SAJÁT MŰLAPOK</strong> <span style="color: #868e96">&bull; legtöbb megtekintést kapott műlapjaid a héten</span></p>';
          $user_content .= $this->Email->artpiece_toplist($artpieces);
          $user_content .= '<p style="font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;"><a href="' . CORE['BASE_URL'] . '/kozosseg/tag_statisztikak/' . $user['link'] . '" style="color: #c95b12; text-decoration: none;">Saját részletes statisztikák &rarr;</a></p>';
        }

        $final_content = $content . $user_content;

        $this->Mongo->insert('jobs', [
          'class' => 'emails',
          'action' => 'send',
          'options' => [
            'user_id' => $user['id'],
            'subject' => $subject,
            'from_name' => 'Köztérkép Minerva',
            'from_email' => 'minerva@kozterkep.hu',
            'body' => texts('emails/minerva_weekly_harvest', [
              'name' => $user['name'],
              'content' => $final_content,
            ])
          ],
          'created' => date('Y-m-d H:i:s'),
        ]);
      }
    }

    return true;
  }




  /**
   *
   * Napi futár
   *
   * @return bool
   */
  public function daily() {

    $users = $this->DB->find('users', [
      'conditions' => 'JSON_EXTRACT(newsletter_settings, "$.daily") = 1',
      'fields' => ['id', 'name', 'email']
    ]);

    if (count($users) > 0) {

      $artpieces = $this->DB->find('artpieces', [
        'conditions' => [
          'published >' => strtotime('-24 hours'),
          'status_id' => 5,
        ],
        'order' => 'published ASC',
      ]);

      if (count($artpieces) > 0) {

        $subject = count($artpieces) . ' új műlap';

        $list = $this->artpiece_table($artpieces, [
          'cols' => 2,
          'artpiece' => [
            'details' => true,
          ]
        ]);

        foreach ($users as $user) {

          if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            continue;
          }

          $this->Mongo->insert('jobs', [
            'class' => 'emails',
            'action' => 'send',
            'options' => [
              'user_id' => $user['id'],
              'subject' => $subject,
              'from_name' => 'Köztérkép Minerva',
              'from_email' => 'minerva@kozterkep.hu',
              'body' => texts('emails/minerva_daily', [
                'name' => $user['name'],
                'list' => $list,
              ])
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
        }


      }
    }

    return true;
  }



  /**
   *
   * Kiküldésre összerak egy newsletters táblába szúrt custom
   * levelet minden címzettnek.
   *
   * @return bool
   */
  public function custom_send () {
    $newsletter = $this->DB->first('newsletters', [
      'custom' => 1,
      'weekly_harvest' => 0,
      'sendable' => 1,
      'sent' => 0,
    ], [
      'order' => 'id ASC'
    ]);

    if ($newsletter) {
      $users = $this->DB->find('users', [
        'conditions' => '(' . $newsletter['user_conditions'] . ') AND blocked = 0 AND harakiri = 0',
        'fields' => 'id, name',
      ]);

      if (count($users) > 0) {
        $receivers = 0;
        foreach ($users as $user) {
          $this->Mongo->insert('jobs', [
            'class' => 'emails',
            'action' => 'send',
            'options' => [
              'user_id' => $user['id'],
              'subject' => $newsletter['subject'],
              'from_name' => 'Köztérkép',
              'from_email' => 'hello@kozterkep.hu',
              'template' => $newsletter['template'],
              'body' => texts($newsletter['body'], [
                'name' => $user['name'],
              ], true)
            ],
            'created' => date('Y-m-d H:i:s'),
          ]);
          $receivers++;
        }
      }
      $this->DB->update('newsletters', [
        'sent' => time(),
        'receiver_count' => $receivers,
      ], $newsletter['id']);
    }

    return true;
  }


  /**
   *
   * Műlap tábla
   *
   * @param $artpieces
   * @param array $options
   * @return string
   */
  private function artpiece_table($artpieces, $options = []) {
    $options = (array)@$options + [
      'cols' => 2,
      'artpiece' => [
        'details' => false,
      ]
    ];
    $list = '';
    if (count($artpieces) > 0) {
      $list .= '<table border="0" cellpadding="5" cellspacing="0"><tr>';
      $i = 0;
      foreach ($artpieces as $artpiece) {

        // Egy műlap opciói
        $artpiece_options = $options['artpiece'];
        if ($artpiece_options['details']) {
          $artpiece_options += [
            'place' => $this->Places->name($artpiece['place_id'], ['link' => false]),
            'year' => $this->Artpieces->get_artpiece_year($artpiece['dates']),
          ];
        }

        $i++;
        if ($i % $options['cols'] == 1 && $i > 1) {
          $list .= '</tr><tr>';
        }
        $list .= '<td width="' . floor(100/$options['cols']) . '%" valign="top">';
        $list .= $this->Email->artpiece_html($artpiece, $artpiece_options);
        $list .= '</td>';
      }
      $list .= '</tr></table>';
    }

    return $list;
  }



  private function highlighted_table($highlighteds, $options = []) {
    $options = (array)@$options + [
    ];

    $list = '';

    if (count($highlighteds) > 0) {
      foreach ($highlighteds as $key => $highlighted) {
        // Ha group és a cím + szöveg is azonos, akkor ugrunk a következőre,
        // és csak egyet mutatunk gíy a groupból
        if (isset($highlighteds[$key+1])) {
          $next = $highlighteds[$key + 1];
          if ($highlighted['group'] == 1 && $next['group'] == 1
            && $highlighted['text'] == $next['text']) {
            continue;
          }
        }

        $artpiece = $this->MC->t('artpieces', $highlighted['artpiece_id']);
        $list .= '<div style="background-color: #efede5; padding: 0; border-radius: 20px; margin-bottom: 20px;">';

        $list .= '<table border="0" cellpadding="10" cellspacing="0" width="100%"><tr><td width="25%">';

        // Fotó
        $list .= '<a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="text-decoration: none;">';
        $list .= '<img src="' . C_WS_S3['url'] . 'photos/' . $highlighted['photo_slug'] . '_4.jpg" alt="' . $artpiece['title'] . ' műlap borítóképe" border="0" style="border-radius: 8px; border: 6px solid #ffffff; width: 100%">';
        $list .= '</a>';

        $list .= '</td><td valign="top" style="font-style: italic;">';

        $list .= '<div style="padding-top: 3px;">';
        // Műlap cím
        $list .= '<div style="font-weight: bold; margin-bottom: 5px;"><a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="color: #c95b12; text-decoration: none;">';
        $list .= $artpiece['title'] . ' &rarr;';
        $list .= '</a></div>';
        // Szöveg
        $list .= '<strong>' . $highlighted['title'] . '</strong> &bull; ' . $highlighted['text'];
        $list .= '</div>';

        $list .= '</td></tr></table>';

        $list .= '</div>';
      }
    }

    return $list;
  }

}