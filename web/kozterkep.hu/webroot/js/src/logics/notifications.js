var s,
  Notifications = {

    settings: {
    },

    init: function () {
      s = this.settings;

      if ($app.auth && $app.user_pause == 0 && $app.user_notification_pause == 0 && Store.get('notification-count') > 0) {
        this.setCount(Store.get('notification-count'));
      }

      if ($app.user_pause == 1 || $app.user_notification_pause == 1) {
        $('.notifications-empty').html('Kikapcsoltad az értesítéseket, ' + Html.link('kezelés', '/tagsag/beallitasok#ertesitesek') + '.');
        $('.notification-icon-container span.far').removeClass('fa-bell').addClass('fa-bell-slash');
      }

      this.bindUIActions();
    },

    bindUIActions: function () {

      var that = this;

    },


    /**
     * Szám módosítása kapott értékre
     * @todo: mobil menü
     * @param count
     */
    setCount: function(count) {
      // Mentés
      Store.set('notification-count', count);

      // Fejlécben és máshol
      if (count > 0) {
        $('.notification-count').addClass('bg-orange-dark').html(count);
        $('.navlink-notification-count').html(' (' + count + ')');
      } else {
        $('.notification-count').removeClass('bg-orange-dark').html('&nbsp;');
        $('.notification-item').remove();
        $('.notifications-empty').show();
        $('.navlink-notification-count').html('');
      }
    },


    /**
     * Lista építés kapott objektumból
     * full_creation = true esetén nulláról építünk,
     * eszerint számolunk is
     *
     * @param object
     * @param full_creation
     */
    buildList: function(object) {

      var that = this,
        start_count = 0,
        added_count = 0,
        shown_ids = [];

      // Megjelenítés
      if (typeof object != "undefined" && object.length > 0) {
        // Van
        $('.notifications-empty').hide();

        $(object).each(function(key, elem) {
          // Ha nincs benne, hozzáadjuk
          if (!$('.notification-' + elem.id)[0]) {

            if (typeof elem.link != 'undefined') {
              var link = elem.link,
                link_class = ' cursor-pointer';

              // Ha épp ott vagyunk, amire mutat ez a noti, akkor olvasottá tesszük
              if (elem.link == $app.here) {
                Notifications.read_toggle(elem.id);
              }
            } else {
              var link = '',
                link_class = '';
            }

            var item = '<div class="dropdown-item p-2 border-bottom notification-' + elem.id + ' notification-item" id="notification-' + elem.id + '">'
              + '<div class="' + link_class +'" ia-href="' + link + '"'
                + ' ia-bind="notifications.read_toggle" ia-pass="' + elem.id + '" ia-hide=".notification-' + elem.id + '" '
                + ' ia-target=".list-notification-' + elem.id + ' .readToggle .far" ia-toggleclass="fa-dot-circle fa-circle" '
              + '>'
              + '<span class="font-weight-bold">' + elem.title + '</span>&nbsp;'
              + '<span class="text-muted">' + elem.content + '</span>'
              + '</div>'
              + '<div class="row">'
              + '<div class="col-4' + link_class + '" ia-href="' + link + '">'
              + '<small class="text-muted text-nowrap"><span class="far fa-clock"></span> '
                + Helper.timeConverter(elem.created, 'short', true) + '</small>'
              + '</div>'
              + '<div class="col-8 text-right">'
              + '<small class="text-muted">'
              + '<a href="#notification-' + elem.id + '" class="mr-2" '
                + ' ia-bind="notifications.read_toggle" ia-pass="' + elem.id + '" ia-hide=".notification-' + elem.id + '" '
                + ' ia-target=".list-notification-' + elem.id + ' .readToggle .far" ia-toggleclass="fa-dot-circle fa-circle">'
              + '<span class="far fa-lg p-1 fa-dot-circle fa-lg"></span>'
              + '</a>'
              + '</small>'
              + '</div>'
              + '</div>'
              + '</div>';
            $('.notification-list').prepend(item);
          }
          shown_ids[elem.id] = true;

        });

        $('.notification-item').last().removeClass('border-bottom');

        // Végigpörgetjük, ami kint van, hogy minden kell-e még
        // mert közben más eszközön olvasottá tehettük
        $('.notification-item').each(function(key, elem) {
          var id = $(this).attr('id').split('-')[1];
          if (typeof shown_ids[id] == 'undefined') {
            $('#notification-' + id).remove();
          }
        });
      }

      // Mentjük és kijelezzük az új számot, ha volt bővítés, ha nem
      var new_count = typeof object != "undefined" ? object.length : 0;
      Store.set('notification-count', new_count);

      if ($app.auth && $app.user_pause == 0 && $app.user_notification_pause == 0) {
        that.setCount(new_count);
      }
    },


    /**
     * Értesítést olvasottá/-lanná tesszük
     * @param pass
     */
    read_toggle: function(pass) {

      var that = this;

      Http.put(
        'api/notifications/put/' + pass,
        { 'read_toggle': 1 },
        function(response) {
          Users.getLatest();
          // Csak itt tudom megoldani a lista színezést...
          $('.list-notification-' + pass).toggleClass('bg-yellow-light');
          $('.list-notification-' + pass + ' .title').toggleClass('font-weight-bold');

          // Trükkösen itt léptetek, hogy "azonnali" legyen az élmény a számlálónál
          if ($('.list-notification-' + pass + ' .title').hasClass('font-weight-bold')) {
            var new_count = parseInt(Store.get('notification-count')) + 1;
          } else {
            var new_count = parseInt(Store.get('notification-count')) - 1;
          }
          Store.set('notification-count', new_count);
          that.setCount(new_count);
        }
      );

    },


    /**
     * Minden értesítést olvasottá teszünk
     * @param pass
     */
    read_all: function(pass) {

      var that = this;

      Http.post(
        'api/notifications/read_all/',
        {  },
        function(response) {
          Users.getLatest(); // majd legközelebb lefrissül
          $('.list-notification').removeClass('bg-yellow-light');
          $('.list-notification .title').removeClass('font-weight-bold');
          $('.list-notification .readToggle span').removeClass('fa-dot-circle').addClass('fa-circle');
          Store.set('notification-count', 0);
          that.setCount(0);
        }
      );

    },


  };
