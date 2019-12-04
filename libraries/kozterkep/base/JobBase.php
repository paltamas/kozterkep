<?php
namespace Kozterkep;

class JobBase {

  public static $_options;
  public static $_argv;

  public function __construct() {
    // Komponensek
    $this->Log = new LogComponent();
    $this->DB = new DatabaseComponent('kt');
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Cache = new CacheComponent();
    $this->Shell = new ShellComponent();
    $this->Cache = new CacheComponent();

    // Logikák
    $this->Migrations = new MigrationsLogic;
    $this->Artpieces = new ArtpiecesLogic(false, $this->DB);
    $this->Places = new PlacesLogic(false, $this->DB);
    $this->Notifications = new NotificationsLogic(false, $this->DB);
    $this->Events = new EventsLogic(false, $this->DB);
    $this->Sets = new SetsLogic($this->DB, $this->Mongo);

    // Helperek
    $this->Arrays = new ArraysHelper;
    $this->Email = new EmailHelper();
    $this->Curl = new CurlHelper;
    $this->File = new FileHelper($this->DB, $this->Mongo);
    $this->Text = new TextHelper;
    $this->Time = new TimeHelper;
    $this->Translation = new TranslationHelper;
    $this->Location = new LocationHelper;

    self::$_options = [];
    self::$_argv = [];
  }




  /**
   *
   * JOB futtatás
   *
   * argumentek:
   *  class -> class neve kisbetűvel, Job nélkül, pl: emails
   *  action -> action neve, pl.: send
   *  id -> mongoID, ahol az option van
   *
   * MongoID is elég lenne, de az pont nem kötelező.
   *
   * @param $arguments
   * @return bool
   */
  public function run($arguments) {

    /**
     * Szuperveszélyes, de sajnos vannak jobok, amik tényleg percekig futnak (UsersJob::scores)
     * Figyelni kell ezt, meg gondolkodni rajta.
     * Aha, oké, jó :]
     */
    ini_set('max_execution_time', 30*60);

    $start_time = round(microtime(true) * 1000);

    if ($arguments['class'] != '' && $arguments['action'] != '') {

      $class_name = ucfirst($arguments['class']) . 'Job';

      $controller = new $class_name;
      $action = $arguments['action'];

      if (method_exists($class_name, $action)) {

        if (@$arguments['id'] != '') {
          // Ha így jön, akkor konzolozás van:
          // -f=filename
          // Egyébként mongo ID lesz
          if (strpos($arguments['id'], '-') === 0) {
            // Kinyerjük a változónevet
            $p = explode('=', $arguments['id']);
            $variable_name = ltrim($p[0], '-');
            // Átadjuk
            self::$_options[$variable_name] = str_replace($p[0] . '=', '', $arguments['id']);
          } else {
            // Mongo opció tömb kinyerése
            $job = $this->Mongo->first('jobs', $arguments['id']);
            if (isset($job['options'])) {
              // Ez publikus, így minden job látja
              self::$_options = (array)$job['options'];
            }
          }
        }

        self::$_argv = $arguments;

        /**
         * Feladat futtatása
         * kritikus, hogy minden action bool válaszban elmondja, hogy
         * sikerült-e lefutnia!
         */
        $success = $controller->$action();

        // Megírjuk a joblogot
        $this->Log->job(
          $class_name,
          $action,
          $success === false ? false : true,
          [
            'level' => $success === false ? 1 : 0,
            'run_time' => round(microtime(true) * 1000) - $start_time,
            'data' => @count(@$job['options']) > 0 ? $job['options'] : []
          ]
        );

        return $success;

      } else {
        $this->Log->write($arguments['action'] . ' => ismeretlen metódus');
      }
    }

    return false;
  }
}