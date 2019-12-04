var s, Alerts = {

  settings: {
  },

  init: function () {
    var that = this;
    s = this.settings;
  },


  /**
   * Kiírja, ha a rendereléskor lát kiírandót (js_loader-ben adjuk meg)
   */
  flashOut: function() {
    var that = this;

    if ($app.flash.length > 0) {
      $($app.flash).each(function(key, item) {
        if (item[2] == 'modal') {
          that.flashModal(item[0], item[1], {'removeAfter': item[3]});
        } else if (item[2] == 'bubble') {
          that.flashBubble(item[0], item[1], {'removeAfter': item[3]});
        } else {
          that.flashDiv(item[0], item[1], {'removeAfter': item[3]});
        }
      });
    }

    $('.alertContainer').on('closed.bs.alert', function () {
      $(this).parent('.alertContainer').remove();
    });
  },


  /**
   * Fenti divbe írja ki
   * @param text
   * @param type
   * @param options
   */
  flashDiv: function(text, type, options) {
    options = typeof options == 'undefined' ? [] : options;

    // Alert ID
    var id = typeof options.id == "undefined" ? Helper.randId() : options.id;

    // Alert típus
    if (typeof type == "undefined") {
      type = 'info';
    }

    // Extra class az alert doboznak
    if (typeof options.extraClass == "undefined") {
      options.extraClass= '';
    }

    // Megjelenítés előtt minden mást töröl
    if (typeof options.delBefore != "undefined" && options.delBefore) {
      $('.alertContainer').remove();
    }

    // Megjelenítés előtt törli az azonos ID-jű alertet
    if (typeof options.delSameId != "undefined" && options.delSameId) {
      $('.alertContainer#alert-' + id).remove();
    }

    // Automatikus bezáródás
    if ((type == 'info' || type == 'success')
      && (typeof options.removeAfter != 'undefined' && options.removeAfter > 0)) {
      var plus = options.removeAfter == 'undefined' ? 4000 : parseInt(options.removeAfter) * 1000;
      plus = Math.min(plus, 4000);
      var removeafter = ' ia-removeafter="' + (_now() + plus) + '" ';
    } else {
      var removeafter = '';
    }

    // Hol írjuk ki
    var target = typeof options.target == 'undefined' ? 'body main' : options.target;

    // Bezárható-e
    var dismissable = typeof options.dismissable == 'undefined' ? true : options.dismissable;

    var s = '<div class="container"><div class="row clear alertContainer" ' + removeafter + ' id="alert-' + id + '">'
      + '<div class="col-md-12 px-0">'
      + '<div class="alert alert-' + type + ' alert-dismissible fade show ' + options.extraClass + '" role="alert">'
      + text;

    if (dismissable) {
      s += '<a href="#" class="close" data-dismiss="alert" aria-label="Bezárás">'
        + '<span aria-hidden="true" class="far fa-times-circle"></span>'
        + '</a>';
    }

    s += '</div>'
      + '</div>'
      + '</div>'
      + '</div>';

    $(target).prepend(s);
  },


  /**
   * Alsó buborékba írja ki
   * @param text
   * @param type
   * @param delBefore
   */
  flashBubble: function(text, type, options) {
    options = typeof options == 'undefined' ? [] : options;

    // Alert ID
    var id = typeof options.id === "undefined" ? Helper.randId() : options.id;

    // Alert típus
    if (typeof type === "undefined") {
      type = 'info';
    }

    // Megjelenítés előtt minden mást töröl, ha mondjuk vagy ha nem mondjuk és infó
    if ((typeof options.delBefore != "undefined" && options.delBefore)
      || (typeof options.delBefore == "undefined" && type == 'info')) {
      $('.alertContainer').remove();
    }

    // Megjelenítés előtt törli az azonos ID-jű alertet
    if (typeof options.delSameId != "undefined" && options.delSameId) {
      $('.alertContainer#alert-' + id).remove();
    }

    // Automatikus bezáródás
    if ((type == 'info' || type == 'success' || type == 'warning')
      && ((typeof options.removeAfter != 'undefined' && options.removeAfter > 0)
        || typeof options.removeAfter == 'undefined')) {
      var plus = options.removeAfter == 'undefined' ? 4000 : parseInt(options.removeAfter) * 1000;
      plus = Math.min(plus, 4000);
      var removeafter = ' ia-removeafter="' + (_now() + plus) + '" ';
    } else {
      var removeafter = '';
    }

    var bubble = '<span class="d-block alert alert-' + type + ' alert-dismissible alertContainer" ' + removeafter + ' role="alert" id="alert-' + id + '">'
      + text
      + '<a href="#" class="close" data-dismiss="alert" aria-label="Bezárás">'
      + '<span aria-hidden="true" class="far fa-times-circle"></span>'
      + '</a>'
      + '</span>';
    $(bubble).appendTo('.bubbleContainer').hide(0).slideDown(300);
  },

  /**
   * Modalba írja ki
   * @param text
   * @param type
   */
  flashModal: function (text, type, options) {
    options = typeof options == 'undefined' ? [] : options;

    var id = Helper.randId();
    if (typeof type === "undefined") {
      type = 'info';
    }

    // Minden előzőt kukázunk, mert zavaró több egymáson
    $('.modal').modal('hide');
    $('.modal.alertContainer').remove();

    if ((type == 'info' || type == 'success')
      && (typeof options.removeAfter != 'undefined' && options.removeAfter > 0)) {
      var plus = options.removeAfter == 'undefined' ? 4000 : parseInt(options.removeAfter) * 1000;
      plus = Math.min(plus, 4000);
      var removeafter = ' ia-removeafter="' + (_now() + plus) + '" ';
    } else {
      var removeafter = '';
    }

    $('body').append('<div class="modal alertContainer" id="alert-' + id + '" ' + removeafter + ' tabindex="-1" role="dialog" aria-hidden="true">'
      + '<div class="modal-dialog" role="document">'
      + '<div class="alert alert-' + type + ' alert-dismissible">'
      + '<a href="#" class="close" data-dismiss="modal" aria-label="Bezárás">'
      + '<span aria-hidden="true" class="far fa-times-circle"></span>'
      + '</a>'
      + text
      + '</div>'
      + '</div>'
      + '</div>'
    );
    $('#alert-' + id).modal();
  },
};