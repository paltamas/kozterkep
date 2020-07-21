var s,
  Showroom = {

    files: [],
    pointer: 0,
    touchposition: 0,
    thumbnails_html: '',

    settings: {
      base_hash: '',
      referrer: document.referrer,
      original_meta_image: $('meta#metaimage').attr('content'),
    },

    init: function () {

      var that = this;

      var init_file = Helper.getURLHashParameter('vetito');

      if (init_file && $('[ia-showroom]')[0]) {
        $('[ia-showroom]').each(function(key, elem) {
          if ($(elem).attr('ia-showroom-file') == init_file && $('#showroom').length == 0) {
            that.build(elem);
          }
        });
      }

      if ($('[ia-showroom]')[0]) {
        this.bindUIActions();

        window.addEventListener("hashchange", function() {
          if ($('#showroom')[0] && !Helper.getURLHashParameter('vetito')) {
            Showroom.close();
          } else if (!$('#showroom')[0] && Helper.getURLHashParameter('vetito')) {
            that.build(Helper.getURLHashParameter('vetito'));
          } else if ($('#showroom')[0] && Helper.getURLHashParameter('vetito')) {
            var file = $('[ia-showroom-file=' + Helper.getURLHashParameter('vetito') + ']');
            that.load_file('#showroom .file-container', file);
          }
        }, false);
      }
    },


    bindUIActions: function () {

      var that = this;

      $(document).on('click', '[ia-showroom]', function(e) {
        e.preventDefault();
        that.build(this);
      });

      $(document).on('click', '.load-file', function(e) {
        e.preventDefault();
        var file_id = $(this).data('file-id');
        var file = $('[ia-showroom-file=' + file_id + ']');
        $.each(Showroom.files, function(key, elem) {
          if (elem.id == file_id) {
            Showroom.pointer = key;
            return;
          }
        });
        $('.act-pointer').html(Showroom.pointer + 1);
        that.load_file('#showroom .file-container', file);
      });

      $(document).on('click', '.close-showroom', function(e) {
        e.preventDefault();
        Showroom.close();
      });

      $(document).keyup(function (e) {
        if ($('#showroom')[0]) {
          if (e.keyCode == 27) {
            Showroom.close();
          }

          if (e.keyCode == 37 || e.keyCode == 39) {
            var step = e.keyCode == 37 ? -1 : 1;
            Showroom.step(step);
          }
        }
      });

      $(document).on('click', 'div.swipe-step', function(e) {
        e.preventDefault();
        Showroom.step(1);
      });

      $(document).on('click', '.step-left', function(e) {
        e.preventDefault();
        Showroom.step(-1);
      });
      $(document).on('click', '.step-right', function(e) {
        e.preventDefault();
        Showroom.step(1);
      });

      // Khm.
      $(window).resize(function() {
        $('.img-fluid').css('max-height', $(window).height());
        $('#showroom .info-container .info').css('min-height', $(window).height());
      });
    },


    // Kiszerveztem, mert a megnyitáskor és az URL miatti megnyíláskor is meg kell hívni
    handleSwipe: function() {
      if (Store.get('isTouch') == 1) {
        var hammertime = new Hammer(document.getElementById('swipeable'), {});
        hammertime.on('swipeleft', function(ev) {
          Showroom.step(1);
        });
        hammertime.on('swiperight', function(ev) {
          Showroom.step(-1);
        });
        hammertime.on('pinch pinchmove', function(ev) {
          return true;
        });
      }
    },


    /**
     * Megépítés
     * @param file
     */
    build: function(file) {

      //_c($(file).attr('ia-showroom-container'));

      var elements = '[ia-showroom-file]';
      if ($(file).attr('ia-showroom-container') && $(file).attr('ia-showroom-container') != '') {
        elements = $(file).attr('ia-showroom-container') + ' ' + elements;
      }


      // Ha nem fájlra kattintottunk, akkor megkeressük az elsőt
      if (typeof $(file).attr('ia-showroom-file') == 'undefined') {
        var file = $(elements)[0];
      }

      var room_id = $(file).attr('ia-showroom'),
        act_file_id = $(file).attr('ia-showroom-file'),
        act_file_path = $(file).attr('ia-showroom-file-path'),
        act_file_type = $(file).attr('ia-showroom-file-type'),
        base_hash = $(file).attr('ia-showroom-hash') && $(file).attr('ia-showroom-hash') != ''
          ? $(file).attr('ia-showroom-hash') : '',
        i = 0;

      if (base_hash != '') {
        Showroom.settings.base_hash = base_hash;
      }

      // Showroom div
      $('body').css('overflow', 'hidden');
      $('body').prepend('<div class="position-fixed w-100 h-100 bg-black" style="z-index: 1000; overflow-y: scroll; -webkit-overflow-scrolling: touch" id="showroom"></div>');

      // Hogy belenagyíthassanak
      if ($('meta#viewport')[0]) {
        $('meta#viewport').attr('name', 'viewport-inact');
        $('head').append('<meta name="viewport" id="viewport-showroom" content="width=device-width, initial-scale=1, maximum-scale=12.0, minimum-scale=.25, user-scalable=yes">');

      }

      // Kezdő fájl és showroom blokkok
      $('#showroom').html(
        '<div class="fixed-top text-left p-1 btn-group tools-container pl-md-2">'
        + '<div class="col-6 text-left p-0 m-0">'
        + '<div class="btn-group">'
        + '<a href="#" class="step-left btn btn-secondary" data-toggle="tooltip" title="A fájlok közti lépkedéshez használhatod a billentyűzeted jobbra-balra nyilait."><span class="far fa-arrow-left"></span></a>'
        + '<a href="#" class="step-right btn btn-secondary" data-toggle="tooltip" title="A fájlok közti lépkedéshez használhatod a billentyűzeted jobbra-balra nyilait."><span class="far fa-arrow-right"></span></a>'
        + '</div>' // btn-group
        + '<span class="small text-white ml-2 text-shadow"><span class="act-pointer"></span> / <span class="files-count"></span></span>'
        + '</div>' // step-col

        + '<div class="col-6 text-right p-0 m-0 pr-3">'
        + '<a href="#" class="close-showroom btn btn-secondary d-inline-block px-3"><span class="far fa-times"></span></a>'
        + '</div>' // close-col
        + '</div>' // top-container

        + '<div class="row m-0 h-100 p-0 d-flex">'
        + '<div class="col-lg-9 col-md-8 text-center px-0 pt-5 pt-md-0 justify-content-center align-self-top">'
        + '<div class="file-container swipe-step" id="swipeable"></div>'
        + '</div>' // col-9
        + '<div class="col-lg-3 col-md-4 p-0 pt-md-2 mt-4 mt-md-0 info-container bg-gray-kt">'
        + '</div>' // col-3
        + '</div>' // row
      );

      // Megépítem a fájl sort
      $(elements).each(function(key, elem) {
        Showroom.files[i] = {
          'id': $(elem).attr('ia-showroom-file'),
          'path': $(elem).attr('ia-showroom-file-path'),
          'type': $(elem).attr('ia-showroom-file-type'),
        };

        if (act_file_id == $(elem).attr('ia-showroom-file')) {
          Showroom.pointer = i;
          $('.act-pointer').html(i+1);
        }

        i++;
      });

      Showroom.thumbnails_html = Showroom.build_thumbnails(Showroom.files);

      $('.files-count').html(Showroom.files.length);

      // Ne zavarjon be
      $('.scroll-top').hide();

      // Fájl kép betöltése, ez tölti az infót is
      Showroom.load_file('#showroom .file-container', file);

      // Swipe itt, mert már van mire
      Showroom.handleSwipe();

      //
      Layout.initListeners('#showroom');
    },

    load_file: function(target, file_element, file_id, file_type, file_path) {
      // Hogy ne essen össze mobilon betöltődés közben
      $(target).css('min-height', '35vh');

      // Ha nem elementből olvasunk, hanem kapjuk
      if (file_element) {
        var file_id = $(file_element).attr('ia-showroom-file'),
          file_type = $(file_element).attr('ia-showroom-file-type'),
          file_path = $(file_element).attr('ia-showroom-file-path');
      }

      var preview = Helper.filePreview(file_type, file_path, 'img-fluid', 'fa-10x text-white', file_id);

      // Head og:image cserélése, hogy megosztáskor szép legyen
      if (file_type == 'image') {
        $('meta#metaimage').attr('content', $app.domain + '/eszkozok/kepmutato/' + file_id + '?meret=1');
      }

      window.location.hash = 'vetito=' + file_id;
      $(target).html(preview);

      $('.img-fluid').css('max-height', $(window).height());

      // Információk betöltése
      $('#showroom .info-container').html($('.showroom-info-source#file-info-' + file_id).html());
      $('#showroom .info-container .info').css('min-height', $(window).height());

      $('#showroom .thumbnails').html(Showroom.thumbnails_html);
      // Thumbnaileken is
      $('.thumbnails .img-thumbnail').removeClass('bg-primary');
      $('.thumbnails .showroom-thumbnail-' + (Showroom.pointer+1)).addClass('bg-primary');
    },

    step: function(step) {
      var pointer = Showroom.pointer;

      // Léptetem
      pointer += step;

      if (Showroom.files.length <= pointer) {
        // Elértük a végét előre lépkedve
        pointer = 0;
      } else if (pointer < 0) {
        // Elejére értünk visszafelé lépkedve
        pointer = Showroom.files.length-1;
      } else {
        // Egyébként előtöltjük a köv.következő képet
        var next = pointer+1;
        if (typeof Showroom.files[next] != 'undefined') {
          Helper.preloadImg(Showroom.files[next].path);
        }
      }

      // Ha már, akkor load
      if (pointer != Showroom.pointer) {
        Showroom.load_file(
          '.file-container',
          false,
          Showroom.files[pointer].id,
          Showroom.files[pointer].type,
          Showroom.files[pointer].path
        );
        Showroom.pointer = pointer;
      }

      // Hol állunk
      $('.act-pointer').html(Showroom.pointer+1);
    },


    build_thumbnails: function(files) {
      var s = '', i = 0, active_bg = '';
      if (files.length > 1) {
        $(files).each(function(key, file) {

          if (file.type == 'image') {
            i++;
            var src = (file.path).replace('_1.jpg', '_5.jpg'),
              image_width = files.length < 24 ? 75 : 40;
              image = '<img src="' + src + '" class="s3-image showroom-thumbnail-' + i + ' img-thumbnail'
                + ' img-fluid mb-1 mr-1 img-fluid" width="' + image_width + '" >';
            s += Html.link(image, '#', {
              'class': 'load-file',
              'data-file-id': file.id,
            });
          }
        });

        if (i > 0) {
          s = '<hr class="my-4" />' + s;
        }
      }

      return s;
    },

    close: function() {
      Showroom.just_closed = true;
      $('#showroom').remove();
      if (Showroom.settings.base_hash != '') {
        if ($('#' + Showroom.settings.base_hash + '-tab')[0] && !$('#' + Showroom.settings.base_hash + '-tab').hasClass('active')) {
          window.location.hash = Showroom.settings.base_hash;
          $('#' + Showroom.settings.base_hash + '-tab').tab('show');
        }
      } else {
        window.location.hash = '';
      }
      $('body').css('overflow', 'auto');
      //$('meta[name=viewport]').remove();
      //$('head').append('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no, user-scalable=no">');

      if ($('meta#viewport-showroom')[0]) {
        $('meta#viewport-showroom').remove();
        $('meta#viewport').attr('name', 'viewport');
      }
      $('meta#metaimage').attr('content', Showroom.settings.original_meta_image);
    },
  };