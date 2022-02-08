/**
 * @file addtocal.js
 */
(function ($) {
  // Don't need to re-attach this function, so we do it here instead
  // of in Drupal.behaviors.
  $(document).ready(function () {
    var $button = $('.addtocal');
    var button_id = $button.attr('id');
    var $menu = $('#' + button_id + '-menu');

    // When clicking anywhere in the window, hide menu.
    $(window).click(function () {
      if ($(window).data('addtocalVisible')) {
        $menu.hide();
        $(window).data('addtocalVisible', false);
      }
    });

    $('.addtocal').on('click',function (e) {
      e.stopPropagation();
      e.preventDefault();

      // Set position always on click to get the most accurate result.
      var offset = $button.position();
      $menu.css({
        'top': offset.top + $button.outerHeight(),
        'left': offset.left
      });
      $menu.toggle(0, function () {
        if ($(this).is(":visible")) {
          $(window).data('addtocalVisible', true);
        }
        else {
          $(window).data('addtocalVisible', false);
        }
      });
    });
  });
})(jQuery);
