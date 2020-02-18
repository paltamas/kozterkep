var
  Maps = {

    myMap: [],
    myMarker: false,
    myPosMarker: false,
    mapLayer: false,
    mapLayerType: false, // a neve
    markerClusterer: false,
    markerList: [],
    settings: {
      fullPage: false,
      allowedLayers: [
        'osm.streets',
        'wikimedia.osm',
        //'google.satellite',
        'google.hybrid',
        //'google.terrain'
      ],
      markerDraggable: false,
      markerImageFromZoom: 5,
      editing: false,
      urlPosition: false,
      artpieceMarkers: false,
      artpieceIds: false,
      showMe: false,
      defaultPosition: false,
      positionMarker: false,
      oldPositionMarker: false,
      defaultZoom: false,
      defaultLayer: false,
      minZoom: Store.get('isTouch') == 1 ? 12 : 7,
      autoLocation: true,
      followMe: false,
      googleGeocoder: false,
    },
    icons: {
      location: new L.Icon({
        iconUrl: '/img/maps/kt-dot.png',
        iconSize: [24, 24],
        iconAnchor: [24, 24],
        className: 'itsme'
      }),
      default: new L.Icon({
        iconUrl: '/img/maps/marker-icon-2x-orange.png',
        shadowUrl: '/img/maps/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
      }),
      grey: new L.Icon({
        iconUrl: '/img/maps/marker-icon-2x-grey.png',
        shadowUrl: '/img/maps/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
      }),
    },

    init: function () {

      if ($('#map')[0]) {

        if ($('#map').hasClass('fullPage')) {
          // Ezt itt nem nyomkodhassuk
          $('.hide-when-mapping').remove();
          Maps.settings.urlPosition = true;
        }

        // Kellenek-e a műlapok
        if ($('#map').attr('ia-maps-artpieces') == 'true') {
          Maps.artpieceMarkers = true;
        } else {
          Maps.artpieceMarkers = false;
        }

        // Honnan jöjjenek a műlapok?
        if ($('#map').attr('ia-maps-artpiece_ids') && $('#map').attr('ia-maps-artpiece_ids') != 'false') {
          Maps.artpieceMarkers = true; // ha esetleg...
          Maps.artpieceIds = $('#map').attr('ia-maps-artpiece_ids');
          // Ezt átállítjuk, hogy a fitbound le tudjon futni
          Maps.settings.minZoom = 1;
          // Ezzel leállítjuk a figyelést, ami moveend után újratölt
          Maps.settings.autoLocation = false;
        }

        if ($('#map').attr('ia-maps-showme') == 'true') {
          Maps.settings.showMe = true;
        }

        if ($('#map').attr('ia-maps-showme') == 'false') {
          Maps.settings.autoLocation = false;
        }

        if ($('#map').attr('ia-maps-position') != undefined) {
          Maps.settings.defaultPosition = eval($('#map').attr('ia-maps-position'));
          Maps.settings.defaultZoom = 17;
        }

        if ($('#map').attr('ia-maps-zoom') > 0) {
          Maps.settings.defaultZoom = $('#map').attr('ia-maps-zoom');
        }

        if ($('#map').attr('ia-maps-markpos') == 'true') {
          Maps.settings.positionMarker = true;
        }

        if ($('#map').attr('ia-maps-position0')!= undefined) {
          Maps.settings.oldPositionMarker = eval($('#map').attr('ia-maps-position0'));
        }

        if ($('#map').attr('ia-maps-edit') == 'true') {
          Maps.googleGeocodingInit();
          Maps.settings.markerDraggable = true;
          Maps.settings.editing = true;
        } else {
          Maps.settings.markerDraggable = false;
          Maps.settings.editing = false;
        }

        if ($('#map').hasClass('fullPage')) {
          Maps.googleGeocodingInit();
          Maps.settings.fullPage = true;
        }

        Maps.draw();
        Maps.bindEvents();
        Maps.follow_me();

        $(window).on('resize', function (e) {
          Maps.setSize();
        });


        if ($('#map').hasClass('fullPage')) {
          // Full oldalas térképen létrehozzuk a map history state-et, hogy ezt frissítgessük
          // és visszánál ne menjünk visszafelé végig minden panningon
          history.pushState({id: 'start'}, 'Térkép', document.location);
          var referrer = document.referrer;

          window.onpopstate = function (event) {
            if (history.state && history.state.id !== 'start') {
              window.location = referrer;
            }
          };
        }
      }

    },

    draw: function () {
      Maps.setSize();

      // Betöltési pozíció; attól függ
      if (Maps.settings.defaultPosition) {
        var defaultPosition = Maps.settings.defaultPosition;
      } else if (Helper.getURLHashParameter('lat') && Helper.getURLHashParameter('lon')) {
        // URL-ben kapott center
        var defaultPosition = [Helper.getURLHashParameter('lat'), Helper.getURLHashParameter('lon')];
      } else if (Store.get('map_mycenter') && 1 == 2) {
        // Utolsó center -- ezt most nem
        var defaultPosition = JSON.parse(Store.get('map_mycenter'));
      } else if ($myPos) {
        // Saját helyzetem
        var defaultPosition = $myPos;
      } else if (Store.get('map_mypos')) {
        // Saját utolsó helyzetem
        var defaultPosition = JSON.parse(Store.get('map_mypos'));
      } else {
        // Nulla km kő
        var defaultPosition = [47.497845084, 19.04021590];
      }

      // Betöltési zoom
      if (Maps.settings.defaultZoom) {
        var defaultZoom = Maps.settings.defaultZoom;
      } else if (Helper.getURLHashParameter('zoom')) {
        var defaultZoom = Helper.getURLHashParameter('zoom');
      } else if (Store.get('map_zoom')) {
        var defaultZoom = Store.get('map_zoom');
      } else {
        var defaultZoom = 16;
      }

      // Betöltési layer
      if (Maps.settings.defaultLayer) {
        var defaultLayer = Maps.settings.defaultLayer;
      } else if (Helper.getURLHashParameter('layer')) {
        var defaultLayer = Helper.getURLHashParameter('layer');
      } else if (Store.get('map_zoom')) {
        var defaultLayer = Store.get('map_layer');
      } else {
        var defaultLayer = 'wikimedia.osm';
      }

      Maps.myMap = L.map('map', { zoomControl: false }).on('load', function(){
        Maps.setLayer(defaultLayer);
        Maps.actualize();
      });
      L.control.zoom({
        position: Store.get('isTouch') == 1 && Maps.settings.fullPage ? 'bottomright' : 'topleft'
      }).addTo(Maps.myMap);

      Maps.myMap.setView(defaultPosition, defaultZoom);

      if (Maps.settings.positionMarker) {
        Maps.addMyMarker(defaultPosition[0], defaultPosition[1], true);
      }

      if (Maps.settings.oldPositionMarker) {
        Maps.addMyMarker(Maps.settings.oldPositionMarker[0], Maps.settings.oldPositionMarker[1], false, 'grey');
      }

      if ($('#map').attr('ia-maps-nozoom') == 'true') {
        Maps.myMap.scrollWheelZoom.disable();
        if (Maps.myMap.tap) {
          // Ezzek kiiktatjuk a rátapintással scrollozást,
          // így normálisan átgörgethetünk rajta
          Maps.myMap.tap.disable();
        }
      }

      if (Maps.settings.autoLocation) {
        Maps.locateAndListen(true, false);
      }

      if (Maps.settings.editing) {
        Maps.editFunctions();
      }

      // Műlapok betöltése
      Maps.markerClusterer = L.markerClusterGroup({
        chunkedLoading: true,
        chunkProgress: Maps.marker_progress,
      });

      if (!Maps.settings.editing && Maps.artpieceMarkers) {
        if (Maps.artpieceIds) {
          Maps.artpieces_by_ids(Maps.artpieceIds);
        } else {
          Maps.artpieces_by_bounds();
        }
      }


      // Popup nyílás figyelése
      Maps.myMap.on('popupopen', function(e) {

        // Popup nyíláskor mozogjunk oda
        // @thx: https://stackoverflow.com/a/23960984
        var px = Maps.myMap.project(e.popup._latlng);
        px.y -= e.popup._container.clientHeight/2 + 10;
        Maps.myMap.panTo(Maps.myMap.unproject(px),{animate: true});

        // Nyissunk műlapot nagyobban, ha nyitva van a pane
        if ($('.artpiece-container:visible')[0]) {
          Maps.artpiece_view(e.popup._source.options.data_id);
        }

      });

    },

    bindEvents: function () {
      $(document).on('click', '.hide-artpieces-pane', function (e) {
        e.preventDefault();
        $('.artpiece-container').removeClass('d-md-block');
        $('.map-container').removeClass('col-md-8');
        $('#map').width('100%');
        Maps.setSize(true);
        $('.show-artpieces-pane').addClass('d-md-inline-block');
      });

      $(document).on('click', '.show-artpieces-pane', function (e) {
        e.preventDefault();
        $('.artpiece-container').addClass('d-md-block');
        $('.map-container').addClass('col-md-8');
        $('#map').width('100%');
        Maps.setSize(true);
        $('.show-artpieces-pane').removeClass('d-md-inline-block');
      });

      $(document).on('click', '.mapHome', function (e) {
        e.preventDefault();
        Maps.goHome();
      });

      $(document).on('click', '.mapLayer', function (e) {
        e.preventDefault();
        Maps.setLayer($(this).data('layer'));
        Maps.actualize();
      });

      $(document).on('click', '.place-search', function (e) {
        $('.keyword').focus();
      });

      $(document).on('click', '.geocoder .geocode', function (e) {
        e.preventDefault();
        Maps.googleGeocode();
        //Maps.geoCode();
      });
      $(document).on('keyup', '.geocoder .keyword', function (e) {
        e.preventDefault();
        if (e.keyCode === 13) {
          Maps.googleGeocode();
          //Maps.geoCode();
        }
      });

      if (Store.get('map_layer')) {
        $('.mapLayer').each(function (key, elem) {
          if ($(this).data('layer') == Store.get('map_layer')) {
            $(this).addClass('active');
          }
        })
      }

      if (Store.get('map_watch') == 1) {
        $('.mapWatch').addClass('active');
      }

      $(document).on('click', '.showOnMap', function(e) {
        e.preventDefault();
        if ($('.map-container').hasClass('d-none')) {
          // Ha épp mobilon nyitva a műlap lista
          // ugyanezeket használjuk a showArtpieceList klikk utáni rejtéskor!
          $('.close-mobile-alist').remove();
          $('.mobile-alist-title').remove();
          $('.artpiece-container').removeClass('col-12').addClass('d-none col-md-4 col-0');
          $('.map-container').removeClass('d-none');
        }
        Maps.showOnMap(this);
      });

      // Mobilos műlap lista a térképre töltődik
      $(document).on('click', '.showArtpieceList', function(e) {
        e.preventDefault();
        $('.map-container').addClass('d-none');
        $('.artpiece-container ul.nav').addClass('d-none').after(Html.link('', '#', {
          'icon': 'times',
          'class': 'float-right btn btn-secondary close-mobile-alist'
        }) + '<h4 class="mb-3 mobile-alist-title">Alkotások a térképen</h4>');
        $('.hide-artpieces-pane').addClass('d-none');
        $('.artpiece-container').removeClass('d-none col-md-4 col-0').addClass('col-12');
        Maps.artpiece_thumbnail_list();
        $(document).on('click', '.close-mobile-alist', function(e) {
          e.preventDefault();
          $('.close-mobile-alist').remove();
          $('.mobile-alist-title').remove();
          $('.artpiece-container').removeClass('col-12').addClass('d-none col-md-4 col-0');
          $('.map-container').removeClass('d-none');
        });
      });
    },


    /**
     * Popup nyitáskor, ha látszik az .artpieces-container,
     * a tetejére betöltjük külön bezárható dobozban a Mongoból kiolvasott
     * műlap adatokat.
     *
     * Kell: mongoba pakolni az alkotók neves listáját, településnevet,
     * akt leírásokat stb. Végig kell gondolni, mit írjunk
     * és hogyan a mongoba.
     *
     * @param id
     */
    artpiece_view: function(id) {
      // @todo...
    },



    /**
     * Odaugrunk a tékrépen műlap ID-hez
     *
     * Egyelőre nem tudom kibogozni a clustert, és megnyitni a popupot, mert
     * elég komplex foreach kellene ehhez. Hasonló ahhoz, amit get_bound_artpieces()
     * esetében használok, de az is eléág böngészőgyilkos bír lenni
     * mondjuk kis zoomnál vagy BP belvárosában.
     *
     * @param elem
     */
    showOnMap: function(elem) {

      var lat = $(elem).data('lat'),
        lon = $(elem).data('lon'),
        id = $(elem).data('id'),
        animation = $(elem).data('animation') == 0 ? false : true;

      // Az animáció kikapcoslása nélkül nincs moveend érzékelés képernyőn kívül ugráskor
      // hopp! :)
      Maps.myMap.panTo([lat,lon], {animate: animation});
      Maps.myMap.setView([lat,lon], 19);
      $('img.artpiece-marker-' + id).click();
      $('img.artpiece-marker-' + id).addClass('bg-primary');
      setTimeout(function() {
        $('img.artpiece-marker-' + id).removeClass('bg-primary');
      }, 1500);
    },



    googleGeocodingInit: function() {
      Maps.settings.googleGeocoder = new google.maps.Geocoder();
    },

    googleGeocode: function () {
      if ($('.geocoder .keyword').val().length > 3) {
        var keyword = Helper.replaceAll(' ', '+', $('.geocoder .keyword').val());
        var geocoder = Maps.settings.googleGeocoder;
        geocoder.geocode({'address': keyword}, function(results, status) {
          if (status === 'OK') {
            var location = [
              results[0].geometry.location.lat(),
              results[0].geometry.location.lng(),
            ];
          } else {
            var location = false;
            // Hátha...
            Maps.geoCode();
          }
          Maps.geocodeThis(location);
        });
      }
    },

    geoCode: function () {
      if ($('.geocoder .keyword').val().length > 3) {
        var keyword = Helper.replaceAll(' ', '+', $('.geocoder .keyword').val());
        Http.get('https://nominatim.openstreetmap.org/?format=json&addressdetails=0&q=' + keyword + '&format=json&limit=1', function (data) {
          if (typeof data[0] !== "undefined" && typeof data[0]['lat'] !== "undefined") {
            var location = [data[0]['lat'], data[0]['lon']];
          } else {
            var location = false;
          }
          Maps.geocodeThis(location);
        });
      }
    },


    geocodeThis: function(location) {
      if (location) {
        $('.noGeoCodeSuccess').remove();

        // Kikapcsoljuk a követést, hogy ne ugrándozzon
        Store.set('map_watch', 0);
        $('.mapWatch').removeClass('active');

        Maps.myMap.setView(location, Math.max(Store.get('map_zoom'), 15));

        Maps.addMyMarker(location[0], location[1], true);

        Maps.actualize();

        // Ha a markert is vinni kell a lokációra
        if ($('.geocoder .keyword').attr('ia-maps-search-marker')) {
          Maps.addMyMarker(location[0], location[1], true);
        }
      } else {
        $('.geocoder').after('<span class="text-danger noGeoCodeSuccess mx-2"><small>Sajnos nem találtunk ilyen címet.</small></span>');
      }
    },


    /**
     * Saját (ismert) lokációra ugrás
     */
    goHome: function () {
      if (Store.get('map_posblock') == 0) {
        var zoom = Math.max(Store.get('map_zoom'), 16);
        $myPos = JSON.parse(Store.get('map_mypos')); // kell! kell?
        Maps.myMap.setView($myPos, zoom);
        Store.set('map_mycenter', $myPos);
      }
    },


    /**
     * Kiválasztott layer beállítása
     * @param layerType
     */
    setLayer: function (layerType) {
      if ($.inArray(layerType, Maps.settings.allowedLayers) === -1) {
        layerType = Maps.settings.allowedLayers[0];
      }

      if (Maps.mapLayer) {
        Maps.myMap.removeLayer(Maps.mapLayer);
      }

      switch (layerType) {
        case 'osm.streets':
        case 'wikimedia.osm':
          Maps.mapLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright" target="_blank" title="OpenStreetMap közreműködői" data-toggle="tooltip">OSM közr.</a>',
            minZoom: Maps.settings.minZoom,
            maxZoom: 19,
          });
          break;

        /*case 'wikimedia.osm':
          Maps.mapLayer = L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://foundation.wikimedia.org/wiki/Maps_Terms_of_Use" target="_blank">Wikimedia Maps</a>, <a href="http://osm.org/copyright" target="_blank" title="OpenStreetMap közreműködői" data-toggle="tooltip">OSM közr.</a>',
            minZoom: Maps.settings.minZoom,
            maxZoom: 19,
          });
          break;*/

        case 'google.hybrid':
          Maps.mapLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            attribution: '&copy; <a href="http://maps.google.com" target="_blank">Google</a>',
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            minZoom: Maps.settings.minZoom,
            maxZoom: 19,
          });
          break;

        /*case 'google.satellite':
          Maps.mapLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: '&copy; <a href="http://maps.google.com" target="_blank">Google</a>',
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            minZoom: Maps.settings.minZoom,
            maxZoom: 19
          });
          break;*/

        /*case 'google.terrain':
          Maps.mapLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
            attribution: '&copy; <a href="http://maps.google.com" target="_blank">Google</a>',
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            minZoom: Maps.settings.minZoom,
            maxZoom: 15
          });
          break;*/
      }

      Maps.myMap.addLayer(Maps.mapLayer);

      Maps.mapLayerType = layerType;

      // Menü
      $('.mapLayer').removeClass('active');
      $('.mapLayer[data-layer="' + layerType + '"]').addClass('active');

      // Tároljuk a váltást
      Store.set('map_layer', layerType);
    },


    /**
     * Beállítja a linkeket, hasht, cache-t
     * @param lat
     * @param lon
     */
    actualize: function(lat, lon) {
      var lat = typeof lat == 'undefined' ? Maps.myMap.getCenter().lat : lat;
      var lon = typeof lon == 'undefined' ? Maps.myMap.getCenter().lng : lon;
      var zoom = Maps.myMap.getZoom();
      var layer = Maps.mapLayerType;

      // Most léptünk a képesítős zoom levelre
      if (zoom >= Maps.settings.markerImageFromZoom
        && Store.get('map_zoom') < Maps.settings.markerImageFromZoom) {
        location.reload();
      }

      // App cache
      Store.set('map_mycenter', [lat, lon]);
      Store.set('map_zoom', zoom);

      // Ha van térkép
      if ($('.gmap-link')[0]) {
        $('.gmap-link').attr('href', 'https://www.google.com/maps/@' + lat + ',' + lon + ',215m/data=!3m1!1e3');
      }

      if (Maps.settings.urlPosition) {
        var url_vars = 'lat=' + lat + '&lon=' + lon + '&zoom=' + zoom + '&layer=' + layer;

        if ($('.map-popup:visible').length == 1) {
          url_vars += '&mulap=' + $('.map-popup:visible').data('id');
        } else if (Helper.getURLHashParameter('mulap') > 0) {
          url_vars += '&mulap=' + Helper.getURLHashParameter('mulap');
        }

        if (Helper.getURLHashParameter('kereses')) {
          url_vars += '&kereses';
        }


        // Url hash
        url_hash = url_vars;
        window.location.hash = url_hash;

        history.replaceState({id: 'terkep'}, 'Térkép', document.location);

        // Link, ha van ablaka
        if ($('.mapLink')[0]) {
          $('.mapLink').html(url_vars);
        }
      }
    },


    /**
     * Térkép mozgatás utáni aktualizálások
     * Figyelni tudja a mozgásunkat, és ránk beállítja a nézetet, ha kell
     * @param watchMe - figyelje, ahogy mozgunk
     * @param watchMe - oda is pakolja a nézetet
     */
    locateAndListen: function (watchMe, setView) {
      var setView = typeof setView == 'undefined' ? true : setView;

      _c('Helyzet-megosztási kérés futott le.');

      Maps.myMap.locate({
        setView: setView,
        watch: watchMe,
        enableHighAccuracy: true
      })
        .on('moveend', function (e) {
          Maps.actualize();
        })
        .on('zoomend', function (e) {
          Maps.actualize();
        })
        .on('locationfound', function (e) {
          if (Maps.settings.showMe) {
            // Ez adja a saját pontot hozzá
            Maps.addMyPosMarker(e.latitude, e.longitude);
          }
          $('.mapAccurancy').html(Math.round(e.accuracy));
          $('.mapAltitude').html(e.altitude > 0 ? Math.round(e.altitude) + 'm' : '-');
          Maps.actualize();
          if ($('.mapHome').hasClass('d-none')) {
            $('.mapHome').removeClass('d-none').addClass('d-inline-block');
          }
          // Itt is kell állítanunk, mert a Layout.getMyPos()-ban
          // deklarált figyelés nem aktiválja magát onfocus event után
          // sőt; tökre befagy.
          Store.set('map_mypos', [e.latitude, e.longitude]);
          $myPos = [e.latitude, e.longitude];

          // Ha követés van
          if (Store.get('isTouch') == 1 && Store.get('follow_me') == 1) {
            Maps.myMap.panTo([e.latitude, e.longitude], {animate: true});
          }

        })
        .on('locationerror', function (e) {
          if ($('.mapHome').hasClass('d-inline-block')) {
            $('.mapHome').removeClass('d-inline-block').addClass('d-none');
          }
          //alert("Idő közben a telefonod letiltotta a helyzeted megosztását. Ha szeretnéd, hogy mutassuk, hol jársz, frissítsd az oldalt.");
        });
    },


    follow_me: function() {
      if (Store.get('isTouch') == 0) {
        return;
      }

      if ($('.follow-me-on-map').hasClass('d-none')) {
        $('.follow-me-on-map').removeClass('d-none');
      }

      if (!Store.get('follow_me')) {
        Store.set('follow_me', 0);
      }

      if (Store.get('follow_me') == 1) {
        $('.map-settings-icon').removeClass('fa-cog').addClass('fa-walking');
        $('.follow-me-on-map').addClass('active');
      } else {
        $('.map-settings-icon').removeClass('fa-walking').addClass('fa-cog');
        $('.follow-me-on-map').removeClass('active');
      }

      $(document).on('click', '.follow-me-on-map', function(e) {
        e.preventDefault();

        if (Store.get('follow_me') == 1) {
          Store.set('follow_me', 0);
          $('.follow-me-on-map').removeClass('active');
          $('.map-settings-icon').removeClass('fa-walking').addClass('fa-cog');
        } else {
          Store.set('follow_me', 1);
          $('.follow-me-on-map').addClass('active');
          $('.map-settings-icon').removeClass('fa-cog').addClass('fa-walking');
          Maps.locateAndListen(true, false);
        }
      });
    },


    /**
     * Sima location marker rögzítése
     * @param lat
     * @param lon
     * @param erase
     */
    addMyMarker: function(lat, lon, erase, iconType, callNominatim) {
      var erase = typeof erase == 'undefined' ? false : erase;
      var iconType = typeof iconType == 'undefined' ? 'default' : iconType;
      var callNominatim = typeof callNominatim == 'undefined' ? true : callNominatim;
      if (erase && Maps.myMarker) { // ha van, akkor töröljük, ha nem ugyanott vagyunk
        // ugyanaz, nem csinálunk semmit
        if (Maps.myMarker.getLatLng().lat == lat && Maps.myMarker.getLatLng().lng == lon) {
          return;
        }
        Maps.myMap.removeLayer(Maps.myMarker);
      }
      Maps.myMarker = L.marker([lat, lon], {
        icon: Maps.icons[iconType],
        draggable: Maps.settings.markerDraggable
      });
      Maps.myMap.addLayer(Maps.myMarker);

      if (Maps.settings.editing) {
        Artpieces.map_edit(lat, lon, callNominatim);

        Maps.myMarker.on('dragend', function(e) {
          var call_nominatim = e.distance > 20 ? true : false;
          Artpieces.map_edit(e.target._latlng.lat, e.target._latlng.lng, call_nominatim);
        });
      }
    },


    /**
     * Saját pozíciót jelölő marker rögzítése
     * @param lat
     * @param lon
     */
    addMyPosMarker: function(lat, lon) {
      if (Maps.myPosMarker) {
        // ugyanott vagyunk, nem csinálunk semmit
        if (Maps.myPosMarker.getLatLng().lat == lat && Maps.myPosMarker.getLatLng().lng == lon) {
          return;
        }
        Maps.myMap.removeLayer(Maps.myPosMarker);
      }
      Maps.myPosMarker = L.marker([lat, lon], { icon: Maps.icons.location });
      Maps.myMap.addLayer(Maps.myPosMarker);
    },


    /**
     * Szerkesztési funkciók
     */
    editFunctions: function () {
      // Marker lepakolás kattintással
      Maps.myMap.on('click', function(e) {
        Maps.addMyMarker(e.latlng.lat, e.latlng.lng, true);
        Maps.myMap.setView([e.latlng.lat, e.latlng.lng]);
      });

      if (!Maps.myMarker) {
        Maps.addMyMarker(Maps.myMap.getCenter().lat, Maps.myMap.getCenter().lng, false, 'default', true);
      }
    },


    /**
     * Map méretezés;
     * fontos az outer-dolog
     */
    setSize: function (redraw) {
      if (Maps.settings.fullPage) {
        var search_width = $('.artpiece-container').is(':visible') ? $('.artpiece-container').outerWidth() : 0,
          map_height = $(window).height() - $('.header').height();

        $('.artpiece-container').height(map_height);

        $('body').css({
          'padding': 0,
          'margin': 0,
          'overflow-y': 'hidden',
        });
        $('#map').width($(window).outerWidth() - search_width)
          .height(map_height);
        $('#map-topnav').css({
          'top': $('.header').height(),
          'right': search_width
        }).removeClass('d-none');
      }

      if (typeof redraw != 'undefined' && redraw) {
        setTimeout(function() {
          Maps.myMap.invalidateSize();
        }, 375);
      }
    },


    /**
     * Saját lokáció ikonjának villogtatása
     * Remélem, nem epilepszia-generátor. Ha jeleznek, levenni!
     */
    locationIcon: function () {
      setInterval(function(e) {
        if ($('.leaflet-marker-pane img.itsme')[0]) {
          $('.leaflet-marker-pane img.itsme').attr('src', '/img/maps/kt-dot-pulse.png');
          setTimeout(function () {
            $('.leaflet-marker-pane img.itsme').attr('src', '/img/maps/kt-dot.png');
          }, 150);
        }
      }, 2000);
    },


    /**
     * Műlaplista kezelése
     *
     * Az első logika az volt, hogy mindig lekérjük a bound szerinti listát, de
     * ez egy 10-12-es zoomon 1 MB körül volt. Ami ugye minden moveend után lecuppant!...
     * Tehát bevezettem az $app.page_session hash-t, ami a view renderelésekor generálódik és egyedi.
     * Ezt psess néven beküldöm a Artpieces.load_by_bounds függvényben az API-nak, aki
     * így elkezdi gyűjteni a már 1-szer átadott ID-ket és amikor egy kérés átfedésben van
     * egy azonos session-ben már kért bounddal, akkor csak a különbözetet adja az API.
     * Emiatt viszont nem tudtam az eddigi műlap lista logikát az API válaszra húzni, mert ugye
     * nem jön mindig minden, ami a boundban van.
     * Ezért egy új logikát készítettem, ami kiolvassa a bound szerinti markereket,
     * amikbe belepakoltam a legfontosabb néhány adatot. Ebből építi meg a műlap listát.
     * Tehát nincs plussz API hívás.
     *
     * Gondolkodtam még azon, hogy a térkép megnyitásakor 1-szer leszedjem az egészet
     * local store-ba. De ezzel meg az a baj, hogy hogy a mongo lat,lng radius logikája tökéletes,
     * a local store-ból viszont bajosabb kiolvasni. Persze ki is rakhatnám azzal az egy kéréssel, de akkor
     * a lassabb netesek böngije beáll két percre, mert a markerek kipakolásakor a kiskép is letöltődik
     * a szerverről, vagyis 30.000 request megy az amazonnak egy ilyen építéskor. Nem mintha nem
     * ugyanennyi megy akkor, ha valaki pl. kap egy linket, amin a föld teljes képe látszik zoom szerint
     * és ugyanígy indul... ehh. Ezért jutok sokszor arra, hogy kisebb zoomnál egyszerűen nem kell
     * kiolvasni mindent. De akkor mit? Na, erre gondolom megvan a megfelelő matek/spatial képlet, hogy
     * kiszedjük a kis zoom esetén az összes olyan koordinátát, amivel a legnagyobb "láttatással"
     * fedjük le az összes gócpontot egy látható területen. Tessék: szabad a pálya ;)
     *
     * A nagyobb kérések miatt overly logikát írtam, így a 13-az zoomnál kisebb esetekben
     * blokkolom a page-et amíg tölt az API.
     *
     */
    artpieces_by_bounds: function(artpiece_ids) {
      // Adott bound betöltése, ha
      var bounds = Maps.myMap.getBounds(),
        building = true;

      if (Store.get('map_zoom') < 7 && Maps.artpieceMarkers) {
        Html.overlay('show');
      }

      Artpieces.load_by_bounds(bounds, function(response) {
        Maps.artpiece_markers(response);
        Maps.artpiece_thumbnail_list(bounds, { limit: 100 });
        Html.overlay('hide');
        building = false;

        // Műlap AZ az URL-ben; így indultunk, most nyithatjuk
        if (Helper.getURLHashParameter('mulap')) {
          $('img.artpiece-marker-' + Helper.getURLHashParameter('mulap')).click();
        }
      });

      Maps.myMap.on('moveend', function(e) {
        if (Store.get('map_zoom') < 7) {
          Html.overlay('show');
        }

        if (!building) {
          building = true;
          bounds = Maps.myMap.getBounds();
          Artpieces.load_by_bounds(bounds, function (response) {
            Maps.artpiece_markers(response);
            Maps.artpiece_thumbnail_list(bounds, {limit: 100});
            Html.overlay('hide');
            building = false;
          });
        }
      });
    },


    /**
     * Kapott id lista betöltése a térképre
     * @param artpiece_ids
     */
    artpieces_by_ids: function (artpiece_ids) {

      Artpieces.load_by_ids(artpiece_ids, function(response) {
        Maps.artpiece_markers(response, true);
        Maps.artpiece_thumbnail_list(false, {limit: 100});
      });

    },


    /**
     * Alkotás markerek kipakolása, ha kell
     * @param artpieces
     * @param fit_bounds - true esetén kizoomolunk, hogy beférjen minden kirakott marker
     */
    artpiece_markers: function(artpieces, fit_bounds) {
      if (Maps.artpieceMarkers) {

        var fit_bounds = typeof fit_bounds == 'undefined' ? false : fit_bounds;

        if (fit_bounds) {
          var fit_markers = new L.featureGroup();
        }

        artpieces.forEach(function (elem) {
          if (typeof Maps.markerList[elem.i] == 'undefined') {

            if (Maps.myMap.getZoom() >= Maps.settings.markerImageFromZoom) {
              var icon = L.icon({
                //iconUrl: '/img/maps/marker-icon-2x-grey.png',
                iconUrl: $app.s3_photos + elem.p + '_6.jpg',
                iconSize: [50, 50],
                iconAnchor: [25, 25],
                className: 'img-thumbnail artpiece-marker artpiece-marker-' + elem.i
              });
            } else {
              var icon = L.icon({
                iconUrl: '/img/maps/marker-icon-2x-grey.png',
                shadowUrl: '/img/maps/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41],
                className: 'artpiece-marker artpiece-marker-' + elem.i
              });
            }

            marker = L.marker(
              new L.LatLng(parseFloat(elem.l[1]), parseFloat(elem.l[0])),
              {
                icon: icon,
                title: elem.t,
                data_title: elem.t,
                data_photo: elem.p,
                data_id: elem.i,
                data_condition: elem.c,
                data_location: elem.l2,
              }
            );

            var condition_text = '';
            if (elem.c > 1) {
              var cond = $sDB['artpiece_conditions'][elem.c];
              condition_text += '<div class="font-weight-normal badge badge-lg badge-' + cond[4] + '">';
              condition_text += '<span class="fal fa-' + cond[5] + ' mr-2"></span>';
              condition_text += cond[0];
              condition_text += '</div>';
            }

            var popupContent = '<div class="map-popup text-center" data-id="' + elem.i + '">'
              + Html.link('<img src="' + $app.s3_photos + elem.p + '_5.jpg" class="img-thumbnail img-fluid">', '', {'artpiece': {id: elem.i, title: elem.t}})
              + '<h5 class="mt-2">' + Html.link(elem.t, '', {'artpiece': {id: elem.i, title: elem.t}}) + '</h5>'
              + condition_text
              + '</div>';
            marker.bindPopup(popupContent);
            Maps.markerClusterer.addLayer(marker);
            //Maps.markerList[elem.i] = clusteredMarker;
            // a fotó kell, azt cseréljük majd
            Maps.markerList[elem.i] = true;

            if (fit_bounds) {
              fit_markers.addLayer(marker);
            }
          }
        });
        Maps.myMap.addLayer(Maps.markerClusterer);


        if (fit_bounds) {
          Maps.myMap.fitBounds(fit_markers.getBounds());
        }
      }
    },


    /**
     * Aktuális bound alapján mutatja a műlap listát,
     * kapott limitben maximalizálva
     *
     * @param options
     */
    artpiece_thumbnail_list: function (bounds, options) {
      if ($('.artpiece-container:visible').length == 0) {
        return;
      }

      if (!bounds) {
        bounds = Maps.myMap.getBounds();
      }

      var options = _arr(options, {
        limit: 200,
      });

      $('.maxInfo').remove();
      $('.artpiece-list .artpiece-item').remove();

      var counted = 0;

      Maps.get_bound_artpieces(bounds, {limit: options.limit}, function(artpiece) {
        var a = {
          id: artpiece.data_id,
          title: artpiece.data_title,
          photo_slug: artpiece.data_photo,
          lat: artpiece.lat,
          lon: artpiece.lon,
        };
        if (!$('.artpiece-list .artpiece-' + a.id)[0]) {
          var s = '<div class="col-6 col-md-4 mb-4 artpiece-item artpiece-' + a.id + ' text-center">';

          s += Html.link(
            '<img src="' + $app.s3_photos + a.photo_slug + '_5.jpg" class="img-fluid img-thumbnail" />',
            '#',
            {
              'class': 'showOnMap',
              'data-lat': a.lat,
              'data-lon': a.lon,
              'data-id': a.id,
            }
          );


          s += '<div class="mt-1 text-center small font-weight-bold">' + Html.link(a.title, '', {
              artpiece: {id: a.id, title: a.title},
              icon_right: 'arrow-right fal',
            }) + '</div>';

          s += '</div>';

          $('.artpiece-list').append(s);
          counted++;
        }
      });

      if (counted == options.limit) {
        $('.artpiece-list').append('<div class="col-12 py-2 text-muted text-center maxInfo">Maximum ' + options.limit + ' alkotást mutatunk a térképszelvényről.</div>');
      }
    },


    /**
     * Artpiece markerek artpiece jellegű adatainak kiolvasása a látható boundon belül.
     * Nem volt egyszerű.
     * Alapvetően innen indultam:
     * https://stackoverflow.com/questions/22081680/get-a-list-of-markers-layers-within-current-map-bounds-in-leaflet
     *
     * Ezt megfűszerezte a markercluserer. Tehát végigprögetek mindent,
     * és ha az adott boundban van és van artpieces-re utaló adata, akkor rátoljuk
     * a callbacke-et.
     *
     *
     * @param bounds
     * @param options
     * @param callback
     */
    get_bound_artpieces: function(bounds, options, callback) {
      var options = _arr(options, {
          limit: 200,
        }),
        artpieces = [],
        counted = 0;

      Maps.myMap.eachLayer (function(layer) {
        if (layer instanceof L.Marker && bounds.contains(layer.getLatLng())) {
          if (typeof (layer._markers) !== 'undefined') {
            (layer._markers).forEach(function (marker) {
              if (typeof marker.options.data_id != 'undefined') {
                counted++;
                if (counted > options.limit) {
                  return;
                }
                if (typeof artpieces['id' + marker.options.data_id] == 'undefined') {
                  // Még nincs meg, futtatjuk a callback-et
                  marker.options.lat = marker._latlng.lat;
                  marker.options.lon = marker._latlng.lng;
                  callback(marker.options);
                }
                artpieces['id' + marker.options.data_id] = true;
              }
            });
          } else if (typeof (layer.options.data_title) !== 'undefined') {
            counted++;
            if (counted > options.limit) {
              return;
            }
            if (typeof artpieces['id' + layer.options.data_id] == 'undefined') {
              // Még nincs meg, futtatjuk a callback-et
              layer.options.lat = layer._latlng.lat;
              layer.options.lon = layer._latlng.lng;
              callback(layer.options);
            }
            artpieces['id' + layer.options.data_id] = true;
          }
        } else if (layer instanceof L.MarkerClusterGroup) {
          // Clusetered markerek...
          layer.eachLayer(function(marker) {
            if (bounds.contains(marker.getLatLng())) {
              counted++;
              if (counted > options.limit) {
                return;
              }
              if (typeof artpieces['id' + marker.options.data_id] == 'undefined') {
                // Még nincs meg, futtatjuk a callback-et
                marker.options.lat = marker._latlng.lat;
                marker.options.lon = marker._latlng.lng;
                callback(marker.options);
              }
              artpieces['id' + marker.options.data_id] = true;
            }
          });
        }
      });

      return;
    },


    /**
     * Clustering folyamat, de nem igazán bír megjelenni
     * @todo: jelenjen meg! :)
     *
     * @param processed
     * @param total
     * @param elapsed
     * @param layersArray
     */
    marker_progress: function (processed, total, elapsed, layersArray) {
      if ($('#leaflet-progress')[0]) {
        if (elapsed > 100) {
          $('#leaflet-progress').removeClass('d-none').addClass('d-block');
          $('#leaflet-progress-bar').css({
            'width': Math.round(processed / total * 100) + '%'
          });
          if (processed === total) {
            $('#leaflet-progress').removeClass('d-block').addClass('d-none');
          }
        }
      }
    },


  };