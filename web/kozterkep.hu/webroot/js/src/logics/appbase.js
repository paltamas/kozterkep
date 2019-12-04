var s,

Appbase = {

  settings: {},

  init: function () {
    var that = this;
    s = this.settings;

    /**
     * Modal nyitás, hash alapján
     */
      // Window load után
    var modal = Helper.getURLHashParameter('modal');
    this.modals(modal);

    // Hash módosulás után
    window.onhashchange = function () {
      var modal = Helper.getURLHashParameter('modal');
      if (modal) {
        Appbase.modals(modal);
      }
    };

    this.ajaxdivs();
    this.connections();
    this.links();
    this.confirm_links();
    this.unisearch();
    this.bind();
    this.dragOrder();
    this.tooltips();
    this.inputValidations();
    this.edit_texts();
    this.etc();
  },


  /**
   * Modal open kapott path-ra
   * ill. modal nyitás linkre kattolva
   *
   * @param modal
   * @private
   */
  modals: function (modal) {
    if (modal) {
      Modals.open({
        'path': modal,
        'onClose': function () {
          window.location.hash = '';
        }
      });
    }
  },


  /**
   * Események bekötése logic/method-ba
   * @private
   */
  bind: function () {

    var that = this;

    $(document).on('change', 'select[ia-bind], input[ia-bind]', function (e) {
      $(this).click();
    });

    $(document).on('click', '[ia-bind]', function (e) {

      e.preventDefault();

      // Mindenféle segéd-dolog
      var
        that = this,

        // Rá vonatkoznak a dolgok
        target = $(this).attr('ia-target') ? $(this).attr('ia-target') : '',

        // Rejtjük
        hideable = $(this).attr('ia-hide') ? $(this).attr('ia-hide') : '',

        // Mutatjuk
        showable = $(this).attr('ia-show') ? $(this).attr('ia-show') : '',

        // Klasszik add, removeClass
        removeclass = $(this).attr('ia-removeclass') ? $(this).attr('ia-removeclass') : '',
        addclass = $(this).attr('ia-addclass') ? $(this).attr('ia-addclass') : '',

        // Togglézzuk ez(eke)t (ha kettő, akkor köztük, ugye, egyébként le fel)
        toggleclass = $(this).attr('ia-toggleclass') ? $(this).attr('ia-toggleclass') : '',

        // Togglézzuk a címeket
        toggletitles = $(this).attr('ia-toggletitle') ? $(this).attr('ia-toggletitle').split('||') : '',

        // Eltüntetendő
        destroyable = $(this).attr('ia-destroy') ? $(this).attr('ia-destroy') : '',

        // Tovább kell dobni
        redirect = $(this).attr('ia-redirect') ? $(this).attr('ia-redirect') : '';

      // Akármit akar, először confirm
      confirmthis = $(this).attr('ia-confirm') ? $(this).attr('ia-confirm') : false;

      // This is the target ;]
      if (target == 'this') {
        target = this;
      }


      // metódus meghívása, ha van.
      var bindTo = $(this).attr('ia-bind').split('.');

      Appbase.bindMethod(bindTo, this, redirect);

      if (hideable != '') {
        hideable == 'this' ? this : hideable;
        $(hideable).slideUp(200);
      }

      if (showable != '') {
        showable == 'this' ? this : showable;
        $(showable).show();
      }

      if (destroyable != '') {
        destroyable == 'this' ? this : destroyable;
        $(destroyable).remove();
      }

      if (toggleclass != '' && target != '') {
        $(target).toggleClass(toggleclass);
      }

      // @todo ez bugos, pontosabban összeakad a BS tooltip-pel...
      if (toggletitles.length != '' && toggletitles.length == 2) {
        // Saját target kellhet, mert a title-t jellemzően az ikon parent a-ja hordozza
        target = $(this).attr('ia-toggletitle-target') ? $(this).attr('ia-toggletitle-target') : target;
        var key = $(target).prop('title') == toggletitles[1] ? 0 : 1;
        $(target).prop('title', toggletitles[key]);
      }

      if (removeclass != '' && target != '') {
        $(target).removeClass(removeclass);
      }

      if (addclass != '' && target != '') {
        $(target).addClass(addclass);
      }

      if (redirect != '' && typeof bindTo[1] == 'undefined') {
        window.location = $app.path + redirect;
      }
    });

  },


  bindMethod: function (bindTo, elem, redirect) {
    // Ha van valami a bind-ban, akkor meghívjuk
    if (typeof bindTo[1] !== 'undefined') {
      // Osztály
      var Logic = Helper.ucfirst(bindTo[0]),
        // Megtódus
        method = bindTo[1],
        // Ezt adjuk át majd a meghívott metódusnak
        pass = $(elem).attr('ia-pass') ? $(elem).attr('ia-pass') : '',
        // Ebbe tesszük
        vars = [];

      // Select / input esetében lehet a value is a pass!
      pass = pass == 'this.value' ? $(elem).val() : pass;

      // Átadott egyéb változó objektum
      $($(elem)[0].attributes).each(function (key, attr) {
        if (attr.nodeName.indexOf('ia-vars') > -1) {
          var varName = attr.nodeName.replace('ia-vars-', '');
          var varValue = attr.nodeValue;
          vars[varName] = varValue;
        }
      });

      // Meghívom a dolgot
      if (typeof eval(Logic)[method] !== "undefined") {
        eval(Logic)[method](pass, vars, redirect, elem);
      } else {
        _c('Hianyzo logika: ' + Logic + '.' + method, 1);
      }
    }
  },


  ajaxdivs: function() {
    if ($('[ia-ajaxdiv]')[0]) {
      $('[ia-ajaxdiv]').html(Html.loading()).addClass('ajaxdiv');
      $('[ia-ajaxdiv]').each(function(key, div) {
        Helper.loadDiv($(div).attr('ia-ajaxdiv'), div);
      });


      // Kattintásra frissülő ajaxdiv
      $(document).on('click', '[ia-ajaxdiv-load-simple]', function(e) {
        e.preventDefault();
        Helper.loadDiv($(this).attr('ia-ajaxdiv-load-simple'), $(this).attr('ia-ajaxdiv-target'));
      });

      // Duplakattintásra frissülő ajaxdiv
      $(document).on('dblclick', '[ia-ajaxdiv-load]', function(e) {
        e.preventDefault();
        Helper.loadDiv($(this).attr('ia-ajaxdiv-load'), $(this).attr('ia-ajaxdiv-target'));
      });
    }
  },


  /**
   * Konfirmálós linkek
   */
  confirm_links: function () {
    var that = this;

    /*
     * Konfirm linkek átalakítása
     * Beszúrunk a link mögé egy modalt és abban elhelyezzük a konfirmálandó üzenetet.
     * Kiszedjük az eredeti link összes ia-... attribútumát, és átpakoljuk a modal
     * OK gombjára.
     *
     */
    $('[ia-confirm]').each(function (key, elem) {

      var elem = this,
        elemHtml = $(this).html();

      if ($(elem).hasClass('disabled') || $(elem).attr('disabled')) {
        return true;
      }


      var modalId = $(this).attr('id') + 'Modal',
        modalBody = '<div class="mb-3">' + $(elem).attr('ia-confirm') + '</div>'
          + '<p><a href="#" class="btn btn-danger mr-5 okButton" data-dismiss="modal">Igen, mehet</a><a href="#" class="btn btn-outline-info float-right" data-dismiss="modal">Mégsem</a></p>';

      Modals.prepare(modalId, {
        title: 'Jóváhagyás',
        body: modalBody,
        size_class: 'modal-sm'
      });

      // Minden attribútumot átpakolunk a modal gombjára
      $(this.attributes).each(function () {
        if (this.specified) {
          if (this.name !== 'ia-confirm' && ((this.name).indexOf('ia-') !== -1 || this.name == 'href')) {
            $('#' + modalId + ' .okButton').attr(this.name, this.value);
            // Link lesz, nem kell modal-dismiss, mert az ellopja a click figyelést
            if (this.name == 'href' && this.value != '#' && this.value != '') {
              $('#' + modalId + ' .okButton').removeAttr('data-dismiss');
              $(elem).attr('href', '#');
            } else if (1 == 2) {
              $(elem).removeAttr(this.name);
            } else {
              $(elem).removeAttr(this.name);
            }
          }
        }
      });

      // Elveszett közben
      $(elem).attr('href', '#');

      $(this).addClass('cursor-pointer');

      // Modal nyitás
      $(this).click(function (e) {
        e.preventDefault();
        $('#' + modalId + ' .modal-loading').remove();
        $('#' + modalId + ' .modal-body').show();
        $('#' + modalId).modal('show');
        $('#' + modalId).on('shown.bs.modal', function (e) {
          $('#' + modalId + ' .okButton').focus();
        });
      });

      $(document).on('click', '#' + modalId + ' .okButton', function (e) {
        $('#' + modalId + ' .modal-body').hide();
        $('#' + modalId + ' .modal-body').after(Html.loading('modal-loading p-3', '', 'A művelet elvégzése folyamatban...'));
      });
    });
  },



  /**
   * Okos linkek
   */
  links: function () {

    var that = this;

    // Sima link
    $(document).on('click', '[ia-href]', function (e) {
      e.preventDefault();
      var url = $(this).attr('ia-href');
      if (url !== undefined && url != '') {
        window.location.href = decodeURIComponent(url);
      }
    });


    // Előző
    $(document).on('click', '[ia-history-back]', function (e) {
      if ($(this).attr('ia-href') == '' || $(this).attr('ia-href') == '#') {
        e.preventDefault();
        window.history.back();
      }
      return true;
    });


    // Ha ide kattintasz, valami fókuszt kap
    $(document).on('click', '[ia-focus]', function (e) {
      var that = this;
      // timeout, mert sokszor toggle-val jön elő a div, amiben van a fókuszandó
      setTimeout(function() {
        $($(that).attr('ia-focus')).focus();
      }, 300);
      return true;
    });


    // Futtat egy method-ot. Abban különbözik a bind-tól, hogy utána lefut a link
    $(document).on('click', '[ia-run]', function (e) {
      that.run($(this).attr('ia-run'), this);
      return true;
    });


    // Reloadolja az oldalt
    $(document).on('click', '[ia-refresh]', function (e) {
      e.preventDefault();
      location.reload();
    });


    // Scrollto-dolog
    $(document).on('click', '[ia-scrollto]', function(e) {
      e.preventDefault();
      var target = $(this).attr('href'),
        targetPosition = $(target).position();
      $('html, body').animate({scrollTop: targetPosition.top}, 'slow');
      window.location.hash = target;
    });

    // Modalban nyitja az URL-t
    $(document).on('click', '[ia-modal]', function(e) {
      if ($(this).attr('ia-modal') == '' || $(this).attr('ia-modal') == '0'
        || $(this).attr('ia-modal') == 'false') {
        return true;
      }
      e.preventDefault();
      $('.progress').removeClass('d-none');
      var url = $(this).attr('href');
      Modals.open({
        size_class: $(this).attr('ia-modal'),
        modal_id: $(this).attr('id') + '-modal',
        path: url,
      });
    });

    // Másolom a sztringet
    $(document).on('click', '[ia-copy-this]', function(e) {
      e.preventDefault();
      var string = $(this).attr('ia-copy-this'),
        input_id = $(this).attr('id') + '-copy-string';
      if (!$('#' + input_id)[0]) {
        $(this).after('<span style="position:absolute; left: -100000px;"><textarea id="' + input_id + '">' + string + '</textarea></span>');
        $('#' + input_id).select();
        document.execCommand('copy');
        Alerts.flashBubble('A linket a vágólapra másoltuk.');
      }

    });


  },


  /**
   * A stringben kapott dolgokat futtatja.
   * "," az elválasztójel a függvények közt.
   * Darabolás után tud még ":" utáni átadott értéket nézni.
   * Persze ez utóbbi csak string lehet.
   * @param string
   * @param elem
   */
  run: function (string, elem) {
    if (typeof string != 'undefined' && string != '') {
      var methods = Helper.arraize(string, ',');
      methods.forEach(function (method_string) {
        var method_attributes = Helper.arraize(method_string.trim(), ':');
        if (method_attributes.length == 2) {
          eval(Helper.ucfirst(method_attributes[0]))(method_attributes[1], elem);
        } else {
          eval(Helper.ucfirst(method_attributes[0]))(elem);
        }
      });
    }
  },


  /**
   * Szöveges keresés kiemeléssel egy már kiírt listában
   * x-ia tag-et használok, hogy ne kavarodjon össze semmi span-dolog,
   * CSAK SIMA SZÖVEGRE MEGY!, esetleg * és _
   */
  unisearch: function () {
    $(document).on('keyup', '[name=unisearch]', function (e) {
      e.preventDefault();
      var keyword = ($(this).val()).toLocaleLowerCase(),
        container = $(this).attr('ia-unisearch-container'),
        items = $(this).attr('ia-unisearch-items'),
        item_container = $(this).attr('ia-unisearch-item-container');

      if (keyword.length >= 2) {
        $(container + ' ' + items).each(function (key, elem) {

          // Kitakarítom az előző kiemelést
          var cleaned = Helper.replaceAll('<x-ia class="bg-highlighted">', '', $(this).html());
          var cleaned = Helper.replaceAll('</x-ia>', '', cleaned);
          $(this).html(cleaned);

          var originalContent = $(this).html(),
            searchContent = ($(this).html()).toLocaleLowerCase(),
            exists = searchContent.indexOf(keyword);

          if (exists == -1) {
            // Elrejtem, amiben nincs ez a kulcsszó
            $(this).closest(item_container).addClass('d-none');
          } else {
            // Hátha van rajta
            $(this).closest(item_container).removeClass('d-none');
            // Egyébként kiszedem a szót, ahogy van
            var originalForm = originalContent.substring(exists, exists + keyword.length);
            // Aztán színezem
            var highlighted = Helper.replaceAll(
              originalForm,
              '<x-ia class="bg-highlighted">' + originalForm + '</x-ia>',
              $(this).html()
            );
            $(this).html(highlighted);
          }
        });
      } else {
        // Takarítás és visszahozás
        $(container + ' ' + items).each(function (key, elem) {
          var cleaned = Helper.replaceAll('<x-ia class="bg-highlighted">', '', $(this).html());
          var cleaned = Helper.replaceAll('</x-ia>', '', cleaned);
          $(this).html(cleaned);
          $(this).closest(item_container).removeClass('d-none');
        });
      }
    });
  },


  dragOrder: function(invalids) {
    if (Store.get('isTouch') == 1) {
      //return;
    }

    var invalids = typeof invalids == 'undefined' ? true : invalids;

    if ($('[ia-dragorder]')[0]) {
      $('[ia-dragorder]').each(function() {
        var container_id = $(this).attr('id'),
          handlerClass = $(this).attr('ia-draghandler'),
          targetInput = $(this).attr('ia-dragorder'),
          container = this;
        $('.' + handlerClass).css('cursor', 'grab');

        // alapból saját magán belül mozgathatjuk
        var containers_object = [document.querySelector('#' + container_id)];
        // de ha van tesó, akkor oda át is
        if (typeof $(container).attr('ia-dragbrother') != 'undefined') {
          containers_object.push(document.querySelector($(container).attr('ia-dragbrother')));
        }
        var drake = dragula({
          containers: containers_object,
          invalid: function (el, handle) {
            if ($(handle).hasClass(handlerClass)) {
              return false;
            }
            if ($(handle).hasClass('nodrag')) {
              return true;
            }
            return invalids;
          }
        });

        drake.on('drag', function() {
          $('.' + handlerClass).css('cursor', 'grabbing');
        });
        drake.on('drop', function(e) {
          $('.' + handlerClass).css('cursor', 'grab');

          var i = 0;
          $(targetInput).each(function(key, elem) {
            i++;
            $(this).val(i).change();
          });

          if ($(container).attr('ia-dragcallback')) {
            Appbase.run($(container).attr('ia-dragcallback'));
          }

          if (typeof $(e).attr('ia-bind') != "undefined") {
            Appbase.run($(e).attr('ia-bind'));
          }

        });
      });
    }
  },


  tooltips: function() {
    if (Store.get('isTouch') == 1) {
      return;
    }

    var timer;

    $(document).on('mouseover', '[ia-tooltip]', function() {

      var that = this,
        path = 'eszkozok/' + $(this).attr('ia-tooltip') + '_ablak/' + $(this).attr('ia-tooltip-id');

      $('.tooltipster-base').hide();

      timer = setTimeout(function() {

        $(that).tooltipster({
          debug: false,
          updateAnimation: 'null',
          animation: 'fade',
          delay: [1000, 0],
          trigger: 'hover',
          contentAsHTML: true,
          contentCloning: true,
          content: Html.loading(),
          functionInit: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data('loaded') !== true) {
              Http.get(path, function (response) {
                if (response.body) {
                  instance.content(response.body);
                  $origin.data('loaded', true);
                  _c(instance);
                }
              });
            }
          },
        }).tooltipster('open');
      }, 300);
    });

    $(document).on('mouseleave', '[ia-tooltip]', function() {
      clearTimeout(timer);
    });
  },


  tooltips_bak: function() {
    if (Store.get('isTouch') == 1) {
      return;
    }

    var timer;

    $(document).on('mouseover', '[ia-tooltip]', function() {

      var that = this;

      var tooltip_body_id = 'tooltip-' + $(that).attr('ia-tooltip') + '-' + $(that).attr('ia-tooltip-id'),
        path = 'eszkozok/' + $(that).attr('ia-tooltip') + '_ablak/' + $(that).attr('ia-tooltip-id'),
        auto_hide = $(that).attr('ia-tooltip-autohide') > 0 ? parseInt($(that).attr('ia-tooltip-autohide')) : false;

      $(that).attr('data-html', true);

      if ($('#' + tooltip_body_id).is(':visible')) {
        return true;
      }

      // Beírtuk-e már a DOM-ba
      if ($('#' + tooltip_body_id + '-content')[0]) {
        // DOM-ba írtat olvassuk vissza
        $(that).popover({
          placement : 'bottom',
          html : true,
          trigger : 'hover',
          delay: {
            show: 500,
            hide: 300, // sztem ez nem megy!
          },
          content: $('#' + tooltip_body_id + '-content').html()
        });

      } else {

        // Késleltetünk, hogy egy egérvégighúzáskor ne küldjünk egy egy tucat HTTP rikvesztet
        timer = setTimeout(function() {

          Http.get(path, function (response) {
            if (response.body) {

              if ($('.popover')[0]) {
                $('.popover').hide();
              }

              $(that).popover({
                placement : 'bottom',
                html : true,
                trigger : 'hover',
                content: response.body,
                delay: {
                  show: 500,
                  hide: 300, // sztem ez nem megy!
                },
              });

              // Domba írjuk
              if (!$('#' + tooltip_body_id + '-content')[0]) {
                $('body').append('<div class="d-none" id="'
                  + tooltip_body_id + '-content"><div id="'
                  + tooltip_body_id + '">' + response.body
                  + '</div></div>');
              }

              $(that).popover('show');

              //$(that).trigger('mouseenter');

              if (auto_hide) {
                $(that).on('shown.bs.popover', function () {
                  setTimeout(function () {
                    $(that).popover('hide');
                  }, auto_hide * 1000);
                });
              }

            }
          });

        }, 300);
      }
    });

    // Ürítjük a timert
    $(document).on('mouseleave', '[ia-tooltip]', function() {
      clearTimeout(timer);
    });

    $(document).on('click', '.close-popover', function(e) {
      e.preventDefault();
      $('.popover').popover('hide');
    });


  },


  /**
   * Egymásra ható eventek
   * jellemzően inputok változásainak figyelése
   * és más inputok piszkálása
   */
  connections: function() {

    // Egymást kioltó checkboxok
    $(document).on('click', '[ia-conn-unset]', function(e) {
      var id = $(this).attr('id');
      if ($(this).is(':checked')) {
        $($(this).attr('ia-conn-unset') + ':not(#' + id + ')').prop('checked', false);
      }
    });


    // Kapcsolódó select, amit mutatunk, ha pipált, és ha van conn-select-default, arra állunk benne
    // Majd kipippantáskor rejtés és üresre állítás
    // Egyelőre div és label nélkül jó
    $(document).on('click', '[ia-conn-select]', function(e) {
      var select_elem = $(this).attr('ia-conn-select');
      if ($(this).is(':checked')) {
        $(select_elem).removeClass('d-none');
        if ($(this).attr('ia-conn-select-default')) {
          $(select_elem).val($(this).attr('ia-conn-select-default'));
        }
      } else {
        $(select_elem)
          .addClass('d-none')
          .val('');
      }
    });


    // Input value által meghatározott divet megjelenít,
    // a csoport többi div tagját eltünteti.
    $(document).on('change', '[ia-conn-show]', function(e) {
      var elem = $(this).attr('ia-conn-show');
      $('.' + elem).addClass('fade d-none');
      $('#' + elem + '-' + $(this).val()).removeClass('fade d-none');
    });
    $(document).on('change', '[ia-conn-show]', function(e) {
      var elem = $(this).attr('ia-conn-show');
      $('.' + elem).addClass('fade d-none');
      $('#' + elem + '-' + $(this).val()).removeClass('fade d-none');
    });


    // Input focus esetén megjelenítjük a targeteket
    $(document).on('focus', '[ia-conn-focus-show]', function(e) {
      var target = $(this).attr('ia-conn-focus-show');
      $(target).removeClass('d-none');
    });

  },


  /**
   * Az
   */
  inputValidations: function() {

    // Ha kattintás előtt checked volt, nem kikattintható checkbox :)
    $(document).on('change', '[ia-input]', function(e) {
      var elem = this,
        val = $(this).val(),
        type = $(this).attr('ia-input'),
        message = '';

      Forms.removeInvalidInfo(elem);

      if (type == 'number') {
        if (parseInt(val) != val) {
          message += 'Ez a mező csak számot tartalmazhat.';
        } else if (typeof $(elem).attr('ia-input-min') != "undefined"
          && val < parseInt($(elem).attr('ia-input-min'))) {
          message += 'Minimum érték: ' + $(elem).attr('ia-input-min');
        } else if (typeof $(elem).attr('ia-input-max') != "undefined"
          && val > parseInt($(elem).attr('ia-input-max'))) {
          message += 'Maximum érték: ' + $(elem).attr('ia-input-max');
        }
      }

      // Ürítünk hiba esetén
      if (message != '') {
        $(elem).val('');
        Forms.addInvalidInfo(elem, true, message);
        setTimeout(function() {
          Forms.removeInvalidInfo(elem);
        }, 5000);
      }
    });

  },


  /**
   * Mindenféle egyéb event
   */
  etc: function() {

    // Ha kattintás előtt checked volt, nem kikattintható checkbox :)
    $(document).on('click', '[ia-uncheck-all]', function(e) {
      if ($(this).attr('href') == '#') {
        // Link
        e.preventDefault();
      } else {
        // Checkbox
        // kicsekkoláskor nem kell futtatni
        if ($(this).prop('checked') == false) {
          return;
        }
      }
      var target_container = $(this).attr('ia-uncheck-all');
      $(target_container).find('input:checkbox').prop('checked', false);
    });

    // Ha kattintás előtt checked volt, nem kikattintható checkbox :)
    $(document).on('click', '[ia-uncheckable=1]', function(e) {
      if ($(this).is(':checked') == false) {
        // most lenne kikattintva
        e.preventDefault();
      }
    });


    // Input változás reload + url change-t eredményez a megadott query-ben
    // pl. lista pagináció stb.
    $(document).on('change', '[ia-urlchange-input]', function(e) {
      var urlVar = $(this).attr('ia-urlchange-input'),
        actValue = Helper.getURLParameter(urlVar),
        newValue = $(this).val();

      if (!actValue) {
        var url = $app.here;
        if (url.indexOf('?') !== -1) {
          url += '&';
        } else {
          url += '?';
        }
        url += urlVar + '=' + newValue;
      } else {
        var url = ($app.here).replace(urlVar + '=' + actValue, urlVar + '=' + newValue);
      }
      window.location = url;
    });

    // A fenti, checkboxra
    $(document).on('change', '[ia-urlchange-checkbox]', function(e) {
      var urlVar = $(this).attr('ia-urlchange-checkbox'),
        actValue = Helper.getURLParameter(urlVar),
        newValue = $(this).prop('checked') ? 1 : 0;

      if (!actValue) {
        var url = $app.here;
        if (url.indexOf('?') !== -1) {
          url += '&';
        } else {
          url += '?';
        }
        url += urlVar + '=' + newValue;
      } else {
        var url = ($app.here).replace(urlVar + '=' + actValue, urlVar + '=' + newValue);
      }
      window.location = url;
    });


    // Megjegyzős collapse
    if ($('[ia-collapse-memory]')[0]) {
      $('[ia-collapse-memory]').each(function() {
        var target = $(this).data('target');
        var saved = Store.get('collapse_memories.' + target);
        if (saved && saved == 0) {
          $(target).removeClass('show');
          $(this).find('.fas').toggleClass('fa-minus-square fa-plus-square');
        }
      });
    }
    $(document).on('click', '[ia-collapse-memory]', function(e) {
      var target = $(this).data('target');
      if ($(this).find('.fas').hasClass('fa-plus-square')) {
        Store.set('collapse_memories.' + target, 1);
      } else {
        Store.set('collapse_memories.' + target, 0);
      }
      $(this).find('.fas').toggleClass('fa-minus-square fa-plus-square');
    });
    // Megjegyzős collapse --

  },


  /**
   * Szöveg szerkesztése és mentése megadott végpontra
   */
  edit_texts: function() {
    $(document).on('click', '[ia-edit-text]', function(e) {
      e.preventDefault();

      // Amit kattoltunk
      var edit_link = this;
      // Szerkesztendő szöveg
      var text = $($(this).attr('ia-edit-text')).text();
      // Amiben a szöveg van, togglézandó
      var container = $(this).attr('ia-edit-text-container');
      // Ahova mentünk
      var save_url = $(this).attr('ia-edit-text-url');
      // Amilyen ID-re mentünk
      var model_id = $(this).attr('ia-edit-text-id');
      // A form azonosítója, amivel mentünk
      var form_id = 'edit_text_' + Helper.randId();

      $(container).addClass('d-none');
      $(this).addClass('d-none');

      // Az űrlap létrehozása és hozzácsapása
      var form = '<div id="' + form_id + '">';
      form += Html.input('edit_text', {
        type: 'textarea',
        value: text
      });
      form += Html.link('Mentés', '#', {
        class: 'btn btn-primary save-edit-text',
      });
      form += Html.link('Mégsem', '#', {
        class: 'btn btn-link float-right cancel-edit-text',
      });
      form += '</div>';

      $(container).after(form);
      Layout.initListeners();
      $('#' + form_id + ' textarea').focus();


      // Mentés
      $(document).on('click', '#' + form_id + ' .save-edit-text', function(e) {
        e.preventDefault();

        var new_text = $('#' + form_id + ' textarea').val();

        Http.post(save_url, {
          id: model_id,
          text: new_text
        }, function(response) {
          $('#' + form_id).remove();
          $($(edit_link).attr('ia-edit-text')).html(Helper.nl2br(new_text));
          Layout.initListeners($(edit_link).attr('ia-edit-text'));

          if (response.success > 0) {
            $($(edit_link).attr('ia-edit-text') + '-timestamp').html(Helper.timeConverter(response.success));
          }

          $(container).removeClass('d-none');
          $(edit_link).removeClass('d-none');
          Alerts.flashBubble(_text('sikeres_mentes'));
        });
      });

      // Visszavonás
      $(document).on('click', '#' + form_id + ' .cancel-edit-text', function(e) {
        e.preventDefault();
        $('#' + form_id).remove();
        $(container).removeClass('d-none');
        $(edit_link).removeClass('d-none');
      });

    });

  },


};