// Mindenhol használható függvények
var Basefunctions = { init: function() {} };


/**
 * ;)
 *
 * @param what
 * @param error
 * @private
 */
function _c(what, type) {
  if (typeof type != "undefined" && type == 1) {
    console.error(what);
  } else {
    console.log(what);
    // az a baj ezzel, hogy FF alatt 36px line-height kellene, de nem OK akkor chromeban
    //console.log('%c' + what, 'border-left: 7px solid #c95b12; padding: 3px 5px; border-radius: 10px;');
  }
}


/**
 *
 * PHP (array)options + (array)defaults logika mása
 * Tehát az options-be beteszi a default értékeket, hacsak már nincsenek benne eleve.
 *
 * @param input_array
 * @param defaults
 * @returns {*}
 * @private
 */
function _arr(input_array, defaults) {
  var input_array = typeof input_array == 'undefined' ? [] : input_array;
  $.each(defaults, function(key, value) {
    if (typeof input_array[key] == 'undefined') {
      input_array[key] = value;
    }
  });
  return input_array;
}


/**
 *
 * Benne van-e
 * ha tömb, akkor abban értékként
 *
 * @todo: ezzel valami gond van.
 *
 *
 * @param string
 * @param needles
 * @param exact tömb esetén pontos egyezés-e?
 * @returns {boolean}
 * @private
 */
function _contains(string, needles, exact) {
  exact = typeof exact == 'undefined' ? false : exact;

  if (needles.length > 0) {
    $(needles).each(function(key, needle) {
      if ((exact && string == needle)
        || (!exact && string.indexOf(needle) > -1)) {
        return true;
      }
    });
  } else if (string.indexOf(needle) > -1) {
    return true;
  }
  return false;
}



/**
 * Szöveg kiírás kulcs szerint
 * texts/texts.php-ból
 * @param key
 * @returns {string}
 */
function _text (key) {
  var s = '';
  if (typeof $sDB['texts'][key] != 'undefined') {
    s = $sDB['texts'][key];
  }
  return s;
}


/**
 * Annyiban eltér a PHP redirect()-től, hogy ha a path
 * false, akkor az aktuális URI-ra reloadol
 */
function _redirect (path, flash) {
  var flash_params = '',
    separator = '',
    hash_params = '',
    tab_params = '';
  if (typeof flash === 'object') {
    flash_params = 'flash=' + encodeURI('["' + flash[0] + '","' + flash[1]  + '"]');
  }
  if (!path) {
    path = window.location.pathname;
  } else if (path.substring(0, $app.path.length) != $app.path) {
    path = $app.path + path;
  }

  if (flash_params != '') {
    separator = path.indexOf('?') > -1 ? '&' : '?';
  }

  if ($app.redirect_tab > 0) {
    // Ha tabra kell dobni, ez erősebb
    tab_params = '&tab=' + $app.redirect_tab;
  } else if ($app.redirect_hash != '') {
    // Ha hash-re kell dobni majd
    hash_params = '#' + $app.redirect_hash;
  }

  window.location = path + separator + flash_params + tab_params + hash_params;
}

/**
 * Késleltetés, callbackkel; legszebb! thx!
 * innen: https://stackoverflow.com/a/1909508/1118965
 * @param callback
 * @param ms
 * @returns {Function}
 */
function _delay(callback, ms) {
  var timer = 0;
  return function() {
    var context = this, args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      callback.apply(context, args);
    }, ms || 0);
  };
}

/**
 * Hogy tényleg most legyen a most
 * @returns {number}
 * @private
 */
function _now() {
  return Date.now();
}