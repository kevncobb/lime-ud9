(function ($, Drupal) {
  Drupal.behaviors.addtocal = {
    attach: function (context, settings) {
      // Hide and show the menu on click.
      once('addtocal','body').forEach($body => {
        $($body).click(function (event) {
          var $target = $(event.target);
          var $menu = null;
          if ($target.hasClass('addtocal')) {
            event.preventDefault();

            var offset = $target.position();
            $menu = $('#' + $target.attr('id') + '-menu');

            $menu.css({
              'top': offset.top + $target.outerHeight(),
              'left': offset.left
            });
            $menu.toggle();
          }

          $('.addtocal-menu').not($menu).hide();
        })
      })
    }
  };

  // Hide the menu on Esc key.
  $(document).keyup(function(event) {
    if (event.keyCode === 27) {
      $('.addtocal-menu').hide();
    }
  });

})(jQuery, Drupal);
