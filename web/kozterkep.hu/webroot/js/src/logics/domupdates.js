var s,
  Domupdates = {

    settings: {
    },

    init: function () {
      var that = this;

      this.bindUIActions();
      this.updateTimes($app.time_interval * 1000);

      // Init
      this.setAlertCounts(true);

      // Folyamatos figyelések
      setInterval(function () {
        that.setAlertCounts(false);
        that.removeRemovables();
      }, 1000);

    },

    bindUIActions: function () {

      var that = this;

    },


    /**
     * DOM időbélyegek frissítése eltelt idővel
     *
     * Azért nem vizsgáljuk, hogy létezik-e a [ia-timestamp],
     * mert lehet, hogy az első kör előtt még nincs, és akkor be se indul
     * a buli.
     *
     * @param interval
     */
    updateTimes: function (interval) {

      var that = this;
      $app.ic['time_updates'] = setInterval(function() {
        $('[ia-timestamp]').each(function(key, elem) {
          $(elem).html(Helper.timeAgoInWords($(elem).attr('ia-timestamp')));
        });
      }, interval);

    },


    // Közös alert számállító és favikon bizgető
    setAlertCounts: function (init) {
      if ($app.auth && $app.user_pause == 0) {
        // Ha nincs külön csak az értesítés kikapcsolva, akkor összeadjuk a kettőt
        if ($app.user_notification_pause == 0) {
          var sum_count = Number(Store.get('conversation-count')) + Number(Store.get('notification-count'));
        } else {
          var sum_count = Number(Store.get('conversation-count'));
        }
        var alert_count = Store.get('alert-count');

        sum_count = sum_count > 0 ? sum_count : 0;
        alert_count = alert_count > 0 ? alert_count : 0;

        if (sum_count > 0) {
          $('.sum-alert-count').removeClass('px-2').addClass('bg-orange-dark px-1').html(sum_count);
          Layout.changeFavicon(true);
        } else {
          $('.sum-alert-count').removeClass('bg-orange-dark px-1').addClass('px-2').html('&nbsp;');
          Layout.changeFavicon(false);
        }

        Store.set('alert-count', sum_count);
      }
    },


    /**
     * Kiszedjük azokat a dolgokat, amiket ki lehet már
     */
    removeRemovables: function() {
      $('[ia-removeafter]').each(function(key, elem){
        if ($(this).attr('ia-removeafter') < Date.now()) {
          $(this).slideDown(300).remove();
        }
      });
    },


    /**
     * Képeket jelenít meg dom-ba írt adatok alapján
     * @todo: igazából nem érzi, hogy megvan-e csinálva,
     * ezért nem lehet meghívni többször egymás után ugyanarra
     */
    showPreviews: function(parent) {
      var parent = typeof parent != 'undefined' && parent != '' ? parent + ' ' : '';
      if ($(parent + '.file-attachment')[0]) {
        $(parent + '.file-attachment').each(function(key, elem) {
          // Újra kiolvassuk, hogy lássuk, nincs-e megcsinálva
          if (!$(parent + '#image-preview-' + $(elem).attr('id')).length) {
            Helper.urlIsImage($(elem).attr('href'), function() {

              var image = '<img src="' + $(elem).attr('href') + '" class="img-fluid img-thumbnail w-25"/>';
              var image_link = Html.link(image, $(elem).attr('href'), {
                target: '_blank'
              });

              var s = $("<div/>", {
                class: 'mt-1',
                id: 'image-preview-' + $(elem).attr('id'),
                html: image_link
              });

              $(elem).after(s);
            });
          }
        });
      }
    },


  };