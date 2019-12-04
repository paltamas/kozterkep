var s,
  Webstat = {

    settings: {

    },

    init: function () {

      var that = this,
        path = window.location.pathname;

      this.visit(path, true);

      $app.ic['visits'] = window.setInterval(function () {
        if (Store.get('windowVisible') == 1) { // csak ha itt vagyunk tényleg
          that.visit(path, false);
        }
      }, $app.visit_interval * 1000);

      this.load_view_stats();
    },

    /**
     * Látogatás rögzítése
     * @param path
     */
    visit: function(path, visit) {

      Http.request('post', {
        'path': 'api/visits',
        'data': {
          visit: visit ? 1 : 0, // false = itt van csak
          path: path,
          full_path: $app.here,
          referrer: document.referrer,
          vp: $app.model,
          vi: $app.model_id,
        },
        'success': function(response) {
          //_c(response);
        },
        'options': {
          silent: true
        }
      });
    },




    /**
     *
     * Ha az oldalon van view stat, betölti azt
     * A műlapokon kívül mindenre jó. A műlapoknál azért nem ezt használjuk,
     * hogy ne növeljük a request számot. Ott ezt is más dolgokkal
     * együtt kérjük le.
     *
     */
    load_view_stats: function() {
      if (!$('.model-view-stats')[0] || $app.model == '' || $app.model_id == '') {
        return;
      }

      Http.request('get', {
        'path': 'api/visits/view_stats?model=' + $app.model + '&model_id=' + $app.model_id,
        'success': function(response) {

          if (typeof response['view_total'] != 'undefined') {
            var s = '';
            s += Helper.number(response['view_total']) + ', eheti: ' + Helper.number(response['view_week']);
            s += Html.link('', '/webstat/oldalak?vp=' + $app.model + '&vi=' + $app.model_id, {
              'icon': 'chart-line fa-fw',
              'title': 'További részletek a Webstaton',
              'class': 'ml-2'
            });
          } else {
            var s = '-';
          }

          $('.model-view-stats').html(s);

        },
        'options': {
          silent: true
        }
      });
    },

  };