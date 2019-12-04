/**
 * Gyengénlátókat támogató logikák.
 * Nagy kontrasztú felület és szövegméret manipuláció
 */
var s,
  Aria = {

    settings: {
    },

    init: function () {
      s = this.settings;

      this.bindUIActions();
    },


    bindUIActions: function () {

      var that = this;

      /**
       * Kontrasztos nézet ki-bekapcsolás
       */
      $('.ariaSwitch').on('click', function (e) {
        if ($('body').hasClass('aria')) {
          that.ariaOff();
        } else {
          that.ariaOn();
        }
      });


      /**
       * Szövegméret piszkálása
       */
      var textSize = 1;
      $('.textSize').on('click', function (e) {
        var rate = eval($(this).data('size'));
        textSize = textSize + (1 * rate / 100);
        that.setText(textSize);
      });
      /**
       * Szövegméret visszaállítása
       */
      $('.textSizeReset').on('click', function (e) {
        that.setText(false);
      });

    },


    /**
     * Szövegméret beállítása;
     * ha nem szám a textSize, akkor visszaállunk, de kell egy reload,
     * hogy minden TÉNYLEG visszaálljon a meghekkelt CSS állapotból
     */
    setText: function(textSize) {
      if (textSize > 0) {
        $('main *').css('font-size', textSize + 'em');
        Store.set('testSize', textSize);
      } else {
        Store.remove('testSize');
        location.reload();
      }
    },

    /**
     * Megjelenés inicializáló
     */
    setView: function() {
      var that = this;

      /**
       * Kontrasztos felület inicializálása
       */
      if (Store.get('aria') == 1) {
        that.ariaOn();
      } else {
        $('.aria-show').addClass('d-none');
      }

      /**
       * Egyedi betűméret inicializálása
       */
      if (Store.get('testSize') > 0) {
        that.setText(Store.get('testSize'));
      }
    },

    /**
     * Kontrasztos felület bekapcsolása
     */
    ariaOn: function() {
      $('body').addClass('aria');
      $('.aria-show').removeClass('d-none');
      $('.aria-hide').addClass('d-none');
      Store.set('aria', 1);
    },

    /**
     * Kontrasztos felület kikapcsolása
     */
    ariaOff: function() {
      $('body').removeClass('aria');
      $('.aria-show').addClass('d-none');
      $('.aria-hide').removeClass('d-none');
      Store.set('aria', 0);
    },
    
  };
