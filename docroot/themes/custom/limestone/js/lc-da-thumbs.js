/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.da_thumbs = {
    attach: function (context, settings) {
      $(' #da-thumbs > li ').each(function() {
        $(this).hoverdir();
      });
    }
  };

})(jQuery, Drupal);
