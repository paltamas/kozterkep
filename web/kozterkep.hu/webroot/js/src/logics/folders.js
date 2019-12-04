var s,
  Folders = {

    settings: {
    },

    init: function () {
      s = this.settings;

      var that = this;

      this.bindUIActions();
    },

    bindUIActions: function () {

      var that = this;

    },

    cover_file: function (pass, vars, redirect, elem) {
      var that = this;

      Http.put(
        'api/folders',
        {
          'cover_file': pass,
          'folder_id': vars.folder_id
        },
        function(response) {
          if (response.errors) {
            Alerts.flashBubble(response.errors, 'danger', {'delBefore': true});
          } else {
            $('.setCover:not(#' + $(elem).attr('id') + ') .far')
              .removeClass('fa-badge-check')
              .addClass('fa-badge');
            Alerts.flashBubble('Módosítottuk a mappa borítóját', 'info', {'delBefore': true});
          }
        }
      );
    },

    delete_file: function (pass, vars, redirect) {
      var that = this;

      Http.put(
        'api/folders',
        {
          'delete_file': pass,
          'folder_id': vars.folder_id
        },
        function(response) {
          if (response.errors) {
            Alerts.flashBubble(response.errors, 'danger', {'delBefore': true});
          } else {
            Alerts.flashBubble('Véglegesen töröltük a fájlt', 'info', {'delBefore': true});
            $('.folder-file-' + pass).remove();
          }
        }
      );
    },

    switch_to_ranking: function(pass, vars, redirect, elem) {
      $('.card').parent('div').removeClass('col-lg-3 col-md-4').addClass('col-lg-2 col-md-2 m-0 p-1');
      $('.card').find('.far').removeClass('fa-6x').addClass('fa-4x');
      $('.card').addClass('m-0 p-1 small');
      $('.card').find('img').removeClass('img-thumbnail');
      $('.card-header').remove();
      $('.icons').remove();
      $('.card-body').addClass('draghandler').removeClass('card-body');
      $(elem)
        .after('<span class="text-muted">Mentés után visszaállunk az eredeti nézetre.</span> <a href="#" ia-refresh>Mégsem mentem.</a>')
        .remove();
      // Kiiktatjuk az invalid logikát, hogy a preview container
      Appbase.dragOrder(false);
    }

  };
