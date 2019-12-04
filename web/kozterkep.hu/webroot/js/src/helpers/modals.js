var s, Modals = {
  init: function () {

    s = this.settings;

    this.bindUIActions();
  },

  bindUIActions: function () {

    var that = this;

    // Link által hivatkozott div modalba nyitása
    $(document).on('click', '.in-modal', function(e) {
      e.preventDefault();
      var divToTransform = $(this).data('source');
      that.open({
        'title': $(this).data('title'),
        'content': $(divToTransform).html()
      });
    });


    /**
     * Scroll bug fix és más dolgok bepakolása nyitáskor
     */
    $('body').on('touchmove', function(e){
      if($('.scroll-disable').has($(e.target)).length) e.preventDefault();
    });
    $('body').on('shown.bs.modal', function(){

      $(this).addClass('scroll-disable');

      // Induljon be a default auto kitöltés
      Autocomplete.bindUIActions();

      // Menjen az autosize, mert egyébként nem megy
      autosize($('.modal textarea'));

      // zárjuk be a popovereket, amik beragadnak
      $('.popover').popover('hide');

      // Ezt is
      $('.tooltipster-base').hide();

      // Ha van térkép -- legyen
      if ($('.modal #map')[0]) {
        Maps.init();
      }

      // Ha van komment -- legyen
      if ($('.modal .comment-thread')[0]) {
        $('.modal .comment-thread').html(Html.loading('comments-loading'));
        Comments.build_thread(false, '.modal');
      }

    });
    $('body').on('hidden.bs.modal', function(){
      $(this).removeClass('scroll-disable');
    });

  },


  /**
   * Modal előkészítés, nyitás nélkül
   * unimodal struktúráját használva fel.
   * @param options
   */
  prepare: function(modalId, options) {
    options = _arr(options, {
      title: '',
      body: '',
      size_class: 'modal-md',
    });

    if ($('#' + modalId)[0]) {
      // Már elkészítettük a modalt
      return true;
    }

    // hozzáírjuk a body-hoz
    $('body').append('<div class="modal fade confirmModal" id="' + modalId + '" tabindex="-1" role="dialog">'
      + $('.uniModal').html()
      + '</div>');

    $('#' + modalId + ' .modal-dialog').removeClass('modal-lg modal-md modal-sm').addClass(options.size_class);
    $('#' + modalId + ' .modal-title').html(options.title);
    $('#' + modalId + ' .modal-body').html(options.body);

    return true;
  },


  /**
   * Modal nyitás
   * @param title
   * @param content
   */
  open: function(options) {
    options = _arr(options, {
      size_class: 'modal-md',
      modal_id: Helper.randId(),
      important: false,
    });

    var modal_options = [],
      this_unimodal = $('.uniModal');

    if (options['important'] == true) {
      modal_options.push({keyboard: false});
    }

    $('.uniModal').attr('id', options.modal_id);

    $('.uniModal .modal-dialog').removeClass('modal-lg modal-md modal-sm').addClass(options.size_class);

    if (typeof options.path != 'undefined') {

      Http.get(options.path, function(response) {
        if (typeof response.body != 'undefined') {
          $('.uniModal .modal-title').html(response.title);
          $('.uniModal .modal-body').html(response.body).linkify(Layout.settings.linkifyOptions);
          $('.uniModal .modal-body linkify_custom').linkify(Layout.settings.linkifyOptions);
          $('.uniModal').modal('show', modal_options);

          // Dolgok, amik benne lehetnek, és be kell röffenteni őket
          if ($('.uniModal .comment-thread')[0]) {
            Comments.build_thread();
          }
          // ..
          $('.uniModal .modal-body .remove-in-modal').remove();
          $('.uniModal .modal-body .only-in-modal').removeClass('d-none');

          // hogy okés legyen minden
          Layout.initListeners('.uniModal');
          Appbase.confirm_links();

          $('.progress').addClass('d-none');
        }
      });

    } else if(typeof options.content != 'undefined') {

      if (typeof options.title != 'undefined') {
        $('.uniModal .modal-title').html(options.title);
      }

      $('.uniModal .modal-body').html(options.content);
      $('.uniModal').modal('show', modal_options);

      // hogy okés legyen minden
      Layout.initListeners('.uniModal');
      Appbase.confirm_links();

    } else {

      _c('Nincs modal tartalom. Nem nyitunk ki semmit.', 1);

    }

    $(this_unimodal).on('shown.bs.modal', function (e) {
      if (typeof options.onShow != 'undefined') {
        options.onShow();
      }
    });

    $(this_unimodal).on('hidden.bs.modal', function (e) {
      if (typeof options.onClose != 'undefined') {
        options.onClose();
      }
      $(this_unimodal).find('.modal-body').html('');
    });
  },

  close: function(elem) {
    $(elem).modal('hide');
  },



};