/**
 * @file
 * JavaScript integration between Highcharts and Drupal.
 */
(function ($) {
  'use strict';

  Drupal.behaviors.chartsHighcharts = {
    attach: function (context, settings) {
      var contents = new Drupal.Charts.Contents();

      $('.charts-highchart', context).once().each(function () {
        var id = $(this).attr('id');
        $(this).highcharts(contents.getData(id));
      });
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        var highcharts_in_context = $('.charts-highchart', context).highcharts();
        if (highcharts_in_context) {
          highcharts_in_context.destroy();
        }
      }
    }

  };
}(jQuery));
