<?php
namespace Kozterkep;

class AppBase {

  public function __construct($app_config) {
    // Itt most mindent meghívunk. Egyesével.

    // Mielőtt bármi további lenne, mert ebben átdobás lesz
    $this->Oldthings = new OldthingsLogic($app_config);
    $this->Oldthings->redirects();

    // Komponensek
    $this->Auth = new AuthComponent($app_config);
    $this->Request = new RequestComponent($app_config);
    $this->Render = new RenderComponent($app_config, $this->Request);
    $this->Log = new LogComponent();
    $this->Session = new SessionComponent($app_config);
    $this->DB = new DatabaseComponent('kt');
    $this->Cache = new CacheComponent();
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Validation = new ValidationComponent($app_config, $this->Request, $this->DB, $this->Mongo);
    $this->Cookie = new CookieComponent($app_config);
    $this->Shell = new ShellComponent();

    // Logikák
    $this->Users = new UsersLogic($app_config, $this->DB);
    $this->Artpieces = new ArtpiecesLogic($app_config, $this->DB);
    $this->Photos = new PhotosLogic($app_config, $this->DB);
    $this->Places = new PlacesLogic($app_config, $this->DB);
    $this->Artists = new ArtistsLogic($app_config, $this->DB);
    $this->Comments = new CommentsLogic($app_config, $this->DB);
    $this->Events = new EventsLogic($app_config, $this->DB);
    $this->Conversations = new ConversationsLogic($app_config, $this->DB);
    $this->Notifications = new NotificationsLogic($app_config, $this->DB);
    $this->Sets = new SetsLogic($app_config, $this->DB);

    // Helperek
    $this->Arrays = new ArraysHelper;
    $this->Curl = new CurlHelper;
    $this->Search = new SearchHelper($this->DB, $this->Mongo);
    $this->File = new FileHelper($this->DB, $this->Mongo);
    $this->Form = new FormHelper($app_config, $this->Request);
    $this->Html = new HtmlHelper($app_config);
    $this->Email = new EmailHelper($app_config);
    $this->Image = new ImageHelper;
    $this->Text = new TextHelper;
    $this->Blog = new BlogHelper($app_config, $this->DB);
    $this->Time = new TimeHelper;
    $this->Translation = new TranslationHelper;
    $this->Location = new LocationHelper;

    // Core változók
    $this->views_dir = C_PATH . DS . 'web' . DS . $app_config['path'] . DS . 'views' . DS;
    $this->error_4xx = $this->views_dir . $app_config['error_4xx'];
    $this->error_5xx = $this->views_dir . $app_config['error_5xx'];

    if (!$this->MC->t('users', 1)) {
      mydie('Cache kiolvasási hiba. A hiba meggátolta a weblap megfelelő működését.');
    }
  }


  /**
   *
   * Flash message író / olvasó
   * Lustahasználatra.
   *
   * @param bool $message
   * @param string $type
   * @return string|void
   */
  public function flash($message = false, $type = 'info', $format = '', $remove_after = 0) {
    if ($message) {
      return $this->Session->set_message($message, $type, $format, $remove_after);
    } else {
      return $this->Session->get_messages();
    }
  }


  /**
   *
   * Mi a request?
   * Lustahasználatra.
   *
   * @param bool $what
   * @return bool
   */
  public function is($what = false) {
    return $this->Request->is($what);
  }


  /**
   *
   * Log írás
   * bővebben a meghívottban
   * Lustahasználatra.
   *
   * @param $message
   * @param string $type
   * @param int $level
   */
  public function log($message, $type = 'error', $level = 0) {
    return $this->Log->write($message, $type, $level);
  }


  /**
   *
   * Redirect, ami üzenetet is tud
   *
   * @param null $url
   * @param bool $message
   * @param int $status (301 = permanent, 302 = temporary)
   */
  public function redirect($url = null, $message = false, $status = 302) {
    if ($url == 'back') {
      $url = $this->Request->referer();
    }
    if ($url == 'referer') {
      $url = $this->Request->here();
    }
    $url = $url == null ? '/' : $url;

    if ($this->Session->get('_redirect_hash')) {
      $url .= '#' . $this->Session->get('_redirect_hash');
      $this->Session->delete('_redirect_hash');
    }

    if ($message) {
      $message_text = is_array($message) ? $message[0] : $message;
      $message_type = is_array($message) ? $message[1] : 'info';
      $this->Session->set_message($message_text, $message_type);
    }
    header("Location: " . $url, false, $status);
    exit;
  }


  /**
   *
   * CSRF tokent ad vissza
   * cookie-ba is beírja, ha kérjük
   *
   * @param bool $write_cookie
   * @return bool
   * @throws \Exception
   */
  public function csrf_token($write_cookie = false) {
    $token = $this->Session->get('csrf_token', bin2hex(random_bytes(24)));
    if ($write_cookie) {
      $this->Cookie->set('KT-csrf', $token);
    }
    return $token;
  }


  /**
   * Cache header-t adunk; ajaxb válaszoknál hasznos
   * @param int $seconds
   */
  public function cache_header ($seconds = 3600) {
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT";
    header("Expires: $ts");
    header("Pragma: cache");
    header("Cache-Control: max-age=$seconds");
  }
}