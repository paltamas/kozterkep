
var s,
  $myPos = false,
  $user = { 'id': 0 },
  App = {

    init: function () {
      Layout.getMyPos(function(my_pos) {
        $myPos = my_pos;
      });

      Layout.displayHelp();
      Layout.switchView();
      Layout.setVars();
      Layout.scrollTopInit();
      Layout.handleTabs();

      Alerts.flashOut();

      Aria.setView();
    },

  };