var s,
  Autocomplete = {

    settings: {
      focus: {},
      lastval: {},
    },

    init: function () {
      s = this.settings;
      this.bindUIActions();
      this.inputId = '';
    },

    bindUIActions: function () {

      var i = 0;

      Store.set('autoVal', '');

      // Userdolgok
      if ($('[ia-autouser]')[0]) {
        $('[ia-auto]').attr('autocomplete', 'ne-toltsd-ki');
        this.checkUserValues();
        this.watchUserInputs();
      }

      // Általános autocomplete
      if ($('[ia-auto]')[0]) {
        // és ez sem megy
        // semmi nem megy. bekakilok :[
        // https://stackoverflow.com/questions/15738259/disabling-chrome-autofill
        $('[ia-auto]').attr('autocomplete', 'ne-toltsd-ki');
        this.watchInputs();
      }
    },



    watchInputs: function() {

      $('[ia-auto]').each(function(key, elem) {
        Autocomplete.settings.focus[$(elem).attr('id')] = 0;
        Autocomplete.settings.lastval[$(elem).attr('id')] = $(elem).val();
      });

      $(document).on('keyup', '[ia-auto]', _delay(function (e) {
        e.preventDefault();

        var that = this,
          val = $(this).val(),
          addAllowed = false,
          modelName = $(this).attr('ia-auto'),
          minChar = $(this).attr('ia-auto-min') ? $(this).attr('ia-auto-min') : 2,
          queryField = $(this).attr('ia-auto-query'),
          selectKey = $(this).attr('ia-auto-key'),
          selectTarget = $(this).attr('ia-auto-target'),
          excludedID = $(this).attr('ia-auto-excluded');

        if ($(this).attr('ia-auto-add') == 'true') {
          addAllowed = true;
        } else if ($(this).attr('ia-auto-add') && $(this).attr('ia-auto-add') != '') {
          addAllowed = $(this).attr('ia-auto-add');
        }

        $('#new-item-info-' + $(that).attr('id')).remove();

        if (e.keyCode != 38 && e.keyCode != 40 && e.keyCode != 14 && e.keyCode != 27
          && $(this).is(':focus') && val.length >= minChar
          && Autocomplete.settings.lastval[$(this).attr('id')] != $(this).val()) {

          //Autocomplete.unkownValue(that, selectTarget, addAllowed);

          var list = Http.get('api/autocompletes?m=' + modelName + '&f=' + queryField + '&v=' + val + '&ex=' + excludedID, function (response) {

            Autocomplete.buildDropdown(that, response, selectKey);

            // Ha pontosan valamelyik lista értéket írta be,
            // kiválasztás nélkül is beírjuk már az ID-t, mert lehet, hogy nem választ
            var wasSame = false;
            $(response).each(function (key, elem) {
              if (elem['value'].toLocaleLowerCase() == val.toLocaleLowerCase()) {
                $(that).data('selected', elem['value']);
                $(selectTarget).val(elem[selectKey]);
                wasSame = true;
              }
            });

          });

        } else if (val.length < minChar) {

          $(selectTarget).val(0);

          _delay(function() {
            Autocomplete.notEnough(that, selectTarget, minChar);
            $('.autocomplete-dropdown').remove();
          }, 1000);

        } else if (!$(this).is(':focus')) {
          $('.autocomplete-dropdown').remove();
        }
      }, 300));


      $(document).on('keyup', '[ia-auto]', function (e) {
        e.preventDefault();

        var selectTarget = $(this).attr('ia-auto-target'),
          i = Autocomplete.settings.focus[$(this).attr('id')];

        // Dropdown lépkedés
        if ($('.autocomplete-dropdown')[0] && $(this).is(':focus') && e.keyCode == 38) { // fel
          if (i > 0) {
            i--;
            $(".autocomplete-dropdown a").removeClass("active");
            $($(".autocomplete-dropdown a")[i]).addClass("active");
          }
        }
        if ($('.autocomplete-dropdown')[0] && $(this).is(':focus') && e.keyCode == 40) {
          if (i < $(".autocomplete-dropdown a").length - 1) {
            i++;
            $(".autocomplete-dropdown a").removeClass("active");
            $($(".autocomplete-dropdown a")[i]).addClass("active");
          }
        }


        // Kiválasztás enterrel
        if ($('.autocomplete-dropdown')[0] && e.keyCode == 13) {
          if ($($(".autocomplete-dropdown a")[i]).data('key')) {
            Autocomplete.selectItem(
              $($(".autocomplete-dropdown a")[i]).data('key'),
              $($(".autocomplete-dropdown a")[i]).data('value'),
              this,
              selectTarget
            );
          }
        }

        Autocomplete.settings.focus[$(this).attr('id')] = i;
      });


      // Kiválasztás kattintással
      $(document).on('click', '.autocomplete-dropdown a.dropdown-item:not(.item-create)', function(e) {
        e.preventDefault();
        var clicked = this,
          parent_id = '#' + $(this).parent('div.dropdown-menu').data('parent-id');

        Autocomplete.selectItem(
          $(clicked).data('key'),
          $(clicked).data('value'),
          parent_id,
          $(parent_id).attr('ia-auto-target')
        );
      });

    },

    buildDropdown: function(field, response, selectKey) {
      // Dropdown lista összeállítás
      var link_list = '',
        container_class = 'auto-dropdown-container-' + $(field).attr('id'),
        addAllowed = false,
        selectTarget = $(field).attr('ia-auto-target');

      if ($(field).attr('ia-auto-add') == 'true') {
        addAllowed = true;
      } else if (typeof $(field).attr('ia-auto-add') != undefined
          && $(field).attr('ia-auto-add') != '') {
        addAllowed = $(field).attr('ia-auto-add');
      }

      $('.autocomplete-dropdown').remove();

      Autocomplete.settings.focus[$(field).attr('id')] = 0;

      // Ha még nem pakoltuk a drd wrapperbe
      if ($('.' + container_class).length == 0) {
        $(field).wrap('<div class="dropdown ' + container_class + '"></div>');
      }

      if (response.length > 0) {

        $.each(response, function (key, elem) {
          link_list += '<a href="#" class="dropdown-item pt-2" '
            + 'data-key="' + elem[selectKey] + '" data-label="' + elem['label'] + '" data-value="' + elem['value'] + '">';
          link_list += '<span class="d-inline-block py-1">' + elem['label'] + '</span>';
          link_list += '</a>';
        });

        if (addAllowed) {
          link_list += Html.link('Új létrehozása', '#', {
            'icon': 'plus',
            'ia-bind': addAllowed,
            'ia-pass': $(field).val(),
            'ia-confirm': 'Biztosan létrehozod az új elemet, mert nem találod a felajánlottak között?',
            'class': 'dropdown-item item-create'
          });
        }

        // Dropdown létrehozás
        $(field).after('<div class="dropdown-menu dropdown-no-focus show autocomplete-dropdown" '
          + ' data-parent-id="' + $(field).attr('id') + '">' + link_list + '</div>');

        $(field).focus();

        Autocomplete.settings.focus[$(field).attr('id')] = 0;
        i = Autocomplete.settings.focus[$(field).attr('id')];
        $($(".autocomplete-dropdown a")[i]).addClass("active");

      } else {

        Autocomplete.unkownValue(field, selectTarget, addAllowed);

      }

      $(document).on('click', ':not(.autocomplete-dropdown)', function() {
        $('.autocomplete-dropdown').remove();
      });
    },

    selectItem: function(selectedKey, selectedLabel, inputId, targetField) {
      $(targetField).val(selectedKey);
      $(inputId).val(selectedLabel).data('selected', selectedLabel);

      if ($(inputId).attr('ia-auto-target-run')) {
        eval(Helper.ucfirst($(inputId).attr('ia-auto-target-run')))(selectedKey, selectedLabel);
      }

      $('#new-item-info-' + $(inputId).attr('id')).remove();

      Autocomplete.settings.lastval[$(inputId).attr('id')] = $(inputId).val();

      $('.autocomplete-dropdown').remove();
    },


    notEnough: function(field, selectTarget, minChar) {
      $(selectTarget).val(0);
      var s = '<div class="dropdown-menu dropdown-no-focus show autocomplete-dropdown"'
        + 'data-parent-id="' + $(field).attr('id') + '"><div class="dropdown-header">Legalább ' + minChar + ' karaktert adj meg.</div>';
      s += '</div>';
      $(field).after(s);
      $(field).focus();
    },


    unkownValue: function(field, selectTarget, addAllowed) {
      $(selectTarget).val(0);

      var s = '<div class="dropdown-menu dropdown-no-focus show autocomplete-dropdown"'
        + 'data-parent-id="' + $(field).attr('id') + '"><div class="dropdown-header">Nincs találat</div>';

      if (addAllowed) {
        s += Html.link('Új létrehozása', '#', {
          'icon': 'plus',
          'ia-bind': addAllowed,
          'ia-pass': $(field).val(),
          'ia-confirm': 'Biztosan létrehozod az új elemet, mert nem találod a felajánlottak között?',
          'class': 'dropdown-item item-create'
        });
      }

      s += '</div>';

      $(field).after(s);

      $(field).focus();

      if (addAllowed) {
        Appbase.confirm_links(); // hogy menjen a confirm
      }
    },





    //////////////////////////////// USER



    /**
     * Változás figyelése autocomplete mezőkben
     */
    watchUserInputs: function() {
      $(document).on('keyup', '[ia-autouser]', _delay(function(e) {

        e.preventDefault();

        var that = this,
          val = $(this).val(),
          minChar = $(this).attr('ia-autouser-min'),
          queryField = $(this).attr('ia-autouser-query'),
          selectKey = $(this).attr('ia-autouser-key'),
          selectLabel = $(this).attr('ia-autouser-label') ? $(this).attr('ia-autouser-label') : queryField,
          selectImage = $(this).attr('ia-autouser-image') ? $(this).attr('ia-autouser-image') : '';

        if (
          e.keyCode != 38 && e.keyCode != 40 &&
          (!Store.get('autoVal') || Store.get('autoVal') != val) &&
          $(this).is(':focus') && val.length >= minChar) {

          var list = Http.get('api/users?' + queryField + '=' + val + '&order=has_photo', function(response) {

            // Dropdown lista összeállítás
            var link_list = '';

            $('.autocomplete-dropdown').remove();

            if (response.length > 0) {

              $.each(response, function (key, elem) {
                link_list += '<a href="#" class="dropdown-item pt-2" '
                  + 'data-key="' + elem[selectKey] + '" data-label="' + elem[selectLabel] + '" data-image="' + elem[selectImage] + '">';
                if (elem[selectImage] != '') {
                  link_list += '<img src="/tagok/' + elem[selectImage] + '_3.jpg" class="rounded-circle img-fluid float-left mr-2" width="30">';
                }
                link_list += '<span class="d-inline-block py-1">' + elem[selectLabel] + '</span>';
                link_list += '</a>';
              });

              // Dropdown létrehozás
              $(that).parent().addClass('dropdown');
              $(that).after('<div class="dropdown-menu dropdown-no-focus show autocomplete-dropdown user-auto" '
                + 'data-parent-id="' + $(that).attr('id') + '">' + link_list + '</div>');

              i = 0;
              $($(".autocomplete-dropdown a")[i]).addClass("active");
            } else {

              $(that).parent().addClass('dropdown');
              $(that).after('<div class="dropdown-menu dropdown-no-focus show autocomplete-dropdown user-auto" '
                + 'data-parent-id="' + $(that).attr('id') + '"><div class="dropdown-header">Nincs találat</div></div>');

            }

            Store.set('autoVal', val);
          });

        } else if (val.length < minChar || !$(this).is(':focus')) {
          $('.autocomplete-dropdown').remove();
          Store.set('autoVal', '');
        }
      }, 500));



      $(document).on('keyup', '[ia-autouser]', function(e) {
        e.preventDefault();

        // Dropdown lépkedés
        if ($('.autocomplete-dropdown')[0] && $(this).is(':focus') && e.keyCode == 38) { // fel
          if (i > 0) {
            i--;
            $(".autocomplete-dropdown a").removeClass("active");
            $($(".autocomplete-dropdown a")[i]).addClass("active");
          }
        }
        if ($('.autocomplete-dropdown')[0] && $(this).is(':focus') && e.keyCode == 40) {
          if (i < $(".autocomplete-dropdown a").length - 1) {
            i++;
            $(".autocomplete-dropdown a").removeClass("active");
            $($(".autocomplete-dropdown a")[i]).addClass("active");
          }
        }


        // Kiválasztás enterrel
        if ($('.autocomplete-dropdown')[0] && e.keyCode == 13) {
          Autocomplete.selectUserItem(
            $($(".autocomplete-dropdown a")[i]).data('key'),
            $($(".autocomplete-dropdown a")[i]).data('label'),
            $($(".autocomplete-dropdown a")[i]).data('image'),
            $(this).attr('id')
          );
        }
      });


      // Kiválasztás kattintással
      $(document).on('click', '.autocomplete-dropdown.user-auto a.dropdown-item', function(e) {
        e.preventDefault();
        var clicked = this;
        Autocomplete.selectUserItem(
          $(clicked).data('key'),
          $(clicked).data('label'),
          $(clicked).data('image'),
          $(clicked).parent('.dropdown-menu').data('parent-id')
        );
      });

    },


    /**
     *
     * Kitöltött mezők figyelése
     *
     * Úgy jövünk, hogy meg van adva a mező értéke
     * elkérjük a feliratot hozzá, és kiválasztottá
     * tesszük
     *
     */
    checkUserValues: function() {
      $('[ia-autouser]').each(function(key, elem) {
        if ($(this).val() != '') {

          var that = this,
            val = $(this).val(),
            queryField = $(this).attr('ia-autouser-query'),
            selectKey = $(this).attr('ia-autouser-key'),
            selectLabel = $(this).attr('ia-autouser-label') ? $(this).attr('ia-autouser-label') : queryField,
            selectImage = $(this).attr('ia-autouser-image') ? $(this).attr('ia-autouser-image') : '';

          var list = Http.get('api/users?' + selectKey + '=' + val, function(response) {
            if (response.length == 1) {
              Autocomplete.selectUserItem(
                response[0][selectKey],
                response[0][selectLabel],
                response[0][selectImage],
                $(that).attr('id')
              );
            }
          });
        }
      });
    },


    /**
     *
     * Kiválasztás
     *
     * @param selectedKey
     * @param selectedLabel
     * @param inputId
     */
    selectUserItem: function(selectedKey, selectedLabel, selectedImage, inputId) {

      $('#selectedValue-' + inputId).remove();

      var image = selectedImage != '' ? '<img src="/tagok/' + selectedImage + '_3.jpg" class="rounded-circle img-fluid float-left mr-1 mr-md-2" width="30" />' : '';

      $('#' + inputId + 'Help').addClass('d-none');

      $('#' + inputId).val(selectedKey)
        .addClass('d-none')
        .after('<div id="selectedValue-' + inputId + '">'
          + '<span class="badge badge-secondary pt-2 px-md-3 pb-1">'
          + image
          + '<h5 class="d-inline-block">' + selectedLabel + '</h5>'
          + '<a href="#" class="pl-1 pl-md-5 pr-1 d-inline-block delAutocompleteSelected" data-id="del-' + inputId + '">'
          + '<span class="far fa-times-circle fa-2x pt-1 text-white"></span>'
          + '</a>'
          + '</span></div>');

      if ($('#' + inputId).attr('ia-autouser-next-focus')) {
        $($('#' + inputId).attr('ia-autouser-next-focus')).focus();
      }

      $('.autocomplete-dropdown').remove();

      $(document).on('click', '.delAutocompleteSelected', function(e) {
        e.preventDefault();
        Autocomplete.delUserItem($(this).data('id').split('del-')[1]);
      });

      Forms.autoSave();
    },

    delUserItem: function (delInputId) {
      $('#selectedValue-' + delInputId).remove();
      $('#' + delInputId + 'Help').removeClass('d-none');
      $('#' + delInputId).removeClass('d-none').val('').focus();
      Forms.autoSave();
    },



    // Műlap lista készítése
    // az artpieces.connected_artpiece_add egyszerűsített és námileg átszabott változata ez
    // egy hidden inputba menti JSON-ként a kapcsolódó ID-k listáját
    connected_artpiece_add: function(connected_artpiece_id, connected_artpiece_title) {
      if ($('.connected-artpiece-row-' + connected_artpiece_id)[0]) {
        // Megeshet, hogy már van, ill. az event figyelésben kattintáskor bug van
        // történnek és többször beszúrja.
        return true;
      }

      var s = '<div class="row bg-light py-2 mb-2 connected-artpiece-row connected-artpiece-row-' + connected_artpiece_id + '" data-id="' + connected_artpiece_id + '">';

      // Kép
      s += '<div class="col-6 col-md-2 pb-2 pb-md-0">'
        + '' // @todo
        + '</div>';

      // Cím
      s += '<div class="col-md-8 col-10 pt-0 pt-lg-2">'
        + Html.link(connected_artpiece_title, '#', {
          'artpiece': {
            'id': connected_artpiece_id,
            'title': connected_artpiece_title,
          },
          'target': '_blank',
          'class': 'font-weight-bold',
        })
        + '</div>';

      // Ikonok
      s += '<div class="col-2 pt-2 text-right">'
        + Html.link('', '#', {
          'icon': 'trash fa-lg',
          'class': 'text-muted mr-2 cursor-pointer',
          'ia-confirm': 'Biztosan törlöd ezt a kapcsolatot? A szerkesztés jóváhagyásakor a kapcsolódó műlapról is lekerül a kapcsolás.',
          'ia-bind': 'autocomplete.connected_artpiece_delete',
          'ia-pass': connected_artpiece_id,
          'title': 'Törlés',
        })
        + '</div>';

      s += '</div>'; // row

      $('#connected-artpiece-list').append(s);

      $('#New-connected-artpiece').val('');

      var array = [];
      $('.connected-artpiece-row').each(function(key, elem) {
        array.push('' + $(elem).data('id') + '');
      });
      $('#Connected-artpieces').val(encodeURI(JSON.stringify(array)));
    },

    connected_artpiece_delete: function(connected_artpiece_id) {
      $('.connected-artpiece-row-' + connected_artpiece_id).remove();

      var array = [];
      $('.connected-artpiece-row').each(function(key, elem) {
        array.push('' + $(elem).data('id') + '');
      });
      $('#Connected-artpieces').val(encodeURI(JSON.stringify(array)));
    },




  };