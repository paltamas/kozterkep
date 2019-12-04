var s, Http = {

  settings: {
    timeout: false
  },

  init: function () {

    s = this.settings;

  },

  ajaxError: function (response, path) {
    _c(response, 1);

    var etc = '';

    if (typeof path !== 'undefined') {
      _c(path, 1);
      etc += ' \nútvonal: ' + path;
    }

    //Alerts.flashBubble(_text('kommunikacios_hiba') + etc, 'danger', true);
    _c('Betöltési hiba!' + etc, 1);
  },


  request: function (type, params) {

    switch (type) {
      case 'post':
        return this.post(params.path, params.data, params.success, params.options);
        break;
      case 'get':
        return this.get(params.path, params.success, params.options);
        break;
      case 'put':
        return this.put(params.path, params.data, params.success, params.options);
        break;
    }

  },


  get: function (path, success, options) {

    var that = this,
      options = _arr(options, {
        datatype: 'json',
      });

    // Ha nem külső URL-t hívtunk, és nincs már az elején a prefix eleve
    if (path.indexOf('http') == -1 && path.substring(0, $app.path.length) != $app.path) {
      path = $app.path + path;
    }

    if (path.indexOf('api/') !== -1) {
      headers = {'X-CSRF-TOKEN': $app.token};
    } else {
      headers = {};
    }

    $.ajax({
      headers: headers,
      type: 'GET',
      cache: typeof options.cache == 'undefined' ? false : options.cache,
      url: path,
      timeout: that.settings.timeout,
      dataType: options.datatype,
      success: success,
      error: function (response) {
        that.ajaxError(response, path);
      },
    });

  },


  post: function (path, data, success, options) {

    var that = this,
      options = _arr(options, {
        datatype: 'json',
      }),
      silent = typeof options.silent == 'undefined' ? false : options.silent;

    if (!silent) {
      $('.progress').removeClass('d-none');
    }

    // Ha nem külső URL-t hívtunk
    if (path.indexOf('http') == -1) {
      path = $app.path + path;
    }

    if (path.indexOf('api/') !== -1) {
      headers = {'X-CSRF-TOKEN': $app.token};
    } else {
      headers = {};
    }

    $.ajax({
      headers: headers,
      type: 'POST',
      cache: false,
      url: path,
      data: data,
      timeout: that.settings.timeout,
      dataType: options.datatype,
      success: success,
      error: function (response) {
        that.ajaxError(response, path);
      },
    }).done(function() {
      if (!silent) {
        setTimeout(function () {
          $('.progress').addClass('d-none');
        }, 300);
      }
    });

  },


  put: function (path, data, success, options) {

    var that = this,
      options = _arr(options, {
        datatype: 'json',
      }),
      silent = typeof options.silent == 'undefined' ? false : options.silent;

    if (!silent) {
      $('.progress').removeClass('d-none');
    }

    // Ha nem külső URL-t hívtunk
    if (path.indexOf('http') == -1) {
      path = $app.path + path;
    }

    if (path.indexOf('api/') !== -1) {
      headers = {'X-CSRF-TOKEN': $app.token};
    } else {
      headers = {};
    }

    $.ajax({
      headers: headers,
      type: 'PUT',
      cache: false,
      url: path,
      data: data,
      timeout: that.settings.timeout,
      dataType: options.datatype,
      success: success,
      error: function (response) {
        that.ajaxError(response, path);
      },
    }).done(function() {
      if (!silent) {
        setTimeout(function () {
          $('.progress').addClass('d-none');
        }, 300);
      }
    });

  },


};