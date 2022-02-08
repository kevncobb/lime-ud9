/**
 * @file
 * Contains javascript to refresh alert div contents.
 */

(function ($, Drupal) {

  Drupal.behaviors.alert = {
    attach: function (context, settings) {
      var alert_section = $(".alert-section");
      var alert_icon = $(".alert-section #alert--icon").html();

      var top_menu_first = $("#block-topmenu > ul.menu.dropdown > li:first-child");


      // when clicking on close
      $(context).find('.alert-section a.close').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $( "<li class='top-menu-alert-icon'><a href='#' title='Show Campus Alert'>" + alert_icon + "<span class='show-for-sr'>Show Alert</span></a></li>" ).detach().insertBefore( top_menu_first );
        alert_section.slideUp();
        var inHalfADay = 0.5;
        Cookies.set('alert', 'true', {
          expires: inHalfADay
        });
        // when opening from red icon
        $('#block-topmenu ul.menu.dropdown li.top-menu-alert-icon a').bind('touchstart click', function (event) {
          event.stopPropagation();
          event.preventDefault();
          //console.log('in 2nd alert function');
          alert_section.slideDown();
          $("#block-topmenu ul li.top-menu-alert-icon").remove();
          Cookies.remove('alert');
          $('.alert-section').focus();
        });
      });
      // when opening from red icon
      $('#block-topmenu ul.menu.dropdown li.top-menu-alert-icon a').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        //console.log('in 2nd alert function');
        alert_section.slideDown();
        $("#block-topmenu ul li.top-menu-alert-icon").remove();
        Cookies.remove('alert');
      });
      // when loading page without cookie
      $('.alert-section', context).once('alertReadClosed').each(function () {
        // Apply the alertReadClosed effect to the elements only once.
        var closed_for_the_day = Cookies.get('alert');
        if (closed_for_the_day != 'true') {
          alert_section.show();
        } else {
          $( "<li class='top-menu-alert-icon'><a href='#' title='Show Campus Alert'>" + alert_icon + "<span class='show-for-sr'>Show Alert</span></a></li>" ).detach().insertBefore( top_menu_first );
          // when opening from red icon
          $('#block-topmenu ul.menu.dropdown li.top-menu-alert-icon a').bind('touchstart click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            //console.log('in 2nd alert function');
            alert_section.slideDown();
            $("#block-topmenu ul li.top-menu-alert-icon").remove();
            Cookies.remove('alert');
          });
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
