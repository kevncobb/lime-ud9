/**
 * @file
 * JavaScript integration between Billboard and Drupal.
 */
(function ($) {
  'use strict';

  Drupal.behaviors.chartsBillboard = {
    attach: function (context, settings) {
      var contents = new Drupal.Charts.Contents();

      $('.charts-billboard', context).once().each(function () {
        var id = $(this).attr('id');
        bb.generate(contents.getData(id));
      });
    }
  };
}(jQuery));
