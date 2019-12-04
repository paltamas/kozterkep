var s,
  Users = {

    settings: {},

    init: function () {
      s = this.settings;

      this.bindUIActions();

      this.getLatest();
      clearTimeout($app.ic['latest']);
      this.pollLatest($app.latest_interval * 1000);
    },

    bindUIActions: function () {

      var that = this;

      this.follows();

    },


    /**
     * Egyszeri lekérés, minden újdonságra
     */
    getLatest: function () {

      var that = this;

      if ($app.auth && $app.user_pause == 0) {
        Http.get(
          'api/updates',
          function (response) {
            if ($app.auth && $app.user_notification_pause == 0) {
              Notifications.buildList(response['notifications'], true);
            }
            Conversations.buildList(response['conversations'], true);
          }
        );
      }

    },


    /**
     * Pollozás minden olvasatlan újdonságra
     *
     * @param interval
     */
    pollLatest: function (interval) {

      var that = this;

      if ($app.auth && $app.user_pause == 0) {
        $app.ic['latest'] = window.setTimeout(function () {
          Http.get(
            'api/updates',
            function (response) {
              if ($app.auth && $app.user_notification_pause == 0) {
                Notifications.buildList(response['notifications'], true);
              }
              Conversations.buildList(response['conversations'], true);
              that.pollLatest(interval);
            }
          );
        }, interval);
      }
    },


    /**
     * UI beállítások
     * @param pass
     */
    tiny_settings: function (pass, vars, redirect, elem) {
      var that = this;

      // Select button típusú dolgok
      if (typeof vars.toggle_button != 'undefined' && vars.toggle_button != '') {
        var variable_name = vars.toggle_button;
        vars = {};
        vars[variable_name] = $(elem).val();
      }

      Http.put(
        'api/users',
        $.extend({tiny_settings: 1}, vars),
        function (response) {
          // .. intézi a Layout.switchView()
        }
      );
    },


    /**
     * Követés és a követő gombok állapotának betöltése
     */
    follows: function () {
      if (!$app.auth) {
        return;
      }

      $('[ia-follow]').removeClass('d-none');

      // Jelölés
      $(document).on('click', '[ia-follow]', function (e) {
        e.preventDefault();

        var that = this;

        if ($(this).attr('ia-follow') == 'this') {
          var model = $app.model,
            model_id = $app.model_id;
        } else {
          var model = $(this).attr('ia-follow').split(':')[0],
            model_id = $(this).attr('ia-follow').split(':')[1];
        }

        if (model != '' && (model_id > 0 || model_id != '')) {
          Http.put(
            'api/follows/toggle',
            {
              model: model,
              model_id: model_id,
            },
            function (response) {
              if (response.success == 1) {
                // Follow ON
                $(that).find('.link-text').html('<span class="d-none d-md-inline">Követed</span>');
                $(that).find('.fa-star').removeClass('far').addClass('fas text-primary');
              } else {
                // Follow OFF
                $(that).find('.link-text').html('<span class="d-none d-md-inline">Követés</span>');
                $(that).find('.fa-star').removeClass('fas text-primary').addClass('far');
              }
            }
          );
        }

      });

      // Gombok állapota
      var follows = [],
        multi = false,
        single = false;
      $('[ia-follow]').each(function () {
        if ($(this).attr('ia-follow') != 'this') {
          follows.push([
            $(this).attr('ia-follow').split(':')[0],
            $(this).attr('ia-follow').split(':')[1]
          ]);
          multi = true;
        }
      });

      if (!multi && ($app.model_id > 0 || $app.model_id != '')) {
        follows.push([ $app.model, $app.model_id ]);
        single = true;
      }

      if (follows.length > 0) {
        Http.post(
          'api/follows/info',
          { 'data': follows },
          function (response) {
            if (response.length > 0) {
              if (single) {
                $('[ia-follow]').find('.fa-star')
                  .removeClass('far').addClass('fas text-primary');
                $('[ia-follow]').find('.link-text').html('<span class="d-none d-md-inline">Követed</span>');
              } else {
                $(response).each(function () {
                  $('[ia-follow="' + this[0] + ':' + this[1] + '"]').find('.fa-star')
                    .removeClass('far').addClass('fas text-primary');
                });
                $('[ia-follow]').find('.link-text').html('<span class="d-none d-md-inline">Követés</span>');
              }
            }
          }, {
            'silent': true
          });
      }

    },

    logoutClear: function() {
      Store.clearAll();
      Store.set('cookie-accept', 1);
    },


  };
