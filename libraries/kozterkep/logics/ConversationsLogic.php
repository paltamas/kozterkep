<?php
namespace Kozterkep;

class ConversationsLogic {

  private $DB;
  private $app_config;

  public function __construct($app_config, $DB) {
    $this->app_config = $app_config;
    $this->DB = $DB;
    $this->Mongo = new MongoComponent();
    $this->MC = new MemcacheComponent();
    $this->Email = new EmailHelper($app_config);
  }


  public function delete($conversation, $user_id) {
    if (($key = array_search($user_id, $conversation['deleted'])) === false) {
      $conversation['deleted'][] = $user_id;
    }

    // Az eddigi üzenetekre rájegyezzük, hogy töröltek ennél a usernél
    $messages[] = [];
    foreach ($conversation['messages'] as $key => $message) {
      if (!isset($message['deleted'][$user_id])) {
        $message['deleted'][$user_id] = time();
      }
      $messages[$key] = $message;
    }

    $every_user_deleted = array_unique($conversation['users']) == array_unique($conversation['deleted']) ? 1 : 0;

    $this->Mongo->update('conversations', [
      'deleted' => $conversation['deleted'],
      'every_user_deleted' => $every_user_deleted,
      'messages' => $messages
    ], [
      'users' => $user_id,
      '_id' => $conversation['id']
    ]);

    return true;
  }
}

