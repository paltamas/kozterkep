var s,
  Helper = {

    settings: {},

    init: function () {
      s = this.settings;
      this.bindUIActions();
    },

    bindUIActions: function () {

      $(document).on('click', '.openUrl', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        if (url !== undefined) {
          window.location.href = decodeURIComponent(url);
        }
      });

    },


    randId: function () {
      return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    },

    rand_interval: function(min, max) {
      return Math.floor(Math.random() * (max + 1)) + min;
    },

    stripTags: function(str) {
      return str.replace(/(<([^>]+)>)/ig,"");
    },

    urlParams: function (obj) {
      var str = "";
      for (var key in obj) {
        if (str != "") {
          str += "&";
        }
        str += key + "=" + encodeURIComponent(obj[key]);
      }
      return str;
    },

    getURLHashParameter: function (key) {
      var matches = location.hash.match(new RegExp(key+'=([^&]*)'));
      return matches ? matches[1] : null;
    },

    getURLParameter: function (name) {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has(name)) {
        return urlParams.get(name);
      } else {
        return false;
      }
      /*var resp = decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(window.location.search) || [, 'isempty'])[1]);
      return resp === 'isempty' ? false : resp;*/
    },

    getUrlLastPart: function() {
      var path = window.location.href;
      if (path.indexOf('#') !== -1) {
        path = path.split('#')[0];
      }
      if (path.indexOf('?') !== -1) {
        path = path.split('?')[0];
      }
      var urlParts = path.split('/');
      return urlParts[urlParts.length-1];
    },

    getDomain: function(string, parts) {
      if (string.indexOf('http') > -1) {
        var l = document.createElement("a");
        l.href = string;
        if (typeof parts != 'undefined' && parts) {
          return [l.hostname, l.pathname];
        }
        return l.hostname;
      } else {
        return string;
      }
    },

    preloadImg: function(url) {
      var img = new Image();
      img.src = url;
      return img;
    },

    urlIsImage: function(url, callback) {
      if (url.indexOf('fajl_mutato') !== 1) {
        // Fájl mutató
        Http.get(url + '?type', function(response) {
          if (response.indexOf('image') !== -1) {
            callback();
          }
        }, { datatype: 'text' });
      } else if (url.indexOf('jpg') !== -1
        || url.indexOf('jpeg') !== -1
        || url.indexOf('png') !== -1
        || url.indexOf('gif') !== -1
        || url.indexOf('bmp') !== -1) {
        // Már S3-on van, normál URL
        callback();
      }
      return false;
    },

    truncate: function (str, length, strip_tags) {
      if (typeof strip_tags != 'undefined' && strip_tags) {
        str = Helper.stripTags(str);
      }
      return str.length > length ? str.substring(0, length - 3) + '...' : str;
    },

    textFormat: function(str, nl2br) {
      if (typeof nl2br == 'undefined') {
        var nl2br = true;
      }
      //str = Helper.replaceAll(/\*(.*?)\*/i, '<b>$1</b>', str);
      return nl2br ? Helper.nl2br(str) : str;
    },

    nl2br: function(str) {
      if (typeof str === 'undefined' || str === null) {
        return '';
      }
      var breakTag = '<br>';
      return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    },

    timeAgoInWords: function (timestamp, format) {
      var t;

      if (typeof format === "undefined") {
        var format = 'ago';
      }

      var now = Math.floor(new Date().getTime() / 1000),
        nowTime = new Date(timestamp * 1000),
        timestampTime = new Date(timestamp * 1000);

      if (timestamp > now - 30 * 24 * 60 * 60) {
        switch (true) {
          case timestamp > now - 60:
            t = 'épp most';
            break;
          case timestamp > now - 60 * 60:
            t = Math.floor((now - timestamp) / 60) + ' perce';
            break;
          case timestamp > now - 40 * 60 * 60:
            t = Math.floor((now - timestamp) / (60 * 60)) + ' órája';
            break;
          default:
            t = Math.ceil((now - timestamp) / (60 * 60 * 24)) + ' napja';
            break;
        }
      } else {
        t = timestampTime.getFullYear() < nowTime.getFullYear()
          ? this.timeConverter(timestamp) : this.timeConverter(timestamp, '');
      }

      return t;
    },

    timeConverter: function (timestamp, type, ago) {
      if (typeof ago === 'undefined') {
        var ago = false;
      }
      var a = new Date(timestamp * 1000);
      var year = a.getFullYear();
      var month = a.getMonth() + 1;
      var date = a.getDate();
      var hour = a.getHours();
      var min = a.getMinutes();
      var sec = a.getSeconds();
      // Kezdő nulla és más csúnyaságok
      month = month < 10 ? '0' + month : month;
      date = date < 10 ? '0' + date : date;
      hour = hour < 10 ? '0' + hour : hour;
      min = min < 10 ? '0' + min : min;
      sec = sec < 10 ? '0' + sec : sec;
      if (typeof(type) !== 'undefined' && type == 'short') {
        var time = month + '.' + date + '.' + ' ' + hour + ':' + min;
      } else {
        var time = year + '.' + month + '.' + date + '. ' + hour + ':' + min;
      }
      if (typeof(type) !== 'undefined' && type == 'full') {
        time += ':' + sec;
      }

      if (ago) {
        return '<span ia-timestamp="' + timestamp + '" title="' + time + '">' + this.timeAgoInWords(timestamp) + '</span>';
      } else {
        return time;
      }
    },

    timeToDate: function (timestamp) {
      var a = new Date(timestamp * 1000);
      var year = a.getFullYear();
      var month = a.getMonth() + 1;
      var date = a.getDate();
      // Kezdő nulla és más csúnyaságok
      month = month < 10 ? '0' + month : month;
      date = date < 10 ? '0' + date : date;
      var time = year + '.' + month + '.' + date + '.';
      return time;
    },

    today: function (diff) {
      if (typeof (diff) == "undefined") {
        diff = 0;
      }
      var today = new Date();

      today.setDate(today.getDate() + parseInt(diff));
      var dd = today.getDate(),
        mm = today.getMonth() + 1,
        yyyy = today.getFullYear();
      if (dd < 10) {
        dd = '0' + dd;
      }
      if (mm < 10) {
        mm = '0' + mm;
      }
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },

    now: function (separator, type) {
      if (typeof(separator) == 'undefined') {
        separator = '.';
      }
      var a = new Date();
      var year = a.getFullYear();
      var month = a.getMonth() + 1;
      var date = a.getDate();
      var hour = a.getHours();
      var min = a.getMinutes();
      var sec = a.getSeconds();
      // Kezdő nulla és más csúnyaságok
      month = month < 10 ? '0' + month : month;
      date = date < 10 ? '0' + date : date;
      hour = hour < 10 ? '0' + hour : hour;
      min = min < 10 ? '0' + min : min;
      sec = sec < 10 ? '0' + sec : sec;
      var time = year + separator + month + separator + date + ' ' + hour + ':' + min;
      if (typeof(type) !== 'undefined' && type == 'full') {
        time += ':' + sec;
      }
      return time;
    },

    caseConvert: function (value, newCase) {
      if (newCase === 'lower') {
        return $.trim(value).replace(/ +/g, ' ').toLocaleLowerCase();
      }
      if (newCase === 'upper') {
        return $.trim(value).replace(/ +/g, ' ').toLocaleUpperCase();
      }
    },


    // korábbi slug, ékezetet nem tud
    /**
      slugify: function (text) {
        return text.toString().toLowerCase()
          .replace(/\s+/g, '-')           // Replace spaces with -
          .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
          .replace(/\-\-+/g, '-')         // Replace multiple - with single -
          .replace(/^-+/, '')             // Trim - from start of text
          .replace(/-+$/, '');            // Trim - from end of text
      },
    */

    /**
     *
     * Slugosító, hogy menjenek az ékezetek
     *
     * INNEN:
     * https://gist.github.com/sgmurphy/3095196
     *
     * Create a web friendly URL slug from a string.
     *
     * Requires XRegExp (http://xregexp.com) with unicode add-ons for UTF-8 support.
     *
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string s
     * @param object opt
     * @return string
     */
    slugify: function (s, opt) {
      s = String(s);
      opt = Object(opt);

      var defaults = {
        'delimiter': '-',
        'limit': undefined,
        'lowercase': true,
        'replacements': {},
        'transliterate': (typeof(XRegExp) === 'undefined') ? true : false
      };

      // Merge options
      for (var k in defaults) {
        if (!opt.hasOwnProperty(k)) {
          opt[k] = defaults[k];
        }
      }

      var char_map = {
        // Latin
        'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
        'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
        'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O',
        'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH',
        'ß': 'ss',
        'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c',
        'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
        'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o',
        'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th',
        'ÿ': 'y',

        // Latin symbols
        '©': '(c)',

        // Greek
        'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
        'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
        'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
        'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I',
        'Ϋ': 'Y',
        'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
        'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
        'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
        'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's',
        'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i',

        // Turkish
        'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G',
        'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g',

        // Russian
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
        'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
        'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C',
        'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
        'Я': 'Ya',
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
        'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c',
        'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
        'я': 'ya',

        // Ukrainian
        'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G',
        'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g',

        // Czech
        'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U',
        'Ž': 'Z',
        'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u',
        'ž': 'z',

        // Polish
        'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z',
        'Ż': 'Z',
        'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z',
        'ż': 'z',

        // Latvian
        'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N',
        'Š': 'S', 'Ū': 'u', 'Ž': 'Z',
        'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n',
        'š': 's', 'ū': 'u', 'ž': 'z'
      };

      // Make custom replacements
      for (var k in opt.replacements) {
        s = s.replace(RegExp(k, 'g'), opt.replacements[k]);
      }

      // Transliterate characters to ASCII
      if (opt.transliterate) {
        for (var k in char_map) {
          s = s.replace(RegExp(k, 'g'), char_map[k]);
        }
      }

      // Replace non-alphanumeric characters with our delimiter
      var alnum = (typeof(XRegExp) === 'undefined') ? RegExp('[^a-z0-9]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
      s = s.replace(alnum, opt.delimiter);

      // Remove duplicate delimiters
      s = s.replace(RegExp('[' + opt.delimiter + ']{2,}', 'g'), opt.delimiter);

      // Truncate slug to max. characters
      s = s.substring(0, opt.limit);

      // Remove delimiter from ends
      s = s.replace(RegExp('(^' + opt.delimiter + '|' + opt.delimiter + '$)', 'g'), '');

      return opt.lowercase ? s.toLowerCase() : s;
    },

    // Ez ezt okozza: Uncaught RangeError: Maximum call stack size exceeded
    replaceAll__: function (omit, place, string, prevstring) {
      if (prevstring && string === prevstring)
        return string;
      prevstring = string.replace(omit, place);
      return this.replaceAll(omit, place, prevstring, string)
    },

    replaceAll: function (from, to, string) {
      if (typeof string == 'undefined') {
        return '';
      }
      // ha numeric a string, akkor a lenti regepx hibára fut
      string = string.toString();
      return string.replace(new RegExp(from, 'g'), to);
    },

    decodeHTMLEntities: function (text) {
      return $('<textarea/>').html(text).text();
    },

    numberFormat: function (number, sep) {
      sep = typeof sep == "undefined" ? ' ' : sep;
      number = typeof number != "undefined" && number > 0 ? number : "";
      number = number.replace(new RegExp("^(\\d{" + (number.length % 3 ? number.length % 3 : 0) + "})(\\d{3})", "g"), "$1" + sep + "$2").replace(/(\d{3})+?/gi, "$1.");
      number = (number.length && number[0] == '.') ? number.slice(1) : number;
      number = (number.length && number[number.length - 1] == '.') ? number.slice(0, -1) : number;
      if (sep != ".") {
        number = number.replace(/\s/g, sep);
      }
      return number == '' ? '-' : number;
    },

    number: function(n, d) {
      d = typeof d == 'undefined' ? 0 : d;
      return '<span class="small-spaces text-nowrap">' + this.numberFormat(n.toFixed(d)) + '</span>';
    },


    /**
     * Egyszerű logikával kiírja a távolságot
     * @param distance
     * @param options
     * @returns {string}
     */
    show_distance: function(distance, options) {
      return parseInt(distance) < 1000 ? parseInt(distance) + 'm' : Helper.number(distance/1000,1) + 'km';
    },

    distance: function (lat1, lon1, lat2, lon2, unit) {
      if ((lat1 == lat2) && (lon1 == lon2)) {
        return 0;
      }
      else {
        var radlat1 = Math.PI * lat1/180;
        var radlat2 = Math.PI * lat2/180;
        var theta = lon1-lon2;
        var radtheta = Math.PI * theta/180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        if (dist > 1) {
          dist = 1;
        }
        dist = Math.acos(dist);
        dist = dist * 180/Math.PI;
        dist = dist * 60 * 1.1515;
        // km-re
        dist = dist * 1.609344;

        if (typeof unit != 'undefined' && unit == 'm') {
          dist = Math.round(dist * 1000);
        }

        return dist;
      }
    },

    /**
     * Egy asszoc. tömb egy elemét adja vissza key_ alapján
     * @param array
     * @param key_name
     * @param key_value
     * @returns {boolean}
     */
    getArrayItem: function (array, key_name, key_value) {
      var item = false;
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          if (elem[key_name] == key_value) {
            item = array[index];
          }
        });
      }
      return item;
    },

    /**
     * Egy asszoc. tömb egyik elemét írjuk vissza megváltozott paraméterekkel key_ alapján
     * @param array
     * @param key_name
     * @param key_value
     * @param new_item
     * @returns {boolean}
     */
    replaceArrayItem: function (array, key_name, key_value, new_item) {
      var item = false;
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          if (elem[key_name] == key_value) {
            item = true;
            array[index] = new_item;
          }
        });
      }
      return item;
    },

    /**
     * Egy asszoc. tömb key_ által megfogott elemének egy értékét módosítjuk
     * @param array
     * @param key_name
     * @param key_value
     * @param set_key_name
     * @param set_key_value
     * @returns {boolean}
     */
    updateArrayItem: function (array, key_name, key_value, set_key_name, set_key_value) {
      var item = false;
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          if (elem[key_name] == key_value) {
            item = array[index];
            item[set_key_name] = set_key_value;
            array[index] = item;
          }
        });
      }
      return array;
    },

    /**
     * Egy asszoc tömb összes elemének egy értékét frissítjük
     * @param array
     * @param set_key_name
     * @param set_key_value
     * @returns {*}
     */
    updateArray: function (array, set_key_name, set_key_value) {
      var item = false;
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          item = array[index];
          item[set_key_name] = set_key_value;
          array[index] = item;
        });
      }
      return array;
    },

    /**
     * Egy asszoc. tömb egy elemét töröljük key_ alapján
     * @param array
     * @param key_name
     * @param key_value
     * @returns {Array}
     */
    delArrayItem: function (array, key_name, key_value) {
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          if (elem[key_name] == key_value) {
            array.splice(index, 1);
          }
        });
        return array;
      }
      array = [];
      return array;
    },

    /**
     * Ellenőrizzük, hogy egy asszoc. tömbben szerepel-e egy elem key_ alapján
     * @param array
     * @param key_name
     * @param key_value
     * @returns {boolean}
     */
    inArrayByKeyValue: function (array, key_name, key_value) {
      var fffound = false;
      if (typeof array !== 'undefined') {
        $(array).each(function (index, elem) {
          if (elem[key_name] == key_value) {
            fffound = true;
          }
        });
      }
      return fffound;
    },


    /**
     * Összeolvaszt két objektumot obj, src sorrendben
     * asszem van ilyen a jqueryben... bah.
     * @param obj
     * @param src
     * @returns {*}
     */
    extendObject: function (obj, src) {
      for (var key in src) {
        if (src.hasOwnProperty(key)) obj[key] = src[key];
      }
      return obj;
    },


    /**
     * ucfirst, like php, de itt ilyen nincs
     * @param string
     * @returns {string}
     */
    ucfirst: function (string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },


    /**
     * Formok objektummá alakítása
     * @param form
     * @param excluded_keys
     */
    formToObject: function (form, excluded_keys) {
      var formData = {};
      var formJSON = $(form).serializeJSON();
      var formObject = JSON.parse(formJSON);

      var excluded_keys = typeof excluded_keys == 'undefined' ? [] : excluded_keys;

      $.each(formObject, function(key, elem) {
        //console.log(elem);
        if ($.inArray(key, excluded_keys) === -1) {
          formData[key] = typeof elem == 'object' ? Helper.cleanObject(elem) : elem;
        }
      });

      return formData;
    },


    /**
     * Üres elemek takarítása
     * @param obj
     * @returns {*}
     */
    cleanObject: function (obj) {
      var filtered = obj.filter(function (el) {
        return el != null;
      });

      return filtered;
    },


    /**
     * Objektum sorbarendezés kulcs szerint
     * https://stackoverflow.com/a/31102605/1118965
     * @param obj
     */
    sortObject: function(obj) {
      var ordered = {};
      Object.keys(obj).sort().forEach(function(key) {
        ordered[key] = obj[key];
      });
      return ordered;
    },


    /**
     * Üres elemek takarítása
     * @param array
     * @returns {Array}
     */
    cleanArray: function(array) {
      var array_ = [];
      $(array).each(function(key, elem) {
        if (typeof elem != 'undefined' && elem != null) {
          array_[eval(key)] = $.makeArray(elem);
        }
      });
      return array_;
    },


    /**
     * JSON konvertálás, ha az, különben false
     * @param str
     * @returns {*}
     */
    pJson: function (str) {
      try {
        var object = JSON.parse(str);
      } catch (e) {
        return false;
      }
      return object;
    },


    arraize: function(string, delimiter) {
      var array;
      if (string != '' && string.indexOf(delimiter)) {
        array = string.split(delimiter);
      } else {
        array = [string];
      }
      return array;
    },


    /**
     * hihi.
     * @param array
     * @returns {*}
     */
    arrayWithoutMe: function(array) {
      if ($app.auth) {
        var me = $('.whatsmyname').html();
        array.splice(array.indexOf(me), 1);
        return array;
      } else {
        return array;
      }
    },

    /**
     * Típustól függő fájl előkép
     * @param type
     * @param src
     * @returns {string}
     */
    filePreview: function(type, src, image_class, icon_class, downloadable_id) {
      var preview,
        image_class = typeof image_class == 'undefined' ? 'img-thumbnail' : image_class,
        icon_class = typeof icon_class == 'undefined' ? 'fa-4x fa-border text-muted' : icon_class;

      if (typeof downloadable_id != 'undefined' && downloadable_id > 0) {
        var dl = '<div class="small mt-2 text-light">A fájlt a "Fájl letöltése" gombra kattintva töltheted le.</div>';
      } else {
        var dl = '';
      }

      switch (true) {
        case (type).indexOf('image') > -1:
          preview = '<img src="' + src + '" class="' + image_class + '" id="previmage" />';
          break;
        case (type).indexOf('pdf') > -1:
          preview = '<span class="far fa-file-pdf ' + icon_class + '"></span>' + dl;
          break;
        case (type).indexOf('text/plain') > -1:
          preview = '<span class="far fa-file-alt ' + icon_class + '"></span>' + dl;
          break;
        case (type).indexOf('excel') > -1:
          preview = '<span class="far fa-file-excel ' + icon_class + '"></span>' + dl;
          break;
        case (type).indexOf('word') > -1:
          preview = '<span class="far fa-file-word ' + icon_class + '"></span>' + dl;
          break;
        case (type).indexOf('audio') > -1:
          preview = '<span class="far fa-file-audio ' + icon_class + '"></span>' + dl;
          break;
        case (type).indexOf('video') > -1:
          preview = '<span class="far fa-file-video ' + icon_class + '"></span>' + dl;
          break;
        default:
          preview = '<span class="far fa-file ' + icon_class + '"></span>' + dl;
          break;
      }

      return preview;
    },


    /**
     * Startpoint és Endpoint közt megmondja az irányt
     * Innen: https://stackoverflow.com/a/22740092/1118965
     * pici bugfix/módosítás volt benne
     * @param endpoint
     * @param startpoint
     * @returns {*[]}
     */
    getDirection: function(endpoint, startpoint) {
      var x1 = endpoint[0],
      y1 = endpoint[1],
      x2 = startpoint[0],
      y2 = startpoint[1];

      var radians = getAtan2((y1 - y2), (x1 - x2));

      function getAtan2(y, x) {
        return Math.atan2(y, x);
      };

      var compassReading = radians * (180 / Math.PI);

      // 0-260-ig alakítom
      var rotation = compassReading > 0 ? compassReading : 360 + compassReading;

      var coordNames = ["É", "ÉK", "K", "DK", "D", "DNy", "Ny", "ÉNy", "É"];
      var coordIndex = Math.round(compassReading / 45);
      if (coordIndex < 0) {
        coordIndex = coordIndex + 8
      };

      return [parseInt(rotation), coordNames[coordIndex]];
    },


    /**
     * Minden intervalt elállít
     */
    killAllIntervals: function() {
      $.each($app.ic, function(key, name) {
        clearInterval($app.ic[name]);
      });
    },


    getFavicon: function(){
      var favicon = undefined;
      var nodeList = document.getElementsByTagName("link");
      for (var i = 0; i < nodeList.length; i++)
      {
        if((nodeList[i].getAttribute("rel") == "icon")||(nodeList[i].getAttribute("rel") == "shortcut icon"))
        {
          favicon = nodeList[i].getAttribute("href");
        }
      }
      return favicon;
    },


    loadDiv: function(url, target_div) {
      var rand_class = 'ajaxdiv-' + Helper.randId();
      $(target_div).html(Html.loading()).addClass('ajaxdiv ' + rand_class);
      Http.get(url, function(response) {
        if (typeof response.body != undefined) {
          $(target_div).html(response.body);
          Layout.initListeners('.' + rand_class);
          if ($(target_div).find('.comment-thread')) {
            Comments.build_thread(false);
          }
        } else {
          $(target_div).html('<div class="text-danger my-2">'
            + '<span class="fal fa-exclamation-triangle mr-1">'
            + '</span>Betöltési hiba.</div>')
        }
      });
    },

  };
