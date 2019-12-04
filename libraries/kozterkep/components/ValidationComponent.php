<?php
namespace Kozterkep;

class ValidationComponent {

  public function __construct($app_config, $Request, $DB, $Mongo) {
    $this->app_config = $app_config;
    $this->Request = $Request;
    $this->DB = $DB;
    $this->Mongo = $Mongo;
    $this->Session = new SessionComponent($app_config);
    $this->Cache = new CacheComponent();
    $this->field_errors = [];
  }


  /**
   *
   * Ellenőriz és upsert-el
   *
   * @param $data
   * @param $rules
   * @param bool $upsert_table
   * @param array $options
   * @return bool
   */
  public function process($data, $rules, $upsert_table = false, $options = []) {
    $options = (array)$options + [
      // true esetén megengedi a rules-ban nem deklarált mezők beküldését is
      // nem kellene ilyet csinálni! @todo (kukázzuk ezeket pl?)
      'allow_undeclared' => false,
      'echo' => true,
      'db' => 'mysql',
      // nem ellenőrizendő, controllerben generált adatok (idő, user_id, stb) (meg a bizalom)
      'defaults' => [],
      'redirect' => false,
    ];

    // a csrf tokennel nem foglalkozunk
    unset($data['_token']);

    foreach ($data as $field => $value) {
      // ID-re nincs validáció, azt csak update-hez használjuk
      if ($field == 'id') {
        continue;
      }

      if (!isset($rules[$field])
        && !$options['allow_undeclared'] && !isset($options['defaults'][$field])) {
        $this->redirect(false, ['<strong>"' . $field . '"</strong> mező beküldése nem engedélyezett!', 'danger']);
      }

      $rule = is_array($rules[$field]) ? $rules[$field]['rule'] : $rules[$field];

      switch (true) {

        case $rule == 'unset':
          unset($data[$field]);
          break;

        case $rule == 'not_empty':
          if ($value == '') {
            $this->field_errors[$field] = 'A mező kitöltése kötelező';
          }
          break;

        case $rule == 'tinyint':
          if (!in_array((int)$value, [0,1])) {
            $this->field_errors[$field] = 'Hibás kitöltés';
          } else {
            $data[$field] = (int)$value;
          }
          break;

        case $rule == 'array':
          if (!is_array($value)) {
            $this->field_errors[$field] = 'Hibás kitöltés';
          }
          break;

        case $rule == 'json_array':
          if (!is_array($value)) {
            $this->field_errors[$field] = 'Hibás kitöltés';
          } else {
            $data[$field] = _json_encode($value);
          }
          break;

        case $rule == 'numeric':
          if ($value != '' && !is_numeric($value)) {
            $this->field_errors[$field] = 'A mező csak számot tartalmazhat';
          } else {
            $data[$field] = (int)$value;
          }
          break;

        case $rule == 'birth_year':
          if (!is_numeric($value) || $value < 1900 || $value > date('Y')) {
            $this->field_errors[$field] = 'Érvényes születési évet adj meg';
          }
          break;

        case $rule == 'string':
          if (!is_string($value)) {
            $this->field_errors[$field] = 'A mező csak szöveget tartalmazhat';
          }
          break;

        case strpos($rule, 'minlength_') !== false:
          $p = explode('_', $rule);
          $length = $p[1];
          if (strlen($value) < $length) {
            $this->field_errors[$field] = 'Legalább ' . $length . ' karaktert adj meg';
          }
          break;

        case strpos($rule, 'maxlength_') !== false:
          $p = explode('_', $rule);
          $length = $p[1];
          if (strlen($value) > $length) {
            $this->field_errors[$field] = 'Maximum ' . $length . ' karaktert adj meg';
          }
          break;

        case $rule == 'email':
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->field_errors[$field] = 'Helytelen formátumú email cím';
          }
          break;


        case is_array($rules[$field] && $rule == 'exists'):
          $rule_options = $rules[$field];
          if (isset($rule['table']) && isset($rule_options['field'])) {
            $find_by = 'find_by' . $rule_options['field'];
            $exists = $this->DB->$find_by($rule_options['table'], $value);
            if (!$exists) {
              $this->field_errors[$field] = 'Nem létezik ilyen elem a hivatkozott adatbázis táblában.';
            }
          }
          break;
      }
    }

    if (count($options['defaults']) > 0) {
      $data = array_merge($data, $options['defaults']);
    }

    if (count($this->field_errors) > 0) {
      if ($options['echo']) {
        $this->Session->set($this->app_config['sessions']['form_message_name'], $this->field_errors);
        $this->Session->set_message('<strong>Hibás kitöltés</strong>, ellenőrizd a jelölt mezőket.', 'danger');
        return false;
      } else {
        return $this->field_errors;
      }
    }

    if (is_string($upsert_table)) {
      if ($options['db'] == 'mysql') {
        $result = $this->DB->upsert($upsert_table, $data, ['debug' => true]);

        if (!$options['redirect']) {
          return $result;
        }
      } elseif ($options['db'] == 'mongo') {
        $result = $this->Mongo->upsert($upsert_table, $data);
        if (!$options['redirect']) {
          return $result;
        }
      }
    }

    // Cache törlés(ek)
    if ($options['cache']) {
      if (is_array($options['cache']) && count($options['cache']) > 0) {
        foreach ($options['cache'] as $cache_name) {
          $this->Cache->delete($cache_name);
        }
      } else {
        $this->Cache->delete($options['cache']);
      }
    }

    if ($options['redirect']) {
      if (is_string($result) || is_numeric($result)) {
        $path = str_replace('{id}', $result, $options['redirect'][0]);
      } else {
        $path = $options['redirect'][0];
      }

      $redirect_options = $options['redirect'][1];

      // Hiba van
      if ($result === false) {
        $redirect_options = [texts('varatlan_hiba'), 'danger'];
      }

      $this->redirect($path, $redirect_options);
    }

    return true;
  }

  /**
   *
   * Saját redirectünk
   *
   * @param bool $target
   */
  private function redirect($target = false, $message = false) {
    if (!$target) {
      $target = $this->app_config['security']['black_hole'];
    }
    if ($message) {
      $message_text = is_array($message) ? $message[0] : $message;
      $message_type = is_array($message) ? $message[1] : 'info';
      $this->Session->set_message($message_text, $message_type);
    }
    header("Location: " . $target, false, 302);
    exit;
  }
}
