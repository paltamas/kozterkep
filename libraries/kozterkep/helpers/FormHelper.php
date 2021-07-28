<?php
namespace Kozterkep;

/**
 *
 * Kifejezetten Bootstrap 4-re, meg saját biztonsági logikára kihegyezve
 * *
 * Class FormHelper
 * @package Kozterkep
 *
 */

class FormHelper {

  private $model;
  private $classes;
  private $_id;
  private $_type;

  public function __construct($app_config, $Request) {
    $this->app_config = $app_config;
    $this->Html = new HtmlHelper($app_config, $Request);
    $this->Session = new SessionComponent($app_config);
    $this->Text = new TextHelper();
    $this->Request = $Request;
    
    // Ahonnan a value jön az inputba
    // Csak array-t szeretünk
    $this->model = false;
    
    // Default classok
    $this->classes = [
      'input' => 'form-control',
      'date' => 'form-control',
      'textarea' => 'form-control',
      'select' => 'form-control',
      'checkbox' => 'form-check-input',
      'radio' => 'form-check-input',
      'file' => 'form-control-file',
      'button' => 'btn btn-primary',
      'help' => 'formHelp',
    ];
    
    // Input element ID
    $this->_id = '';
    $this->_type = '';
    
    // Helphez
    $this->_help_id_suffix = 'Help';

    $this->_field_errors = false;
  }


  /**
   * 
   * Form létrehozás
   * 
   * @param type $model
   * @param type $attributes
   * @return string
   */
  public function create($model = false, $attributes = [], $csrf = true) {
    $attributes = (array)$attributes + [
      'action' => $this->Request->here(),
      'method' => 'get',
      'id' => 'Form-' . ucfirst($this->Request->controller()) . '-' . ucfirst($this->Request->action()),
      'enctype' => 'multipart/form-data'
    ];

    $parsed_attributes = $this->Html->parse_attributes($attributes);
    
    $s = '<form ' . $parsed_attributes . '>';

    if ($attributes['method'] == 'post') {
      $s .= $this->input('_token', [
        'type' => 'hidden',
        'value' => $this->Session->get('csrf_token', bin2hex(random_bytes(24))),
        'id' => 'token' . uniqid()
      ]);
    }
    
    // A modell, amiből építkezünk
    if ($model) {
      if ($this->Request->is('post')) {
        $this->model = (array)$this->Request->data();
      } elseif (is_array($model)) {
        $this->model = $model;
      }
    }

    if ($this->_field_errors = $this->Session->get($this->app_config['sessions']['form_message_name'])) {
      $this->Session->delete($this->app_config['sessions']['form_message_name']);
    }

    return $s;
  }
  
  
  /**
   * 
   * Form zárás, opcionálisan submit gombbal
   * 
   * @param type $submit_title - ha kell submit
   * @param type $submit_attributes - submit attribútumok
   * @return string
   */
  public function end ($submit_title = false, $submit_attributes = []) {
    $s = '';
    
    if ($submit_title) {
      $s .= $this->submit($submit_title, $submit_attributes);
    }
    
    $s .= '</form>';
    return $s;
  }


  /**
   *
   * Select shorthand
   *
   * @param $name
   * @param array $attributes
   * @return type
   */
  public function select(string $name, $attributes = []) {
    $attributes['type'] = 'select';
    return $this->input($name, $attributes);
  }


  /**
   *
   * Checkbox shorthand
   *
   * @param $name
   * @param array $attributes
   * @return type
   */
  public function checkbox(string $name, $attributes = []) {
    $attributes['type'] = 'checkbox';
    return $this->input($name, $attributes);
  }


  /**
   *
   * Radio shorthand
   *
   * @param $name
   * @param array $attributes
   * @return type
   */
  public function radio(string $name, $attributes = []) {
    $attributes['type'] = 'radio';
    return $this->input($name, $attributes);
  }


  /**
   *
   * Input
   *
   * @param string $name
   * @param array $attributes
   * @return string
   */
  public function input(string $name, $attributes = []) {
    // ID meghatározása
    $this->_id = ucfirst($this->Text->slug($name));
    
    // Érték meghatározása
    $value = isset($this->model[$name]) ? str_replace('"', '&#34;', $this->model[$name]) : '';

    // Ha options => Select
    if (isset($attributes['options'])
      && !@in_array($attributes['type'], ['select', 'select_button'])) {
      $attributes['type'] = 'select';
    }

    // Button => Submit
    if (@$attributes['type'] == 'button') {
      $attributes['type'] = 'submit';
    }

    // textarea_short => textarea.textarea-short
    if (@$attributes['type'] == 'textarea_short') {
      $attributes['type'] = 'textarea';
      @$attributes['class'] .= 'textarea-short';
    }
    
    // Default
    $attributes = (array)$attributes + [
      'type' => 'text',
      'name' => $name,
      'label' => false,
      'divs' => 'form-group',
      'inline' => false,
      'help' => false,
      'id' => $this->_id,
      'autocomplete' => 'off',
      'value' => $value,
      'prepend_icon' => false
    ];

    // Ha string, akkor class jött
    if (is_string($attributes['divs'])) {
      $attributes['divs'] = ['class' => $attributes['divs']];
    } elseif ($attributes['divs'] && !isset($attributes['divs']['class'])) {
      $attributes['divs']['class'] = 'form-group';
    }

    // Type
    $this->_type = $attributes['type'];

    // Ha hidden, akkor nem kell bepakolni semmibe
    if ($this->_type == 'hidden') {
      $attributes['divs'] = false;
      $attributes['div_attributes'] = false;
      $attributes['class'] = '';
    }

    $s = $input = '';
    $extra_after_input = false;

    // Ha van prepend, akkor előkészítjük
    if (in_array($attributes['type'], ['text', 'date', 'email', 'number', 'password']) && $attributes['prepend_icon']) {
      $input .= '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><span class="far fa-' . $attributes['prepend_icon'] . '"></span></span></div>';
      $extra_after_input = '</div>';
    }
    
    // Opciók is jönnek...
    $options = $attributes;
    // ...amik nem kellenek attribútumba
    unset($attributes['divs']);
    unset($attributes['label']);
    unset($attributes['help']);
    unset($attributes['inline']);
    unset($attributes['prepend_icon']);

    // Ha van help, akkor nem kell mb-XX azoknál, ahol a help külön divbe megy
    if ($options['divs'] && $options['help'] && in_array($options['type'], ['checkbox', 'radio'])) {
      $options['divs']['class'] .= ' mb-1';
    }

    // Div start
    $div_attributes = $options['divs'] ? $this->Html->parse_attributes($options['divs']) : '';
    $s .= $options['divs'] ? '<div' . $div_attributes . '>' : '';

    // Aria description, ha van help
    if ($options['help']) {
      $attributes['aria-describedby'] = $this->_id . $this->_help_id_suffix;
    }

    // Ha mezőhiba, akkor megplusszoljuk a classt
    if (@$this->_field_errors[$name] != '') {
      @$attributes['class'] .= ' is-invalid';
    }

    /*
     * Típustól függő attribútumok és megjelenés
     */
    switch ($attributes['type']) {
      case 'text':
      case 'date':
      case 'email':
      case 'number':
      case 'password':
      case 'hidden':
        $attributes = $this->Html->parse_attributes($attributes, [
          'class' => $this->classes['input']
        ]);
        $input .= '<input' . $attributes . '>';
        break;

      case 'file':
        $multiple = @$attributes['multiple'] == true ? ' multiple' : '';
        $attributes = $this->Html->parse_attributes($attributes, [
          'class' => $this->classes['file']
        ], [], ['multiple']);
        $input .= '<input' . $attributes . $multiple . '>';
        break;
      
      case 'textarea':
        $value = $attributes['value'];
        unset($attributes['value']);
        $attributes = $this->Html->parse_attributes($attributes, [
          'class' => $this->classes['textarea'],
          'rows' => 3
        ]);
        $input .= '<textarea' . $attributes . '>' . $value . '</textarea>';
        break;
      
      case 'select_button':
        $input .= $this->_select_button($attributes);
        break;

      case 'select':
        $input .= $this->_select($attributes);
        break;
      
      case 'checkbox':
        if (!isset($attributes['checked'])) {
          $attributes['checked'] = $attributes['value'] == $value ? true : false;
        }
        $input .= $this->_checkbox($attributes);
        break;
      
      case 'radio':
        // Ennél fontos, hogy az options['label'] az a rádió
        // csoport label-je, nem az egyed opcióké
        $input .= $this->_radio($attributes);
        break;
    }


    // Label / Input sorrent és class
    if ($options['label']) {
      list($label_text, $label_attributes) = $this->_attribute($options['label']);

      // Labelnél külön mezőhiba
      if (@$this->_field_errors[$name] != '') {
        @$label_attributes['class'] .= 'text-danger';
      }
      
      if ($label_text != '') {
        switch ($options['type']) {
          case 'checkbox':
            $inline = $options['inline'] ? ' form-check-inline' : '';
            $label_attributes += ['class' => 'form-check-label'];
            $s .= '<div class="form-check' . $inline .  '">';
            $s .= $input . $this->label($label_text, $label_attributes);
            $s .= '</div>';
            break;

          case 'radio':
            $label_attributes += ['class' => 'mb-1'];
            $s .= $this->label($label_text, $label_attributes);
            $s .= $options['inline'] ? '<div>' : '';
            $s .= $input;
            $s .= $options['inline'] ? '</div>' : '';
            break;
          
          default:
            $s .= $this->label($label_text, $label_attributes) . $input;
            break;
        }
      }
    } else {
      $s .= $input;
    }
    
    if ($extra_after_input) {
      $s .= $extra_after_input;
    }

    // Mezőhiba
    if (@$this->_field_errors[$name] != '') {
      $s .= '<div class="small text-danger">' . $this->_field_errors[$name] . '</div>';
    }
    
    // Help
    if ($options['help']) {
      // Help float-ol checkbox, radio esetén, ezért kell egy kis form hekk
      if (in_array($options['type'], ['checkbox', 'radio']) && $options['divs']) {
        $s .= '</div><div class="mb-4">';
      }
      
      list($help_text, $help_attributes) = $this->_attribute($options['help']);
      $help_attributes['id'] = $this->_id . $this->_help_id_suffix;
      $s .= $help_text != '' ? $this->help($help_text, $help_attributes) : '';
    }
    
    // Div end
    $s .= $options['divs'] ? '</div>' : '';

    return $s;
  }
  
  
  /**
   * 
   * Szuper select, tudása most:
   *  - options []
   *  - empty (false vagy érték)
   *  - value
   * 
   * @param type $attributes
   * @return string
   */
  private function _select($attributes = []) {
    $options = $attributes;
    
    // Ezek nem kellenek
    _unset($attributes, ['options', 'select_options', 'value']);
    
    $attributes = $this->Html->parse_attributes($attributes, [
      'class' => $this->classes['select']
    ]);
    
    $s = '';
    
    // Select start
    $s .= '<select' . $attributes . '>';

    // Üres, default állás
    if (isset($options['empty'])) {
      if (is_array($options['empty'])) {
        $value = array_keys($options['empty'])[0];
        $label = array_values($options['empty'])[0];
      } else {
        $value = '';
        $label = $options['empty'];
      }
      $selected = @$options['value'] === false ? ' selected' : '';
      $s .= '<option value="' . $value . '"' . $selected . '>';
      $s .= $label;
      $s .= '</option>';
    }
    
    // Opciók
    if (count($options['options']) > 0) {
      // Itt trükk van, mert jöhet egy sima, DB-ből kiolvasott array
      // ebben az esetben id és name-ből építjük, vagy a megadott
      // select_options tömb key, value mezőiből, ha jön
      foreach ($options['options'] as $k => $v) {
        list($option_value, $option_text) = $this->_build_select_options($k, $v, @$options['select_options']);
        $selected = @$options['value'] == $option_value ? ' selected' : '';
        $s .= '<option value="' . $option_value . '"' . $selected . '>';
        $s .= $option_text;
        $s .= '</option>';
      }
    }
    
    // Select end
    $s .= '</select>';
    
    return $s;
  }


  /**
   *
   * Egy button group,
   * ami hidden input-ba teszi az értékét
   *
   * @param array $attributes
   * @return string
   */
  private function _select_button($attributes = []) {
    $s = '';

    $s .= '<div class="d-block btn-group" role="group" aria-label="' . $attributes['name'] . ' választógombok">';
    foreach ($attributes['options'] as $k => $v) {

      list($option_value, $option_text) = $this->_build_select_options($k, $v, @$attributes['select_options']);

      $button_class = $attributes['value'] == $option_value
        ? 'btn-secondary active' // aktív állapot
        : 'btn-outline-secondary'; // alap állapot
      $button_class .= @$attributes['class'] != '' ? ' ' . $attributes['class'] : ' btn-sm';

      $s .= '<button type="button" ia-form-select-button="#' . $attributes['id'] . '" ';
      $s .= 'ia-form-select-value="' . $option_value
        . '" id="button-' . $attributes['id'] . '-' . $option_value . '" class="btn ' . $button_class . '" ';
      $s .= '>';
      $s .= $option_text;
      $s .= '</button>';
    }
    $s .= '</div>';

    if (is_array(@$attributes['input'])) {
      $input_attributes = $this->Html->parse_attributes($attributes['input']);
    } else {
      $input_attributes = '';
    }

    $s .= '<input type="hidden" '
      . 'name="' . $attributes['name'] . '" '
      . 'id="' . $attributes['id'] . '" '
      . 'value="' . $attributes['value'] . '" ' . $input_attributes . ' />';

    return $s;
  }




  private function _build_select_options($key, $value, $select_options) {
    if (is_array($value) && count($value) > 0) {
      $array_value_field = @$select_options['value'] != '' ? $select_options['value'] : 'id';
      $array_text_field = @$select_options['text'] != '' ? $select_options['text'] : 'name';
      $option_value = $value[$array_value_field];
      $option_text = $value[$array_text_field];
    } else {
      $option_value = $key;
      $option_text = $value;
    }

    return [$option_value, $option_text];
  }

  
  
  /**
   * 
   * Checkbox
   * 
   * @param type $attributes
   * @return string
   */
  private function _checkbox($attributes = []) {
    $options = (array)$attributes + ['checked' => false];
    
    unset($attributes['checked']);
    unset($attributes['inline']);
    
    $attributes = $this->Html->parse_attributes($attributes, [
      'class' => $this->classes['checkbox']
    ]);
    
    $checked = $options['checked'] ? ' checked' : '';

    // Ez azért kell, hogy a nem pipált is elposztolódjon
    $s = '<input type="hidden" value="0" name="' . $options['name'] . '">';
    $s .= '<input' . $attributes . $checked . '>';

    return $s;
  }
  
  
  /**
   * 
   * Radio
   * 
   * @param type $attributes
   * @return string
   */
  private function _radio($attributes = []) {
    $options = (array)$attributes + [];
    
    unset($attributes['value']);
    unset($attributes['options']);
    unset($attributes['inline']);
    unset($attributes['id']);
    
    $attributes = $this->Html->parse_attributes($attributes, [
      'class' => $this->classes['radio']
    ]);
    
    $s = '';
    
    // Opciók
    if (count($options['options']) > 0) {
      foreach ($options['options'] as $value => $text) {
        $checked = isset($options['value']) && $options['value'] && $options['value'] == $value 
          ? ' checked' : '';
        $inline = $options['inline'] ? ' form-check-inline' : '';
        $option_id = $options['id'] . '[' . $value . ']';
        $s .= '<div class="form-check' . $inline .  '">';
        $s .= '<input' . $attributes . ' id="' . $option_id . '" 
          value="' . $value . '"' . $checked . '>';
        $s .= $this->label($text, [
          'class' => 'form-check-label',
          'for' => $option_id
        ]);
        $s .= '</div>';
      }
    }
    
    return $s;
  }
  
  
  /**
   * 
   * Label
   * 
   * @param type $text
   * @param type $attributes
   * @return type
   */
  public function label(string $text, $attributes = []) {
    $attributes = (array)$attributes + [
      'icon' => '',
      'class' => ' pr-2' // az inline megjelenés rácuppanása miatt
    ];

    if ($attributes['icon'] != '') {
      $text = '<span class="far fa-' . $attributes['icon'] . ' mr-1"></span>' . $text;
    }
    unset($attributes['icon']);
    $attributes = $this->Html->parse_attributes($attributes, [
      'for' => $this->_id
    ]);
    return '<label' . $attributes . '>' . $text . '</label>';
  }
  
  
  /**
   * 
   * Segítség szöveg
   * 
   * @param type $text
   * @param type $attributes
   * @return type
   */
  public function help(string $text, $attributes = []) {
    if (isset($attributes['icon'])) {
      $icon = $this->Html->icon($attributes['icon'], ['class' => 'mr-1']);
      unset($attributes['icon']);
    } else {
      $icon = '';
    }
    $attributes = $this->Html->parse_attributes($attributes, [
      'class' => 'formHelp form-text text-muted small ' . $this->classes['help']
    ]);
    $text = strlen($text) > 300 ? $this->Text->read_more($text, 300) : $text;
    return '<span' . $attributes . '>' . $icon . $text . '</span>';
  }


  /**
   *
   * Captcha generátor
   *
   * @param array $attributes
   * @return string
   */
  public function captcha($attributes = []) {
    $rand_id = uniqid() . '-' . md5(uniqid());
    $numbers = sDB['numbers'];
    $n1 = rand(10,20);
    $n2 = rand(1,10);
    $answer = $n1 + $n2;
    $attributes['label'] = 'Mennyi ' . $numbers[$n1] . ' meg ' . $numbers[$n2] . '?';
    $attributes['help'] = 'Számot adj meg. Ezzel a mezővel ellenőrizzük, hogy nem vagy-e <span class="far fa-robot mr-1 ml-1"></span>robot.';
    $attributes['type'] = 'text';
    $attributes['class'] = 'narrow';
    $this->Session->set('captcha_' . $rand_id, $answer);
    $s = $this->input('captcha_' . $rand_id, $attributes);
    return $s;
  }


  /**
   *
   * Captcha ellenőrző
   *
   * @param $form_data
   * @return bool
   */
  public function check_captcha($form_data) {
    if (is_array($form_data) && count($form_data) > 0) {
      foreach ($form_data as $name => $value) {
        if (strpos($name, 'captcha_') === 0) {
          $cid = str_replace('captcha_', '', $name);
          if ($this->Session->get('captcha_' . $cid) == (int)trim($value)) {
            return true;
          }
        }
      }
    }

    // Hiba van
    $captcha_error_count = $this->Session->get('captcha_error_count') > 0
      ? $this->Session->get('captcha_error_count') + 1 : 1;
    $this->Session->set('captcha_error_count', $captcha_error_count);
    $this->Session->set('captcha_error_last', time());

    return false;
  }
  
  
  /**
   * 
   * Attribútum parsolás, mert jöhet tömb is, ami okosabb
   * 
   * 2 formátumban jöhet a attr:
   * 'help' => 'Apróbetűs szöveg'
   * 'help' => ['Apróbetűs szöveg', ['class' => 'mr-2']]
   * 
   * @param type $attribute
   * @return type
   */
  private function _attribute($attribute = false) {
    $attr_attributes = [];
    
    if (is_array($attribute)) {
      $text = $attribute[0];
      $attr_attributes = isset($attribute[1]) && is_array($attribute[1]) 
        ? $attribute[1] : [];
    } else {
      $text = $attribute;
    }
    
    return [$text, $attr_attributes];
  }
  
  
  /**
   * 
   * Submit gomb
   * 
   * @param type $title
   * @param type $attributes
   */
  public function submit ($title = '', $attributes = []) {

    $attributes = (array)$attributes + [
      'divs' => false,
      'help' => false,
      'name' => 'submit',
    ];
    
    $options = $attributes;
    
    // Opciónak kellett csak
    unset($attributes['divs']);
    
    // Nehogy legyenek
    unset($attributes['value']);
    unset($attributes['role']);
    unset($attributes['type']);
    unset($attributes['help']);

    $btn_class = $this->classes['button'];

    // btn-secondary, vagy akármi törölje a btn-primary-t
    if (_contains(@$attributes['class'], ['btn-outline-', 'btn-secondary', 'btn-warning', 'btn-success', 'btn-info', 'btn-danger'])) {
      $btn_class = str_replace('btn-primary', '', $btn_class);
    }

    $attributes = $this->Html->parse_attributes($attributes, ['class' => $btn_class]);
    
    $s = '';
    
    // Div start
    $s .= $options['divs'] ? '<div class="' . $options['divs'] . '">' : '';
    
    // A gombi
    $s .= '<input type="submit" role="button"' . $attributes . ' value="' . $title . '">';

    // Help
    if ($options['help']) {
      list($help_text, $help_attributes) = $this->_attribute($options['help']);
      $help_attributes['id'] = $this->_id . $this->_help_id_suffix;
      $s .= $help_text != '' ? $this->help($help_text, $help_attributes) : '';
    }
    
    // Div end
    $s .= $options['divs'] ? '</div>' : '';
    
    return $s;
  }
}
