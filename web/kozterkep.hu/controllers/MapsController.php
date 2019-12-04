<?php
use Kozterkep\AppBase as AppBase;

class MapsController extends AppController {

  public function __construct() {
    AppBase::__construct(APP);
    $this->user = self::$_user;
    $this->params = self::$_params;
  }

  public function index() {
    $title_for_page = 'Műlapok térképen';
    $list_page_type = false;

    $query = $this->params->query;

    $artpiece_ids = [];


    /**
     * Műlap keresés jön URL-ben
     */
    if (@$query['gyujtemeny'] != '') {
      $set = $this->Mongo->first('sets', $query['gyujtemeny']);

      if ($set) {
        $artpiece_ids = $this->Sets->get_artpieces($set, ['only_ids' => true]);
        $list_page_type = true;
      }
    } elseif (count($query) > 0) {
      // Paraméterek; itt, mert kell a kondihoz is
      $artpiece_parameters = $this->DB->find('parameters', [
        'conditions' => ['hidden' => 0],
        'order' => 'parameter_group_id ASC, parameter_subgroup_id ASC, rank ASC'
      ]);
      $conditions = $this->Search->build_artpiece_search_conditions($query, $artpiece_parameters, $this->user);

      $artpiece_ids = $this->DB->find('artpieces', [
        'type' => 'fieldlist',
        'conditions' => implode(' AND ', $conditions),
        'fields' => ['id'],
        'order' => 'view_total DESC',
        'limit' => APP['map']['max_id'],
        'debug' => false,
      ]);

      $list_page_type = count($artpiece_ids) > 0 ? true : false;
    }

    // Ha jött URL-ben cím
    $title_for_page = @$this->params->query['oldalcim'] != '' ? $this->params->query['oldalcim'] : $title_for_page;

    // Ha nem jött oldalcím, akkor a keresőből jöttünk és oda is menjünk
    // vissza, ne a lista nézetes kereső oldalra, mert az frusztráló
    $back_path = @$this->params->query['oldalcim'] != '' ? '/kereses/lista' : '/kereses';

    $this->set([
      '_active_menu' => 'Térkép',
      '_map_layout' => true,
      '_title' => $title_for_page,
      '_mobile_header' => false,
      '_sidemenu' => false,
      '_breadcrumbs_menu' => false,
      '_title_row' => false,
      '_footer' => false,

      'list_page_type' => $list_page_type,
      'back_path' => $back_path,
      'artpiece_ids' => $artpiece_ids
    ]);

  }


  public function iframe() {
    $this->layout('iframe');
  }

}