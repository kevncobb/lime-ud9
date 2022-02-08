/**
 * @file
 * JavaScript integration between C3 and Drupal.
 */
(function ($) {
  'use strict';

  Drupal.behaviors.chartsC3 = {
    attach: function (context, settings) {
      var contents = new Drupal.Charts.Contents();

      $('.charts-c3', context).once().each(function () {
        var id = $(this).attr('id');
        c3.generate(contents.getData(id));
      });
    }
  };
}(jQuery));
