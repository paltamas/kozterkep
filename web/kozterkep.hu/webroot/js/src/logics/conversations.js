var s,
  Conversations = {

    settings: {
    },

    init: function () {
      s = this.settings;

      var that = this;

      if ($app.auth && $app.user_pause == 0 && Store.get('conversation-count') > 0) {
        this.setCount(Store.get('conversation-count'));
      }

      if ($app.user_pause == 1) {
        $('.conversations-empty').html('Kikapcsoltad az értesítéseket, ' + Html.link('kezelés', '/tagsag/beallitasok#ertesitesek') + '.');
        $('.conversation-icon-container span.far').removeClass('fa-comments-alt').addClass('fa-comment-alt-slash disabled');
      }

      this.bindUIActions();

      if ($app.auth) {
        $app.ic['conversation_thread'] = window.setInterval(function () {
          that.refresh_thread();
        }, $app.conversation_thread_interval * 1000);
      }
    },

    bindUIActions: function () {

      var that = this;

    },


    /**
     * Szám módosítása kapott értékre
     * @param count
     */
    setCount: function(count) {

      // Fejlécben és máshol
      if (count > 0) {
        $('.conversation-count').addClass('bg-orange-dark').html(count);
        $('.navlink-conversation-count').html(' (' + count + ')');
      } else {
        $('.conversation-count').removeClass('bg-orange-dark').html('&nbsp;');
        $('.conversation-item').remove();
        $('.conversation-empty').show();
        $('.navlink-conversation-count').html('');
      }
    },


    /**
     * Lista építés
     * @param object
     */
    buildList: function(object) {


      var that = this,
        start_count = 0,
        added_count = 0,
        shown_ids = [];

      // Megjelenítés
      if (typeof object != 'undefined' && object.length > 0) {
        // Van
        $('.conversations-empty').hide();

        $(object).each(function(key, elem) {
          // Ha nincs benne, hozzáadjuk
          if (!$('.conversation-' + elem.id)[0] && elem.messages.length > 0) {
            var item = '<div class="dropdown-item p-2 border-bottom conversation-' + elem.id + ' conversation-item" id="conversation-' + elem.id + '">'
              + '<div class="cursor-pointer" ia-href="/beszelgetesek/folyam/' + elem.id + '">'
              + '<span class="font-weight-bold d-inline-block">' + elem.subject + '</span>&nbsp;'
              + '<span class="text-muted d-none d-md-inline-block">' + Helper.textFormat(Helper.truncate(elem.messages[elem.messages.length - 1]['body'], 42), false) + '</span>'
              + '</div>'
              + '<div class="row">'
              + '<div class="col-12 cursor-pointer" ia-href="/beszelgetesek/folyam/' + elem.id + '">'
              + '<small class="text-muted text-nowrap"><span class="far fa-clock"></span> '
              + Helper.timeConverter(elem.messages[elem.messages.length - 1]['created'], 'short', true) + ', '
                + Helper.arrayWithoutMe(elem.user_names).join(', ')
              + '</small>'
              + '</div>'
              + '</div>'
              + '</div>';
            $('.conversation-list').prepend(item);
          }

          shown_ids[elem.id] = true;
        });

        $('.conversation-item').last().removeClass('border-bottom');

        // Végigpörgetjük, ami kint van, hogy minden kell-e még
        // mert közben más eszközön olvasottá tehettük
        $('.conversation-item').each(function(key, elem) {
          var id = $(this).attr('id').split('-')[1];
          if (typeof shown_ids[id] == 'undefined') {
            $('#conversation-' + id).remove();
          }
        });
      }

      // Mentjük és kijelezzük az új számot, ha volt bővítés, ha nem
      var new_count = typeof object != "undefined" ? object.length : 0;
      Store.set('conversation-count', new_count);
      that.setCount(new_count);
    },


    refresh_thread: function() {

      if ($('.conversation-thread')[0]) {

        var id = Helper.getUrlLastPart();

        Http.get(
          'api/conversations?id=' + id,
          function (response) {
            if (response.length == 1) {
              $(response[0]['messages']).each(function (key, elem) {
                if (!$('#message-' + elem.mid)[0]) {
                  var itsMe = md5(elem.user_id) == $app.user_hash,
                    bg = !itsMe ? 'bg-gray' : '';

                  var file_list = '';
                  if (typeof elem.files != 'undefined' && elem.files.length > 0) {
                    file_list += elem.body != '' ? '<hr />' : '';
                    elem.files.forEach(function(file) {
                      file_list += '<a href="' + $app.path + 'mappak/fajl_mutato/' + file[0] + '" target="_blank" ia-file="' + file[0] + '" class="mr-3 file-attachment"><span class="far fa-paperclip mr-1"></span>' + file[1] + '</a>';
                    });
                  }

                  var row = '<div id="message-' + elem.mid + '" class="row border rounded bg-light shadow-sm my-4 py-3 ' + bg + '">'
                    + '<div class="col-md-12">'
                    + '<div class="mb-2 text-muted">'
                    + '<span class="font-weight-bold mr-2">' + elem.user_name + '</span>'
                    + '<span>' + Helper.timeConverter(elem.created, 'full') + '</span>'
                    + '<hr class="my-2" /></div>' // user
                    + '<div class="message-text">'
                    + Helper.textFormat(elem.body)
                    + '</div>' // message-text
                    + file_list
                    + '</div>' // md-12
                    + '</div>'; // row

                  // Beszúrom az új üzenetet és linkesítem
                  $('.conversation-thread')
                    .prepend(row)
                    .linkify(Layout.settings.linkifyOptions);
                }
              });

              // Fenti üzenetinfót frissítjük
              $('.thread-info [ia-timestamp]').attr(
                'ia-timestamp',
                response[0]['messages'][response[0]['messages'].length-1]['created']
              );
              $('.thread-info .count').html(response[0]['messages'].length);
            }
          }
        );
      }

    },


    read_toggle: function (pass, vars, redirect) {

      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'read_toggle': 1},
        function(response) {
          //that.get(); // majd legközelebb lefrissül
          // Csak itt tudom megoldani a lista színezést...
          $('.list-conversation-' + pass).toggleClass('bg-yellow-light');
          $('.list-conversation-' + pass + ' .names, .list-conversation-' + pass + ' .texts .subject').toggleClass('font-weight-bold');

          // Trükkösen itt léptetek, hogy "azonnali" legyen az élmény a számlálónál
          // Csak listában
          if ($('.list-conversation-' + pass + ' .names')[0]) {
            if ($('.list-conversation-' + pass + ' .names').hasClass('font-weight-bold')) {
              var new_count = parseInt(Store.get('conversation-count')) + 1;
            } else {
              var new_count = parseInt(Store.get('conversation-count')) - 1;
            }
            Store.set('conversation-count', new_count);
            that.setCount(new_count);
          }

          if (redirect != '') {
            // Biztosan olvasatlanná tétel van
            _redirect(redirect, ['Olvasatlanná tettük a beszélgetést', 'info']);
          }
        }
      );

    },


    favor_toggle: function (pass) {
      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'favor_toggle': 1},
        function(response) {
          //Alerts.flashBubble('Archiváltuk a beszélgetést', 'info', true);
        }
      );
    },

    archive: function (pass, vars, redirect) {
      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'archive': 1},
        function(response) {
          var message = 'Archiváltuk a beszélgetést';
          if (redirect != '') {
            _redirect(redirect, [message, 'info']);
          }
          Alerts.flashBubble(message, 'info', {'delBefore': true});
        }
      );
    },

    trash: function (pass, vars, redirect) {
      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'trash': 1},
        function(response) {
          var message = 'A beszélgetést áthelyeztük a kukába';
          if (redirect != '') {
            _redirect(redirect, [message, 'info']);
          }
          Alerts.flashBubble(message, 'info', {'delBefore': true});
        }
      );
    },

    restore: function (pass, vars, redirect) {
      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'active': 1},
        function(response) {
          var message = 'A beszélgetést visszatettük az aktív beszélgetések közé';
          if (redirect != '') {
            _redirect(redirect, [message, 'info']);
          }
          Alerts.flashBubble(message, 'info', {'delBefore': true});
        }
      );
    },

    delete: function (pass, vars, redirect) {
      var that = this;

      Http.put(
        'api/conversations/alter/' + pass,
        {'delete': 1},
        function(response) {
          var message = 'Véglegesen töröltük a beszélgetést';
          if (redirect != '') {
            _redirect(redirect, [message, 'info']);
          }
          Alerts.flashBubble(message, 'info', {'delBefore': true});
        }
      );
    },

    delete_all: function () {
      var that = this;

      Http.post(
        'api/conversations/empty_trash',
        {},
        function(response) {
          _redirect('beszelgetesek/kuka', ['Véglegesen töröltük az összes kukában lévő beszélgetést', 'info']);
        }
      );
    },

  };
