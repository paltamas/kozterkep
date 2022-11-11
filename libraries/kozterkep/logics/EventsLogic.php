<?php
namespace Kozterkep;

class EventsLogic {

  private $DB;

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();
  }


  /**
   *
   * Esemény létrehozása
   *
   * @param int $type_id
   * @param array $fields
   * @return bool
   */
  public function create($type_id = 0, $fields = [], $options = []) {
    $options = (array)$options + ['cache_delete' => true];

    $type = sDB['event_types'][$type_id];

    // KöztérGép mondja-e?
    if ($type[3] == 1) {
      if (!isset($fields['target_user_id'])) {
        $fields['target_user_id'] = @$fields['user_id'] > 0 ? (int)$fields['user_id'] : 0;
      }
      $fields['user_id'] = CORE['ROBOT'];
    }

    // Kapcsolódó userek tömbjének megépítése
    if (isset($fields['related_users']) && count($fields['related_users']) > 0) {
      $related_users = $fields['related_users'];
    } else {
      $related_users = [];
    }

    if (@$fields['target_user_id'] > 0) {
      $related_users[] = (int)$fields['target_user_id'];
    }

    if (@$fields['user_id'] > 0 && $fields['user_id'] != CORE['ROBOT']) {
      $related_users[] = (int)$fields['user_id'];
    }

    $fields['related_users'] = array_unique($related_users);

    $data = (array)$fields + [
      'type_id' => (int)$type_id,
      'user_id' => CORE['ROBOT'],
      'target_user_id' => 0,
      'public' => (int)$type[2],
      'created' => time(),
    ];

    $result = $this->Mongo->insert('events', $data);

    if ($options['cache_delete']) {
      $this->Cache->delete('latest_events');
      $this->Cache->delete('cached-view-space-index');
      $this->Cache->delete('cached-view-space-headitorium');
    }

    return $result ? true : false;
  }


  /**
   *
   * Eseményfüggő szöveg visszaadása
   *
   * @param $event
   * @return string
   */
  public function text($event) {
    if (!is_array($event)) {
      $event = (array)$event;
    }

    $s = '';

    if (@$event['artpiece_id'] > 0) {
      $artpiece = $this->MC->t('artpieces', $event['artpiece_id']);
      $auser = $this->MC->t('users', $artpiece['user_id']);
    }

    if (@$event['artist_id'] > 0) {
      $artist = $this->MC->t('artists', $event['artist_id']);
    }

    if (@$event['target_user_id'] > 0) {
      $tuser = $this->MC->t('users', $event['target_user_id']);
    }

    switch ($event['type_id']) {

      // MŰLAPOSOK

      case 1:
        $s .= $auser['name'] . ' Köztérre küldte "' . $artpiece['title'] . '" c. műlapját';
        break;

      case 2:
        $s .= $auser['name'] . ' visszavette a Köztérről műlapját';
        break;

      case 3:
        $s .= '"' . $artpiece['title'] . '" c. műlapunk visszaküldésre került';
        break;

      case 4:
        $s .= $auser['name'] . ' publikálta "' . $artpiece['title'] . '" c. műlapját';
        break;

      case 5:
        $s .= 'A közösség publikálta ' . $auser['name'] . ' "' . $artpiece['title'] . '" c. műlapját';
        break;

      case 6:
        $start = @$event['photo_count'] > 0 ? $event['photo_count'] . ' új' : 'Új';
        $s .= $start . ' fotót töltöttem a' . _z($artpiece['title'], true) . ' műlaphoz';
        break;

      case 7:
        $s .= 'Megérintettem a' . _z($artpiece['title'], true) . ' alkotást';
        break;

      case 8:
        $s .= 'Feltörtem a' . _z($artpiece['title'], true) . ' alkotásnál elhelyezett térkapszulát';
        break;

      case 9:
        $s .= 'A főszerkesztők Példás műlapnak szavazták meg a' . _z($artpiece['title'], true) . ' feltöltésünket';
        break;

      case 10:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlap szerintem Szép munka';
        break;

      case 11:
        $to_user = isset($tuser['name']) ? $tuser['name'] . ' tagunknak' : '';
        $s .= 'A' . _z($artpiece['title'], true) . ' műlap gondozása átadásra került' . $to_user;
        break;

      case 29:
        $s .= '"' . $artpiece['title'] . '" c. műlapunk visszanyitásra került, újra beküldhető';
        break;



      // SZERKESZTÉSESEK

      case 12:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlapon jóváhagyásra került egy szerkesztés';
        break;

      case 13:
        $s .= 'A Közösség megszavazta a' . _z($artpiece['title']) . ' műlapon várakozó szerkesztést';
        break;

      case 14:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlapon elutasításra került egy szerkesztés';
        break;

      case 15:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlapon visszavonta szerkesztését tagunk';
        break;

      case 16:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlapon visszanyitottunk egy korábban elvetett szerkesztését';
        break;

      case 17:
        $s .= 'A' . _z($artpiece['title'], true) . ' műlapon visszaállítottunk egy korábban jóváhagyott szerkesztését';
        break;



      // ALKOTÓK

      case 28:
        $s .= 'Új fotót töltöttem ' . $artist['name'] . ' adatlapjára';
        break;



      // TAGI ESEMÉNYEK

      case 18:
        $s .= 'Új tagunk érkezett: ' . $tuser['name'] . '. Üdv a fedélzeten!';
        break;

      case 19:
        $s .= $tuser['name'] . ' tagunk saját publikálóvá vált, gratulálunk!';
        break;

      case 20:
        $s .= $tuser['name'] . ' tagunk kapta a "Heti kiemelt szerkesztő" címet, gratulálunk neki!';
        break;

      case 21:
        $s .= $tuser['name'] . ' tagunk munkájában mérföldkőhöz érkezett!';
        break;



      // POSZTOK

      case 22:
        $s .= 'Új blogbejegyzést publikáltam';
        break;

      case 23:
        $s .= 'Új gépház hírt publikáltunk';
        break;



      // EGYÉB

      case 24:
        $s .= 'Új könyvet töltöttem fel a könyvtárba';
        break;

      case 25:
        $s .= 'Mappámat megosztottam a közösséggel';
        break;

      case 26:
        $s .= 'Új naptáreseményt rögzítettem';
        break;

      case 27:
        $s .= 'KöztérGép mondja:';
        break;


    }

    if (@$event['text'] != '') {
      $s .= '<br />' . $event['text'];
    }

    return $s;
  }


  /**
   *
   * Törli a fotó eseményt.
   * De egy eseményhez több fotó is kapcsolódhat, így ekkor csak
   * az adott fotót törli belőle, és egyébként anélkül visszamenti.
   * Ha csak egy fotó van hozzá kapcsolva, akkor törli az eseményt is.
   *
   * @param $photo_id
   * @return bool
   */
  public function delete_with_photo($del_photo_id) {
    $event = $this->Mongo->first('events', [
      'photos' => ['$elemMatch' => ['id' => (int)$del_photo_id]]
    ]);

    if ($event) {
      $photos = _json_decode($event['photos']);

      if (count($photos) == 1) {
        $this->Mongo->delete('events', ['_id' => $event['id']]);
      } else {
        foreach ($photos as $key => $photo_item) {
          if ($photo_item['id'] == $del_photo_id) {
            unset($photos[$key]);
          }
        }
        $this->Mongo->update('events', [
          'photo_count' => count($photos),
          'photos' => $photos,
        ], ['_id' => $event['id']]);
      }
    }
    // Mindenképp
    return true;
  }

}

