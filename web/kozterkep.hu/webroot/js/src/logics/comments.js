var s,
  Comments = {

    settings: {
    },

    init: function () {
      s = this.settings;

      var that = this;

      this.bindUIActions();

      if (!$app.auth) {
        // Ha nem vagyunk belépve, nincs komment doboz
        Http.get('eszkozok/csak_bejelentkezessel', function(response) {
          $('.commentLink').remove();
          $('.commentForm').remove();
          $('.comment-thread').html(response.body).removeClass('.comment-thread');
        });
      } else {

        if ($('.commentLink').hasClass('d-none')) {
          $('.commentLink').removeClass('d-none').addClass('d-block');
        }


        /**
         * Ez az ajax betöltött kommenteket kezeli, frissíti
         * ezekre nem szabad rátenni a thread-refresh-t
         */
        if ($('.comment-thread')[0]) {
          $('.comment-thread').html(Html.loading('comments-loading'));

          this.build_thread(false);

          $('.commentForm').collapse('hide');

          if ($app.auth) {
            $app.ic['comment_thread'] = window.setInterval(function () {
              that.build_thread(false);
            }, $app.comment_thread_interval * 1000);
          }
        }

        /**
         * Ez a statikusan behúzott kommenteket frissíti
         */
        if ($('.thread-refresh')[0]) {
          $('.thread-refresh').each(function(key, elem) {
            var div = '.thread-refresh';
            if ($(elem).attr('id') != '') {
              div = '#' + $(elem).attr('id');
            }

            $app.ic['comment_thread'] = window.setInterval(function () {
              Comments.prepend_comment(div);
            }, $app.comment_thread_interval * 1000);
          });
        }
      }

      this.comment_dropdown();
    },

    bindUIActions: function () {

      var that = this;

      $(document).on('click', '.replyComment', function(e) {
        e.preventDefault();
        $('.commentForm .default-text').addClass('d-none');
        $('.commentForm .reply-box').remove();
        var s = '<div class="reply-box alert alert-info p-2 small"><strong>Válasz erre:</strong> ';
        s += Helper.truncate($('#comment-row-' + $(this).data('id') + ' .comment-text').html(), 20, true);
        s += '<a href="#" class="float-right delReply" data-dismiss="alert"><span class="far fa-times-circle"></span></a>';
        s += '<input type="hidden" name="answered_id" id="AnsweredId" value="' + $(this).data('id') + '" />';

        // Ha van benne kapcsolódó ID, akkor átörökítjük
        // Szerkesztés; ez speckó
        if ($('#comment-row-' + $(this).data('id') + ' .comment-connected-edit')[0]) {
          var id_ = $('#comment-row-' + $(this).data('id') + ' .comment-connected-edit').data('id');
          s += '<input type="hidden" value="artpiece_edits_id" name="custom_field">';
          s += '<input type="hidden" value="' + id_ + '" name="custom_value">';
        }
        // Minden más

        var connecteds = ['artpiece', 'artist', 'place', 'folder', 'post', 'forum_topic', 'book'],
          this_comment = this;

        connecteds.forEach(function(connected) {
          if ($('#comment-row-' + $(this_comment).data('id') + ' .comment-connected-' + connected)[0]) {
            var id_ = $('#comment-row-' + $(this_comment).data('id') + ' .comment-connected-' + connected).data('id');
            //s += '<input type="hidden" value="artpiece_id" name="custom_field">';
            //s += '<input type="hidden" value="' + id_ + '" name="custom_value">';
            $('.commentForm #Model-name').val(connected);
            $('.commentForm #Model-id').val(id_);

            if (typeof $sDB['model_parameters'][connected + 's'] != 'undefined'
              && typeof $sDB['model_parameters'][connected + 's'][0] != 'undefined'
              && connected != 'forum_topic') {
              var thing = $sDB['model_parameters'][connected + 's'][0];
              s += '<hr /><span class="fa fa-info-circle mr-2"></span><strong>Egy ' + thing + ' kapcsolódik</strong> ahhoz, amire válaszolsz.';
            }
          }
        });

        //if ()
        s += '</div>';
        $('.commentForm').prepend(s);
        $('.commentForm').collapse('show');
        $('#Comment').focus();
      });

      $(document).on('click', '.delReply', function(e) {
        e.preventDefault();
        $('.commentForm .reply-box').remove();
        $('.commentForm #AnsweredId').remove();
        $('.commentForm .default-text').removeClass('d-none');
        $('.commentForm #Model-name').val($('.commentForm #Model-name').data('default'));
        $('.commentForm #Model-id').val($('.commentForm #Model-id').data('default'));
      });

      $(document).on('click', '.comment-row.bg-gray-kt', function(e) {
        $(this).removeClass('bg-gray-kt');
      });
    },


    /**
     *
     * Új komment hozzáadása a thread tetejére
     *
     * @returns {boolean}
     */
    prepend_comment: function(div) {
      /*if ($(div).is(':visible') == false) {
        return;
      }*/

      var url_suffix = '',
        model_name = '',
        model_id = '',
        custom_params = '';

      // Van fórum téma
      if ($(div).data('forum-topic-id') > 0) {
        model_name = 'forum_topic';
        model_id = parseInt($(div).data('forum-topic-id'));
      } else if ($(div).attr('ia-model-name')) {
        model_name = $(div).attr('ia-model-name');
        model_id = parseInt($(div).attr('ia-model-id'));
      }

      if ($(div).attr('ia-custom-field') != '' && $(div).attr('ia-custom-value') != '') {
        custom_params = '&custom_field=' + $(div).attr('ia-custom-field')
          + '&custom_value=' + $(div).attr('ia-custom-value');
      }

      Comments.get_thread({
        action: '/latests' + url_suffix,
        model_name: model_name,
        model_id: model_id,
        custom_params: custom_params,
        limit: 10,
      }, function(response) {
        // Végigfutok rajta, és ami nincs még kint, azt kiteszem
        $(response).each(function(key, elem) {
          var row_id = 'comment-row-' + elem.id;
          if ($(div).find('#' + row_id).length == 0) {
            Comments.add_row(elem, div, 'prepend');
          }
        });

        Layout.initListeners();
      });

      return true;
    },


    refresh_thread: function() {
      return true;
    },



    /**
     *
     * Teljes thread újraépítése
     * full JS komment thread-logikánál használjuk
     *
     */
    build_thread: function(only_if_visible, parent) {
      var only_if_visible = typeof only_if_visible == 'undefined'
        ? true : only_if_visible;

      var parent = typeof parent == 'undefined'
        ? '' : parent + ' ';

      if (only_if_visible && $(parent + '.comment-thread').is(':visible') == false) {
        return;
      }
      $(parent + '.comment-thread').each(function() {
        var that = this;

        var model_name = $(this).attr('ia-model-name'),
          model_id = $(this).attr('ia-model-id'),
          limit = $(this).attr('ia-limit'),
          custom_field = $(this).attr('ia-custom-field') ? $(this).attr('ia-custom-field') : '',
          custom_value = $(this).attr('ia-custom-value') ? $(this).attr('ia-custom-value') : '',
          custom_params = '';

        if (custom_field != '' && custom_value != '') {
          custom_params = '&custom_field=' + custom_field + '&custom_value=' + custom_value;
        }

        Comments.get_thread({
          model_name: model_name,
          model_id: model_id,
          custom_params: custom_params,
          limit: limit > 0 ? limit : 100,
        }, function(response) {
          // Végigfutok rajta, és ami nincs még kint, azt kiteszem
          $(response).each(function(key, elem) {
            var row_id = 'comment-row-' + elem.id;

            if ($(that).find('#' + row_id).length == 0) {
              Comments.add_row(elem, that, 'prepend', model_name, model_id, custom_field, custom_value);
            }
          });
          Layout.initListeners();

          if ($('.comment-' + model_id + '-count')[0]) {
            if (response.length > 0) {
              $('.comment-' + model_id + '-count').html(' (' + response.length + ')');
              setTimeout(function() {
                $('.comment-' + model_id + '-count-text')
                  .html('<strong>' + response.length + '</strong> hozzászólás')
                  .removeClass('d-none');
              }, 750);
            } else {
              $('.comment-' + model_id + '-count').html('');
              $('.comment-' + model_id + '-count-text')
                .html('')
                .addClass('d-none');
            }
          }
        });

      });

    },

    get_thread: function(options, callback) {
      callback = typeof callback == 'undefined' ? function() {} : callback;
      var options = _arr(options, {
        action: '',
        model_name: '',
        model_id: '',
        custom_params: '',
        limit: 100,
      });

      Http.get(
        'api/comments' + options.action
          + '?limit=' + options.limit
          + '&model_name=' + options.model_name
          + '&model_id=' + options.model_id
          + options.custom_params,

        function (response) {
          if (response.errors) {
            Alerts.flashBubble(response.errors[0], 'danger');
            return;
          }

          callback(response);

          $('.comments-loading').remove();
        }
      );
    },

    // position = 'prepend' : 'append'
    add_row: function(elem, thread, position, model_name, model_id, custom_field, custom_value) {
      var file_list = '',
        model_name = typeof model_name == 'undefined' ? false : model_name,
        model_id = typeof model_id == 'undefined' ? false : model_id,
        custom_field = typeof custom_field == 'undefined' ? false : custom_field,
        custom_value = typeof custom_value == 'undefined' ? false : custom_value;

      if (typeof elem.files != 'undefined' && elem.files.length > 0) {
        file_list += '<div class="">';
        elem.files.forEach(function(file) {
          file_list += Html.link(file[1], $app.path + 'mappak/fajl_mutato/' + file[0], {
            'target': '_blank',
            'class': 'mr-3 small file-attachment',
            'icon': 'paperclip',
            'ia-file': file[0]
          });
        });
        file_list += '</div>';
      }


      // Valamire válasz
      var answeredComment = '';
      if (typeof elem.answered_id != 'undefined' && elem.answered_id != '') {
        var acText = '';
        // Párbeszéd megnyitása
        if (typeof elem.parent_answered_id != 'undefined' && elem.parent_answered_id != '') {
          acText += Html.link('Párbeszéd', '/kozter/parbeszed/' + elem.parent_answered_id, {
            icon: 'stream',
          });
        }

        answeredComment = '<span class="small text-muted">' + acText + '</span>';
      }


      // Kapcsolódó dolgok, ha nem épp ott vagyunk!
      var connectedThings = '';

      // műlap
      if (typeof elem.artpiece_id != 'undefined' && elem.artpiece_id != ''
      && model_name != 'artpiece' && model_id != elem.artpiece_id) {
        var link_text = 'Műlaphoz';
        if ($('.comment-connected-artpiece[data-id="' + elem.artpiece_id + '"]')[0]) {
          link_text = $($('.comment-connected-artpiece[data-id="' + elem.artpiece_id + '"] a .link-text')[0]).text();
          connectedThings += '<span class="far fa-map-marker mr-1"></span>';
        }
        var margin = typeof elem.artpiece_edits_id != 'undefined' && elem.artpiece_edits_id != ''
      && custom_field != 'artpiece_edits_id' ? '' : 'mr-3';
        connectedThings += '<span class="' + margin + ' comment-connected-artpiece" data-id="' + elem.artpiece_id + '">'
          + Html.link(link_text, '', {
            'artpiece': {id: elem.artpiece_id, title: ''},
            'ia-tooltip': 'mulap',
            'ia-tooltip-id': elem.artpiece_id,
            'class': 'd-inline-block font-weight-semibold',
          })
          + '</span>';
      }

      // szerkesztés
      if (typeof elem.artpiece_edits_id != 'undefined' && elem.artpiece_edits_id != ''
      && custom_field != 'artpiece_edits_id') {

        if (typeof elem.artpiece_id != 'undefined' && elem.artpiece_id != ''
      && model_name != 'artpiece' && model_id != elem.artpiece_id) {
          connectedThings += '<span class="far fa-arrow-right mx-2"></span>';
        }

        connectedThings += '<span class="mr-3 comment-connected-edit" data-id="' + elem.artpiece_edits_id + '">'
          + Html.link('Szerkesztéshez', '/mulapok/szerkesztes_reszletek/' + elem.artpiece_id + '/' + elem.artpiece_edits_id, {
            'icon': 'comment-edit',
            'class': 'text-nowrap font-weight-semibold',
          })
          + '</span>';
      }

      // fórumtéma
      if (typeof elem.forum_topic_id != 'undefined' && elem.forum_topic_id > 0
        && model_name != 'forum_topic') {
        var link_text = 'Fórumban';
        if ($('.comment-connected-forum_topic[data-id="' + elem.forum_topic_id + '"]')[0]) {
          link_text = $($('.comment-connected-forum_topic[data-id="' + elem.forum_topic_id + '"] a .link-text')[0]).text();
        }
        connectedThings += '<span class="mr-4 comment-connected-forum_topic" data-id="' + elem.forum_topic_id + '">'
          + Html.link(link_text, '/kozter/forum-tema/' + elem.forum_topic_id, {
            'icon': 'comments',
            'class': 'text-nowrap font-weight-semibold',
          })
          + '</span>';
      }
      // poszt
      if (typeof elem.post_id != 'undefined' && elem.post_id > 0
        && model_name != 'post') {
        var link_text = 'Bejegyzéshez';
        if ($('.comment-connected-post[data-id="' + elem.post_id + '"]')[0]) {
          link_text = $($('.comment-connected-post[data-id="' + elem.post_id + '"] a .link-text')[0]).text();
        }
        connectedThings += '<span class="mr-4 comment-connected-post" data-id="' + elem.post_id + '">'
          + Html.link(link_text, '', {
            'icon': 'newspaper',
            'class': 'text-nowrap font-weight-semibold',
            'post': {id: elem.post_id, title: ''}
          })
          + '</span>';
      }
      // alkotó
      if (typeof elem.artist_id != 'undefined' && elem.artist_id > 0
        && model_name != 'artist') {
        var link_text = 'Alkotóhoz';
        if ($('.comment-connected-artist[data-id="' + elem.artist_id + '"]')[0]) {
          link_text = $($('.comment-connected-artist[data-id="' + elem.artist_id + '"] a .link-text')[0]).text();
        }
        connectedThings += '<span class="mr-4 comment-connected-artist" data-id="' + elem.artist_id + '">'
          + Html.link(link_text, '', {
            'icon': 'user',
            'class': 'text-nowrap font-weight-semibold',
            'artist': {id: elem.artist_id, name: ''}
          })
          + '</span>';
      }
      // hely
      if (typeof elem.place_id != 'undefined' && elem.place_id > 0
        && model_name != 'place') {
        var link_text = 'Helyhez';
        if ($('.comment-connected-place[data-id="' + elem.place_id + '"]')[0]) {
          link_text = $($('.comment-connected-place[data-id="' + elem.place_id + '"] a .link-text')[0]).text();
        }
        connectedThings += '<span class="mr-4 comment-connected-place" data-id="' + elem.place_id + '">'
          + Html.link(link_text, '', {
            'icon': 'map-pin',
            'class': 'text-nowrap font-weight-semibold',
            'place': {id: elem.place_id, name: ''}
          })
          + '</span>';
      }
      // mappa
      if (typeof elem.folder_id != 'undefined' && elem.folder_id > 0
        && model_name != 'folder') {
        var link_text = 'Mappához';
        if ($('.comment-connected-folder[data-id="' + elem.folder_id + '"]')[0]) {
          link_text = $($('.comment-connected-folder[data-id="' + elem.folder_id + '"] a .link-text')[0]).text();
        }
        connectedThings += '<span class="mr-4 comment-connected-folder" data-id="' + elem.folder_id + '">'
          + Html.link(link_text, '/mappak/megtekintes/' + elem.folder_id, {
            'icon': 'folder',
            'class': 'font-weight-semibold'
          })
          + '</span>';
      }

      if (connectedThings != '') {
        connectedThings = '<div class="small text-muted ml-1">' + connectedThings + '</div><hr class="my-1" />';
      }

      var highlighted_bg = '';

      if (Helper.getURLParameter('komment') == elem.id) {
        highlighted_bg = ' bg-gray-kt bg-whitening ';
        setTimeout(function () {
          $('#comment-row-' + elem.id).removeClass('bg-gray-kt');
        }, 7000);
      }


      var highlighted = '';
      if (elem.artpiece_id > 0 && elem.highlighted > 0) {
        highlighted = '<span class="fal fa-sm fa-angle-double-up ml-2" data-toggle="tooltip" title="Kiemelt hozzászólás aktualitás miatt eddig: ' + Helper.timeConverter(elem.highlighted + ($sDB['limits']['comments']['highlight_months']*30*24*60*60)) + '"></span>';
      }

      var row = '<div id="comment-row-' + elem.id + '" class="row comment-row border rounded mb-3 py-2 mx-0' + highlighted_bg + '">'
        + '<div class="col-md-12 comment-container px-2">'

        + '<div class="comment-text-box">'

        // Ikonok és időbélyeg
        + '<div class="float-right">'
        + Html.link('Válasz', '#', {
          'hide_text': true,
          'class': 'small replyComment text-muted',
          'data-id': elem.id,
          'icon': 'reply'
        })
        + '</div>'

        + '<div class="mb-1">'
        + '<span class="font-weight-bold mr-2 comment-user">'
        + '<img src="/eszkozok/profilfoto/' + elem.user_id + '?s=4" class="img-fluid rounded-circle mr-1">'
        + Html.link(elem.user_name, '', {
          user_id: elem.user_id,
          'ia-tooltip': 'tag',
          'ia-tooltip-id': elem.user_id
        }) + '</span>'
        + '</div>'

        + connectedThings

        + '<div class="comment-text my-2">'
        + Helper.textFormat(elem.text)
        + '</div>'
        + '</div>' // comment-text-box

        + '<div class="float-right">'
        + answeredComment
        + Html.link('', '/kozter/komment/' + elem.id, {
          'class': 'small text-muted ml-3',
          'data-id': elem.id,
          'icon': 'ellipsis-h fas',
          'ia-modal': 'modal-md',
        })
        + '</div>'

        + '<small class="text-muted">' + Helper.timeAgoInWords(elem.created) + '</small>'

        + highlighted

        + file_list

        + '</div>' // md-12
        + '</div>'; // row

      if (position == 'prepend') {
        $(thread).prepend(row).linkify(Layout.settings.linkifyOptions);
      } else {
        $(thread).append(row).linkify(Layout.settings.linkifyOptions);
      }
      $(thread).find('linkify_custom').linkify(Layout.settings.linkifyCustomOptions);
    },


    comment_dropdown: function() {
      $(document).on('click', '.comment-dropdown', function(e) {
        e.preventDefault();
      });
    },




    edit: function(comment_id, vars) {
      var comment_text = $('.modal-content #comment-row-' + comment_id + ' .comment-text-for-edit').text();
      $('.modal-content #comment-row-' + comment_id + ' .comment-text').hide();

      // Szerk. form
      var form = '<div id="comment-edit-form-' + comment_id + '">';
      form += Html.input('edit_comment', {
        type: 'textarea',
        value: comment_text,
        class: 'edit_comment_text',
        "data-comment_id": comment_id,
      });
      form += Html.link('Mégsem', '#', {
        'class': 'btn btn-outline-secondary btn-sm comment_edit_cancel float-right'
      });
      form += Html.input('comment_save', {
        type: 'submit',
        class: 'btn-sm comment_save',
        value: 'Mentés',
      });
      form += '</div>';
      $('.modal-content #comment-row-' + comment_id + ' .comment-text').after(form);

      // Mégsem szerkesztjük
      $(document).on('click', '.comment_edit_cancel', function(e) {
        e.preventDefault();
        $('.modal-content #comment-edit-form-' + comment_id).remove();
        $('.modal-content #comment-row-' + comment_id + ' .comment-text').show();
      });

      // Szerkesztés mentése
      $(document).on('click', '.comment_save', function(e) {
        e.preventDefault();
        var new_text = $('.edit_comment_text').val(),
          comment_id = $('.edit_comment_text').data('comment_id');

        Http.put('api/comments', {
          id: comment_id,
          text: new_text,
        }, function(response) {
          $('.modal-content #comment-edit-form-' + comment_id).remove();
          $('#comment-row-' + comment_id + ' .comment-text-for-edit').html(Helper.nl2br(new_text)).show();
          $('#comment-row-' + comment_id + ' .comment-text').html(Helper.nl2br(new_text)).show();
          $('#comment-row-' + comment_id + ' .comment-text').linkify(Layout.settings.linkifyOptions);
          $('#comment-row-' + comment_id + ' linkify_custom.comment-text').linkify(Layout.settings.linkifyCustomOptions);
          setTimeout(function() {
            Modals.close('.uniModal');
          }, 750);
          Alerts.flashBubble('Sikeresen módosítottad a hozzászólást.');
        });
      });

      Layout.initListeners();
    },



    /**
     *
     * Sztorivá alakítás (editként beszúrás)
     *
     * @param comment_id
     */
    story_convert: function(comment_id, vars) {
      Http.post('api/comments/story_convert', {
        'id': comment_id
      }, function (response) {
        $('#comment-row-' + comment_id).remove();
        Modals.close('.uniModal');
        setTimeout(function() {
          _redirect('/mulapok/szerkesztes_reszletek/' + vars['artpiece_id'] + '/' + response.success, 'Sikeresen sztorivá alakítottuk hozzászólást. Az új sztori jóváhagyás után jelenik meg a műlapon.');
        }, 1200);
      });
    },


    /**
     *
     * Kiemelés, és vissza (toggle)
     *
     * @param comment_id
     */
    highlight_toggle: function(comment_id, vars) {
      Http.post('api/comments/highlight_toggle', {
        'id': comment_id
      }, function (response) {
        Modals.close('.uniModal');
        Alerts.flashBubble('Sikeresen módosítottad a hozzászólás kiemtl státuszát. Frissíts, hogy lásd a műlap változását.');
      });
    },


    /**
     *
     * Alkotó adalékká alakítás (artist-descriptions beszúrás)
     *
     * @param comment_id
     */
    artist_description_convert: function(comment_id, vars) {
      Http.post('api/comments/artist_description_convert', {
        'id': comment_id,
      }, function (response) {
        $('#comment-row-' + comment_id).remove();
        Modals.close('.uniModal');
        setTimeout(function() {
          _redirect('/alkotok/megtekintes/' + vars['artist_id'], 'Sikeresen adalékká alakítottuk a hozzászólást.');
        }, 1200);
      });
    },


    /**
     *
     * Alkotó hozzászólássá visszaalakítás
     *
     * @param description_id
     */
    artist_description_convert_back: function(description_id, vars) {
      Http.post('api/comments/artist_description_convert_back', {
        'id': description_id,
      }, function (response) {
        $('#description-row-' + description_id).remove();
        Modals.close('.uniModal');
      });
    },


    /**
     *
     * Törlés
     *
     * @param comment_id
     */
    delete: function(comment_id, vars) {
      Http.post('api/comments/delete', {
        'id': comment_id
      }, function (response) {
        $('#comment-row-' + comment_id).remove();
        Modals.close('.uniModal');
        Alerts.flashBubble('A hozzászólást töröltük.', 'info', {delBefore: true});
      });
    },

  };
