var Imageditor = {

  init: function() {

    this.bindUIActions();

  },

  bindUIActions: function () {

    var that = this;

    this.rotation();
  },


  rotation: function(element, angle) {
    var angle = 0;
    $(document).on('click', '.rotate-image', function(e) {
      e.preventDefault();
      var image = $(this).data('target'),
        parent_div = $(image).parent('div');
      angle = (angle + 90)%360;
      $(image).removeClass('rotate90 rotate180 rotate270').addClass('rotate' + angle);

      $(parent_div).height(Math.max($(image).width(), $(image).height()));

      $(image).css({
        'max-height': $(parent_div).width(),
        'max-width': $(parent_div).width()
      });

      $('#Angle').val(angle);

    });
  },

};
