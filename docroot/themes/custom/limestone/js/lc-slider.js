/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.lc_slider = {
    attach: function (context, settings) {

      $('.lc-slider').once('lc_slider').each(function() {
        var titles = $('.lc-slide-next ul > li').map(function(i, el) {
          return $(el).text();
        }).get();

        $( ".slideshow-controls a.next-headline" ).each(function(i) {
          if (i == (titles.length - 1) ) {
            $(this).append(titles[0]);
          } else {
            $(this).append(titles[i+1]);
          }
        });
      });


      // Front page Tabs function to activate the 3rd tab on page load. Need to move to new behavior.
      //$("ul.tabs li:nth-child(3) a").click();

    }
  };

  /*
  Drupal.behaviors.front_tabs = {
    attach: function (context, settings) {
      $('#vbp-tab-219').once('front_tabs').each(function() {
        // Front page Tabs function to activate the 3rd tab on page load. Need to move to new behavior.
        $("ul.tabs li:nth-child(3) a").click();
      });
    }
  };
  */

})(jQuery, Drupal);
