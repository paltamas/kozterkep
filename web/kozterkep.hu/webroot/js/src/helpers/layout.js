var s,
  Layout = {

    settings: {

      linkifyOptions: {
        target: '_blank',
        ignoreTags: ['textarea', 'input', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'nolink', 'linkify_custom'],
        format: {
          url: function (value) {
            return Helper.getDomain(value)
          }
        }
      },

      linkifyCustomOptions: {
        target: '_blank',
        ignoreTags: ['textarea', 'input', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'nolink']
      },

    },

    init: function () {
      s = this.settings;

      var that = this;

      this.bindUIActions();

      this.embedVideos();

      this.sideMenu();
      $(window).on('resize', function () {
        that.sideMenu();
      });

      $('#site').linkify(Layout.settings.linkifyOptions);
      $('#site linkify_custom').linkify(Layout.settings.linkifyCustomOptions);

      this.codeEditor();

      this.tabTextarea();

      this.toggleElements();

      this.initListeners();

      this.handleImages();

      this.cookieConsent();

      this.donationBanner();
    },

    bindUIActions: function () {

      _c('Helló, üdv a Köztérképen!\nJó böngészést! :]');

      // Reflektorfény, mindenáron
      $('.focus').focus();
      $('.modal').on('shown.bs.modal', function () {
        $('.modal .focus').focus();
      });

      // Escre mi legyen
      $(document).keyup(function (e) {
        if (e.keyCode == 27) {
          $('*').tooltip('hide');
          $('*').popover('hide');
          $('.tooltipster-base').hide();
          $('.autocomplete-dropdown').remove();
          $('.bubbleContainer .alert-info.alertContainer').remove();
          $('.bubbleContainer .alert-success.alertContainer').remove();
        }
      });

      // Hamburger menü trükközés
      $('.navbar-toggler').bind('click', function (e) {
        $('span.toggler-icon').toggleClass('fa-times pl-2 fa-bars');
      });


      // Accordion read more szöveghez
      $(document).on('hidden.bs.collapse', '.accordion .collapse', function () {
        $($(this).data('collapse-pair')).addClass('show');
      });

      // Odaúszunk egy ID-hez
      if (Helper.getURLHashParameter('hopp')) {
        var target = Helper.getURLHashParameter('hopp');
        var position = $('#' + target).offset();
        $('html, body').stop(true, false).animate({scrollTop: position.top}, 375);
      }


      /**
       *
       */
      if (Store.get('isApp') == 1 && ($app.here).indexOf('/mulapok/szerkesztes/') == -1) {
        var isOnIOS = navigator.userAgent.match(/iPad/i)|| navigator.userAgent.match(/iPhone/i);
        var eventName = isOnIOS ? "pagehide" : "beforeunload";
        window.addEventListener(eventName, function (event) {
          Html.overlay('show');
          return;
        });
      }

      var active_menu_before = '';
      $(document).on('click', '.header-search-toggle', function (e) {
        e.preventDefault();
        if ($app.here_path == '/kereses') {
          return;
        }
        if (!$(this).hasClass('active')) {
          active_menu_before = $('.header .navbar .nav-icon.active');
          $('.header .navbar .nav-icon').removeClass('active');
          $(this).addClass('active');
        } else {
          $(this).removeClass('active');
          $(active_menu_before).addClass('active');
        }
      });


      $(document).on('click', '.dropdown-menu.dont-close-this', function (e) {
        e.stopPropagation();
      });

      // Readmore linkek
      $(document).on('click', '[ia-showfull]', function(e) {
        e.preventDefault();
        var target = $(this).attr('ia-showfull');
        $(target).removeClass('d-none');
        $(target + '-intro').addClass('d-none');
      });
      $(document).on('click', '[ia-showless]', function(e) {
        e.preventDefault();
        var target = $(this).attr('ia-showless');
        $(target).addClass('d-none');
        $(target + '-intro').removeClass('d-none');
      });

      // Robbbbbanás!%**=/%!!
      $(document).on('click', '.explode', function(e) {
        e.preventDefault();
        Layout.explode('circle', e.pageX, e.pageY);
      });
    },


    initListeners: function(parent) {
      var parent = typeof parent != 'undefined' && parent != '' ? parent + ' ' : '';

      // Tooltipek engedélyezése
      if (Store.get('isTouch') != 1) {
        $(parent + '[data-toggle="tooltip"]').tooltip({
          html: true,
          //delay: {show: 200, hide: 0},
        });
      }

      $(parent + '[data-toggle="popover"]').popover({html: true});

      autosize($(parent + 'textarea:not(.code-editor)'));

      Domupdates.showPreviews(parent);

      $('.html-editor').summernote({
        height: 500,
        focus: true,
        toolbar: [
          [1, ['style', 'bold', 'italic', 'underline', 'strikethrough', 'paragraph']],
          [2, ['ul', 'ol', 'table', 'picture', 'video', 'link', 'hr']],
          [3, ['fullscreen', 'codeview', 'clear']],
        ],
        codeviewFilter: false,
        codeviewIframeFilter: true,
      });

      if (parent != '') {
        $(parent).linkify(Layout.settings.linkifyOptions);
        $(parent + ' linkify_custom').linkify(Layout.settings.linkifyCustomOptions);
        Forms.filePreview(parent);
      }
    },

    /**
     * Layout változók local store-ba írása
     */
    setVars: function () {

      // App-e?
      // ha már az, vagy most mondjuk, hogy az
      if (Store.get('isApp') == 1
        || Helper.getURLParameter('android-app') !== false || Helper.getURLParameter('ios-app') !== false) {
        Store.set('isApp', 1);

        if (Helper.getURLParameter('android-app') !== false) {
          Store.set('isAppAndroid', 1);
        }
        if (Helper.getURLParameter('ios-app') !== false) {
          Store.set('isAppIos', 1);
        }

      } else {
        Store.set('isApp', 0);
      }

      // Hogy vissza tujunk állni
      $app.page_content = $('main').html();

      // Érintőkijelzős eszköz
      if ('ontouchstart' in window || navigator.maxTouchPoints) {
        Store.set('isTouch', 1);
        $('.hide-on-touch').addClass('d-none');
        $('.only-on-touch').removeClass('d-none');
      } else {
        Store.set('isTouch', 0);
        $('.only-on-touch').addClass('d-none');
      }

      // App fixációk
      if (Store.get('isApp') == 1) {
        /*$('.header-bottom .navbar').addClass('bg-gray-kt fixed fixed-top');
        $('.container.content').removeClass('mt-3').addClass('mt-5');
        $('.tools-container.fixed-top').addClass('mt-5');
        $('#instant-search-container').removeClass('pt-3').addClass('mt-4 pt-5');*/

        if (Store.get('isAppIos') == 1) {
          // Notch
          $('.container.content').addClass('mb-5');
          $('.map-container .fixed-bottom').addClass('mb-5');
        }
      }

      // Aktív-e az ablak
      Store.set('windowVisible', 1);
      window.onblur = function () {
        Store.set('windowVisible', 0);
        return false;
      }
      window.onfocus = function () {
        Users.getLatest();
        Store.set('windowVisible', 1);
        return true;
      }

      Store.set('legeslegfontosabbvaltozoamisosevaltozik', 'Szuperjó ez a Köztérkép, igaz?!');

      // Hogy.
      if ($('#Email-confirm')[0]) {
        $('#Email-confirm').attr('required', false);
        $('#Email-confirm').parent('.form-group').css('display', 'none');
      }

    },


    /**
     * Tabok logikái
     */
    handleTabs: function () {

      // Ha van hash, akkor azzal dolgozzunk, mert az tab; kivéve a hoppos,
      // ami odaúszos cucc, oda úszni kell
      if ($app.hash !== '' && ($app.hash).indexOf('hopp') === -1) {
        window.scrollTo(0, 0);
        $('ul.nav a[href="' + $app.hash + '"]').tab('show');
        // Beállítjuk a selectedet is, ha
        $app.redirect_hash = ($app.hash).replace('#', '');
      }

      // Tab navigáció okozzon hash-t
      $('.nav-tabs a, .nav-pills a, .my-nav-tabs a, .my-nav-pills a, .tab-button, a.tab-link').click(function (e) {
        window.location.hash = this.hash;
        // A térkép nem jól méretezi magát, szóval ha van...
        if ($('.leaflet-container')[0]) {
          setTimeout(function() {
            Maps.myMap.invalidateSize();
          }, 500);
        }
        // Komment van
        var tab_pane = '#' + $(this).attr('id').replace('-tab', '');
        if ($(tab_pane + ' .comment-thread')[0]) {
          Comments.build_thread();
        }

        // Tab állapotot mentjük egy inputba, hogy tudjuk, hova kell dobni, ha
        $app.redirect_hash = (this.hash).replace('#', '');
      });

      // Tabot nyit és odaúszik...
      $('.tab-button').click(function(e) {
        e.preventDefault();
        $($(this).attr('href') + '-tab').tab('show');
        $('html, body').stop(true, false).animate({scrollTop: 0}, 'slow');
      });

      // ...csak átslattyan
      $('a.tab-link').click(function (e) {
        e.preventDefault();
        window.location.hash = this.hash;
        $('ul.nav a[href="' + this.hash + '"]').tab('show');
      });


      /**
       * Ezzel a trükközéssel azt oldjuk meg, ha az URL-ben van hash,
       * és az nem arra a tabra mutat, ami alapértelmezetten aktív.
       * PHP-val nem lehet hash-t olvasni, vagyis mindenképp JS-sel kell váltani,
       * és ilyenkor ugrál. Nameg a leaflet elromlik, mert még nincs div mérete,
       * amikor renderelődik.
       *
       * Hogy ez menjen és segítsen, a tab-content class-nál manuálisan
       * be kell tenni a d-none class-t is.
       *
       */
      if ($('ul.nav.d-none')[0]) {
        $('.tab-content.d-none').before(Html.loading('tabs-loading'));
        //setTimeout(function() {
          $('ul.nav.d-none').removeClass('d-none');
          if ($('.tab-content.d-none')[0]) {
            $('.tab-content.d-none').removeClass('d-none');
          }
          $('.tabs-loading').remove();
          // Itt is kell térkép méretezés
          if ($('.leaflet-container')[0]) {
            Maps.myMap.invalidateSize();
          }
        //}, 300);
      }
    },


    /**
     * Felgörgető inicializálás
     * nem lehet egybetenni a következővel, mert összeakad
     */
    scrollTopInit: function () {
      var that = this;

      that.scrollTopShow();

      $(window).on('scroll', function () {
        that.scrollTopShow();
      });

      $('.scroll-top').click(function (e) {
        e.preventDefault();
        $('html, body').stop(true, false).animate({scrollTop: 0}, 'slow');
      });
    },

    /**
     * Felgörgetéshez mutató mutatás
     */
    scrollTopShow: function () {
      /*if ($('.navbar-toggler').is(':visible')) {
        return;
      }*/

      var that = this;

      // Nyíl megjelenítése
      var top = $(window).scrollTop();
      if (top < 300) {
        $('.scroll-top').hide();
      } else {
        $('.scroll-top').removeClass('d-none').show();
      }
    },


    /**
     * Adott elemet collapsol. Azért itt,
     * mert van amit csak így lehet runni.
     */
    collapse_toggle: function(elemId) {
      if ($(elemId)[0]) {
        $(elemId).collapse('toggle');
      }
    },

    /**
     * Help szövegek megjelenítése és elrejtése
     */
    displayHelp: function () {

      if (!Store.get('help')) {
        Store.set('help', 1);
      }

      if (Store.get('help') == '0') {
        $('.helpSwitch span.far').removeClass('fa-info-circle').addClass('fa-question');
        $('.formHelp, .helpText').addClass('d-none');
      }

      $('.helpSwitch').on('click', function (e) {
        e.preventDefault();
        if (Store.get('help') == 0) {
          $('.formHelp, .helpText').removeClass('d-none');
          Store.set('help', 1);
          $('.helpSwitch span.far').removeClass('fa-question').addClass('fa-info-circle');
          Alerts.flashBubble('Bekapcsoltuk az űrlapokon a segédleteket.', 'info', {'delBefore': true});
        } else {
          $('.formHelp, .helpText').addClass('d-none');
          Store.set('help', 0);
          $('.helpSwitch span.far').removeClass('fa-info-circle').addClass('fa-question');
          Alerts.flashBubble('Elrejtettük az űrlapokon a segédleteket.', 'info', {'delBefore': true});
        }
      });

    },


    /**
     * Széles és normál nézet közti váltás
     */
    switchView: function () {

      Store.set('fluidView', $app.fluid_view);

      $('.viewSwitch').on('click', function (e) {
        e.preventDefault();
        if (Store.get('fluidView') == 0) {
          $('.container').removeClass('container').addClass('container-fluid');
          Store.set('fluidView', 1);
          $('.viewSwitch span.far').removeClass('fa-tv').addClass('fa-desktop-alt');
          Alerts.flashBubble('Átváltottunk teljes szélességű nézetre.', 'info', {'delBefore': true});
        } else {
          $('.container-fluid').removeClass('container-fluid').addClass('container');
          Store.set('fluidView', 0);
          $('.viewSwitch span.far').removeClass('fa-desktop-alt').addClass('fa-tv');
          Alerts.flashBubble('Visszaváltottunk normál szélességű nézetre.', 'info', {'delBefore': true});
        }
      });

    },


    /**
     * head favicon csere, alert és normál közt tud most ugratni
     * @param alert
     */
    changeFavicon: function (alert) {
      if (typeof alert == 'undefined') {
        var alert = false;
      }

      var default_icon = '/img/kozterkep-app-icon.png',
        alert_icon = '/img/kozterkep-app-icon-alerted.png',
        current_icon = Helper.getFavicon();

      // Beállítjuk, ha más böngi fül még nem tette volna
      if ((!alert && Store.get('favicon') == 'alerted')
        || (alert && Store.get('favicon') == 'default')) {
        Store.set('favicon', alert ? 'alerted' : 'default');
      }

      // ...és az ikont is csak akkor állítjuk, ha még nem az ezen a fülön
      if ((alert && current_icon != alert_icon)
        || (!alert && current_icon != default_icon)) {
        var link = document.querySelector("link[rel*='icon']") || document.createElement('link');
        link.type = 'image/x-icon';
        link.rel = 'shortcut icon';
        link.href = alert ? alert_icon : default_icon;
        document.getElementsByTagName('head')[0].appendChild(link);
        if (!alert) {
          // Most lett nulla, így ezt csúnyán megelőlegezzük
          $('.sum-alert-count').removeClass('bg-orange-dark px-1').addClass('px-2').html('&nbsp;');
        }
      }
    },


    // Sidemenu coollapse legyen kisebb kijelzőkön
    sideMenu: function () {
      if ($('.navbar-toggler').is(':visible')) {
        if (!$('.sideMenuToggle')[0]) {
          $('.sidemenu-title span').wrap('<a href="#sideMenuCollapse" data-toggle="collapse" class="text-muted d-inline-block w-75 sideMenuToggle"></a>');
          $('.sidemenu-title').append('<a href="#sideMenuCollapse" data-toggle="collapse" class="sideMenuToggle float-right d-block link-gray"><span class="far fa-chevron-square-down fa-lg"></span></a>');
          $('.sidemenu-list').addClass('collapse').attr('id', 'sideMenuCollapse');
          $('.sidemenu-list .list-group').removeClass('d-none d-md-block');
        }
      } else {
        $('.sideMenuToggle').remove();
        $('.sidemenu-list').removeClass('collapse').attr('id', '');
        $('.sidemenu-list .list-group').addClass('d-none d-md-block');
      }
    },


    /**
     * HTML-editorban beillesztett videó reszponzívvá tétele
     */
    embedVideos: function() {
      if ($('.embed-container iframe.note-video-clip')[0]) {
        $('.embed-container iframe.note-video-clip').each(function(key, elem) {
          $(elem).addClass('embed-responsive-item')
            .wrap('<div class="embed-responsive embed-responsive-16by9"></div>')
            .css('display', 'block');
        });
      }
    },


    // Szuperegyszerű kódszerkesztő
    codeEditor: function() {
      if ($('.code-editor')[0]) {
        var editor = $('.code-editor');


        /**
         * Összerakom
         */
        $(editor).addClass('tab-enabled');

        var buttons = '<div class="float-right">'
          + '<a href="#" class="editor-full">Teljes képernyő</a>'
          + '<a href="#" class="editor-preview ml-3">Előnézet</a>';
        $(editor).before(buttons);

        $(editor)
          .height($(window).height())
          .css({
            'font-size': 13,
            'font-family': '"Consolas", monospace',
            'font-weight': 600,
            'color': '#2a2a2a',
          });


        /**
         * Események
         */

        // Teljes képernyő
        $(document).on('click', '.editor-full', function(e) {
          e.preventDefault();
          $(editor).closest('form').toggleClass('position-fixed w-100 h-100 fixed-top bg-white p-5');
        });

        // Előnézet
        $(document).on('click', '.editor-preview', function(e) {
          e.preventDefault();

          $(editor).closest('form').removeClass('position-fixed w-100 h-100 fixed-top bg-white p-5');

          var preview = '<div id="editor-preview" style="min-height: 100vh;">'
            + '<div class="container py-3">'
            + '<a href="#" class="editor-close-preview float-right"><span class="far fa-times btn btn-secondary"></span></a>'
            + $(editor).val()
            + '</div>'
            + '</div>';
          $('body').prepend(preview);

          // Előnézet bezárása
          $(document).on('click', '.editor-close-preview', function(e) {
            e.preventDefault();
            $('#editor-preview').remove();
          });
        });
      }
    },


    /**
     * Tab engedélyezése és érzékelése textareaban
     * thx: https://stackoverflow.com/a/6140696/1118965
     *
     */
    tabTextarea: function() {
      $($('textarea.tab-enabled')).keydown(function(e) {
        if(e.keyCode === 9) { // tab was pressed
          // get caret position/selection
          var start = this.selectionStart;
          var end = this.selectionEnd;

          var $this = $(this);
          var value = $this.val();

          // set textarea value to: text before caret + tab + text after caret
          $this.val(value.substring(0, start)
            + "\t"
            + value.substring(end));

          // put caret at right position again (add one for the tab)
          this.selectionStart = this.selectionEnd = start + 1;

          // prevent the focus lose
          e.preventDefault();
        }
      });
    },


    /**
     * Feltételesen megjelemő div-ek,
     * amik input, select value-től vagy checkbox pipálástól függenek
     */
    toggleElements: function() {
      if ($('[ia-toggleelement-parent]')[0]) {
        $('[ia-toggleelement-parent]').each(function(key, elem) {
          var parent = $(elem).attr('ia-toggleelement-parent'),
            value = $(elem).attr('ia-toggleelement-value');

          if ($(parent).attr('type') == 'checkbox') {
            var parent_value = $(parent).is(':checked') ? 1 : 0;
          } else {
            var parent_value = $(parent).val();
          }

          // Látható-e
          if (parent_value != value) {
            $(elem).addClass('d-none');
          } else {
            $(elem).removeClass('d-none');
          }

          // Figyeljük a szülő változást
          $(parent).change(function(e) {
            $('[ia-toggleelement-parent="' + parent + '"]').each(function(key, target) {
              if ($(parent).attr('type') == 'checkbox') {
                var target_parent_value = $(parent).is(':checked') ? 1 : 0;
              } else {
                var target_parent_value = $(parent).val();
              }
              if ($(target).attr('ia-toggleelement-value') == target_parent_value) {
                $(target).removeClass('d-none');
              } else {
                $(target).addClass('d-none');
              }
            });
          });
        });
      }
    },


    alertIfPostblock:function () {
      if (Store.get('map_posblock') == 1) {
        Alerts.flashModal('Ehhez a funkcióhoz engedélyezned kell a helyzeted megosztását. Ha ezt korábban (véletlenül) letiltottad, akkor a böngésződ beállításaiban tudod ezt megváltoztatni.', 'warning');
      }
    },


    getMyPos: function (success) {
      if ($('#get-location')[0]
        || ($('#get-location-touch')[0] && Store.get('isTouch') == 1)) {

        _c('Helyzet-megosztási kérés futott le.');

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function (e) {
              Store.set('map_mypos', [e.coords.latitude, e.coords.longitude]);
              Store.set('map_posblock', 0);
              $myPos = [e.coords.latitude, e.coords.longitude];

              var watchID = navigator.geolocation.watchPosition(function (position) {
                Store.set('map_mypos', [position.coords.latitude, position.coords.longitude]);
                $myPos = [position.coords.latitude, position.coords.longitude];
              });

              if ($('.mapHome')[0] && $('.mapHome').hasClass('d-none')) {
                $('.mapHome').removeClass('d-none').addClass('d-inline-block');
              }

              success([e.coords.latitude, e.coords.longitude]);

            }, function () {
              Store.set('map_posblock', 1);
              $myPos = false;
              if ($('.mapHome')[0] && $('.mapHome').hasClass('d-inline-block')) {
                $('.mapHome').removeClass('d-inline-block').addClass('d-none');
              }
              return false;
            },
            {
              enableHighAccuracy: true,
              maximumAge: 30000,
              timeout: 27000
            });
        } else {
          Store.set('map_posblock', 1);
          $myPos = false;
          if ($('.mapHome')[0] && $('.mapHome').hasClass('d-inline-block')) {
            $('.mapHome').removeClass('d-inline-block').addClass('d-none');
          }
          return false;
        }
      }

      return false;
    },


    handleImages: function() {

      if ($('.s3-image')[0]) {
        $('.s3-image').each(function(key, elem) {
          var image = new Image();
          image.onerror = function () {
            $(elem).attr('src', '/img/placeholder.png').width(125).after('<div class="mt-1 mb-2 pl-1 text-muted small"><span class="fa fa-info-circle mr-2"></span>Feldolgozás alatt...</div>');
          }
          image.src = $(elem).attr('src');
        });
      }

      /*
      Kell valami, de ez nem OK
      if ($('.img-fluid.presize')[0]) {
        $('.img-fluid.presize').each(function(key, img) {
          $(img).width('100%');
          $(img).height($(img).width());
        });
      }*/

    },


    /**
     * Robbanás
     * css => _layout.scss
     * @source: https://codepen.io/alek/pen/EyyLgp
     *
     * @param x
     * @param y
     */
    explode: function(type_class, x, y) {
      if (typeof type_class == 'undefined') {
        var type_class = 'circle';
      }

      if (typeof x == 'undefined' && typeof y == 'undefined') {
        var x = $(window).width() * 0.5,
          y = $(window).height() * 0.5;
      }

      var particles = 30,
        // explosion container and its reference to be able to delete it on animation end
        explosion = $('<div class="explosion ' + type_class + '"></div>');

      // put the explosion container into the body to be able to get it's size
      $('body').append(explosion);

      // position the container to be centered on click
      explosion.css('left', x - explosion.width() / 2);
      explosion.css('top', y - explosion.height() / 2);

      for (var i = 0; i < particles; i++) {
        // positioning x,y of the particle on the circle (little randomized radius)
        var x = (explosion.width() / 2) + Helper.rand_interval(80, 150) * Math.cos(2 * Math.PI * i / Helper.rand_interval(particles - 10, particles + 10)),
          y = (explosion.height() / 2) + Helper.rand_interval(80, 150) * Math.sin(2 * Math.PI * i / Helper.rand_interval(particles - 10, particles + 10)),
          color = Helper.rand_interval(0, 255) + ', ' + Helper.rand_interval(0, 255) + ', ' + Helper.rand_interval(0, 255),
          // randomize the color rgb
          // particle element creation (could be anything other than div)
          elm = $('<div class="particle" style="' +
            'background-color: rgb(' + color + ') ;' +
            'top: ' + y + 'px; ' +
            'left: ' + x + 'px"></div>');

        if (i == 0) { // no need to add the listener on all generated elements
          // css3 animation end detection
          elm.one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function(e) {
            explosion.remove(); // remove this explosion container when animation ended
          });
        }
        explosion.append(elm);
      }
    },


    // Cookie elfogadó sáv megjelenítése és az elfogadás logikája
    cookieConsent: function() {
      if (Store.get('isApp') == 1) {
        return;
      }

      if (Store.get('cookie-accept') != 1) {
        $('#cookie-consent').hide(0).addClass('fixed-bottom').removeClass('d-none').slideDown(100);
      }

      $(document).on('click', '.accept-cookies', function(e) {
        e.preventDefault();
        Store.set('cookie-accept', 1);
        setTimeout(function() {
          $('.uniModal').modal('hide');
          $('#cookie-consent').addClass('d-none').removeClass('fixed-bottom');
        }, 500);
      });
    },


    donationBanner: function() {
      if (Store.get('isApp') == 1) {
        return;
      }

      var bannerHidden = Store.get('donation_banner'),
        currentMonth = $('#donationBanner').data('month');

      if (bannerHidden != currentMonth) {
        $('#donationBanner').removeClass('d-none').hide(0);
        setTimeout(function() {
          $('#donationBanner').slideDown(400);
        }, 100);
      }

      $(document).on('click', '.hideDonationBanner', function(e) {
        e.preventDefault();
        $('#donationBanner').slideUp(400);
        Store.set('donation_banner', currentMonth);
      });
    },

  };
