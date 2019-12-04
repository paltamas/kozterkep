<?php
namespace Kozterkep;

class AuthComponent {

  private $app_config;

  public function __construct($app_config) {
    $this->Text = new TextHelper();
    $this->Session = new SessionComponent($app_config);
    $this->DB = new DatabaseComponent('kt');
    $this->Cookie = new CookieComponent($app_config);
    $this->Request = new RequestComponent($app_config);
    $this->Email = new EmailHelper($app_config);

    $this->Mongo = new MongoComponent();

    $this->app_config = $app_config;
    $this->routes = $app_config['routes'];

    if (CORE['USERS_BLOCKED']) {
      if ($user = $this->user()) {
        if ($user['admin'] == 0) {
          $this->logout();
        }
      }
      return false;
    }
  }


  /**
   *
   * Beléptető logika
   *
   * data lehet array vagy int
   * ARRAY:
   * kötelező: email, password; opcionális: remember_me
   * INT:
   * user.id jön és azt beléptetjük; felelősen használd!
   *
   *
   * @param bool $data
   * @return array|bool|int|mixed
   * @throws \Exception
   */
  public function login($data = false) {
    if (is_array($data) &&
      (@$data['email'] == '' || @$data['password'] == '')) {
      $this->Session->set_message('Az email cím és a jelszó mező kitöltése is kötelező.', 'danger');
      return false;
    }

    $conditions = [
      'active' => 1,
      'harakiri' => 0,
      'blocked' => 0
    ];

    if (is_numeric($data)) {
      $conditions['id'] = $data;
    } else {
      $conditions['email'] = $data['email'];
    }

    $user = $this->DB->first('users', $conditions);

    // Jelszó ellenőrzés
    if ($user && is_array($data) && !password_verify($data['password'], $user['password'])) {
      if ($user['repassword'] == 1) {
        $this->Session->set_message('<strong>Hozzáférésedet az új Köztérképen újra kell aktiválnod</strong>. Kérjük ellenőrizd email postafiókodat, ahova egy 1 napig érvényes jelszó beállító linket küldtünk!', 'info');
        $this->repassword($user['email']);
        return false;
      }
      $user = false;
    }

    if (!$user) {
      $this->Session->set_message('Érvénytelen email / jelszó páros.', 'danger');
      return false;
    }

    if (@$data['remember_me'] == 1) {
      $new_token = $this->token('write', $user);
      $this->Cookie->set($this->app_config['cookies']['remember_name'], $new_token);
    }

    $this->DB->update('users', [
      're_hash' => '',
      'last_here_before' => $user['last_here'],
      'last_here' => time(),
    ], $user['id']);

    unset($user['password']);
    $this->Session->set('user', $user);

    return $user;
  }


  /**
   *
   * Usernek új jelszó generálást rögzít
   *
   * @param $email
   * @return bool
   * @throws \Exception
   */
  public function repassword($email) {
    $conditions = [
      'email' => $email,
      'blocked' => 0,
      'harakiri' => 0
    ];

    $user = $this->DB->first('users', $conditions);

    if ($user) {

      $repassword_hash = bin2hex(random_bytes(16)) . '-' . bin2hex(random_bytes(16));
      $this->DB->update('users', [
        're_hash' => json_encode([time(), $repassword_hash])
      ], $user['id']);

      $subject = 'Jelszó beállítás';
      $template = 'emails/pwreset';

      $this->Mongo->insert('jobs', [
        'class' => 'emails',
        'action' => 'send',
        'options' => [
          'template' => 'system',
          'user_id' => $user['id'],
          'subject' => $subject,
          'body' => texts($template, [
            'name' => $user['name'],
            'link' => $this->app_config['url'] . '/tagsag/jelszo-beallitas/' . $repassword_hash,
            'contact_form' => $this->app_config['url'] . '/oldalak/kapcsolat'
          ])
        ],
        'created' => date('Y-m-d H:i:s'),
      ]);

      return true;
    }
    return false;
  }


  /**
   *
   * Slutty!
   *
   * @return bool
   * @throws \Exception
   */
  public function logout() {
    $this->Session->delete('user');
    $this->Session->delete('csrf_token');
    $this->Cookie->delete('KT-csrf');
    $this->Cookie->delete($this->app_config['cookies']['remember_name']);
    $this->token('delete', $this->user());
    return true;
  }


  /**
   *
   * Belépett user
   *
   * @return array|bool|int|mixed
   */
  public function user() {
    // Sesion próba
    $user = $this->Session->get('user');

    // Kuki próba
    if (!$user && $token = $this->Cookie->get($this->app_config['cookies']['remember_name'])) {
      $user = $this->DB->first('users', [
        'login_tokens LIKE' => '%"' . $token . '"%',
        'active' => 1,
        'harakiri' => 0,
        'blocked' => 0
      ]);
      if ($user) {
        $this->Session->set('user', $user);
      }
    }
    return $user;
  }


  /**
   *
   * Session újragenerálása DB-ből
   *
   * @return bool
   */
  public function resession() {
    // Sesion próba
    $user = $this->Session->get('user');
    if ($user) {
      $this->Session->delete('user');
      $user = $this->DB->find_by_id('users', $user['id']);
      if ($user) {
        $this->Session->set('user', $user);
      }
    }
    return $user;
  }


  /**
   *
   * Login token tömb manipuláció
   *
   * @param $method
   * @param $user
   * @return bool|string
   * @throws \Exception
   */
  private function token($method, $user) {
    // Eddigi token tömb
    $tokens_array = json_decode($user['login_tokens'], true);
    $tokens_array = !is_array($tokens_array) ? array() : $tokens_array;

    // Agent meghatározás
    $browser = sha1($this->Text->slug($this->Request->user_agent()));

    switch ($method) {

      case 'write':
        $new_token = bin2hex(random_bytes(32));
        $tokens_array[$browser] = $new_token;
        $new_token_json = json_encode($tokens_array);

        $this->DB->update('users',
          array('login_tokens' => $new_token_json),
          $user['id']
        );
        return $new_token;
        break;

      case 'check':
        if (array_key_exists($browser, $tokens_array) === true) {
          return $tokens_array[$browser];
        }
        break;


      case 'delete':
        if (!array_key_exists($browser, $tokens_array) === true) {
          return false;
        } else {
          unset($tokens_array[$browser]);
        }
        $new_token_json = json_encode($tokens_array);
        $this->DB->update('users',
          array('login_tokens' => $new_token_json),
          $user['id']
        );
        break;


      case 'destroy':
        $this->DB->update('users',
          array('login_tokens' => ''),
          $user['id']
        );
        return true;
        break;
    }

    return false;
  }

}
