/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.da_thumbs = {
    attach: function (context, settings) {
      $('#ec-map li.map-location .map-pin').bind('touchstart click', function(event) {
        event.stopPropagation();
        event.preventDefault();
        var element = $(this).parents('li.map-location');
        var locations = $('#ec-map li.map-location');
        if (element.hasClass('active')) {
          // do nothing
        } else {
          locations.removeClass('active');
          element.addClass('active');
        }
      });
    }
  };

})(jQuery, Drupal);
