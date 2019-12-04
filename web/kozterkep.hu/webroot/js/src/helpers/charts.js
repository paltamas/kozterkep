var s,
  Charts = {

    settings: {

    },

    init: function () {

      var that = this;

      this.load();

    },


    load: function(load) {

      var that = this;

      if ($('.chart')[0]) {
        $('.chart').each(function(key, elem) {
          that.draw(elem);
        });
      }
    },


    draw: function(elem) {

      var that = this,
        chartDiv = $(elem);

      var myChart = new Chart(chartDiv, {
        type: chartDiv.attr('ia-chart-type'),
        data: {
          labels: eval(chartDiv.attr('ia-chart-labels')),
          datasets: [{
            //label: '', // multi datasetnél kell majd
            data: eval(chartDiv.attr('ia-chart-data')),
            borderWidth: chartDiv.attr('ia-chart-type') == 'bar' ? 1 : 2,
            backgroundColor: chartDiv.attr('ia-chart-type') == 'bar' ? 'rgba(254, 147, 76, 0.5)' : 'rgba(254, 147, 76, 0.2)',
            borderColor: '#c95b12',
          }]
        },
        options: {
          legend: { display: false },
          title: {
            display: chartDiv.attr('ia-chart-title') !== undefined ? true : false,
            text: chartDiv.attr('ia-chart-title')
          },
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });

    },
  };