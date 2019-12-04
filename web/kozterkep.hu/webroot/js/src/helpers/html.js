var Html = {

  init: function() {
    // semmi
  },

  /**
   * Hogy ezt se kelljen
   * @param classes
   * @returns {string}
   */
  loading: function(div_class, icon_classes, text) {
    var div_class = typeof div_class == 'undefined' ? 'my-4' : div_class;
    var icon_classes = typeof icon_classes == 'undefined' ? 'text-muted' : icon_classes;
    var s = '<span class="far fa-compass fa-spin fa-lg ' + icon_classes + '"></span>';
    var text = typeof text != 'undefined' ? '<span class="ml-2 text-muted">' + text + '</span>' : '';
    s = '<div class="' + div_class + '">' + s + text + '</div>';
    return s;
  },


  /**
   * Overlay
   * @param method
   * @param options
   */
  overlay: function(method, options) {
    var options = _arr(options, {
      type: 'page', // vagy full
      loading: true,
    });

    var content = '';

    if (options.loading) {
      content = Html.loading('my-5 py-5 text-center', 'my-5 fa-3x text-white');
    }

    $('.' + options.type + '-overlay').html(content);

    if (method == 'show') {
      $('.' + options.type + '-overlay').removeClass('d-none');
    } else {
      $('.' + options.type + '-overlay').addClass('d-none');
    }
  },


  /**
   *
   * Link építés
   *
   * @param text
   * @param url
   * @param options
   * @returns {string}
   */
  link: function(text, url, options) {
    if (url == 'referer' || url == 'here' || url == 'false') {
      url = $app.here;
    }

    if (typeof options == 'undefined') {
      var options = {};
    }

    // id
    if (typeof options['id'] == 'undefined') {
      options['id'] = 'link-' + Helper.randId();
    }

    // id
    if (typeof options['title'] != 'undefined') {
      options['data-toggle'] = 'tooltip';
    }

    if (typeof options['hide_text'] != 'undefined' && options['hide_text'] == true) {
      text = '<span class="d-none d-sm-inline">' + text + '</span>';
    }

    // bal ikon
    if (typeof options['icon'] != 'undefined' || typeof options['icon_right'] != 'undefined') {
      var icon = typeof options['icon'] != 'undefined' ? options['icon'] : options['icon_right'];

      var margin = text != '' ? '1' : '0',
        fa_style = 'far';
      if (icon.indexOf(' fas') > -1) {
        fa_style = 'fas';
      } else if (icon.indexOf(' far') > -1) {
        fa_style = 'far';
      } else if (icon.indexOf(' fal') > -1) {
        fa_style = 'fal';
      } else if (icon.indexOf(' fab') > -1) {
        fa_style = 'fab';
      }

      if (typeof options['icon'] != 'undefined') {
        text = '<span class="' + fa_style + ' fa-' + icon + ' fa-fw mr-' + margin + '"></span>'
          + '<span class="link-text">' + text + '</span>';
      } else {
        text = '<span class="link-text">' + text + '</span>'
          + '<span class="' + fa_style + ' fa-' + icon + ' fa-fw ml-' + margin + '"></span>';
      }
    }

    // wrapper
    var wrapper_start = '',
      wrapper_end = '';

    if (typeof options['divs'] != 'undefined') {
      wrapper_start = '<div class="' + options['divs'] + '">';
      wrapper_end = '</div>';
    }

    // title
    if (typeof options['title'] != 'undefined') {
      options['data-toggle'] = 'tooltip';
    }


    // műlap
    if (options['artpiece']) {
      var artpiece = options['artpiece'];
      var url_end = typeof artpiece['title'] != 'undefined' ? '/' + Helper.slugify(artpiece['title']) : '';
      url = '/' + artpiece['id'] + url_end;
      delete options['artpiece'];
    }

    // User, de ID alapján
    if (options['user_id']) {
      var user_id = options['user_id'];
      url = '/kozosseg/profil/id:' + user_id;
    }

    var models = {
      'artist': ['alkotok', 'name'],
      'place': ['helyek', 'name'],
      'country': ['orszagok', '1'],
      'county': ['megyek', '0'],
      'district': ['budapesti-keruletek', '0'],
      'set': ['gyujtemenyek', 'name'],
      'post': ['blogok', 'title'],
    };

    $.each(models, function(model_id, params) {
      if (typeof options[model_id] != 'undefined') {
        var item = options[model_id];
        var url_end = typeof item[params[1]] != 'undefined' ? '/' + Helper.slugify(item[params[1]]) : '';
        url = '/' + params[0] + '/megtekintes/' + item['id'] + url_end;
        delete options[model_id];
      }
    });


    var parsed_attributes = Html.parseAttributes(options, ['icon', 'divs', 'hide_text']);

    return wrapper_start + '<a href="' + url + '"' + parsed_attributes +'>' + text + '</a>' + wrapper_end;
  },



  input: function(name, attributes) {
    var s = '';

    var options = attributes;

    attributes['name'] = name;

    var type = typeof attributes['type'] == 'undefined' ? 'text' : attributes['type'];
    var label = typeof attributes['label'] == 'undefined' ? false : attributes['label'];

    if (typeof attributes['autocopmplete'] == 'undefined') {
      attributes['autocopmplete'] = 'off';
    }

    if (typeof attributes['id'] == 'undefined') {
      attributes['id'] = Helper.ucfirst(Helper.slugify(name));
    }

    if (type == 'textarea_input') {
      type = 'textarea';
      if (typeof attributes['class'] != 'undefined') {
        attributes['class'] += ' textarea-short';
      } else {
        attributes['class'] = 'textarea-short';
      }
    }

    var parsed_attributes = Html.parseAttributes(attributes, ['type', 'divs', 'value', 'label', 'options', 'class', 'help']);

    var classes = {
      'text': 'form-control',
      'password': 'form-control',
      'textarea': 'form-control',
      'select': 'form-control',
      'checkbox': 'form-check-input',
      'radio': 'form-check-input',
      'file': 'form-control-file',
      'submit': 'btn btn-primary',
      'button': 'btn btn-secondary',
    };

    var value = typeof attributes['value'] == 'undefined' ? '' : attributes['value'];

    var help = typeof attributes['help'] == 'undefined' ? false : attributes['help'];

    var class_name = classes[type];
    if (typeof options['class'] != 'undefined') {
      class_name += ' ' + options['class']
    }

    switch (type) {

      case 'text':
        s = label ? '<label class="" for="' + options['id'] + '">' + label + '</label>' : '';
        s += '<input type="text" ' + parsed_attributes
          + ' class="' + class_name + '" value="' + value + '" />';
        break;

      case 'password':
        s = label ? '<label class="" for="' + options['id'] + '">' + label + '</label>' : '';
        s += '<input type="password" ' + parsed_attributes
          + ' class="' + class_name + '" value="' + value + '" />';
        break;

      case 'textarea':
        s = label ? '<label class="" for="' + options['id'] + '">' + label + '</label>' : '';
        s += '<textarea ' + parsed_attributes + ' class="'
          + class_name + '">' + value + '</textarea>';
        break;

      case 'select':
        s = label ? '<label class="" for="' + options['id'] + '">' + label + '</label>' : '';
        var select_options = '';

        if (typeof options['empty'] != 'undefined') {
          var val, lab;
          if (typeof options['empty'] == 'object') {
            val = Object.keys(options['empty'])[0];
            lab = options['empty'][Object.keys(options['empty'])[0]];
          } else {
            val = '';
            lab = options['empty'];
          }
          options['options'][val] = lab;
          options['options'] = Helper.sortObject(options['options']);
        }

        $.each(options['options'], function(key, label) {
          var selected = key == value ? 'selected' : '';
          select_options += '<option value="' + key + '" '
            + selected + '>'
            + label + '</option>';
        });
        s += '<select ' + parsed_attributes + ' class="'
          + class_name + '">' + select_options + '</select>';
        break;

      case 'select_button':
        s = label ? '<label class="" for="' + options['id'] + '">' + label + '</label>' : '';
        s += '<div class="d-block btn-group" role="group" aria-label="' + options['name'] + ' választógombok">';
        $.each(options['options'], function(key, label) {
          var button_class = key == value ?
            'btn-secondary active' // aktív állapot
            : 'btn-outline-secondary'; // alap állapot

          button_class += options['class'] != '' ? ' ' + options['class'] : '';

          s += '<button type="button" ia-form-select-button="#' + options['id'] + '" '
            + 'ia-form-select-value="' + key + '" class="btn btn-sm ' + button_class + '" '
            + '>' + label + '</button>';
        });
        s += '</div>';
        s += '<input type="hidden" '
          + 'name="' + options['name'] + '" '
          + 'id="' + options['id'] + '" '
          + 'value="' + options['value'] + '" />';
        break;

      case 'checkbox':
        var checked = value == 1 ? 'checked' : '';
        s = '<div class="form-check-inline">'
          + '<input type="hidden" value="0" name="' + options['name'] + '" />'
          + '<input type="checkbox" ' + parsed_attributes
          + ' class="' + class_name + '" value="1" ' + checked + '" />'
          + '<label class="form-check-label" for="' + options['id'] + '">' + label + '</label>'
          + '</div>';
        break;

      case 'submit':
        s = '<input type="submit" ' + parsed_attributes
          + ' class="' + class_name + '" value="' + value + '" />';
        break;

    }

    if (typeof attributes['divs'] != 'undefined') {
      var div_class = attributes['divs'] != false ? attributes['divs'] : false;
    } else {
      var div_class = '';
    }


    if (help) {
      s += '<span class="formHelp form-text text-muted small">' + help + '</span>';
    }

    if (div_class !== false) {
      s = '<div class="form-group ' + div_class + '">' + s + '</div>';
    }

    return s;
  },


  parseAttributes: function(object, exclude) {
    var s = '',
      exclude = typeof exclude == 'undefined' ? [] : exclude;
    $.each(object, function(key, value) {
      if ($.inArray(key, exclude) == -1) {
        s += ' ' + key + '="' + value + '"';
      }
    });

    return s;
  },



};
