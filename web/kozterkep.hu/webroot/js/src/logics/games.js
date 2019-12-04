var s,
  Games = {

    settings: {
    },

    init: function () {
      s = this.settings;

      var that = this;

      this.bindUIActions();

      if ($('#hug_space')[0] && Store.get('isTouch') == 1) {
        setTimeout(function () {
          // kis késleltetés, hogy már meglegyen az akt legpontosabb koordináta
          that.show_hug_space_div();
          setInterval(function () {
            that.show_hug_space_div();
          }, 3000);
        }, 1000);
      }
    },

    bindUIActions: function () {

      var that = this;

    },


    /**
     * Mutatja a divet, ha elég közel vagyunk.
     *
     * @Kérdés, hogy a dom load után picivel megírt $myPos-t használjuk-e
     * vagy az "utolsó" pozíciót, amit a locastore-ba írtunk.
     * Utóbbi azonnal rendelkezésre áll, de könnyebben hekkelhető,
     * és lehet egy beragadt utolsó hely is simán.
     */
    show_hug_space_div: function() {
      if ($myPos) {
        var div = '#hug_space',
          lat = parseFloat($(div).data('lat')),
          lon = parseFloat($(div).data('lon'));

        var myPos = JSON.parse(Store.get('map_mypos')),
          myLat = parseFloat(myPos[0]),
          myLon = parseFloat(myPos[1]);

        var distance = Helper.distance(myLat, myLon, lat, lon, 'm');

        // X méteren belül már mutatni kezdjük a divet
        if (distance <= $sDB['limits']['games']['hug_container_distance']) {
          $(div).removeClass('d-none');

          var direction = Helper.getDirection([parseFloat(lat), parseFloat(lon)], $myPos);
          $(div).find('#distance-container').html('<strong>' + distance + '</strong> méterre tőled <span class="far fa-compass ml-3 mr-1"></span>' + direction[1] + '');
          if (distance > $sDB['limits']['games']['hug_distance']) {

            // Megnézzük, nem érintette-e már meg mostanában
            var get_closer_info = $sDB['limits']['games']['hug_distance'] + ' méteren belül megérintheted';
            if ($('.event-row[data-type=7]')[0]) {
              $('.event-row[data-type=7]').each(function(key, elem) {
                var days = $sDB['limits']['games']['hug_days'];
                var ts = Math.round((new Date()).getTime() / 1000);
                if (parseInt($(elem).data('user')) == 1
                  && parseInt($(elem).data('time')) > ts - (24*60*60*days)) {
                  get_closer_info = 'Az utolsó érintésed után legalább ' + days + ' nappal érintheted újra az alkotást.';
                }
              });
            }

            $(div).find('#distance-container').append('<div class="get-closer-info px-2"><span class="text-muted small">'
              + get_closer_info + '</div>')
          }
        } else if (!$(div).hasClass('d-none')) {
          $(div).addClass('d-none');
          $(div).find('#distance-container').html('');
        }

        if (distance <= $sDB['limits']['games']['hug_distance']) {
          $('.hug-button').removeClass('d-none');
          $('.spacecapsule-button').removeClass('d-none');
        } else if (!$('.hug-button').hasClass('d-none')) {
          $('.hug-button').addClass('d-none');
          $('.spacecapsule-button').addClass('d-none');
        }
      }
    },


    add_hug: function(pass, vars, redirect, elem) {
      $('.add-hug-button').before(Html.loading('my-3 text-center add-hug-load'));

      Http.post('api/games/add_hug', {
        artpiece_id: pass,
        my_lat: $myPos[0],
        my_lon: $myPos[1],
      }, function (response) {

        $('.add-hug-load').remove();

        if (response['too_far']) {

          $('.add-hug-button').after('<div class="alert alert-warning font-weight-bold">Hupsz, közben úgy tűnik eltávolodtál az alkotástól?</div><p>A böngésződ olyan helyzetet adott át, ami alapján már nem vagy elég közel a műhöz. Ha DE, akkor frissítsd az oldalt, hátha magára talál az eszközöd.</p>').remove();

        } else if (response['success']) {

          $('.add-hug-button').after('<div class="alert alert-success font-weight-bold">Mentettük az értintésedet!</div><p>Ha már erre jársz, kérjük fusd át a műlapot, hogy minden adat megfelelő-e rajta, és van-e elég kép hozzá. Ez egy értékes pillanat, te pedig értékes személy vagy most az alkotás történetének digitalizálásában. Segíts nekünk!</p>').remove();

          $('.near-info').remove();

          // Tyrühááá!!! :D
          Layout.explode('circle');

        } else {
          Alerts.flashBubble(_text('varatlan_hiba'), 'danger');
        }

      });
    },


  };
