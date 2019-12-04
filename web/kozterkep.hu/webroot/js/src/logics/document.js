var s,
  Document = {

    settings: {
    },

    init: function () {
      s = this.settings;
      this.bindUIActions();
    },

    bindUIActions: function () {

      var that = this;

    },


    share: function(pass, vars, redirect, elem) {
      $(elem).popover({
        'html': true,
        'container': 'body',
        'content': '<a href="#" class="float-right text-muted close-popover"><span class="fas fa-times fa-border"></span></a>'
          + '<h6 class="text-center mt-0">Megosztás</h6>'
          + '<div class="p-2">'
          + Html.link('', 'mailto:?subject=' + vars['title'] + '&body=' + $app.domain + $app.here, {
              'icon': 'envelope fa-2x',
              'class': 'mr-2 text-secondary',
              'target': '_blank'
            })
          + Html.link('', 'https://www.facebook.com/sharer/sharer.php?u=' + pass, {
              'icon': 'facebook fab fa-2x',
              'class': 'mr-2 text-secondary',
              'target': '_blank'
            })
          + Html.link('', 'https://twitter.com/intent/tweet?text=' + encodeURI('"' + vars['title'] + '" a Köztérképen!') + '%20' + encodeURI(pass), {
              'icon': 'twitter fab fa-2x',
              'class': 'mr-2 text-secondary',
              'target': '_blank'
            })
          + '</div>'
      }).popover('toggle');

      $(document).on('click', '.close-popover', function(e) {
        e.preventDefault();
        $(elem).popover('hide');
      });
    },



    bookmark: function(pass, vars, redirect, elem) {
      $(elem).popover({
        'html': true,
        'container': 'body',
        'content': '<a href="#" class="float-right text-muted close-popover"><span class="fas fa-times fa-border"></span></a>'
        + '<h6 class="text-center mt-0">Könyvjelző mentése</h6>'
        + '<div class="p-2 text-muted"><span class="fal fa-tools mr-1"></span>Fejlesztés alatt<br />...és itt lesznek a régi raklapjaid is!</div>'
      }).popover('toggle');

      $(document).on('click', '.close-popover', function(e) {
        e.preventDefault();
        $(elem).popover('hide');
      });
    },


    print: function(pass) {
      window.print();
    },


    reload: function(pass) {
      location.reload();
    },


    /**
     *
     * Adott inputokat ürít ki
     *
     * @param pass
     * @returns {boolean}
     */
    empty_input: function(pass) {
      var inputs = pass.split(',');
      $.each(inputs, function(key, input) {
        $(input).val('');
      });
      return true;
    },

  };
