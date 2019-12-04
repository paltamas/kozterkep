<?php
use Kozterkep\AppBase as AppBase;

class MinervaController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;

    $this->set([
      '_active_menu' => 'Hírek',
      '_active_submenu' => 'Minerva hírlevelek',
    ]);
  }

  public function index() {

    $receiver_count = [
      'weekly' => $this->DB->count('users', 'JSON_EXTRACT(newsletter_settings, "$.weekly_harvest") = 1'),
      'daily' => $this->DB->count('users', 'JSON_EXTRACT(newsletter_settings, "$.daily") = 1'),
    ];

    $this->set([
      'receiver_count' => $receiver_count,
      '_title' => 'Minerva bemutatkozik',
    ]);

  }

  public function subscription() {

    $this->set([
      '_title' => 'Hírlevél-kezelés',
    ]);

  }

  public function archive() {
    $conditions = ['weekly_harvest' => 1];

    $total_count = $this->DB->count('newsletters', $conditions);

    $pagination = [
      'page' => @$this->params->query['oldal'] > 0
        ? $this->params->query['oldal'] : 1,
      'limit' => @$this->params->query['elem'] > 0 && $this->params->query['elem'] <= 500
        ? $this->params->query['elem'] : 36,
      'page_selector' => true,
      'total_count' => $total_count,
    ];

    $newsletters = $this->DB->find('newsletters', [
      'conditions' => $conditions,
      'limit' => $pagination['limit'],
      'order' => 'sent DESC',
      'page' => $pagination['page'],
      'debug' => false,
    ]);

    $this->set([
      '_title' => 'Minerva hírlevél archívum',

      'newsletters' => $newsletters,
      'pagination' => $pagination,
      'total_count' => $total_count,
    ]);
  }

  public function archive_view($id) {
    $newsletter = $this->DB->first('newsletters', $id);

    if (!$newsletter) {
      $this->redirect('/minerva/archivum', [texts('hibas_url'), 'warning']);
    }

    $this->set([
      '_title' => 'Minerva hírlevél archívum',
      'newsletter' => $newsletter,
    ]);
  }

}