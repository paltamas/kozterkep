var s,
  Forms = {

    settings: {},

    init: function () {
      s = this.settings;
      this.bindUIActions();
      this.autoLoad();

      this.filePreview();
    },

    bindUIActions: function () {

      var that = this;


      // Form auto mentés
      $('form.autoSave *').keyup(_delay(function () {
        that.autoSave();
      }, 500));


      // Formchange figyelés; egyszerre csak 1 formot tudunk figyelni!
      if ($('[ia-form-change]')[0]) {
        var id = $('[ia-form-change]').attr('id'),
          listened_events = $('#' + id).attr('ia-form-change').split('||'),
          changed = false,
          ignore = false;

        // Változásfigyelés
        $('#' + id).on('change', ':input', function() {
          changed = true;
          if ($.inArray('enable_submit', listened_events) > -1) {
            $('#' + id + ' input[type=submit]').removeClass('disabled');
          }
          $('input[type=submit]').click(function() {
            ignore = true;
            return true;
          });
        });
      }

      // Formchange figyelés
      if ($('[ia-form-change-alert]')[0]) {
        var submitClick = false;

        $('[ia-form-change-alert]').on('click', 'input[type=submit]', function() {
          submitClick = true;
        });

        // Változásfigyelés
        $('[ia-form-change-alert]').contents().find(':input, :checkbox').bind('change', function() {
          window.onbeforeunload = function (e) {
            if (!submitClick) {
              return _text('nem_mentett_modositasok');
            }
          }
        });
      }


      // Form törlés
      $(document).on('click', '.delForm', function (e) {
        e.preventDefault();
        that.reset($(this).parent('form').attr('id'));
      });

      // Form törlés
      $(document).on('click', '.delFormFiles', function (e) {
        e.preventDefault();
        that.reset($(this).closest('form').attr('id'), true);
        $('.delFormFiles').remove();
        $app.files_to_upload = [];
      });


      // Disabled submit ne toljon submitot
      $(document).on('click', 'input[type=submit].disabled', function (e) {
        e.preventDefault();
      });


      // PHP Formhelperben definiált selectbutton kattintás-működése
      $(document).on('click', 'button[ia-form-select-button]', function (e) {
        e.preventDefault();
        $(this).siblings().removeClass('btn-outline-secondary btn-secondary active');
        $(this).siblings().addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-secondary active');
        $($(this).attr('ia-form-select-button')).val($(this).attr('ia-form-select-value')).trigger('change');
      });


      // iOS bug multiple file uploadnál, és inkább ne is legyen multiple
      // elvileg a fájl név ellenőrzéssel és átnevezéssel kezeltem
      // (az okozza a multiple hibát, hogy az ios a tallóz gombra
      // fotózott képeket ugyanolyan fájlnévvel látja el)
      /*if (Store.get('isTouch') == 1) {
        $('input[type=file]').removeAttr('multiple');
      }*/

      // Ajaxform és noEnterForm ne menjen enterre, meg a noEnterInput -ban enterelve se
      $('.ajaxForm input[type=text], .ajaxForm input[type=email], .ajaxForm input[type=password], .noEnterForm input[type=text], .noEnterForm input[type=email], .noEnterForm input[type=password], .noEnterInput').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
          e.preventDefault();
        }
      });

      // Form küldés Ctr Enterre
      $(document).on('keydown', '.controlEnter', function (e) {
        if (e.ctrlKey && e.keyCode == 13) {
          $(this).closest('form').submit();
        }
      });

      // Üres mezők ürítése
      $(document).on('submit', '.unsetEmptyFields', function (e) {
        $.each($('.unsetEmptyFields :input'), function(key, input) {
          if ($(input).val() == 0 || $(input).val() == '' || $(input).hasClass('unset')) {
            $(input).attr("disabled", "disabled");
          }
        });
        // Ha többször egymás után nyomunk rá, elküldi az üreseket is.
        // @todo: kivédeni
        setTimeout(function() {
          return true;
        }, 500);
      });


      // Ajaxform küldés
      $(document).on('submit', '.ajaxForm', function (e) {
        e.preventDefault();

        var formId = $(this).attr('id'),
          url = $(this).attr('ia-form-action'),
          data = Helper.formToObject($(this));

        Forms.submitForm(formId, data, url);
      });


      // Required mezőnek lesz értéke => ne írjuk ki a hibát, ha van
      $(document).on('keyup', ':input[required]', function(e) {
        if ($(this).val() != '') {
          if ($(this).hasClass('is-invalid')) {
            $(this).removeClass('is-invalid');
          }
          var feedback = '#invalid-feedback-' + $(this).attr('id');
          if ($(feedback)[0]) {
            $(feedback).remove();
          }
        }
      });

    },


    reset: function (formId, only_files) {
      if (typeof only_files == 'undefined') {
        only_files = false;
      }

      if (only_files) {
        $('#' + formId + ' input[type=file]').val('');
        $('#' + formId + ' .filePreviews').remove();
        return;
      }

      $('#' + formId).trigger('reset');
      $('#' + formId + ' [ia-autouser]').each(function (key, elem) {
        Autocomplete.delUserItem($(elem).attr('id'));
      });
      if ($('#' + formId).hasClass('autoSave')) {
        Store.set('formdata-' + formId, []);
      }
      $('#' + formId + ' textarea').css({'height': 'auto'});


      $('#' + formId + ' .filePreviews').remove();
    },


    resetField: function(field) {
      $(field).val('');
      return true;
    },


    /**
     * Form küldése ajax-szal
     * @param formId
     * @param postData
     * @param postUrl
     */
    submitForm: function (formId, postData, postUrl) {

      // Kill all
      Helper.killAllIntervals();

      var that = this;

      // Hibaellenőrzés
      this.validateFields(formId);

      var redirect = $('#' + formId).attr('ia-form-redirect')
        ? $('#' + formId).attr('ia-form-redirect') : false;

      // Ha volt kép a formon, akkor azt kiparsoljuk a FileReader eltett tömbjéből
      if ($('#' + formId + ' input[type=file]')[0]) {

        // Ezt kitesszük, mert nem lehet érezni a dolgot
        $('#' + formId).prepend(Html.loading('form-loading my-2', 'text-muted', 'Űrlap mentése...'));

        var inputId = $($('#' + formId + ' [ia-fileupload]:visible')[0]).attr('id');
        if (typeof $app.files_to_upload[inputId] != 'undefined') {
          var files = $app.files_to_upload[inputId];
          postData['_files'] = [];
          files.forEach(function(file) {
            postData['_files'].push([file[0], file[1].result]);
          });
          //_c(postData);
          $app.files_to_upload = [];
        }
        $('#' + formId + ' .fileInput').collapse('hide');
        $('#' + formId + ' .filePreviews').remove();
        $('#' + formId + ' .delFormFiles').remove();
      }
      //return;

      // Küldés
      Http.request(
        $('#' + formId).attr('ia-form-method') == 'put' ? 'put' : 'post',
        {
          'path': postUrl,
          'data': postData,
          'success': function (response) {

            if (response.errors) {
              $('.form-loading').remove();
              Alerts.flashBubble(response.errors, 'danger', {'delBefore': true});

            } else {

              if ($('#' + formId).hasClass('autoSave')) {
                that.reset(formId);
              }

              if (response.success) {
                if (redirect) {
                  $('#' + formId).html(Html.loading('my-4', 'text-muted', 'Sikeres mentés. Ha nem frissülne az oldal, <a href="#" ia-bind="document.reload">kattints ide</a>.'));
                  var url = redirect.replace('{response}', response.success);
                  if (typeof response.message != 'undefined') {
                    _redirect(url, [response.message['text'], response.message['type']]);
                  } else {
                    _redirect(url);
                  }
                }

                if ($('#' + formId).attr('ia-form-trigger')) {
                  $('.form-loading').remove();
                  Appbase.run($('#' + formId).attr('ia-form-trigger'));
                }
              }
            }
          }
        }
      );

    },


    /**
     * Form mezők validációja bennük foglalt szabályok szerint
     * @param formId
     * @returns {boolean}
     */
    validateFields: function (formId) {
      // @todo - majd, ha lesz mit
      //$('#' + formId + ' .alert').remove();
      return true;
    },


    autoSave: function () {
      $('form.autoSave').each(function (key, elem) {
        var id = typeof $(this).attr('id') != 'undefined' ? $(this).attr('id') : false;
        if (id) {
          Store.set('formdata-' + id, Helper.formToObject($('form#' + id)));
        }
      });
    },


    autoLoad: function () {
      if ($('form.autoSave')[0]) {
        $('form.autoSave').each(function (key, elem) {
          var id = $(this).attr('id'),
            formData = Store.get('formdata-' + id);

          if (formData && Helper.pJson(formData)) {
            $.each(JSON.parse(formData), function (key, elem) {
              $('#' + id + ' [name=' + key + ']').val(elem);
            });
          }

          Autocomplete.checkUserValues();
        });
      }
    },

    filePreview: function(container) {
      /**
       * Betallózott képfájlok előnézete az input mező felett
       */

        // Ebben kell, hogy legyen a fájl, ha meg van adva, persze
      var container = typeof container != 'undefined' ? container + ' ' : '';

      if ($('[ia-previewfile]')[0]) {
        $(document).on('change', container + '[ia-previewfile]', function() {

          var that = this;

          var id = $(this).attr('id');
          $('.filePreviews').remove();
          $('.delFormFiles').remove();
          $app.files_to_upload = [];
          $app.files_to_upload[id] = [];
          $(that).after('<div class="row filePreviews my-2 py-2 alert-warning"></div>');
          var files = $(this)[0].files;
          var loaded = 0;
          var last_name = '';
          var sum_size = 0;

          for (var i = 0; i < files.length; i++) {
            (function(file) {

              if ($(that).attr('ia-filetype') && $(that).attr('ia-filetype') == 'images'
                && file.type.indexOf('image') == -1) {
                $('.filePreviews').after('<div class="text-danger"><strong>Fájl hiba: ' + file.name + '</strong> - csak képfájlok feltöltése engedélyezett.</div>');
                files[i] = null;
                return;
              }

              sum_size += file.size;

              if (sum_size > $sDB['limits']['photos']['max_upload_total'] * 1000000) {
                $('.file-maxsize').remove();
                $('.filePreviews').after('<div class="text-danger file-maxsize"><strong>Max. ' + $sDB['limits']['photos']['max_upload_total'] + ' MB</strong> feltöltése lehetséges egyszerre. Ami e felett van már, nem mutatjuk.</div>');
                files[i] = null;
                return;
              }

              // Ha egyszerre több ugyanolyan fájlnév jönne. Pl. iOS
              if (last_name == file.name) {
                var name = Helper.randId() + '-' + file.name;
              } else {
                var name = file.name;
              }
              last_name = name;

              var reader = new FileReader();
              reader.onloadend = function () {
                // Ikont kap, vagy előképet
                var preview = Helper.filePreview(file.type, reader.result);

                $('.filePreviews').append('<div class="col-md-3 mb-3">' + preview + '<br />'
                  //+ '<a href="#" class="delFile" data-name="' + name + '"><span class="far fa-trash mr-2"></span></a>'
                  + '<span class="text-muted small">' + name + '</span></div>');

                // Eltesszük a kiolvasott fájlokat, hogy majd küldeni tudjuk
                $app.files_to_upload[id].push([name, reader]);
                last_filename = name;
                loaded++;

                if (loaded == files.length) {
                  $('.filePreviews').removeClass('alert-warning');
                  $('.filePreviews').after('<div><a href="#" class="delFormFiles"><span class="far fa-trash mr-2"></span>Kiválasztott fájlok törlése</a></div>')
                }
              }
              if (file) {
                reader.readAsDataURL(file);
              }
            })(files[i]);
          }

          $(document).on('click', '.delFile', function(e) {
            e.preventDefault();
            var previewElement = this,
              file_name = $(previewElement).data('name'),
              i = 0;
            $app.files_to_upload[id].forEach(function(elem) {
              if (elem[0] == file_name) {
                $app.files_to_upload[id].splice(i, 1);
                // @todo: Ez nem megy,
                // https://stackoverflow.com/questions/16943605/remove-a-filelist-item-from-a-multiple-inputfile
                that.files.splice(i, 1);
                //_c($(that)[0].files);
                $(previewElement).parent('div').remove();
              }
              i++;
            });
          });

        });
      }
    },


    checkRequired: function(form) {
      var valid = true,
        invalid_visible = true,
        invalids = [];

      if ($(form).find(':input[required]')) {
        $(form).find(':input[required]').each(function(key, elem) {
          if ($(elem).val() == '') {
            Forms.addInvalidInfo(elem, true);
            valid = false;
            invalids.push($($(elem).prop('labels')[0]).text());
          } else {
            Forms.removeInvalidInfo(elem);
          }
        });
      }

      if (!valid) {
        Alerts.flashBubble('Hopp, nincs minden kötelező mező kitöltve! Ezeket keresd: <strong>' + invalids.join('<br /> - ') + '</strong>', 'danger', {
          id: 'missing-inputvals',
          delSameId: true
        });
      }

      return valid;
    },


    addInvalidInfo: function(elem, focus, message) {
      var focus = typeof focus == 'undefined' ? true : focus;
      var message = typeof message == 'undefined' ? 'Kérjük töltsd ki ezt a mezőt.' : message;
      if ($(elem).hasClass('is-invalid') == false) {
        $(elem)
          .addClass('is-invalid')
          .after('<div class="invalid-feedback" id="invalid-feedback-' + $(elem).attr('id') + '">' + message + '</div>');
      }
      if (focus) {
        $(elem).focus();
      }
    },

    removeInvalidInfo: function(elem) {
      if ($(elem).hasClass('is-invalid')) {
        $(elem).removeClass('is-invalid');
        $('#invalid-feedback-' + $(elem).attr('id')).remove();
      }
    },

  };