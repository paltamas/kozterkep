var s,
  Artpieces = {

    artpiece: {
      id: 0,
      status_id: 0,
    },

    edit_form: {
      'changes': -1,
      'last_change_count': 0,
      'data': [],
      'saved': false,
    },

    settings: {},

    init: function () {
      s = this.settings;

      this.artpiece.id = $app.model_id > 0 ? parseInt($app.model_id) : parseInt($('#artpiece_id').val());
      this.artpiece.status_id = parseInt($('#artpiece_status_id').val());

      this.bindUIActions();

      if ($app.model == 'artpieces' && $app.action == 'view') {
        this.artpageLoad(false);
      }

      if ($app.model == 'artpieces' && $app.action == 'edit') {
        this.publication_votes();
        this.checked_votes();
      }
    },

    bindUIActions: function () {
      var that = this;

      if ($app.here == '/mulapok/kozelben') {

        $('.nearby-page-list').html(Html.loading('text-center', 'text-muted', 'Körülnézünk picit...'));

        setTimeout(function() {
          var lastPosition = JSON.parse(Store.get('map_mypos'));
          var lat = $myPos ? $myPos[0] : lastPosition[0];
          var lon = $myPos ? $myPos[1] : lastPosition[1];
          Http.get('api/artpieces?lat=' + lat + '&lon='
            + lon + '&radius=25000' + '&limit=1', function(response) {
            var distance = typeof response[0] != 'undefined' && typeof response[0].d != 'undefined'
              ? Math.min(25000, response[0].d) : 500;
            Artpieces.nearby_page(distance);
          });
        }, 500);
      }

      if ($('#nearby-list')) {
        setTimeout(function() {
          Artpieces.nearby_list();
        }, 500);
      }

      if ($('.artpiece-edit-form')[0]) {
        Artpieces.edit();
        // Indításkor így kiírja, ha a korábbi
        // világban született műlapon lehet vmit javítani
        setTimeout(function() {
          Artpieces.form_check();
        }, 1500);

        // Ezzel figyeljük, ha van új változás, beküldünk egy ellenőrzést
        setInterval(function() {
          if (Artpieces.edit_form.changes > Artpieces.edit_form.last_change_count) {
            Artpieces.edit_form_change($('.artpiece-edit-form'));
          }
        }, $app.here == '/mulapok/letrehozas' ? 1000 : 3000);
      }


      // Modal nyitás és benne az edit
      $('body').on('shown.bs.modal', function(){
        if ($('.modal .edit-votes')[0]) {
          Artpieces.edit_votes_display();
        }
      });
      if ($('.edit-votes')[0]) {
        Artpieces.edit_votes_display();
      }

      // Instant search logika
      var keyTimer;
      $(document).on('keyup', '.instant-search', function() {
        var that = this;
        if (keyTimer) {
          clearTimeout(keyTimer);
        }
        keyTimer = setTimeout(function () {
          Artpieces.instant_search(that);
        }, 500);
      });
    },


    instant_search: function(input) {
      var keyword = $(input).val(),
        target = $(input).data('target') != '' ? $(input).data('target') : false,
        type = $(input).data('type') ? $(input).data('type') : 'full',
        mine = $(input).data('mine') ? $(input).data('mine') : 0,
        for_map = $(input).data('for-map') ? true : false;

      var target_container = target ? target : 'main';

      if (keyword.length > 0) {

        var close_link = Html.link('<span class="fal fa-times-circle"></span>', '#', {
          class: 'close-link text-muted close-instant-search',
          style: 'position: absolute; margin-left: -20px; margin-top: 6px;'
        });

        if (!$('.close-link')[0]) {
          $(input).after(close_link);
        }

        if ($('#map.fullPage')[0] && type == 'full') {
          $(target_container).addClass('p-4');
        }
        $(target_container).html(Html.loading('text-center my-5', 'fa-lg', 'A keresés folyamatban...'));

        var etc_params = type == 'simple' ? '&egyszeru' : '';
        etc_params += for_map ? '&terkepes' : '';
        etc_params += mine == 1 ? '&sajat' : '';

        Http.get($app.path + 'kereses/instant?kulcsszo=' + keyword + etc_params, function(content) {
          $(target_container).html(content.body);
        });

        $('.instant-detail-link').attr('href', '/kereses?kulcsszo=' + keyword);

      } else if (target) {

        $('.instant-detail-link').attr('href', '/kereses');

        $(target_container).html('');
      }

      if (keyword.length == 0) {
        $('.close-link').remove();
      }

      $(document).on('click', '.close-instant-search', function(e) {
        e.preventDefault();
        var prev_content = target ? '' : $app.page_content;
        $(target_container).html(prev_content);
        $('.instant-search').val('');
        $('.close-link').remove();
        if ($('#map.fullPage')[0]) {
          $(target_container).removeClass('p-4');
          // Nem késleltetett setsize se építi újra...
          location.reload();
        }
      });
    },



    /**
     * Szerk. oldal térkép dolgai
     * @param lat
     * @param lon
     */
    map_edit: function(lat, lon, call_nominatim) {
      if (call_nominatim) {
        Http.get('https://nominatim.openstreetmap.org/reverse.php?lat=' + lat + '&lon=' + lon + '&zoom=18&format=json&limit=1&accept-language=en', function (data) {
          $('#Country-code').val(data.address.country_code);
          $('#Address-json').val(JSON.stringify(data.address));
          $($('.artpiece-edit-form')[0]).trigger('change');
          //_c(data);
          $('.nominatim-container').removeClass('d-none');
          $('.nominatim-address').html(data.display_name);
        });
      } else {
        $($('.artpiece-edit-form')[0]).trigger('change');
      }

      $('#Lat').val(lat);
      $('#Lon').val(lon);

      if ($('.nearby-artpieces')[0]) {
        var target_class = '.nearby-artpieces',
          radius = $(target_class).attr('ia-alist-radius') > 0 ? $(target_class).attr('ia-alist-radius') : 500,
          limit = $(target_class).attr('ia-alist-limit') > 0 ? $(target_class).attr('ia-alist-limit') : 200,
          show_direction = $(target_class).attr('ia-alist-showdir') == 'true' ? true : false,
          show_distance = $(target_class).attr('ia-alist-showdist') == 'true' ? true : false;

        $(target_class).html(Html.loading());

        Artpieces.load_nearbys(lat, lon, radius, function (response) {
          Artpieces.thumbnail_list(response, target_class, {
            display_limit: limit,
            show_direction: show_direction,
            show_distance: show_distance,
            div_class: 'col-4 col-md-2 col-lg-1',
          });
        });

        Maps.myMarker.on('dragend', function (e) {
          Maps.myMap.setView([e.target._latlng.lat, e.target._latlng.lng]);
          Artpieces.load_nearbys(e.target._latlng.lat, e.target._latlng.lng, radius, function (response) {
            Artpieces.thumbnail_list(response, target_class, {
              display_limit: limit,
              show_direction: show_direction,
              show_distance: show_distance,
              div_class: 'col-4 col-md-2 col-lg-1',
            });
          });
        });
      }
    },



    /**
     * ID lista (vesszős) betöltése
     * @param id_list
     * @param callback
     */
    load_by_ids: function(id_list, callback) {
      if (typeof id_list == 'undefined' || id_list == '' || !id_list) {
        return;
      }
      Http.get('api/artpieces?ids=' + id_list, function(response) {
        callback(response);
      });
    },



    /**
     * Téglalapon belüliek
     * @param bounds
     * @param callback
     */
    load_by_bounds: function(bounds, callback) {
      if (typeof bounds == 'undefined' || typeof bounds._northEast.lat == 'undefined') {
        return;
      }
      Http.get('api/artpieces?'
        + 'nwlat=' + bounds._northEast.lat + '&nwlon=' + bounds._northEast.lng
        + '&selat=' + bounds._southWest.lat + '&selon=' + bounds._southWest.lng
        + '&padding=0.4'
        + '&psess=' + $app.page_session, function(response) {
        callback(response);
      });
    },

    /**
     * Pont környéke radius (m) szerint
     * @param target
     * @param lat
     * @param lon
     * @param radius
     * @param limit
     * @param callback
     */
    load_nearbys: function(lat, lon, radius, callback) {
      if (typeof lat == 'undefined' || typeof lon == 'undefined' || typeof radius == 'undefined') {
        return;
      }
      Http.get('api/artpieces?lat=' + lat + '&lon=' + lon + '&radius=' + radius, function(response) {
        callback(response);
      });
    },


    /**
     * Műlap kép lista, ha van mit kitenni
     * @param target
     * @param list
     */
    thumbnail_list: function(list, target, options) {
      if (list.length > 0) {
        var options = _arr(options, {
          display_limit: 200,
          show_direction: false,
          show_distance: false,
          image_size: 4,
          div_class: 'col-4 col-md-4 align-top mb-1 p-1 d-flex',
          hide_title: false,
          auto_width: false,
          excluded_id: false,
        });

        var max_width = $(target).attr('ia-alist-img-width') ? $(target).attr('ia-alist-img-width') : 200;
        var div_id = 'artpiece-thumbnail-list-' + Helper.randId();
        $(target).html('<div class="row flex-wrap" id="' + div_id + '"></div>');

        // Hogy ez menjen, nem lehet rejtős tab-contentbe tenni
        if (options.auto_width) {
          var container_width = 'auto';
        } else {
          var container_width = Math.min($(target).width() * 0.33, max_width) + 'px';
        }

        var i = 0;
        $(list).each(function(key, elem) {
          if (!options.excluded_id || (options.excluded_id && elem.i != options.excluded_id)) {
            i++;
            if (i > options.display_limit) {
              return;
            }
            var artpiece_id = typeof elem.i != 'undefined' ? elem.i : elem.id;

            var s = '<div class="' + options.div_class + ' text-wrap" style="width: ' + container_width + ';">'
              + '<div class="border rounded mx-1">'
              // @todo: fájl megjelenítőre váltani! linkelést megcsinálni, tooltip, stb!
              + Html.link(
                '<img src="' + $app.s3_photos + elem.p + '_' + options.image_size + '.jpg" class="img-thumbnail img-fluid presize" />',
                '',
                {
                  artpiece: {id: elem.i, title: elem.t},
                  'ia-tooltip': 'mulap',
                  'ia-tooltip-id': elem.i,
                }
              );

            if (!options.hide_title) {
              s += '<div class="mt-1 text-center smaller">' + Html.link(elem.t, '', {
                  artpiece: {id: elem.i, title: elem.t}
                }) + '</div>';
            }


            // Irány és távolság
            if ($myPos && options.show_direction) {
              s += '<div class="border-top mt-1 text-center pb-1">';
              // Távolság
              if (typeof elem.d !== 'undefined') {
                var distance = Helper.show_distance(elem.d);
                s += ' <div class="d-inline-block w-50 border-right smaller text-muted">' + distance + '</div>';
              }
              // Irány
              var direction = Helper.getDirection([parseFloat(elem.l[1]), parseFloat(elem.l[0])], $myPos);
              //s += '<div class="d-inline-block w-50 py-1"><i class="far fa-location-arrow text-muted" style="transform: rotate(' + direction[0] + 'deg);"></i></div>';
              s += '<div class="d-inline-block w-50 text-muted smaller">' + direction[1] + '</div>';
              s += '</div>';
            } else if (options.show_distance) {
              s += '<div class="border-top mt-1 text-center">';
              // Távolság
              if (typeof elem.d !== 'undefined') {
                var distance = Helper.show_distance(elem.d);
                s += ' <div class="d-inline-block smaller text-muted">' + distance + '</div>';
              }
              s += '</div>';
            }

            s += '</div>'
              + '</div>';

            $('#' + div_id).append(s);
          }
        });
      } else {
        $(target).html('<p class="text-muted">Nincs műlap ezen a területen.</p>');
      }
    },


    nearby_page: function(distance) {
      Layout.alertIfPostblock();
      if (Store.get('map_posblock') == 1) {
        return;
      }

      var target_class = '.nearby-page-list',
        limit = $(target_class).attr('ia-alist-limit') > 0 ? $(target_class).attr('ia-alist-limit') : 200,
        show_direction = $(target_class).attr('ia-alist-showdir') == 'true' ? true : false;

      distance = typeof distance == 'undefined' ? 500 : distance;
      var radius = false;

      $.each([100,500,1000,2000,5000,25000], function(key, value) {
        if (parseInt(distance) <= value && !radius) {
          radius = value;
        }
      });

      $('.radius-select').removeClass('btn-secondary active').addClass('btn-outline-secondary');
      $('.radius-select#button-Radius-' + radius).removeClass('btn-outline-secondary').addClass('btn-secondary active');

      Artpieces.load_nearbys($myPos[0], $myPos[1], radius, function(response) {
        Artpieces.thumbnail_list(response, target_class, {
          display_limit: limit,
          show_direction: show_direction
        });
        if (response.length > limit) {
          $(target_class).before('<p class="text-muted text-center text-md-left my-1 mt-0 listInfo">Maximum ' + limit + ' alkotást mutatunk itt.</p>');
        }
        Layout.handleImages();
      });

      $('#Radius').change(function(e) {
        Layout.alertIfPostblock();
        $('.listInfo').remove();
        var radius = parseInt($(this).val());

        $(target_class).html(Html.loading());

        Layout.getMyPos(function(pos) {
          $myPos = [pos[0], pos[1]];

          Artpieces.load_nearbys(pos[0], pos[1], radius, function(response) {
            $('.listInfo').remove(); // egen, megen mert torlódásnál...
            Artpieces.thumbnail_list(response, target_class, {
              display_limit: limit,
              show_direction: show_direction,
            });

            if (response.length > limit) {
              $(target_class).before('<p class="text-muted text-center text-md-left my-1 mt-0 listInfo">Maximum ' + limit + ' alkotást mutatunk itt.</p>');
            } else if(response.length > 0) {
              $(target_class).before('<p class="text-muted text-center text-md-left my-1 mt-0 listInfo">' + response.length + ' alkotás ' + Helper.show_distance(radius) + '-en belül.</p>');
            }

            Layout.handleImages();
          });
        });
      });
    },


    /**
     * Jellemzően műlapokon használt lista; adott pont közelében lévő dolgok
     * Radius váltás nélkül egyelőre.
     */
    nearby_list: function() {
      var target_div = '#nearby-list',
        radius = 500,
        limit = $(target_div).attr('ia-alist-limit') > 0 ? $(target_div).attr('ia-alist-limit') : 200,
        show_direction = $(target_div).attr('ia-alist-showdir') == 'true' ? true : false,
        latPos = $(target_div).attr('ia-alist-lat'),
        lonPos = $(target_div).attr('ia-alist-lon'),
        excluded = $(target_div).attr('ia-alist-excluded') > 0 ? $(target_div).attr('ia-alist-excluded') : false,
        show_distance = false;

      $(target_div).html(Html.loading());

      Artpieces.load_nearbys(latPos, lonPos, radius, function(response) {
        Artpieces.thumbnail_list(response, target_div, {
          display_limit: limit,
          show_direction: show_direction,
          show_distance: show_distance,
          image_size: 6,
          div_class: 'm-0 d-inline-block p-1 d-flex align-top',
          hide_title: true,
          excluded_id: excluded,
        });
      });
    },


    /**
     * Műlap dolgok betöltése
     *  - aktuális megtekintési statok
     *  - hasonló lapok listája
     *  - szavazatok (szépmunka, példás, publikálható)
     *
     *  Követés már külön megvan
     */
    artpageLoad: function(only_votes) {
      var id = Artpieces.artpiece.id,
        s = '',
        artpiece = false;

      if (id > 0) {
        Http.get('api/artpieces/artpage?id=' + id, function(response) {
          if (typeof response.artpiece == 'undefined') {
            return;
          }

          artpiece = response.artpiece;

          if (!only_votes) {

            // Hasonló lista
            if (response.similars.length > 0) {
              $('.similar-artpieces').removeClass('d-none');
              Artpieces.thumbnail_list(response.similars, '.similar-artpieces .items', {display_limit: 3});
            }

            // Statok
            s = '';
            s += Helper.number(artpiece['view_total']) + ', eheti: ' + Helper.number(artpiece['view_week']);
            s += Html.link('', '/webstat/oldalak?vp=artpieces&vi=' + artpiece['id'], {
              'icon': 'chart-line fa-fw',
              'class': 'ml-2'
            });
            $('.view-stats').html(s);

            // Ha jött user
            if (typeof response.user != 'undefined' && response.user.id > 0) {
              $user = response.user;

              $('.editor-boxes').removeClass('d-none');
              Http.get('mulapok/szerkesztoi_dobozok/' + id, function(content) {
                if (typeof content.body != 'undefined') {
                  $('.editor-boxes').html(content.body);
                  // Itt töltjük, mert már megvan a doboz
                  Artpieces.votes_display(response.votes);
                }
              });
            }
          } else {

            // Csak szavazatok
            Artpieces.votes_display(response.votes);
          }


        });
      }

    },


    /**
     * Szerkesztő űrlap alap funkciója
     */
    edit: function() {
      this.edit_form_events();

      this.photos_manage();

      // a change event csak akkor érzékel, ha kikattintunk a mezőből
      // az nem OK, mert nem lehet "érezni".
      /*$('.artpiece-edit-form').on('keyup change', function (e) {
        if (!$(e.target).hasClass('not-form-change')) {
          Artpieces.edit_form_change_init();
        }
      });*/

      // Késleltetünk gépeléskor, hogy ne húzza a gépelést az ajax lódolgatás
      var timeout = null;
      $('.artpiece-edit-form').on('keyup change', function (e) {
        if (!$(e.target).hasClass('not-form-change')) {
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            Artpieces.edit_form_change_init();
          }, 1500);
        }
      });


      // Szerk mentése gombra katt => Modal komment mezővel
      $(document).on('click', '#save-edits.not-mine', function(e) {
        e.preventDefault();

        var form = Html.input('edit_comment', {
          label: 'Segítsd az elfogadást némi indoklással, forrás megjelöléssel. Ha már megadtad a forrást, akkor is jelezd, mi a szerkesztésed háttere. Köszönjük!',
          type: 'textarea',
          class: 'edit_comment_text',
          help: 'A megjegyzés nem kötelező, de nagyon hasznos. Itt add meg a forrásokat, egyéb indokokat, amivel alátámasztod, miért küldöd be ezt a szerkesztést.'
        });

        form += Html.input('invisible', {
          type: 'checkbox',
          value: 1,
          class: 'edit_invisible',
          label: 'Ez egy láthatatlan szerkesztés',
          help: 'Apró elütések, finomítások esetén hasznos. Nagyobb változásokat eredményező szerkesztéseknél nem javasolt a használata. Csak a műlap gazdája és te látjátok. Elfogadás vagy visszavonás esetében törlődik a naplóból is. Inaktivitás vagy nem kezelés esetén nem kerül a főszerkesztők elé. Ha ezt pipálod, <strong>a hozzászólást nem mentjük.</strong>',
        });

        form += Html.link('Mégsem', '#', {
          'class': 'btn btn-outline-secondary float-right',
          'data-dismiss': 'modal'
        });
        form += Html.input('save_edits_final', {
          type: 'submit',
          class: 'save-edits-final',
          value: 'Szerkesztésem mentése',
        });
        Modals.open({
          modal_id: 'editSaveModal',
          title: 'Szerkesztés mentésének jóváhagyása',
          content: form
        });

        $(document).on('click', '.edit_invisible', function(e) {
          if ($(this).is(':checked')) {
            $('.edit_comment_text').hide();
          } else {
            $('.edit_comment_text').show();
          }
        });

      });

      // Szerk konfirm modalban mentés gomb
      $(document).on('click', '.save-edits-final', function(e) {
        e.preventDefault();

        var comment_text = '',
          invisible_edit = 0;

        if ($('#editSaveModal')[0]) {
          comment_text = $('.edit_comment_text').val();
          invisible_edit = $('.edit_invisible').is(':checked') == true ? 1 : 0;
          $('#editSaveModal').modal('hide');
        }

        $('#save-box').addClass('d-none');
        $('.check-loading').remove();
        $('#save-box').after(Html.loading('check-loading mb-2', '', 'Adatok ellenőrzése és mentése...'));

        var filled = Forms.checkRequired('.artpiece-edit-form');
        if (filled) {
          Artpieces.form_check(function (latest_form_data) {
            Http.post('api/artpieces', {
              'artpiece': latest_form_data,
              'comment': comment_text,
              'invisible': invisible_edit,
            }, function (response) {
              if (response.edits > 0) {
                Artpieces.edit_form.saved = true;
                _redirect(false, ['Sikeresen mentettük a szerkesztést!', 'success']);
              } else if (response.edits == 0) {
                Alerts.flashBubble('Nem érzékeltünk változást.', 'info');
                $('#save-box').addClass('d-none');
                $('.check-loading').remove();
              } else {
                Alerts.flashBubble(_text('varatlan_hiba'), 'danger');
              }
            });
          });
        }
      });


      // Néhány ui-event


      // Szerk tab logikák
      /*if (location.hash == '' || location.hash == '#szerk-szerkkomm') {
        $('#edit-info-column').addClass('d-none');
        $('#edit-form-column').removeClass('col-lg-9 col-sm-7').addClass('col-12');
      }*/

      $(document).on('click', '.hide-info-column', function(e) {
        e.preventDefault();
        $('#edit-info-column').addClass('d-none');
        $('#edit-form-column').removeClass('col-sm-8 col-md-9 col-lg-8').addClass('col-12');
        $('.show-info-column').removeClass('d-none');
      });

      $(document).on('click', '.show-info-column', function(e) {
        e.preventDefault();
        $('#edit-info-column').removeClass('d-none');
        $('#edit-form-column').addClass('col-sm-8 col-md-9 col-lg-8').removeClass('col-12');
        $('.show-info-column').addClass('d-none');
      });

      /*$(document).on('click', '.nav.nav-pills .nav-link', function(e) {
        var href = $(this).attr('href');
        if (href == '#szerk-szerkkomm') {
          $('#edit-info-column').addClass('d-none');
          $('#edit-form-column').removeClass('col-lg-9 col-sm-7').addClass('col-12');
        } else if (href.substring(0,1) == '#') {
          $('#edit-form-column').removeClass('col-12').addClass('col-lg-9 col-sm-7');
          $('#edit-info-column').removeClass('d-none');
        }
      });*/


      // Szerk történetben modal, abban map...

    },


    edit_form_events: function () {
      // Hogy eltűnjön az új hely infó, ha van hely ID
      $(document).on('change', '#Place-id', function() {
        if ($('#Place-id').val() > 0) {
          $('.new-address').remove();
        }
      });
    },


    edit_form_change_init: function() {
      Artpieces.edit_form.changes++;
      if (Artpieces.edit_form.changes > 0) {
        $('#save-box').removeClass('d-none');
        $('#Photo-files, #Photo-submit').prop('disabled', true);
        $('#photos-save-edit-info, #operations-edit-warning').removeClass('d-none');
      }
    },


    edit_form_change: function() {
      var form = $('.artpiece-edit-form');

      if (Artpieces.edit_form.changes > 0) {
        // Induláskor is van change, mert a nominatim dolgokat beírjuk
        _c('szerkesztes tortent');

        var checkable = Forms.checkRequired(form);

        if (checkable) {
          Artpieces.form_check();
        }

        Artpieces.edit_form.last_change_count = Artpieces.edit_form.changes;

        // Jelez, ha van nem mentett form tartalom
        window.onbeforeunload = function () {
          if (($app.here).indexOf('/mulapok/szerkesztes/') > -1 && !Artpieces.edit_form.saved) {
            return _text('nem_mentett_modositasok');
          }
          return;
        }

      }
    },


    form_check: function(valid_callback) {
      var form = $('.artpiece-edit-form');

      Artpieces.edit_form.data = Helper.formToObject(form, ['photoranks']);

      if ($('#Edit-id')[0] && $('#Edit-id').val() != '') {
        Artpieces.edit_form.data['edit_id'] = $('#Edit-id').val();
      }

      //_c(Artpieces.edit_form.data);

      Http.post('api/artpieces/check', {
        'artpiece': Artpieces.edit_form.data
      }, function (response) {

        $('#artpiece-check-info .alertContainer').remove();
        $('.show-info-column').removeClass('btn-primary').addClass('btn-outline-secondary').html('<span class="fal fa-arrow-left mr-1"></span>');

        if (response['messages'].length > 0) {
          response['messages'].forEach(function (message) {
            var alert_id = Helper.slugify(message[0]);
            Alerts.flashDiv(message[0], message[1], {
              id: alert_id,
              target: '#artpiece-check-info',
              removeAfter: 0,
              dismissable: false,
              delSameId: true,
              extraClass: 'p-2',
            });
          });

          if ($('.show-info-column').is(':visible')) {
            $('.show-info-column').removeClass('btn-outline-secondary').addClass('btn-primary')
              .html('<span class="fal fa-arrow-left mr-1"></span>' + response['messages'].length + ' üzenet');
          }
        }

        // Publikálási feltételek
        $.each(response['conditions'], function (condition, status) {
          var content = '';
          if (status[0] == '3') {
            // Nincs, és mehet így
            content += '<span class="fas fa-minus-circle text-muted"></span>';
          } else if (status[0] == '2') {
            // nincs, de kellhet
            content += '<span class="fas fa-minus-circle text-secondary"></span>';
          } else if (status[0] == '1') {
            // van
            content += '<span class="fas fa-check-circle text-success"></span>';
          } else if (status[0] == '0') {
            // nincs és kell
            content += '<span class="fas fa-times-circle text-danger"></span>';
          }
          if (status[1] != '') {
            content += '<span class="text-muted ml-2">' + status[1] + '</span>';
          }
          $('.conditions-' + condition).html(content);
        });


        // Publikálhatóság maga
        $.each(response['operations'], function (operation, value) {
          var content = '';
          if (value == '0') {
            content += '<span class="fas fa-minus-square fa-lg text-secondary" data-toggle="tooltip" title="Néhány feltétel még nincs teljesítve, vagy nem küldhető be a műlap a megadott adatok alapján."></span>';
          } else if (value == '1') {
            content += '<span class="fas fa-check-square fa-lg text-success" data-toggle="tooltip" title="Beküldheted ellenőrzésre a főszerkesztőknek, ha minden ismert és szükséges információt kitöltöttél."></span>';
          }
          $('.operations-' + operation).html(content);
        });
        // Memo
        if (typeof response['operations']['memo'] != 'undefined'
          && response['operations']['memo'] != '') {
          $('#submit-memo').html('<span class="fal fa-exclamation-triangle mr-2"></span>' + response['operations']['memo']).removeClass('d-none');
        } else {
          $('#submit-memo').html('').addClass('d-none');
        }

        Layout.initListeners();


        /**
         * Tesztüzemben megnézzük, hogy mindenképpen menthetőség mellett hogyan
         * működnek a dolgok.
         */

        $(form).find('input[type=submit]').removeClass('disabled');
        $('#save-edits').removeClass('disabled');
        if (typeof valid_callback != 'undefined') {
          valid_callback(Artpieces.edit_form.data);
        }

        /*
        // Valid-e, vagyis menthető-e
        if (response['valid'] || Artpieces.artpiece.status_id == 1) {
          $(form).find('input[type=submit]').removeClass('disabled');
          $('#save-edits').removeClass('disabled');
          if (typeof valid_callback != 'undefined') {
            valid_callback(Artpieces.edit_form.data);
          }
        } else {
          $(form).find('input[type=submit]').addClass('disabled');
          $('#save-edits').addClass('disabled');
        }
        */


      }, {
        silent: true
      });
    },





    // FOTÓK
    photos_manage: function() {

      // Sort oldalra lépés
      $(document).on('click', '.photos-sort-button', function(e) {
        e.preventDefault();
        $('.photos-list-form, .photos-upload-toggle').addClass('d-none');
        $('.photos-sort-form').removeClass('d-none');
      });

      // Lista oldalra lépés
      $(document).on('click', '.photos-list-button', function(e) {
        e.preventDefault();
        $('.photos-list-form, .photos-upload-toggle').removeClass('d-none');
        $('.photos-sort-form').addClass('d-none');
      });

      // Borító módosítás
      $(document).on('click', '.photo-cover-checkbox', function(e) {
        var class_cover = 'bg-gray-kt pt-4 pb-2 mb-4 rounded',
          class_base = 'border-bottom';
        $('.photo-row').removeClass(class_cover).addClass(class_base);
        $(this).closest('.photo-row').removeClass(class_base).addClass(class_cover);
      });

    },


    /**
     * Dragula után fut le
     */
    photos_sorted: function() {

      var new_photos = [],
        was_separator = false,
        i = 0,
        min_top = $sDB['limits']['artpieces']['top_photo_min'],
        max_top = $sDB['limits']['artpieces']['top_photo_max'];

      $('.photo-sort .item').each(function(key, elem) {
        i++;
        if ($(this).hasClass('top-separator')) {
          was_separator = i;
          if (i <= min_top) {
            var if_less = $('.photo-sort .rank-input').length < min_top ? ' Tehát itt mind a(z) ' + $('.photo-sort .rank-input').length + '.' : '';
            Alerts.flashBubble('Lehetőség szerint az első ' + min_top + ' jelenlegi normál fotó mindenképp kiemelt marad.' + if_less, 'warning', {
              delBefore: true
            });
          } else if (i > max_top+1) {
            Alerts.flashBubble('Maximum ' + max_top + ' fotó lehet kiemelt.', 'warning', {
              delBefore: true
            });
          }
        }
        if ($(this).hasClass('rank-input')) {
          new_photos.push({
            'id': parseInt($(elem).data('photo-id')),
            'slug': $(elem).data('photo-slug'),
            'rank': parseInt($(elem).val()),
            // top, ha még nem volt separator, de legalább a min top megvan és max a maxtop
            'top': (was_separator == false || i <= min_top+1) && i <= max_top ? 1 : 0
          });
        }
      });

      //_c(new_photos);

      $('#Photos-sorter').val(encodeURIComponent(JSON.stringify(new_photos)));
      $('#Top-photo-count').val(Math.max(was_separator-1, min_top));

      Artpieces.edit_form_change_init();
    },


    photo_delete_question: function(photo_id, vars, redirect, elem) {
      var artpieces = eval(decodeURIComponent(vars['artpieces']));

      var modal_content = 'A kép törlésével visszavonhatatlanul törlöd a fájlt a szerverről, és a biztonsági mentésből is.';

      if (artpieces.length > 1) {
        modal_content += '<hr /><strong>A kép nem csak ezen a műlapon szerepel.</strong> Van lehetőséged arra is, hogy csak erről a műlapról töröld, és a másikon meghagyd.<br />';
        modal_content += Html.link('Csak itt törlöm, máshol meghagyom', '#', {
          class: 'font-weight-bold',
          'ia-bind': 'artpieces.photo_delete',
          'ia-pass': photo_id,
          'ia-vars-target': 'this',
          'ia-vars-artpiece_id': vars['artpiece_id'],
          'ia-vars-cover': vars['cover'],
        });
        modal_content += '<hr />Ha sehol sem szeretnéd meghagyni, kattints az alábbi linkre.';
      }

      modal_content += '<div class="my-3">'

      modal_content += Html.link('Végleges törlés', '#', {
        icon: 'trash',
        class: 'font-weight-bold focus',
        'ia-bind': 'artpieces.photo_delete',
        'ia-pass': photo_id,
        'ia-vars-target': 'all',
        'ia-vars-artpiece_id': vars['artpiece_id'],
        'ia-vars-cover': vars['cover'],
      });

      modal_content += Html.link('Mégsem', '#', {
        class: 'float-right',
        'data-dismiss': 'modal'
      });

      modal_content += '</div>';

      Modals.open({
        size_class: 'modal-md',
        title: 'Biztosan törlöd ezt a fotót?',
        content: modal_content,
        modal_id: 'photo-delete-modal'
      });

    },

    photo_delete: function(photo_id, vars, redirect, elem) {
      $('#photo-delete-modal .modal-body').html(Html.loading('', '', 'Néhány másodperc kell, hogy minden képméretet töröljünk...'));
      Http.post('api/artpieces/photo_delete', {
        'photo_id': photo_id,
        'target': vars['target'],
        'artpiece_id': vars['artpiece_id'],
        'cover': vars['cover']
      }, function(response) {
        $('#photo-delete-modal').modal('hide');
        if (response.success) {
          Alerts.flashBubble(_text('sikeres_torles'), 'info');
          setTimeout(function() {
            $('.photo-row-' + photo_id).slideUp();
          }, 300);
          $('.photo-sort-card-' + photo_id).remove();
        } else {
          Alerts.flashBubble(_text('varatlan_hiba'), 'danger');
        }
      });
    },

    photo_copy_question: function(photo_id, vars, redirect, elem) {
      var artist_target = vars['delete'] == 1 && typeof vars['artist'] != 'undefined' && vars['artist'] == 1 ? true : false;
      var modal_title = vars['delete'] == 1 ? 'Fotó áthelyezése más műlapra' : 'Fotó másolása más műlapra';

      var modal_content = '<div class="mb-1">Válaszd ki a cél műlapot az alábbi mezőben névre, vagy pontos műlap AZ-ra keresve.</div>';
      modal_content += Html.input('target_artpiece_id', {
        'id': 'photo-copy-target-artpiece-id',
        'type': 'text',
        'class': 'd-none not-form-change',
        'divs': false,
      });
      modal_content += Html.input('target_artpiece', {
        'id': 'photo-copy-target-artpiece',
        'class': 'not-form-change',
        'ia-auto': 'artpieces',
        'ia-auto-query': 'title',
        'ia-auto-key': 'id',
        'ia-auto-target': '#photo-copy-target-artpiece-id',
      });

      if (artist_target) {
        modal_content += '<div class="mt-3 mb-1">A fotó alkotót ábrázol, így áthelyezheted alkotói adatlapra is.</div>';
        modal_content += Html.input('target_artist_id', {
          'id': 'photo-copy-target-artist-id',
          'type': 'text',
          'class': 'd-none not-form-change',
          'divs': false,
        });
        modal_content += Html.input('target_artist', {
          'id': 'photo-copy-target-artist',
          'class': 'not-form-change',
          'ia-auto': 'artists',
          'ia-auto-query': 'name',
          'ia-auto-key': 'id',
          'ia-auto-target': '#photo-copy-target-artist-id',
        });
      }

      if (photo_id == 'all') {
        modal_content += '<p>A gombra kattintva minden fotóra lefuttatjuk a műveletet!</p>';
      }

      modal_content += Html.input('copy_move', {
        'ia-bind': 'artpieces.photo_copy',
        'ia-pass': photo_id,
        'ia-vars-delete': vars['delete'],
        'ia-vars-source_artpiece_id': vars['artpiece_id'],
        'ia-vars-source_cover': vars['cover'],
        'type': 'submit',
        'value': vars['delete'] == 1 ? 'Áthelyezés és törlés itt' : 'Másolás',
      });

      Modals.open({
        size_class: 'modal-md',
        title: modal_title,
        content: modal_content,
        modal_id: 'photo-copy-modal',
        important: true,
      });
    },

    photo_copy: function(photo_id, vars, redirect, elem) {
      if (vars['delete'] == 1 && $('#photo-copy-target-artist-id')[0]) {
        if (($('#photo-copy-target-artpiece-id').val() == '0' || $('#photo-copy-target-artpiece-id').val() == '')
          && ($('#photo-copy-target-artist-id').val() == '0' || $('#photo-copy-target-artist-id').val() == '')) {
          Forms.addInvalidInfo('#photo-copy-target-artpiece', true, 'Legalább ezt add meg....');
          Forms.addInvalidInfo('#photo-copy-target-artist', true, '...vagy ezt.');
          return;
        }
      } else {
        if ($('#photo-copy-target-artpiece-id').val() == '0' || $('#photo-copy-target-artpiece-id').val() == '') {
          Forms.addInvalidInfo('#photo-copy-target-artpiece', true, 'Kérjük, válaszd ki a cél műlapot.');
          return;
        }
      }

      Forms.removeInvalidInfo('#photo-copy-target-artpiece');

      var target_artpiece_id = $('#photo-copy-target-artpiece-id').val();
      var target_artist_id = $('#photo-copy-target-artist-id')[0]
        ? $('#photo-copy-target-artist-id').val() : 0;

      $('#photo-copy-modal .modal-body').html(Html.loading('', '', 'Kis türelmet, amíg pakolunk...'));

      Http.post('api/artpieces/photo_copy', {
        'photo_id': photo_id,
        'delete': vars['delete'],
        'source_artpiece_id': vars['source_artpiece_id'],
        'target_artpiece_id': target_artpiece_id,
        'target_artist_id': target_artist_id,
        'source_cover': vars['source_cover']
      }, function(response) {
        $('#photo-copy-modal').modal('hide');
        if (response.success) {
          var success_text = vars['delete'] == 1 ? 'sikeres_athelyezes' : 'sikeres_masolas';
          if (target_artpiece_id > 0) {
            var target_link = Html.link('A cél műlap megnyitása új lapon.', '#', {
              'target': '_blank',
              'artpiece': {'id': target_artpiece_id}
            });
          } else if (target_artist_id > 0) {
            var target_link = Html.link('A cél alkotói adatlap megnyitása új lapon.', '#', {
              'target': '_blank',
              'artist': {'id': target_artist_id}
            });
          }
          Alerts.flashBubble(_text(success_text) + ' ' + target_link, 'info', {
            removeAfter: 10
          });

          if (vars['delete'] == 1) {
            setTimeout(function () {
              if (photo_id == 'all') {
                $('.photo-row').slideUp();
                $('.photo-sort-card').remove();
              } else {
                $('.photo-row-' + photo_id).slideUp();
                $('.photo-sort-card-' + photo_id).remove();
              }
            }, 300);
          }
        } else {
          Alerts.flashBubble(_text('varatlan_hiba'), 'danger');
        }
      });
    },

    photos_filter: function(user_id, vars, redirect, elem) {
      $('.photo-row').removeClass('d-none');
      $('.photo-user-filter').removeClass('u');
      if (user_id > 0) {
        $('.photo-row').each(function (row) {
          if ($(this).data('user-id') != user_id) {
            $(this).addClass('d-none');
          }
        });
        $('.photo-user-filter-delete').removeClass('d-none').addClass('d-block');
        $(elem).addClass('u');
      } else {
        $('.photo-user-filter-delete').removeClass('d-block').addClass('d-none');
      }
    },



    // FOTÓK --





    // ALKOTÓK
    artist_delete: function(artist_id) {
      var form = $('.artpiece-edit-form');

      $('.artist-row-' + artist_id).remove();

      // Nincs alkotó
      if ($('.artist-row').length == 0) {
        $(form).append('<input type="text" name="no_artists" value="1" id="no-artists" class="d-none">');
      } else {
        $('#no-artists').remove();
      }

      Artpieces.edit_form_change_init();
    },


    artist_add: function(artist_id, artist_name) {
      if ($('.artist-row-' + artist_id)[0]) {
        // Megeshet, hogy már van, ill. az event figyelésben kattintáskor bug van
        // történnek és többször beszúrja.
        return true;
      }

      var form = $('.artpiece-edit-form');

      var s = '<div class="row bg-light py-2 mb-2 artist-row artist-row-' + artist_id + '">';

      s += Html.input('artists[' + artist_id + '][id]', {
        'type': 'text',
        'class': 'd-none',
        'value': artist_id,
        'divs': false,
      });

      s += Html.input('artists[' + artist_id + '][rank]', {
        'type': 'text',
        'class': 'artist-rank d-none',
        'value': $('.artist-row').length + 1,
        'divs': false,
      });

      // Név
      s += '<div class="col-lg-5 mb-2 mb-lg-0">'
        + Html.link(artist_name, '#', {
          'class': 'font-weight-bold',
          'artist': {
            'id': artist_id,
            'name': artist_name
          },
          'target': '_blank'
        })
        + '</div>';

      // Közreműködő?
      s += '<div class="col-lg-3 pl-3 px-lg-0 pb-2 pb-lg-0">'
        + Html.input('artists[' + artist_id + '][contributor]', {
          'type': 'select_button',
          'options': {
            0: 'Alkotó',
            1: 'Közreműködő'
          },
          'value': 0,
          'divs': false,
        })
        + '</div>';

      // Szerep
      var roles = {};
      $.each($sDB['artist_professions'], function(key, profession) {
        if (profession[1] == 1) {
          roles[key] = profession[0];
        }
      });
      s += '<div class="col-lg-2 col-6">'
        + Html.input('artists[' + artist_id + '][profession_id]', {
          'type': 'select',
          'options': roles,
          'class': 'd-inline',
          'value': 1,
          'divs': false,
        })
        + '</div>';

      // Feltételes
      /*s += '<div class="col-lg-1 col-2 pt-1">'
        + Html.input('artists[' + artist_id + '][question]', {
          'type': 'checkbox',
          'label': '<span class="far text-muted fa-question fa-lg" data-toggle="tooltip" title="Az alkotó bizonytalan"></span>',
          'value': 1,
          'class': 'd-inline',
          'title': 'Az alkotó bizonytalan',
          'divs': false,
        })
        + '</div>';*/

      // Ikonok
      s += '<div class="col-lg-2 col-4 pt-1 text-right">'
        + Html.link('', '#', {
          'icon': 'trash fa-lg',
          'class': 'text-muted mr-2 cursor-pointer',
          'ia-confirm': 'Biztosan törlöd ezt az alkotót?',
          'ia-bind': 'artpieces.artist_delete',
          'ia-pass': artist_id,
          'title': 'Törlés',
        })
        + '<span class="fas fa-grip-vertical fa-lg text-muted draghandler hide-on-touch px-1" data-toggle="tooltip" title="Sorrend módosítása áthúzással"></span>'
        + '</div>';

      s += '</div>'; // row

      $('#artist-list .help-box').before(s);

      $('#New-artist').val('');

      Artpieces.edit_form_change_init();
      Appbase.confirm_links();
    },

    artist_create: function(name) {
      Http.post('api/artists', {
        'name': name,
        'artpiece_id': Artpieces.artpiece.id,
      }, function(response) {
        if (response.success) {

          $('#New-artist').val();
          Artpieces.artist_add(response.id, name);

        } else {
          Alerts.flashBubble(_text('varatlan_hiba'), 'danger');
        }
      });

    },
    // ALKOTÓK --




    // DÁTUMOK
    date_add: function(field_type) {

      var form = $('.artpiece-edit-form'),
        s = '',
        date_id = 0,
        last_date_id = 0;

      // A legnagyobb ID + 1 lesz az új ID
      $('.date-id-field').each(function() {
        if (parseInt($(this).val()) > last_date_id) {
          last_date_id = $(this).val();
        }
      });

      date_id = parseInt(last_date_id) + 1;

      s += '<div class="row bg-light py-2 mb-2 date-row date-row-' + date_id + '">';

      s += Html.input('dates[' + date_id + '][id]', {
        'type': 'text',
        'class': 'd-none date-id-field',
        'value': date_id,
        'divs': false,
      });

      s += '<div class="col-lg-5 col-12 mb-2 mb-lg-0 form-inline">';

      if (field_type == 'date') {

        s += Html.input('dates[' + date_id + '][y]', {
          'maxlength': 4,
          'placeholder': 'Évszám',
          'class': 'narrow mr-2',
          'divs': 'm-0 p-0 d-inline',
        });

        s += Html.input('dates[' + date_id + '][m]', {
          'type': 'select',
          'options': $sDB['month_names'],
          'empty': {0: 'Hónap...'},
          'class': 'mr-2',
          'divs': 'm-0 p-0 d-inline',
        });

        s += Html.input('dates[' + date_id + '][d]', {
          'maxlength': 2,
          'placeholder': 'Nap...',
          'class': 'narrow mr-2',
          'divs': 'm-0 p-0 pl-2 d-inline',
        });

      } else if (field_type == 'century') {

        s += Html.input('dates[' + date_id + '][century]', {
          'label': 'Század',
          'maxlength': 2,
          'placeholder': '...',
          'class': 'narrow ml-2',
          'divs': false,
        });

      }

      s += '</div>';
      s += '<div class="col-lg-5 col-12 mb-2 mb-lg-0 form-inline">';

      s += Html.input('dates[' + date_id + '][type]', {
        'type': 'select_button',
        'options': $sDB['date_types'],
        'value': 'erection',
        'divs': 'mb-0 pb-0 d-inline',
      });

      s += '</div>';
      s += '<div class="col-lg-1 col-6 pt-1">';

      if (field_type == 'date') {
        s += Html.input('dates[' + date_id + '][cca]', {
          'type': 'checkbox',
          'label': '<span class="far text-muted fa-question fa-lg" data-toggle="tooltip" title="Az esemény időpontja bizonytalan vagy hozzávetőleges"></span>',
          'value': 1,
          'class': 'd-inline',
          'title': 'Az esemény időpontja bizonytalan vagy hozzávetőleges',
          'divs': false,
        });
      }

      s += '</div>';

      s += '<div class="col-lg-1 col-6 pt-1 text-right">';
      s += Html.link('', '#', {
        'icon': 'trash fa-lg',
        'class': 'text-muted mr-2 cursor-pointer',
        'ia-confirm': 'Biztosan törlöd ezt az eseményt? Módosíthatod is, ha pontosabb időpontot tudsz.',
        'ia-bind': 'artpieces.date_delete',
        'ia-pass': date_id,
        'title': 'Törlés'
      });
      s += '</div>';


      s += '</div>'; // date-row --

      $('#date-list').append(s);

      Artpieces.edit_form_change_init();
      Appbase.confirm_links();
    },

    date_delete: function(date_id) {
      var form = $('.artpiece-edit-form');
      $('.date-row-' + date_id).remove();

      // Nincs dátum
      if ($('.date-row').length == 0) {
        $(form).append('<input type="text" name="no_dates" value="1" id="no-dates" class="d-none">');
      } else {
        $('#no-dates').remove();
      }

      Artpieces.edit_form_change_init();
    },
    // DÁTUMOK --



    // KAPCSOLÓDÓ MŰLAPOK
    connected_artpiece_add: function(connected_artpiece_id, connected_artpiece_title) {
      if ($('.connected-artpiece-row-' + connected_artpiece_id)[0]) {
        // Megeshet, hogy már van, ill. az event figyelésben kattintáskor bug van
        // történnek és többször beszúrja.
        return true;
      }

      var form = $('.artpiece-edit-form');

      var s = '<div class="row bg-light py-2 mb-2 connected-artpiece-row connected-artpiece-row-' + connected_artpiece_id + '">';

      s += Html.input('connected_artpieces[' + connected_artpiece_id + '][id]', {
        'type': 'text',
        'class': 'd-none',
        'value': connected_artpiece_id,
        'divs': false,
      });

      // Kép
      s += '<div class="col-6 col-sm-2 pb-2 pb-md-0">'
        + '' // @todo
        + '</div>';

      // Cím
      s += '<div class="col-lg-3 pl-3 px-lg-0 pb-2 pb-lg-0">'
        + Html.link(connected_artpiece_title, '#', {
          'artpiece': {
            'id': connected_artpiece_id,
            'title': connected_artpiece_title,
          },
          'target': '_blank',
          'class': 'font-weight-bold',
        })
        + '</div>';

      // Típus
      s += '<div class="col-md-4 col-10">'
        + Html.input('connected_artpieces[' + connected_artpiece_id + '][type]', {
          'type': 'select',
          'options': $sDB['artpiece_connection_types'],
          'value': 0,
          'divs': 'mb-0 pb-0 d-inline',
        })
        + '</div>';

      // Ikonok
      s += '<div class="col-2 pt-1 text-right">'
        + Html.link('', '#', {
          'icon': 'trash fa-lg',
          'class': 'text-muted mr-2 cursor-pointer',
          'ia-confirm': 'Biztosan törlöd ezt a kapcsolatot? A szerkesztés jóváhagyásakor a kapcsolódó műlapról is lekerül a kapcsolás.',
          'ia-bind': 'artpieces.connected_artpiece_delete',
          'ia-pass': connected_artpiece_id,
          'title': 'Törlés',
        })
        + '</div>';

      s += '</div>'; // row

      $('#connected-artpiece-list').append(s);

      $('#New-connected-artpiece').val('');

      Artpieces.edit_form_change_init();
      Appbase.confirm_links();
    },

    connected_artpiece_delete: function(connected_artpiece_id) {
      var form = $('.artpiece-edit-form');

      $('.connected-artpiece-row-' + connected_artpiece_id).remove();

      // Nincs dátum
      if ($('.connected-artpiece-row').length == 0) {
        $(form).append('<input type="text" name="no_connected_artpieces" value="1" id="no-connected-artpieces" class="d-none">');
      } else {
        $('#no-connected-artpieces').remove();
      }

      Artpieces.edit_form_change_init();
    },

    // KAPCSOLÓDÓ MŰLAPOK --



    // KAPCSOLÓDÓ GYŰJTEMÉNYEK
    connected_set_add: function(connected_set_id, vars, redirect, elem) {
      if (connected_set_id == '' || $('.connected-set-row-' + connected_set_id + '')[0]) {
        $(elem).val('');
        return;
      }

      var s = '',
        form = $('.artpiece-edit-form'),
        connected_set_name = vars['name'] ? vars['name'] : $(elem).find('option:selected').text();

      s += '<div class="row bg-light py-2 mb-2 connected-set-row connected-set-row-' + connected_set_id
        + '" data-id="' + connected_set_id + '">';
      s += '<div class="col-6 col-md-2 order-2 order-md-1">';
      s += $sDB['set_types'][vars['type']];
      s += '</div>';
      s += '<div class="col-12 col-md-9 order-1 order-md-2">';
      s += Html.link(connected_set_name, '#', {
        'set': {
          'id': connected_set_id,
          'name': connected_set_name,
        },
        'target': '_blank',
        'class': 'font-weight-bold',
      });
      s += '</div>';

      s += '<div class="col-6 col-md-1 order-3 text-right">';
      s += Html.link('', '#', {
        'icon': 'trash fa-lg',
        'class': 'text-muted cursor-pointer',
        'ia-confirm': 'Biztosan kiveszed a műlapot ebből a gyűjteményből?',
        'ia-bind': 'artpieces.connected_set_delete',
        'ia-pass': connected_set_id,
        'title': 'Törlés',
      });
      s += '</div>';


      s += '</div>';

      $('#connected-set-list').append(s);

      $(elem).val('');

      var ids = [];
      $('.connected-set-row').each(function() {
        ids.push($(this).data('id'));
      });
      $('#Connected-sets').val(encodeURIComponent(JSON.stringify(ids)));


      Artpieces.edit_form_change_init();
      Appbase.confirm_links();

    },


    connected_set_delete: function(connected_set_id) {
      var form = $('.artpiece-edit-form');

      $('.connected-set-row-' + connected_set_id).remove();

      if ($('.connected-set-row').length > 0) {
        var ids = [];
        $('.connected-set-row').each(function () {
          ids.push($(this).data('id'));
        });
        $('#Connected-sets').val(encodeURIComponent(JSON.stringify(ids)));
      } else {
        $('#Connected-sets').val('');
      }

      Artpieces.edit_form_change_init();
    },

    // KAPCSOLÓDÓ GYŰJTEMÉNYEK --



    // LEÍRÁSOK
    description_edit: function(id, vars) {
      if ($('.description-edit-' + id)[0]) {
        return;
      }

      var s = '';

      s += '<div class="description-edit-' + id + '">';

      s += Html.input('descriptions[' + vars['key'] + '][id]', {
        value: id,
        class: 'd-none',
      });

      s += Html.input('descriptions[' + vars['key'] + '][lang]', {
        value: $('#description-lang-' + id).html(),
        class: 'd-none',
      });

      s += Html.input('descriptions[' + vars['key'] + '][text]', {
        label: 'Leírás szövege',
        value: Helper.stripTags($('#description-text-' + id).html()),
        type: 'textarea',
        help: 'Ha hivatkoznál a szövegben a számozott forrásokra, akkor használd a sorszámot szögletes zárójelben a kívánt helyen: [1] Szögletes zárójel? Magyar billentyűzeten jobb Alt+F és jobb Alt+G',
      });

      s += Html.input('descriptions[' + vars['key'] + '][source]', {
        label: 'Felhasznált források',
        value: Helper.stripTags($('#description-source-' + id).html()),
        type: 'textarea',
        help: 'Több forrás esetén mindet új sorba írd! Ha hivatkoznál a forrásokra a szövegben, akkor itt sorszámozd őket így: [1] ...szöveg...',
      });

      s += '<div class="mt-2">';
      s += Html.link('Szerkesztés elvetése', '#', {
        'icon': 'undo',
        'class': '',
        'ia-bind': 'artpieces.description_cancel',
        'ia-pass': '#Descriptions-' + vars['key'] + '-text,#Descriptions-' + vars['key'] + '-source',
        'ia-destroy': '.description-edit-' + id,
        'ia-show': '.description-row-' + id + ' .texts',
        'ia-confirm': 'A módosításaid elvetésre kerülnek, nem mentünk semmit belőle.',
      });
      s += '</div>'; // cancel
      s += '</div>'; // edit-div

      $('.description-row-' + id + ' .texts')
        .after(s)
        .hide();

      // hogy okés legyen minden
      Layout.initListeners();
      Appbase.confirm_links();
    },


    description_cancel: function(pass) {
      var inputs = pass.split(','),
        form = $('.artpiece-edit-form');

      $.each(inputs, function(key, input) {
        $(input).val('');
      });

      Artpieces.edit_form_change_init();
    },

    // LEÍRÁSOK --




    // MŰLAP SZAVAZÁSOK

    votes: function(type, vars) {

      var artpiece_id = vars['artpiece_id'],
        vote = typeof vars['vote'] != 'undefined' ? parseInt(vars['vote']) : 0,
        cancel = typeof vars['cancel'] != 'undefined' ? 1 : 0;

      Http.post('api/artpieces/votes', {
        'type': type,
        'id': artpiece_id,
        'vote': vote,
        'cancel': cancel,
      }, function(response) {
        if (typeof response.error != 'undefined') {
          Alerts.flashBubble('Duplikált szavazat, nem mentettük az utolsó gombnyomásodat.<br />Ha úgy gondolod, hogy hibás működés van, frissítsd az oldalt (F5) és próbáld újra a szavazást.', 'warning', {delBefore: true});
        }

        if (typeof response.message != 'undefined' && response.message != '') {
          Alerts.flashBubble(response.message, 'info', {delBefore: true});
        }

        if (typeof response.refresh != 'undefined' && response.refresh > -1) {
          setTimeout(function() {
            location.reload();
          }, response.refresh * 1000);

        }

        Artpieces.artpageLoad(true);
      });

    },


    votes_display: function(votes) {

      $('.publication-votes').html('');
      $('.checked-votes').html('');
      $('.praise-votes').html('');
      $('.superb-votes').html('');
      $('.praise-cancel-link').remove();

      var publish_score = 0,
        superb_vote = 0,
        praise_vote = 0,
        praise_vote_names = '',
        publication_voted = false,
        checked_voted = false,
        superb_voted = false,
        praise_voted = false;

      $.each(votes, function(key, vote) {

        // Publikálás: kitenni a sort, és ha én vagyok, elvenni a gombot
        if (vote.type_id == 1) {
          publish_score += vote.score;
          var s = '<div class="row mt-2"><div class="col font-weight-bold">' + vote.user_name + '</div>';
          if (vote.user_id == $user.id) {
            publication_voted = true;
            $('.publish-button').hide();
            s += '<div class="col">' + Html.link('mégsem', '#', {
              'class': 'small',
              'icon': 'times',
              'ia-bind': 'artpieces.votes',
              'ia-pass': 'publish',
              'ia-vars-artpiece_id': Artpieces.artpiece.id,
              'ia-vars-cancel': 1,
            }) + '</div>';
          }
          s += '</div>';
          $('.publication-votes').append(s);
        }

        // Ellenőrzés
        if (vote.type_id == 8) {
          var s = '<div class="row mt-2"><div class="col font-weight-bold"><span class="fas text-success fa-check-circle mr-2"></span>' + vote.user_name + '</div>';
          if (vote.user_id == $user.id) {
            checked_voted = true;
            $('.checked-button').hide();
            s += '<div class="col">' + Html.link('mégsem', '#', {
              'class': 'small',
              'icon': 'times',
              'ia-bind': 'artpieces.votes',
              'ia-pass': 'checked',
              'ia-vars-artpiece_id': Artpieces.artpiece.id,
              'ia-vars-cancel': 1,
            }) + '</div>';
          }
          s += '</div>';
          $('.checked-votes').append(s);
        }

        // Szép munka!
        if (vote.type_id == 3) {
          praise_vote += 1;
          praise_vote_names += '<span class="text-nowrap mr-2">' + vote.user_name + '</span> ';
          if (vote.user_id == $user.id) {
            praise_voted = true;
            $('.praise-button').hide();
            var s = '<div class="small my-3">' + Html.link('Visszavonom a "Szép munka!" jelölést', '#', {
              'class': 'praise-cancel-link',
              'icon': 'times',
              'ia-bind': 'artpieces.votes',
              'ia-pass': 'praise',
              'ia-vars-artpiece_id': Artpieces.artpiece.id,
              'ia-vars-cancel': 1,
            }) + '</div>';
            $('.praise-button').after(s);
          }
        }

        // Példás műlap!
        if (vote.type_id == 4) {
          superb_vote += 1;
          // Szavazó
          var s = '<div class="row mt-2"><div class="col">' + vote.user_name + '</div>';

          // Szavazat
          s += '<div class="col font-weight-bold">';
          s += vote.score == 1 ? 'példás' : 'nem példás';
          s += '</div>';

          if (vote.user_id == $user.id) {
            superb_voted = true;
            $('.superb-button').hide();
            s += '<div class="col">' + Html.link('mégsem', '#', {
              'class': 'small',
              'icon': 'times',
              'ia-bind': 'artpieces.votes',
              'ia-pass': 'superb',
              'ia-vars-artpiece_id': Artpieces.artpiece.id,
              'ia-vars-cancel': 1,
            }) + '</div>';
          }
          s += '</div>';
          $('.superb-votes').append(s);
        }
      });


      // Publikálás egyéb kiírásai, dolgai
      if (!publication_voted) {
        // Cancelnél hozzuk vissza
        $('.publish-button').show();
      }
      if ($('.publication-votes')[0]) {
        $('.publication-votes').append('<div class="border-top mt-2 pt-1 text-muted">Eddig ' + publish_score + ' pont. Még '
          + (Math.max(0,$sDB['artpiece_vote_types']['publish'][3] - publish_score)) + ' a publikáláshoz.</div>');
      }

      // Átnézés egyéb dolgai
      if (!checked_voted) {
        $('.checked-button').show();
      }


      // Szép munka dolgai
      if (!praise_voted) {
        // Cancelnél hozzuk vissza
        $('.praise-cancel-link').remove();
        $('.praise-button').show();
      }
      if (praise_vote > 0) {
        $('.praise-votes')
          .prop('title', praise_vote_names)
          .attr('data-toggle', 'tooltip')
          .html('<hr class="my-2" /><span class="fas fa-award mr-2 fa-lg text-primary"></span>' + praise_vote + ' × Szép munka!');
      } else {
        $('.praise-votes').html('');
      }


      // Példás műlap egyéb kiírása, dolgai
      if (!superb_voted) {
        // Cancelnél hozzuk vissza
        $('.superb-button').show();
      }

      Layout.initListeners();
    },

    // MŰLAP SZAVAZÁSOK --



    // CSAK PUBLIKÁLÁSI SZAVAZÁSOK
    publication_votes: function() {
      var id = Artpieces.artpiece.id,
        s = '',
        artpiece = false,
        publication_voted = false,
        publish_score = 0;

      if (id > 0) {
        Http.get('api/artpieces/artpage?id=' + id, function (response) {
          if (typeof response.artpiece == 'undefined') {
            return;
          }

          artpiece = response.artpiece;
          votes = response.votes;
          $.each(votes, function(key, vote) {
            if (vote.type_id == 1) {
              s += '<tr><td class="font-weight-bold">' + vote.user_name + '</td></tr>';
            }
          });

          if (s != '') {
            s = '<p>' + Html.link('Publikálásra szavazók', '#publikalasi-szavazatok', {
              icon_right: 'arrow-down',
              'data-toggle': 'collapse',
            }) + '</p>'
              + '<div class="collapse" id="publikalasi-szavazatok">'
              + '<table class="table table-sm table-striped">' + s + '</table>'
              + '</div>';
            $('.publication-votes').append(s);
          }

        });
      }
    },
    // CSAK PUBLIKÁLÁSI SZAVAZÁSOK --



    // CSAK ÁTNÉZÉSEK
    checked_votes: function() {
      var id = Artpieces.artpiece.id,
        s = '',
        artpiece = false,
        checked_voted = false;

      if (id > 0) {
        Http.get('api/artpieces/artpage?id=' + id, function (response) {
          if (typeof response.artpiece == 'undefined') {
            return;
          }

          artpiece = response.artpiece;
          votes = response.votes;
          $.each(votes, function(key, vote) {
            if (vote.type_id == 8) {
              s += '<tr><td class="font-weight-bold"><span class="fas text-success fa-check-circle mr-2"></span>' + vote.user_name + '</td></tr>';
            }
          });

          if (s != '') {
            s = '<p>' + Html.link('Átnézték', '#atnezesi-szavazatok', {
              icon_right: 'arrow-down',
              'data-toggle': 'collapse',
            }) + '</p>'
              + '<div class="collapse" id="atnezesi-szavazatok">'
              + '<table class="table table-sm table-striped">' + s + '</table>'
              + '</div>';
            $('.checked-votes').append(s);
          }

        });
      }
    },
    // CSAK PUBLIKÁLÁSI SZAVAZÁSOK --




    // SZERKESZTÉS SZAVAZÁSOK
    edit_votes: function(type, vars) {

      var edit_id = vars['edit_id'],
        vote = typeof vars['vote'] != 'undefined' ? parseInt(vars['vote']) : 0,
        cancel = typeof vars['cancel'] != 'undefined' ? 1 : 0;

      Http.post('api/artpieces/edit_votes', {
        'type': type,
        'id': edit_id,
        'vote': vote,
        'cancel': cancel,
      }, function(response) {
        if (typeof response.error != 'undefined') {
          Alerts.flashBubble('Duplikált szavazat, nem mentettük az utolsó gombnyomásodat.<br />Ha úgy gondolod, hogy hibás működés van, frissítsd az oldalt (F5) és próbáld újra a szavazást.', 'warning');
        }

        if (typeof response.message != 'undefined' && response.message != '') {
          Alerts.flashBubble(response.message, 'info', {delBefore: true});
        }

        if (typeof response.refresh != 'undefined' && response.refresh > -1) {
          setTimeout(function() {
            location.reload();
          }, response.refresh * 1000);
        }

        Artpieces.edit_votes_display();
      });

    },

    edit_votes_display: function() {
      $('.edit-votes').html('');

      var artpiece_id = $('.edit-votes').data('artpiece_id'),
        edit_id = $('.edit-votes').data('edit_id'),
        accept_score = 0,
        accept_voted = false;

      Http.get('api/uni?model=artpiece_votes&id=' + artpiece_id + '&edit_id=' + edit_id, function(response) {
        var votes = response.results;

        // Ha jött user
        if (typeof response.user.id != 'undefined' && response.user.id > 0) {
          $user = response.user;
        }

        $.each(votes, function(key, vote) {
          if (vote.type_id == 6) {
            accept_score += vote.score;
            var s = '<div class="row mt-2"><div class="col font-weight-bold">' + vote.user_name + '</div>';
            if (vote.user_id == $user.id) {
              accept_voted = true;
              $('.accept-button').hide();
              s += '<div class="col">' + Html.link('mégsem', '#', {
                'class': 'small edit-accept-cancel',
                'icon': 'times',
                'ia-bind': 'artpieces.edit_votes',
                'ia-vars-edit_id': vote.edit_id,
                'ia-vars-cancel': 1,
              }) + '</div>';
            }
            s += '</div>';
            $('.edit-votes').append(s);
          }
        });

        if (!accept_voted) {
          // Cancelnél hozzuk vissza
          $('.accept-button').show();
        }
        if ($('.edit-votes')[0]) {
          if ($sDB['artpiece_vote_types']['edit_accept'][3] - accept_score > 0) {
            $('.edit-votes').append('<div class="border-top mt-2 pt-1 text-muted">Eddig ' + accept_score + ' pont. Még '
              + ($sDB['artpiece_vote_types']['edit_accept'][3] - accept_score) + ' az elfogadáshoz.</div>');
          } else {
            $('.edit-votes').append('<div class="border-top mt-2 pt-1 text-muted">' + accept_score + ' ponttal megszavazásra került a szerkesztés.</div>');
            $('.edit-accept-cancel').remove();
          }
        }
      });
    },


    // SZERKESZTÉS SZAVAZÁSOK --


  };
