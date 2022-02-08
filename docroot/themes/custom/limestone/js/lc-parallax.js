/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.lc_parallax = {
    attach: function (context, settings) {

      $('.hero').once('lc_parallax').each(function() {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {
          $('#ios-notice').removeClass('hidden');
          $('.hero').height( $(window).height() * 0.5 | 0 );
        } else {
          $(window).resize(function(){
            var parallaxHeight = Math.max($(window).height() * 0.7, 200) | 0;
            $('.hero').height(parallaxHeight);
          }).trigger('resize');
        }
      });
    }
  };
})(jQuery, Drupal);
