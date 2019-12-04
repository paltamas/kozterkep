<?php
namespace Kozterkep;

class LogComponent {

  public function __construct() {
    $this->log_level = CORE['LOG_LEVEL'];
    $this->log_path = CORE['PATHS']['LOGS'];

    $this->Notifications = new NotificationsLogic(false, false);
    $this->Mongo = new MongoComponent();
  }



  /**
   *
   * Mongo.joblogs collectionbe ír
   * ez is tud emailezni, ha options['level'] = 1
   *
   * @param $class
   * @param $method
   * @param $options
   * @return mixed
   */
  public function job($class, $method, $run_success, $options) {
    $options = (array)$options + [
      'level' => 0,
      'data' => [],
      'run_time' => 0,
    ];

    // A lefutottakat csak 1 napig őrizzük
    $this->Mongo->delete('joblogs', [
      'created' => ['$lt' => date('Y-m-d H:i:s', strtotime('-1 days'))],
      'ran' => 1,
    ]);

    $saved = $this->Mongo->insert('joblogs', [
      'class' => $class,
      'method' => $method,
      'ran' => $run_success === false ? 0 : 1,
      'run_time' => $options['run_time'],
      'level' => $options['level'],
      'data' => $options['data'],
      'created' => date('Y-m-d H:i:s'),
    ]);

    if (@$options['level'] == 1) {
      $this->notify(http_build_query($options['data'], '', '<br />'), 'JobLog<br />' . $class . '::' . $method);
    }

    return $saved;
  }


  /**
   *
   * Log írás
   *
   * fájlos logika
   *
   *
   * @param string $message: loggolandó szöveg
   * @param string $type: log típusa, ami a fájlt határozza meg
   * @param int $level: 0 - sima, 1 - kritikus
   */
  public function write($message = '', $type = 'error', $level = 0) {

    /**
     * Log level 0, vagy 1, de ez nem kritikus
     * vagy nincs szövegünk.
     */
    if ($this->log_level == 0 || ($this->log_level == 1 && $level == 0) || $message == '') {
      return;
    }

    /**
     * Kapott üzenet szöveggé alakítása
     */
    $string = is_array($message) || is_object($message) || is_bool($message) ? json_encode($message) : $message;

    /**
     * Hibát jelző fájl
     */
    $script_file = str_replace(C_PATH, '', $_SERVER['SCRIPT_NAME']);

    /**
     * Log fájl és megnyitása
     */
    $logfile = $this->log_path . DS . $type . '.log';
    $handle = fopen($logfile, "a+");


    /**
     * Írunk
     */
    fwrite($handle, PHP_EOL . date('Y-m-d H:i:s') . ' | ' . $script_file . ' | ' . $string . '');

    /**
     * Zárunk
     */
    fclose($handle);

    /**
     * Kritikusnál értesítés megy
     */
    if ($level == 1) {
      $this->notify($message, $script_file);
    }
  }


  /**
   *
   * Értesítés és ha kell email készítés a log miatt
   *
   * @param $message
   * @param $script_file
   * @return array|bool|string
   */
  private function notify($message, $script_file) {

    $subject = 'KritLog: ' . $script_file;
    $body = $script_file . ' - ' . substr($message, 0, 300);

    $this->Notifications->create(CORE['USERS']['admins'], $subject, $body);

    if (CORE['LOG_EMAIL'] == 1) {
      $this->Mongo->insert('jobs', [
        'class' => 'emails',
        'action' => 'send',
        'options' => [
          'template' => 'system',
          'to' => CORE['ADMIN_EMAIL'],
          'name' => 'KT Admin',
          'subject' => $subject,
          'body' => $body,
        ],
        'created' => date('Y-m-d H:i:s'),
      ]);
    }

    return true;
  }
}
