var s,
  Store = {

    settings: {

    },

    init: function () {
      s = this.settings;
    },

    get: function (key) {
      if (typeof (Storage) !== "undefined") {
        return localStorage.getItem(key) == null || localStorage.getItem(key) == 'NaN' ? false : localStorage.getItem(key);
      } else {
        return Cookies.get(key) == null || Cookies.get(key) == 'NaN' ? false : Cookies.get(key);
      }
    },
    set: function (key, value) {
      if (typeof (Storage) !== "undefined") {
        if (typeof value === 'object') {
          value = JSON.stringify(value);
        }
        return localStorage.setItem(key, value);
      } else {
        return Cookies.set(key, value, {expires: 30, path: $app.path});
      }
    },
    remove: function (key) {
      if (typeof (Storage) !== "undefined") {
        return localStorage.removeItem(key);
      } else {
        // @TODO https, path
        return Cookies.remove(key, {path: $app.path});
      }
    },
    clearAll: function() {
      if (typeof (Storage) !== "undefined") {
        localStorage.clear();
      }
    }
  };